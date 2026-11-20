<?php
class Reputation {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserReputation($userId) {
        $query = "SELECT reputation_score, penalty_until FROM users WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([(int)$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $score = $row ? (int)$row['reputation_score'] : 100;
        $penaltyUntil = $row['penalty_until'] ?? null;

        $isPenalized = false;
        if ($penaltyUntil && strtotime($penaltyUntil) > time()) {
            $isPenalized = true;
        }

        // Đánh giá cấp độ uy tín
        if ($score >= 90) {
            $tier = 'Gương Mẫu';
            $tierColor = 'emerald';
            $tierBadge = '🌟 Vận Động Viên Gương Mẫu';
        } elseif ($score >= 70) {
            $tier = 'Tốt';
            $tierColor = 'blue';
            $tierBadge = '🟢 Uy Tín Tốt';
        } elseif ($score >= 50) {
            $tier = 'Cảnh Báo';
            $tierColor = 'amber';
            $tierBadge = '⚠️ Cảnh Báo Vi Phạm';
        } else {
            $tier = 'Bị Khóa';
            $tierColor = 'red';
            $tierBadge = '🚫 Bị Khóa Tìm Trận';
        }

        $canMatchmake = true;
        $lockReason = null;

        if ($isPenalized) {
            $canMatchmake = false;
            $lockReason = "Tài khoản đang bị tạm khóa tìm trận do bùng kèo đến ngày " . date('d/m/Y H:i', strtotime($penaltyUntil));
        } elseif ($score < 50) {
            $canMatchmake = false;
            $lockReason = "Điểm uy tín của bạn đang quá thấp ({$score}/100, yêu cầu tối thiểu 50). Hãy liên hệ quản trị viên hoặc hoàn thành các trận tự đặt đơn lẻ để phục hồi uy tín.";
        }

        return [
            'score' => $score,
            'penalty_until' => $penaltyUntil,
            'is_penalized' => $isPenalized,
            'tier' => $tier,
            'tier_color' => $tierColor,
            'tier_badge' => $tierBadge,
            'can_matchmake' => $canMatchmake,
            'lock_reason' => $lockReason
        ];
    }

    public function changeScore($userId, $delta, $reason, $bookingId = null, $ticketId = null) {
        $rep = $this->getUserReputation($userId);
        $currentScore = $rep['score'];
        $newScore = min(100, max(0, $currentScore + (int)$delta));

        // Cập nhật điểm uy tín
        $updateSql = "UPDATE users SET reputation_score = :score";
        $params = [':score' => $newScore, ':user_id' => (int)$userId];

        // Nếu bị trừ nặng (>= 25 điểm, ví dụ No-Show), áp đặt án phạt khóa tìm trận 3 ngày
        if ((int)$delta <= -25) {
            $penaltyTime = date('Y-m-d H:i:s', strtotime('+3 days'));
            $updateSql .= ", penalty_until = :penalty";
            $params[':penalty'] = $penaltyTime;

            // Đồng thời phạt trừ -50 điểm Elo mùa giải
            require_once __DIR__ . '/Ranking.php';
            $ranking = new Ranking($this->conn);
            $season = Ranking::getCurrentSeason();
            $currentElo = $ranking->getSeasonPoints($userId, $season);
            $newElo = max(0, $currentElo - 50);

            $eloSql = "INSERT INTO user_scores (user_id, season, points, highest_points) VALUES (?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE points = ?";
            $this->conn->prepare($eloSql)->execute([$userId, $season, $newElo, $newElo, $newElo]);
        }

        $updateSql .= " WHERE id = :user_id";
        $this->conn->prepare($updateSql)->execute($params);

        // Ghi nhật ký vào bảng reputation_logs
        $logSql = "INSERT INTO reputation_logs (user_id, booking_id, ticket_id, change_amount, current_score, reason)
                   VALUES (:user_id, :booking_id, :ticket_id, :amount, :score, :reason)";
        $this->conn->prepare($logSql)->execute([
            ':user_id' => $userId,
            ':booking_id' => $bookingId,
            ':ticket_id' => $ticketId,
            ':amount' => $delta,
            ':score' => $newScore,
            ':reason' => $reason
        ]);

        // Gửi tin nhắn thông báo nội bộ
        $sign = $delta > 0 ? "+{$delta}" : "{$delta}";
        $msg = "[BIEN DONG DIEM UY TIN FAIR-PLAY] Diem uy tin cua ban vua thay doi {$sign} diem. Ly do: {$reason}. Diem hien tai: {$newScore}/100.";
        
        $adminStmt = $this->conn->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
        $adminId = $adminStmt ? $adminStmt->fetchColumn() : null;
        $senderId = $adminId ? (int)$adminId : (int)$userId;

        $msgSql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
        $this->conn->prepare($msgSql)->execute([$senderId, $userId, $msg]);

        return $newScore;
    }

