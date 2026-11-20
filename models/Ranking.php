<?php
class Ranking {
    const DEFAULT_ELO = 100;
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public static function getCurrentSeason() {
        $year = date('Y');
        $half = (date('n') <= 6) ? 1 : 2;
        return $year . '-H' . $half; // Example: 2026-H1
    }

    public function getPreviousSeason() {
        $year = date('Y');
        $half = (date('n') <= 6) ? 1 : 2;
        if($half == 1) {
            $half = 2;
            $year--;
        } else {
            $half = 1;
        }
        return $year . '-H' . $half;
    }

    public function processMatchResult($host_id, $guest_id, $result) {
        $season = self::getCurrentSeason();
        
        // Chuẩn hóa kết quả theo chuẩn Elo hệ số K=32
        $hostRating = (int)$this->getSeasonPoints($host_id, $season);
        $guestRating = $guest_id ? (int)$this->getSeasonPoints($guest_id, $season) : $hostRating;
        if ($hostRating <= 0) $hostRating = self::DEFAULT_ELO;
        if ($guestRating <= 0) $guestRating = self::DEFAULT_ELO;

        // Tính xác suất thắng kỳ vọng theo công thức Elo
        $expectedHost = 1 / (1 + pow(10, ($guestRating - $hostRating) / 400));
        $expectedGuest = 1 / (1 + pow(10, ($hostRating - $guestRating) / 400));

        $K = 32; // Hệ số K-factor chuẩn
        if ($result == 'host_won' || $result == 'host_win') {
            $actualHost = 1.0;
            $actualGuest = 0.0;
        } elseif ($result == 'guest_won' || $result == 'guest_win') {
            $actualHost = 0.0;
            $actualGuest = 1.0;
        } else { // draw
            $actualHost = 0.5;
            $actualGuest = 0.5;
        }

        $host_points = (int)round($K * ($actualHost - $expectedHost));
        $guest_points = (int)round($K * ($actualGuest - $expectedGuest));

        // Đảm bảo mức tối thiểu khi thắng/thua để giữ động lực thi đấu
        if ($actualHost == 1.0 && $host_points < 10) $host_points = 15;
        if ($actualGuest == 1.0 && $guest_points < 10) $guest_points = 15;

        $this->updatePoints($host_id, $season, $host_points);
        if ($guest_id) {
            $this->updatePoints($guest_id, $season, $guest_points);
        }

        // Gui thong bao chinh xac so diem cong/tru Elo den 2 co thu
        try {
            $notifSql = "INSERT INTO notifications (user_id, title, content, link) VALUES (?, ?, ?, 'index.php?action=smart_match')";
            $notifStmt = $this->conn->prepare($notifSql);

            $hostSign = $host_points >= 0 ? "+{$host_points}" : "{$host_points}";
            $hostTitle = ($actualHost == 1.0) ? "Chiến Thắng Trận Đấu ({$hostSign} Elo)" : (($actualHost == 0.0) ? "Kết Thúc Trận Đấu ({$hostSign} Elo)" : "Trận Đấu Hòa ({$hostSign} Elo)");
            $hostContent = "Trọng tài / Chủ sân đã xác nhận kết quả trận đấu. Điểm Elo của bạn đã thay đổi: {$hostSign} Elo.";
            $notifStmt->execute([$host_id, $hostTitle, $hostContent]);

            if ($guest_id) {
                $guestSign = $guest_points >= 0 ? "+{$guest_points}" : "{$guest_points}";
                $guestTitle = ($actualGuest == 1.0) ? "Chiến Thắng Trận Đấu ({$guestSign} Elo)" : (($actualGuest == 0.0) ? "Kết Thúc Trận Đấu ({$guestSign} Elo)" : "Trận Đấu Hòa ({$guestSign} Elo)");
                $guestContent = "Trọng tài / Chủ sân đã xác nhận kết quả trận đấu. Điểm Elo của bạn đã thay đổi: {$guestSign} Elo.";
                $notifStmt->execute([$guest_id, $guestTitle, $guestContent]);
            }
        } catch (Exception $e) {
            // Khong lam gian doan luong neu loi ghi thong bao
        }
    }

    public function updateScore($host_id, $guest_id, $result) {
        return $this->processMatchResult($host_id, $guest_id, $result);
    }

    public function penalizeNoShow($user_id, $delta = -50) {
        $season = self::getCurrentSeason();
        $this->updatePoints($user_id, $season, $delta);
    }
    
    private function updatePoints($user_id, $season, $delta) {
        $current = $this->getSeasonPoints($user_id, $season);
        $new_points = max(0, $current + $delta);
        
        $query = "INSERT INTO user_scores (user_id, season, points, highest_points) VALUES (?, ?, ?, ?) 
                  ON DUPLICATE KEY UPDATE points = ?, highest_points = GREATEST(highest_points, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $season, $new_points, $new_points, $new_points, $new_points]);
    }

    public function getSeasonPoints($user_id, $season) {
        $query = "SELECT points FROM user_scores WHERE user_id = ? AND season = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $season]);
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return (int)$row['points'];
        }
        return self::DEFAULT_ELO;
    }

    public function getSeasonHighestPoints($user_id, $season) {
        $query = "SELECT highest_points, points FROM user_scores WHERE user_id = ? AND season = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $season]);
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return max((int)$row['highest_points'], (int)$row['points']);
        }
        return self::DEFAULT_ELO;
    }

    public function calculateDiscount($points) {
        if ($points >= 1000) return 20; // Kim Cương
        if ($points >= 600) return 10;  // Vàng
        if ($points >= 300) return 5;   // Bạc
        return 0;
    }

    public function getRankName($points) {
        if ($points >= 1000) return 'Kim Cương';
        if ($points >= 600) return 'Vàng';
        if ($points >= 300) return 'Bạc';
        return 'Đồng';
    }

    public function getActiveDiscount($user_id) {
        $currentPoints = $this->getSeasonHighestPoints($user_id, self::getCurrentSeason());
        $prevPoints = $this->getSeasonHighestPoints($user_id, $this->getPreviousSeason());
        return $this->calculateDiscount(max($currentPoints, $prevPoints));
    }
}
?>