    public function getHistory($userId, $limit = 15) {
        $query = "SELECT * FROM reputation_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reportNoShow($reporterId, $violatorId, $bookingId, $detail = '') {
        $reporterId = (int)$reporterId;
        $violatorId = (int)$violatorId;
        $bookingId = (int)$bookingId;

        // 1. Kiểm tra booking hợp lệ kèm thông tin chủ sân
        $q = "SELECT b.*, p.owner_id as pitch_owner_id FROM bookings b LEFT JOIN pitches p ON b.pitch_id = p.id WHERE b.id = ?";
        $s = $this->conn->prepare($q);
        $s->execute([$bookingId]);
        $booking = $s->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return ['success' => false, 'message' => 'Đơn đặt sân không tồn tại.'];
        }

        // 2. Kiểm tra phân quyền chặt chẽ (Chống IDOR): Người báo cáo phải là thành viên trong trận hoặc chủ sân
        $isPitchOwner = (!empty($booking['pitch_owner_id']) && $booking['pitch_owner_id'] == $reporterId);
        $isAdmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

        // Kiểm tra xem reporterId và violatorId có là đối thủ trong matchmaking không
        $matchStmt = $this->conn->prepare("SELECT * FROM matchmaking WHERE (user_id = ? AND opponent_id = ?) OR (user_id = ? AND opponent_id = ?)");
        $matchStmt->execute([$reporterId, $violatorId, $violatorId, $reporterId]);
        $isMatchOpponent = ($matchStmt->rowCount() > 0);

        $isBookingParty = ($booking['user_id'] == $reporterId || $booking['user_id'] == $violatorId);

        if (!$isPitchOwner && !$isAdmin && !$isMatchOpponent && !$isBookingParty) {
            return ['success' => false, 'message' => 'Lỗi phân quyền: Bạn không có quyền báo cáo đơn đặt sân này.'];
        }

        $reason = "Bỏ trận không đến sân (No-Show / Bùng kèo) tại đơn đặt sân #{$bookingId}";
        if (!empty($detail)) {
            $reason .= " - Chi tiết: " . htmlspecialchars(strip_tags($detail));
        }

        // Nếu là Chủ sân hoặc Admin trực tiếp xử lý: thực hiện trừ điểm ngay
        if ($isPitchOwner || $isAdmin) {
            $newScore = $this->changeScore($violatorId, -30, $reason, $bookingId);
            if ($booking['status'] != 'completed') {
                $this->conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?")->execute([$bookingId]);
            }
            return [
                'success' => true,
                'message' => 'Đã xác nhận bùng kèo và xử phạt đối thủ thành công.',
                'new_score' => $newScore
            ];
        }

        // Nếu là người chơi báo cáo: tạo yêu cầu chờ chủ sân duyệt, không tự phạt ngay
        $upMatch = "UPDATE matchmaking 
                    SET noshow_reported_by = ?, noshow_target_user_id = ?, noshow_status = 'pending', noshow_reason = ? 
                    WHERE (user_id = ? AND opponent_id = ?) OR (user_id = ? AND opponent_id = ?)";
        $this->conn->prepare($upMatch)->execute([
            $reporterId,
            $violatorId,
            $reason,
            $reporterId,
            $violatorId,
            $violatorId,
            $reporterId
        ]);

        // Đồng bộ vé match_tickets nếu có
        $this->conn->prepare("UPDATE match_tickets SET noshow_status = 'pending', noshow_reason = ? WHERE booking_id = ?")
                   ->execute([$reason, $bookingId]);

        // Gửi thông báo tới Chủ sân
        if (!empty($booking['pitch_owner_id'])) {
            require_once __DIR__ . '/Notification.php';
            $notif = new Notification($this->conn);
            $notif->create(
                (int)$booking['pitch_owner_id'],
                "[YEU CAU XAC NHAN BUNG KEO] Co thu bao doi thu vang mat",
                "Co thu bao doi thu vang mat tai don dat san #{$bookingId}. Ly do: {$detail}. Vui long kiem tra va xac nhan.",
                "index.php?action=owner_dashboard"
            );
        }

        return [
            'success' => true,
            'status' => 'pending_owner_approval',
            'message' => 'Đã gửi báo cáo bùng kèo tới Chủ Sân! Chủ sân sẽ kiểm tra thực tế và xác nhận xử phạt.'
        ];
    }
}
?>
