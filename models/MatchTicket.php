<?php
class MatchTicket {
    private $conn;
    private $table_name = "match_tickets";

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureColumnsExist();
    }

    private function ensureColumnsExist() {
        try {
            $check = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name . " LIKE 'has_pitch'");
            if ($check && $check->rowCount() == 0) {
                $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN `has_pitch` TINYINT(1) DEFAULT 0 AFTER `district`, ADD COLUMN `specified_pitch_id` INT(11) DEFAULT NULL AFTER `has_pitch` ");
            }
            $checkGps = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name . " LIKE 'user_lat'");
            if ($checkGps && $checkGps->rowCount() == 0) {
                $this->conn->exec("ALTER TABLE " . $this->table_name . " 
                    ADD COLUMN `user_lat` DECIMAL(10,8) DEFAULT NULL AFTER `district`,
                    ADD COLUMN `user_lng` DECIMAL(11,8) DEFAULT NULL AFTER `user_lat`,
                    ADD COLUMN `radius_km` DECIMAL(5,2) DEFAULT '10.00' AFTER `user_lng`,
                    ADD COLUMN `location_name` VARCHAR(255) DEFAULT '' AFTER `radius_km`
                ");
            }
            $checkUserGps = $this->conn->query("SHOW COLUMNS FROM users LIKE 'latitude'");
            if ($checkUserGps && $checkUserGps->rowCount() == 0) {
                $this->conn->exec("ALTER TABLE users 
                    ADD COLUMN `latitude` DECIMAL(10,8) DEFAULT NULL AFTER `district`,
                    ADD COLUMN `longitude` DECIMAL(11,8) DEFAULT NULL AFTER `latitude`,
                    ADD COLUMN `location_address` VARCHAR(255) DEFAULT '' AFTER `longitude`
                ");
            }
        } catch (Exception $e) {
            // Ignore if columns exist
        }
    }

    public static function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2) {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return 999.0;
        }
        $earthRadius = 6371; // km
        $dLat = deg2rad((float)$lat2 - (float)$lat1);
        $dLon = deg2rad((float)$lon2 - (float)$lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function createTicket($data) {
        $user_id = (int)$data['user_id'];
        $play_date = $data['play_date'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $format = $data['format'] ?? 'Đánh Đôi';
        $district = $data['district'] ?? 'Bình Thạnh';
        $user_lat = !empty($data['user_lat']) ? floatval($data['user_lat']) : null;
        $user_lng = !empty($data['user_lng']) ? floatval($data['user_lng']) : null;
        $radius_km = !empty($data['radius_km']) ? floatval($data['radius_km']) : 10.00;
        $location_name = !empty($data['location_name']) ? htmlspecialchars(strip_tags($data['location_name'])) : '';
        $has_pitch = !empty($data['has_pitch']) ? 1 : 0;
        $specified_pitch_id = !empty($data['specified_pitch_id']) ? (int)$data['specified_pitch_id'] : null;
        $note = $data['note'] ?? '';

        // 1. Kiểm tra Điểm Uy Tín (Fair-Play Check)
        require_once __DIR__ . '/Reputation.php';
        $repModel = new Reputation($this->conn);
        $rep = $repModel->getUserReputation($user_id);
        if (!$rep['can_matchmake']) {
            return [
                'success' => false,
                'message' => $rep['lock_reason']
            ];
        }

        // 2. Tự động lấy điểm Elo & Bậc Rank của User từ hệ thống Ranking
        require_once __DIR__ . '/Ranking.php';
        $ranking = new Ranking($this->conn);
        $season = Ranking::getCurrentSeason();
        $elo = $ranking->getSeasonPoints($user_id, $season);
        if ($elo <= 0) {
            $elo = 100; // Mốc điểm cơ bản tân thủ
        }
        $rankName = $ranking->getRankName($elo);

        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, play_date, start_time, end_time, format, district, user_lat, user_lng, radius_km, location_name, has_pitch, specified_pitch_id, elo_points, rank_name, note, status)
                  VALUES (:user_id, :play_date, :start_time, :end_time, :format, :district, :user_lat, :user_lng, :radius_km, :location_name, :has_pitch, :specified_pitch_id, :elo_points, :rank_name, :note, 'waiting')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':play_date', $play_date);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->bindParam(':format', $format);
        $stmt->bindParam(':district', $district);
        $stmt->bindParam(':user_lat', $user_lat);
        $stmt->bindParam(':user_lng', $user_lng);
        $stmt->bindParam(':radius_km', $radius_km);
        $stmt->bindParam(':location_name', $location_name);
        $stmt->bindParam(':has_pitch', $has_pitch);
        $stmt->bindParam(':specified_pitch_id', $specified_pitch_id);
        $stmt->bindParam(':elo_points', $elo);
        $stmt->bindParam(':rank_name', $rankName);
        $stmt->bindParam(':note', $note);

        if ($stmt->execute()) {
            $ticketId = $this->conn->lastInsertId();
            // Kích hoạt ngay thuật toán quét tìm đối thủ trong hàng đợi
            $matched = $this->autoMatch($ticketId);
            return [
                'success' => true,
                'ticket_id' => $ticketId,
                'matched' => $matched
            ];
        }
        return ['success' => false];
    }

    public function autoMatch($ticketId) {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket || $ticket['status'] != 'waiting') {
            return false;
        }

        // 1. Quét tìm các Ticket khác đang ở trạng thái 'waiting'
        // Tiêu chí bắt buộc: Khác user_id, cùng play_date, có giao thoa khung giờ
        $query = "SELECT t.*, u.name as user_name, u.avatar_url
                  FROM " . $this->table_name . " t
                  INNER JOIN users u ON t.user_id = u.id
                  WHERE t.status = 'waiting' 
                    AND t.user_id != :user_id
                    AND t.play_date = :play_date
                    AND (t.start_time < :end_time AND t.end_time > :start_time)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $ticket['user_id']);
        $stmt->bindParam(':play_date', $ticket['play_date']);
        $stmt->bindParam(':start_time', $ticket['start_time']);
        $stmt->bindParam(':end_time', $ticket['end_time']);
        $stmt->execute();
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($candidates)) {
            return false;
        }

        // 2. Chấm điểm tương đồng (Scoring) cho từng ứng viên theo 3 yếu tố:
        // - Chênh lệch Elo (|ΔElo| càng nhỏ điểm càng cao)
        // - Vị trí địa lý (Cùng quận > quận lân cận)
        // - Thể thức thi đấu (Trùng thể thức)
        $bestCandidate = null;
        $bestScore = -1;

        foreach ($candidates as $cand) {
            // Điểm Elo: biên độ lệch
            $diffElo = abs($ticket['elo_points'] - $cand['elo_points']);
            // Cùng bậc rank thì độ lệch <= 300
            $eloScore = max(0, 100 - ($diffElo * 0.2)); // diff 0 => 100, diff 100 => 80, diff 300 => 40

            // Điểm khu vực / Bán kính GPS
            $districtScore = 50;
            if (!empty($ticket['user_lat']) && !empty($cand['user_lat'])) {
                $distKm = self::calculateHaversineDistance($ticket['user_lat'], $ticket['user_lng'], $cand['user_lat'], $cand['user_lng']);
                $maxRadius = max((float)($ticket['radius_km'] ?? 10), (float)($cand['radius_km'] ?? 10));
                if ($distKm <= $maxRadius) {
                    $districtScore = max(60, (int)round(100 - ($distKm * 2)));
                } else {
                    $districtScore = max(0, (int)round(60 - (($distKm - $maxRadius) * 5)));
                }
            } elseif (empty($ticket['district']) || $ticket['district'] === 'Tất cả' || empty($cand['district']) || $cand['district'] === 'Tất cả') {
                $districtScore = 100;
            } elseif (strcasecmp(trim($ticket['district']), trim($cand['district'])) === 0) {
                $districtScore = 100;
            } elseif (in_array(trim($cand['district']), ['Bình Thạnh', 'Gò Vấp', 'Tân Bình', 'Phú Nhuận'])) {
                $districtScore = 80;
            }

            // Điểm thể thức
            $formatScore = (strcasecmp($ticket['format'], $cand['format']) === 0) ? 100 : 70;

            // Tổng điểm đa mục tiêu
            $totalScore = (0.5 * $eloScore) + (0.3 * $districtScore) + (0.2 * $formatScore);

            if ($totalScore > $bestScore) {
                $bestScore = $totalScore;
                $bestCandidate = $cand;
            }
        }

        if (!$bestCandidate) {
            return false;
        }

        // 3. Tính khung giờ thi đấu chung
        $matchStart = max($ticket['start_time'], $bestCandidate['start_time']);
        $matchEnd = min($ticket['end_time'], $bestCandidate['end_time']);
        
        $durationHours = (strtotime($matchEnd) - strtotime($matchStart)) / 3600;
        if ($durationHours < 1) {
            // Nếu giao thoa dưới 1 giờ thì lấy khung giờ của người đăng trước (Candidate)
            $matchStart = $bestCandidate['start_time'];
            $matchEnd = $bestCandidate['end_time'];
        }

        // 4. Tìm sân cầu lông thích hợp ở khu vực của 2 bên còn trống
        require_once __DIR__ . '/Booking.php';
        $bookingModel = new Booking($this->conn);

        $selectedPitch = null;

        // Ưu tiên 1: Kiểm tra xem người đặt hoặc đối thủ có chỉ định trước sân không
        $specifiedPitchId = !empty($ticket['specified_pitch_id']) ? $ticket['specified_pitch_id'] : (!empty($bestCandidate['specified_pitch_id']) ? $bestCandidate['specified_pitch_id'] : null);

        if ($specifiedPitchId) {
            $pStmt = $this->conn->prepare("SELECT * FROM pitches WHERE id = ? AND status = 'active' LIMIT 1");
            $pStmt->execute([$specifiedPitchId]);
            $sp = $pStmt->fetch(PDO::FETCH_ASSOC);
            if ($sp) {
                $overlapping = $bookingModel->getConfirmedOverlappingCount($sp['id'], $ticket['play_date'], $matchStart, $matchEnd);
                if ($overlapping < $sp['quantity']) {
                    $selectedPitch = $sp;
                }
            }
        }

        // Ưu tiên 2: Nếu chưa chỉ định sân hoặc sân chỉ định hết chỗ, tự chọn sân trống theo GPS / khu vực
        if (!$selectedPitch) {
            $pitchQuery = "SELECT * FROM pitches WHERE status = 'active'";
            $pStmt = $this->conn->prepare($pitchQuery);
            $pStmt->execute();
            $pitches = $pStmt->fetchAll(PDO::FETCH_ASSOC);

            $hasTicketGps = (!empty($ticket['user_lat']) && !empty($ticket['user_lng']));
            $hasCandGps = (!empty($bestCandidate['user_lat']) && !empty($bestCandidate['user_lng']));

            usort($pitches, function($p1, $p2) use ($ticket, $bestCandidate, $hasTicketGps, $hasCandGps) {
                if ($hasTicketGps || $hasCandGps) {
                    $d1_t = $hasTicketGps ? self::calculateHaversineDistance($ticket['user_lat'], $ticket['user_lng'], $p1['latitude'], $p1['longitude']) : 0;
                    $d1_c = $hasCandGps ? self::calculateHaversineDistance($bestCandidate['user_lat'], $bestCandidate['user_lng'], $p1['latitude'], $p1['longitude']) : 0;
                    $sum1 = $d1_t + $d1_c;

                    $d2_t = $hasTicketGps ? self::calculateHaversineDistance($ticket['user_lat'], $ticket['user_lng'], $p2['latitude'], $p2['longitude']) : 0;
                    $d2_c = $hasCandGps ? self::calculateHaversineDistance($bestCandidate['user_lat'], $bestCandidate['user_lng'], $p2['latitude'], $p2['longitude']) : 0;
                    $sum2 = $d2_t + $d2_c;

                    return $sum1 <=> $sum2;
                } else {
                    $match1 = (($p1['district'] == $ticket['district']) ? 2 : 0) + (($p1['district'] == $bestCandidate['district']) ? 1 : 0);
                    $match2 = (($p2['district'] == $ticket['district']) ? 2 : 0) + (($p2['district'] == $bestCandidate['district']) ? 1 : 0);
                    return $match2 <=> $match1;
                }
            });

            foreach ($pitches as $p) {
                $overlapping = $bookingModel->getConfirmedOverlappingCount($p['id'], $ticket['play_date'], $matchStart, $matchEnd);
                if ($overlapping < $p['quantity']) {
                    $selectedPitch = $p;
                    break;
                }
            }
        }

        if (!$selectedPitch) {
            // Không còn sân nào trống trong khung giờ này
            return false;
        }

        // 5. Khớp lệnh thành công: Cập nhật cả 2 ticket sang trạng thái 'matched'
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 hours'));

        // Cập nhật ticket hiện tại
        $up1 = "UPDATE " . $this->table_name . " 
                SET status = 'matched', matched_ticket_id = :m_id, matched_pitch_id = :p_id, 
                    start_time = :s_time, end_time = :e_time, expires_at = :exp,
                    user_confirmed = 0, matched_user_confirmed = 0
                WHERE id = :id";
        $s1 = $this->conn->prepare($up1);
        $s1->execute([
            ':m_id' => $bestCandidate['id'],
            ':p_id' => $selectedPitch['id'],
            ':s_time' => $matchStart,
            ':e_time' => $matchEnd,
            ':exp' => $expiresAt,
            ':id' => $ticket['id']
        ]);

        // Cập nhật ticket của đối thủ
        $up2 = "UPDATE " . $this->table_name . " 
                SET status = 'matched', matched_ticket_id = :m_id, matched_pitch_id = :p_id, 
                    start_time = :s_time, end_time = :e_time, expires_at = :exp,
                    user_confirmed = 0, matched_user_confirmed = 0
                WHERE id = :id";
        $s2 = $this->conn->prepare($up2);
        $s2->execute([
            ':m_id' => $ticket['id'],
            ':p_id' => $selectedPitch['id'],
            ':s_time' => $matchStart,
            ':e_time' => $matchEnd,
            ':exp' => $expiresAt,
            ':id' => $bestCandidate['id']
        ]);

        // 6. Gửi tin nhắn thông báo nội bộ cho cả 2 người
        $msgContent = "🏸 [THÔNG BÁO TÌM TRẬN THÀNH CÔNG] Hệ thống đã tìm thấy đối thủ tương đồng Elo cho bạn vào ngày " . date('d/m', strtotime($ticket['play_date'])) . " lúc " . substr($matchStart, 0, 5) . " tại " . $selectedPitch['name'] . ". Vui lòng vào trang 'Ghép Trận Thông Minh' để XÁC NHẬN KÈO!";
        
        $msgSql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (:sender, :receiver, :content)";
        $mStmt = $this->conn->prepare($msgSql);
        
        // Gửi cho ticket user
        $mStmt->execute([':sender' => $bestCandidate['user_id'], ':receiver' => $ticket['user_id'], ':content' => $msgContent]);
        // Gửi cho candidate user
        $mStmt->execute([':sender' => $ticket['user_id'], ':receiver' => $bestCandidate['user_id'], ':content' => $msgContent]);

        return true;
    }

    public function getTicketById($id) {
        $query = "SELECT t.*, u.name as user_name, u.avatar_url, u.phone as user_phone,
                         p.name as pitch_name, p.location as pitch_location, p.price_per_hour, p.image_url as pitch_image, p.owner_id as pitch_owner_id,
                         cand.id as opp_ticket_id, cand.user_id as opp_user_id, cand.elo_points as opp_elo, cand.rank_name as opp_rank,
                         cand.user_confirmed as opp_confirmed, cand.user_lat as opp_lat, cand.user_lng as opp_lng,
                         cand.payment_method as opp_payment_method, cand.payment_status as opp_payment_status, cand.split_price as opp_split_price,
                         cand.checkin_code as opp_checkin_code, cand.is_checked_in as opp_is_checked_in, cand.checked_in_at as opp_checked_in_at, cand.noshow_status as opp_noshow_status,
                         opp_u.name as opp_name, opp_u.avatar_url as opp_avatar, opp_u.phone as opp_phone,
                         prop_p.name as proposed_pitch_name, prop_p.location as proposed_pitch_location, prop_p.price_per_hour as proposed_pitch_price,
                         prop_u.name as proposed_by_user_name
                  FROM " . $this->table_name . " t
                  INNER JOIN users u ON t.user_id = u.id
                  LEFT JOIN pitches p ON t.matched_pitch_id = p.id
                  LEFT JOIN " . $this->table_name . " cand ON t.matched_ticket_id = cand.id
                  LEFT JOIN users opp_u ON cand.user_id = opp_u.id
                  LEFT JOIN pitches prop_p ON t.proposed_pitch_id = prop_p.id
                  LEFT JOIN users prop_u ON t.proposed_by_user_id = prop_u.id
                  WHERE t.id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserTickets($userId) {
        $query = "SELECT t.*, 
                         p.name as pitch_name, p.location as pitch_location, p.price_per_hour, p.image_url as pitch_image, p.owner_id as pitch_owner_id,
                         cand.id as opp_ticket_id, cand.user_id as opp_user_id, cand.elo_points as opp_elo, cand.rank_name as opp_rank,
                         cand.user_confirmed as opp_confirmed, cand.user_lat as opp_lat, cand.user_lng as opp_lng,
                         cand.payment_method as opp_payment_method, cand.payment_status as opp_payment_status, cand.split_price as opp_split_price,
                         cand.checkin_code as opp_checkin_code, cand.is_checked_in as opp_is_checked_in, cand.checked_in_at as opp_checked_in_at, cand.noshow_status as opp_noshow_status,
                         opp_u.name as opp_name, opp_u.avatar_url as opp_avatar, opp_u.phone as opp_phone,
                         prop_p.name as proposed_pitch_name, prop_p.location as proposed_pitch_location, prop_p.price_per_hour as proposed_pitch_price,
                         prop_u.name as proposed_by_user_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN pitches p ON t.matched_pitch_id = p.id
                  LEFT JOIN " . $this->table_name . " cand ON t.matched_ticket_id = cand.id
                  LEFT JOIN users opp_u ON cand.user_id = opp_u.id
                  LEFT JOIN pitches prop_p ON t.proposed_pitch_id = prop_p.id
                  LEFT JOIN users prop_u ON t.proposed_by_user_id = prop_u.id
                  WHERE t.user_id = ?
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([(int)$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function confirmMatch($ticketId, $userId, $paymentMethod = 'cash', $paymentStatus = 'unpaid') {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket || $ticket['user_id'] != $userId || $ticket['status'] != 'matched') {
            return ['success' => false, 'message' => 'Lệnh tìm trận không hợp lệ.'];
        }

        // Tính toán chi phí 50% của người này (áp dụng giảm giá theo Rank cá nhân)
        $start = strtotime($ticket['start_time']);
        $end = strtotime($ticket['end_time']);
        $hours = max(1, ($end - $start) / 3600);
        $totalBase = $hours * $ticket['price_per_hour'];
        $halfBase = $totalBase / 2;

        require_once __DIR__ . '/Ranking.php';
        $ranking = new Ranking($this->conn);
        $userDiscount = $ranking->getActiveDiscount($userId);
        $userPay = $halfBase * ((100 - $userDiscount) / 100);

        // Đánh dấu user này đã xác nhận kèm phương thức và trạng thái thanh toán 50%
        $up = "UPDATE " . $this->table_name . " 
               SET user_confirmed = 1, payment_method = :pm, payment_status = :ps, split_price = :sp 
               WHERE id = :id";
        $stmt = $this->conn->prepare($up);
        $stmt->execute([
            ':pm' => $paymentMethod,
            ':ps' => $paymentStatus,
            ':sp' => $userPay,
            ':id' => $ticketId
        ]);

        // Đồng thời cập nhật trên ticket của đối phương: matched_user_confirmed = 1
        $upOpp = "UPDATE " . $this->table_name . " SET matched_user_confirmed = 1 WHERE id = ?";
        $stmtOpp = $this->conn->prepare($upOpp);
        $stmtOpp->execute([$ticket['matched_ticket_id']]);

        // Kiểm tra đối phương đã xác nhận chưa
        $oppTicket = $this->getTicketById($ticket['matched_ticket_id']);
        
        if ($oppTicket && $oppTicket['user_confirmed'] == 1) {
            // --- CẢ 2 BÊN ĐÃ XÁC NHẬN & THANH TOÁN 50% -> TIẾN HÀNH KHÓA SÂN VÀ TẠO HÓA ĐƠN CHIA ĐÔI ---
            $oppDiscount = $ranking->getActiveDiscount($ticket['opp_user_id']);
            $oppPay = $halfBase * ((100 - $oppDiscount) / 100);
            $finalTotalPrice = $userPay + $oppPay;

            // Đánh giá trạng thái thanh toán tổng thể đơn đặt
            $bothPaid = ($paymentStatus === 'paid' && ($oppTicket['payment_status'] ?? '') === 'paid');
            $anyPaid = ($paymentStatus === 'paid' || ($oppTicket['payment_status'] ?? '') === 'paid');
            $overallPaymentStatus = $bothPaid ? 'paid' : ($anyPaid ? 'deposited' : 'unpaid');
            
            $bookingPaymentMethod = ($paymentMethod === ($oppTicket['payment_method'] ?? 'cash')) ? $paymentMethod : 'split_mixed';

            // 1. Tạo 2 mã Check-in riêng biệt cho từng cơ thủ
            $userCheckinCode = 'CKIN-T' . $ticketId . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
            $oppCheckinCode = 'CKIN-T' . $ticket['matched_ticket_id'] . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
            $bookingCheckin = 'CKIN' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));

            // Lưu mã Check-in cá nhân vào từng Ticket
            $this->conn->prepare("UPDATE " . $this->table_name . " SET checkin_code = ? WHERE id = ?")->execute([$userCheckinCode, $ticketId]);
            $this->conn->prepare("UPDATE " . $this->table_name . " SET checkin_code = ? WHERE id = ?")->execute([$oppCheckinCode, $ticket['matched_ticket_id']]);

            // 2. Tạo Booking chính thức
            $bookSql = "INSERT INTO bookings 
                        (pitch_id, user_id, customer_name, customer_phone, booking_date, start_time, end_time, total_price, payment_method, payment_status, services_note, checkin_code, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";
            
            $bStmt = $this->conn->prepare($bookSql);
            $userPayDesc = ($paymentMethod === 'vietqr') ? 'VietQR Đã Trả' : 'Tiền mặt';
            $oppPayDesc = (($oppTicket['payment_method'] ?? 'cash') === 'vietqr') ? 'VietQR Đã Trả' : 'Tiền mặt';
            $servicesNote = "Kèo Ghép Thông Minh Elo: {$ticket['user_name']} [Mã Checkin: {$userCheckinCode}] ({$userPayDesc} 50%: " . number_format($userPay,0,',','.') . "đ) vs {$ticket['opp_name']} [Mã Checkin: {$oppCheckinCode}] ({$oppPayDesc} 50%: " . number_format($oppPay,0,',','.') . "đ)";

            $bStmt->execute([
                $ticket['matched_pitch_id'],
                $userId,
                $ticket['user_name'] . ' vs ' . $ticket['opp_name'] . ' (Ghép Kèo AI)',
                $ticket['user_phone'] ?? '0368653018',
                $ticket['play_date'],
                $ticket['start_time'],
                $ticket['end_time'],
                $finalTotalPrice,
                $bookingPaymentMethod,
                $overallPaymentStatus,
                $servicesNote,
                $bookingCheckin
            ]);
            $newBookingId = $this->conn->lastInsertId();

            // 3. Tạo bản ghi trong bảng matchmaking để chủ sân duyệt tiền & chấm điểm Elo sau trận
            $hostPaid = ($paymentStatus === 'paid') ? 1 : 0;
            $guestPaid = (($oppTicket['payment_status'] ?? 'unpaid') === 'paid') ? 1 : 0;

            $matchSql = "INSERT INTO matchmaking 
                         (user_id, pitch_id, play_date, start_time, end_time, format, skill_level, district, message, status, opponent_id, match_result, host_paid, guest_paid, host_checkin, guest_checkin, noshow_status)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'closed', ?, 'pending', ?, ?, 0, 0, 'none')";
            $mStmt = $this->conn->prepare($matchSql);
            $mStmt->execute([
                $userId,
                $ticket['matched_pitch_id'],
                $ticket['play_date'],
                $ticket['start_time'],
                $ticket['end_time'],
                $ticket['format'],
                3, // level trung bình
                $ticket['district'],
                "Ghép trận thông minh tự động (Split 50/50): {$ticket['user_name']} vs {$ticket['opp_name']}",
                $ticket['opp_user_id'],
                $hostPaid,
                $guestPaid
            ]);

            // 4. Hoàn tất cả 2 Ticket: chuyển status sang 'confirmed'
            $upFinish = "UPDATE " . $this->table_name . " SET status = 'confirmed', booking_id = ? WHERE id IN (?, ?)";
            $fStmt = $this->conn->prepare($upFinish);
            $fStmt->execute([$newBookingId, $ticketId, $ticket['matched_ticket_id']]);

            // 5. Gửi tin nhắn chúc mừng và mã checkin riêng biệt cho từng người
            $congrats1 = "🎉 [KÈO ĐÃ ĐƯỢC CHỐT THÀNH CÔNG] Cả hai bên đã hoàn tất xác nhận & thanh toán chia đôi (50/50)! Sân {$ticket['pitch_name']} đã được đặt vào lúc " . substr($ticket['start_time'], 0, 5) . " - " . substr($ticket['end_time'], 0, 5) . " ngày " . date('d/m/Y', strtotime($ticket['play_date'])) . ". Mã Check-in riêng của bạn tại quầy: [{$userCheckinCode}]. Chúc bạn có trận đấu rực lửa!";
            $congrats2 = "🎉 [KÈO ĐÃ ĐƯỢC CHỐT THÀNH CÔNG] Cả hai bên đã hoàn tất xác nhận & thanh toán chia đôi (50/50)! Sân {$ticket['pitch_name']} đã được đặt vào lúc " . substr($ticket['start_time'], 0, 5) . " - " . substr($ticket['end_time'], 0, 5) . " ngày " . date('d/m/Y', strtotime($ticket['play_date'])) . ". Mã Check-in riêng của bạn tại quầy: [{$oppCheckinCode}]. Chúc bạn có trận đấu rực lửa!";
            $msgSql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
            $this->conn->prepare($msgSql)->execute([$ticket['opp_user_id'], $userId, $congrats1]);
            $this->conn->prepare($msgSql)->execute([$userId, $ticket['opp_user_id'], $congrats2]);

            return [
                'success' => true,
                'status' => 'fully_confirmed',
                'booking_id' => $newBookingId,
                'checkin_code' => $userCheckinCode,
                'opp_checkin_code' => $oppCheckinCode,
                'opp_name' => $ticket['opp_name'],
                'user_pay' => $userPay,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus
            ];
        }

        // Báo đối thủ biết qua tin nhắn
        $notifMsg = "🏸 [THÔNG BÁO GHÉP TRẬN] Đối thủ {$ticket['user_name']} đã xác nhận và chọn thanh toán 50% tiền sân! Hãy vào mục 'Ghép Trận Thông Minh' để hoàn tất phần thanh toán của bạn và chốt sân thi đấu.";
        $msgSql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
        $this->conn->prepare($msgSql)->execute([$userId, $ticket['opp_user_id'], $notifMsg]);

        return [
            'success' => true,
            'status' => 'waiting_opponent',
            'message' => 'Bạn đã hoàn tất thanh toán 50% tiền sân! Đang chờ đối thủ hoàn tất để chốt sân...',
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'user_pay' => $userPay
        ];
    }

    public function reportAbsent($ticketId, $userId, $reason = '') {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket || $ticket['user_id'] != $userId) {
            return ['success' => false, 'message' => 'Lệnh tìm trận không hợp lệ.'];
        }

        if ($ticket['status'] !== 'confirmed') {
            return ['success' => false, 'message' => 'Chỉ có thể báo vắng đối thủ sau khi kèo đấu đã được chốt thành công.'];
        }

        $reason = trim($reason);
        if (empty($reason)) {
            $reason = 'Đối thủ không đến sân theo lịch hẹn đã thỏa thuận.';
        }

        // Cập nhật trạng thái chờ chủ sân duyệt trên vé
        $up = "UPDATE " . $this->table_name . " SET noshow_status = 'pending', noshow_reason = ? WHERE id IN (?, ?)";
        $this->conn->prepare($up)->execute([$reason, $ticketId, $ticket['matched_ticket_id']]);

        // Cập nhật trong bảng matchmaking
        $upMatch = "UPDATE matchmaking 
                    SET noshow_reported_by = ?, noshow_target_user_id = ?, noshow_status = 'pending', noshow_reason = ? 
                    WHERE (user_id = ? AND opponent_id = ?) OR (user_id = ? AND opponent_id = ?)";
        $this->conn->prepare($upMatch)->execute([
            $userId,
            $ticket['opp_user_id'],
            $reason,
            $userId,
            $ticket['opp_user_id'],
            $ticket['opp_user_id'],
            $userId
        ]);

        // Bắn thông báo khẩn tới Chủ Sân
        if (!empty($ticket['pitch_owner_id'])) {
            require_once __DIR__ . '/Notification.php';
            $notif = new Notification($this->conn);
            $notif->create(
                $ticket['pitch_owner_id'],
                "⚠️ [YÊU CẦU XÁC NHẬN BÙNG KÈO] Cơ thủ báo đối thủ vắng mặt",
                "Cơ thủ {$ticket['user_name']} báo đối thủ {$ticket['opp_name']} vắng mặt tại sân {$ticket['pitch_name']}. Lý do: {$reason}. Vui lòng kiểm tra thực tế và xác nhận bùng kèo.",
                "index.php?action=owner_dashboard"
            );
        }

        return ['success' => true, 'message' => 'Đã gửi báo cáo vắng mặt tới Chủ Sân! Chủ sân sẽ kiểm tra thực tế tại sân và xác nhận bùng kèo.'];
    }

    public function ownerHandleNoShow($matchId, $ownerId, $decision = 'confirm') {
        $q = "SELECT m.*, p.name as pitch_name, p.owner_id 
              FROM matchmaking m 
              INNER JOIN pitches p ON m.pitch_id = p.id 
              WHERE m.id = ? AND (p.owner_id = ? OR ? = 1)";
        $s = $this->conn->prepare($q);
        $s->execute([$matchId, $ownerId, ($_SESSION['user_role'] ?? '') === 'admin' ? 1 : 0]);
        $m = $s->fetch(PDO::FETCH_ASSOC);

        if (!$m) {
            return ['success' => false, 'message' => 'Không tìm thấy trận đấu hoặc bạn không có quyền xử lý.'];
        }

        if ($decision === 'confirm') {
            $violatorId = $m['noshow_target_user_id'] ? $m['noshow_target_user_id'] : $m['opponent_id'];
            $victimId = ($violatorId == $m['user_id']) ? $m['opponent_id'] : $m['user_id'];

            // 1. Phạt người vi phạm (-30 điểm uy tín, -50 Elo)
            require_once __DIR__ . '/Reputation.php';
            $repModel = new Reputation($this->conn);
            $reason = "Chủ sân {$m['pitch_name']} xác nhận bùng kèo (No-Show) không đến sân tại trận #" . $matchId;
            $repModel->changeScore($violatorId, -30, $reason);

            require_once __DIR__ . '/Ranking.php';
            $ranking = new Ranking($this->conn);
            $ranking->penalizeNoShow($violatorId, -50);

            // 2. Cập nhật bảng matchmaking
            $upM = "UPDATE matchmaking SET noshow_status = 'confirmed_noshow', status = 'cancelled' WHERE id = ?";
            $this->conn->prepare($upM)->execute([$matchId]);

            // 3. Cập nhật vé match_tickets
            $upT = "UPDATE " . $this->table_name . " SET noshow_status = 'confirmed_noshow', status = 'cancelled' 
                    WHERE (user_id = ? OR user_id = ?) AND play_date = ? AND start_time = ?";
            $this->conn->prepare($upT)->execute([$violatorId, $victimId, $m['play_date'], $m['start_time']]);

            // 4. Hủy booking tương ứng để giải phóng sân
            $this->conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE pitch_id = ? AND booking_date = ? AND start_time = ?")
                       ->execute([$m['pitch_id'], $m['play_date'], $m['start_time']]);

            // 5. Gửi thông báo tới người vi phạm và nạn nhân
            require_once __DIR__ . '/Notification.php';
            $notif = new Notification($this->conn);
            $notif->create(
                $violatorId,
                "🚫 [XÁC NHẬN BÙNG KÈO TỪ CHỦ SÂN] Bạn bị xử phạt No-Show",
                "Chủ sân {$m['pitch_name']} đã xác nhận bạn vắng mặt không đến sân. Hệ thống đã phạt trừ 30 điểm uy tín, trừ 50 Elo và tạm khóa tìm trận.",
                "index.php?action=smart_match"
            );
            $notif->create(
                $victimId,
                "🛡️ [CHỦ SÂN ĐÃ XÁC NHẬN BÙNG KÈO] Quyền lợi của bạn đã được bảo vệ",
                "Chủ sân {$m['pitch_name']} đã xác nhận đối thủ bùng kèo và xử phạt đối thủ. Bạn không bị ảnh hưởng điểm uy tín/Elo.",
                "index.php?action=smart_match"
            );

            return ['success' => true, 'message_code' => 'noshow_confirmed'];
        } else {
            // Bác bỏ báo cáo
            $upM = "UPDATE matchmaking SET noshow_status = 'rejected' WHERE id = ?";
            $this->conn->prepare($upM)->execute([$matchId]);

            $upT = "UPDATE " . $this->table_name . " SET noshow_status = 'rejected' 
                    WHERE play_date = ? AND start_time = ? AND matched_pitch_id = ?";
            $this->conn->prepare($upT)->execute([$m['play_date'], $m['start_time'], $m['pitch_id']]);

            return ['success' => true, 'message_code' => 'noshow_rejected'];
        }
    }

    public function checkinPlayer($ticketId, $staffOrOwnerId = null) {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) {
            return ['success' => false, 'message' => 'Không tìm thấy vé thi đấu.'];
        }

        // Cập nhật vé này đã check-in
        $this->conn->prepare("UPDATE " . $this->table_name . " SET is_checked_in = 1, checked_in_at = NOW(), payment_status = 'paid' WHERE id = ?")
                   ->execute([$ticketId]);

        // Cập nhật tương ứng trong bảng matchmaking
        $mStmt = $this->conn->prepare("SELECT * FROM matchmaking WHERE (user_id = ? AND opponent_id = ?) OR (user_id = ? AND opponent_id = ?) ORDER BY id DESC LIMIT 1");
        $mStmt->execute([$ticket['user_id'], $ticket['opp_user_id'], $ticket['opp_user_id'], $ticket['user_id']]);
        if ($m = $mStmt->fetch(PDO::FETCH_ASSOC)) {
            if ($ticket['user_id'] == $m['user_id']) {
                $this->conn->prepare("UPDATE matchmaking SET host_checkin = 1, host_checkin_time = NOW(), host_paid = 1 WHERE id = ?")->execute([$m['id']]);
            } else {
                $this->conn->prepare("UPDATE matchmaking SET guest_checkin = 1, guest_checkin_time = NOW(), guest_paid = 1 WHERE id = ?")->execute([$m['id']]);
            }
        }

        return ['success' => true, 'message' => 'Check-in thành công cho cơ thủ ' . $ticket['user_name']];
    }

    public function cancelTicket($ticketId, $userId) {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket || $ticket['user_id'] != $userId) {
            return false;
        }

        if ($ticket['status'] == 'waiting') {
            $query = "UPDATE " . $this->table_name . " SET status = 'cancelled' WHERE id = ?";
            return $this->conn->prepare($query)->execute([$ticketId]);
        }

        if ($ticket['status'] == 'matched') {
            // Trừ điểm uy tín khi hủy kèo đã khớp
            require_once __DIR__ . '/Reputation.php';
            $repModel = new Reputation($this->conn);

            $matchTimestamp = strtotime($ticket['play_date'] . ' ' . $ticket['start_time']);
            $diffHours = ($matchTimestamp - time()) / 3600;

            if ($diffHours <= 2 && $diffHours >= 0) {
                // Hủy cận giờ (< 2 tiếng): phạt -15 điểm uy tín
                $repModel->changeScore($userId, -15, "Hủy kèo cận giờ thi đấu (< 2 tiếng)", null, $ticketId);
            } else {
                // Hủy sớm: phạt nhẹ -5 điểm uy tín
                $repModel->changeScore($userId, -5, "Hủy kèo sau khi đã khớp đối thủ", null, $ticketId);
            }

            // Người này hủy
            $query = "UPDATE " . $this->table_name . " SET status = 'cancelled' WHERE id = ?";
            $this->conn->prepare($query)->execute([$ticketId]);

            // Đối phương được đưa trở lại hàng chờ 'waiting' để tiếp tục tìm người khác
            if ($ticket['matched_ticket_id']) {
                $upOpp = "UPDATE " . $this->table_name . " 
                          SET status = 'waiting', matched_ticket_id = NULL, matched_pitch_id = NULL, 
                              user_confirmed = 0, matched_user_confirmed = 0, expires_at = NULL 
                          WHERE id = ?";
                $this->conn->prepare($upOpp)->execute([$ticket['matched_ticket_id']]);

                // Báo tin cho đối phương
                $msg = "⚠️ [THÔNG BÁO] Đối thủ vừa từ chối kèo đấu ngày " . date('d/m', strtotime($ticket['play_date'])) . ". Hệ thống đã tự động đưa bạn trở lại hàng chờ để tìm đối thủ mới!";
                $msgSql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
                $this->conn->prepare($msgSql)->execute([$userId, $ticket['opp_user_id'], $msg]);
            }
            return true;
        }

        return false;
    }

    public function getAvailablePitchesForMatch($ticketId) {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return [];

        require_once __DIR__ . '/Booking.php';
        $bookingModel = new Booking($this->conn);

        $query = "SELECT * FROM pitches WHERE status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $pitches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hasUserGps = (!empty($ticket['user_lat']) && !empty($ticket['user_lng']));
        $hasOppGps = (!empty($ticket['opp_lat']) && !empty($ticket['opp_lng']));

        $available = [];
        foreach ($pitches as $p) {
            $overlapping = $bookingModel->getConfirmedOverlappingCount($p['id'], $ticket['play_date'], $ticket['start_time'], $ticket['end_time']);
            if ($overlapping < $p['quantity']) {
                $p['is_current'] = ($p['id'] == $ticket['matched_pitch_id']);
                
                $dUser = ($hasUserGps && !empty($p['latitude'])) ? self::calculateHaversineDistance($ticket['user_lat'], $ticket['user_lng'], $p['latitude'], $p['longitude']) : null;
                $dOpp = ($hasOppGps && !empty($p['latitude'])) ? self::calculateHaversineDistance($ticket['opp_lat'], $ticket['opp_lng'], $p['latitude'], $p['longitude']) : null;
                
                $p['dist_user'] = ($dUser !== null && $dUser < 900) ? round($dUser, 1) : null;
                $p['dist_opp'] = ($dOpp !== null && $dOpp < 900) ? round($dOpp, 1) : null;
                $p['dist_total'] = (($dUser !== null && $dUser < 900) ? $dUser : 10) + (($dOpp !== null && $dOpp < 900) ? $dOpp : 10);
                
                $available[] = $p;
            }
        }

        // Sắp xếp các sân theo tổng khoảng cách từ 2 người gần nhất lên trước
        usort($available, function($a, $b) {
            if ($a['is_current']) return -1;
            if ($b['is_current']) return 1;
            return ($a['dist_total'] <=> $b['dist_total']);
        });

        return $available;
    }

    public function proposeSwitchPitch($ticketId, $newPitchId, $userId) {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket || ($ticket['user_id'] != $userId && $ticket['opp_user_id'] != $userId)) {
            return ['success' => false, 'message' => 'Lệnh tìm trận không hợp lệ hoặc bạn không có quyền đổi sân.'];
        }

        if ($ticket['status'] != 'matched') {
            return ['success' => false, 'message' => 'Chỉ có thể đề xuất đổi sân khi trận đấu đang ở trạng thái chờ xác nhận.'];
        }

        if ($newPitchId == $ticket['matched_pitch_id']) {
            return ['success' => false, 'message' => 'Bạn đang chọn đúng sân hiện tại của trận đấu.'];
        }

        // Kiểm tra sân mới còn trống slot không
        require_once __DIR__ . '/Booking.php';
        $bookingModel = new Booking($this->conn);
        
        $pStmt = $this->conn->prepare("SELECT * FROM pitches WHERE id = ? AND status = 'active' LIMIT 1");
        $pStmt->execute([(int)$newPitchId]);
        $pitch = $pStmt->fetch(PDO::FETCH_ASSOC);

        if (!$pitch) {
            return ['success' => false, 'message' => 'Sân được chọn không tồn tại hoặc ngưng hoạt động.'];
        }

        $overlapping = $bookingModel->getConfirmedOverlappingCount($pitch['id'], $ticket['play_date'], $ticket['start_time'], $ticket['end_time']);
        if ($overlapping >= $pitch['quantity']) {
            return ['success' => false, 'message' => 'Sân này đã hết chỗ trong khung giờ được chọn! Vui lòng chọn sân khác.'];
        }

        // Cập nhật proposed_pitch_id và proposed_by_user_id cho cả 2 ticket
        $up = "UPDATE " . $this->table_name . " 
                SET proposed_pitch_id = ?, proposed_by_user_id = ? 
                WHERE id IN (?, ?)";
        $stmt = $this->conn->prepare($up);
        $stmt->execute([(int)$newPitchId, (int)$userId, $ticket['id'], $ticket['matched_ticket_id']]);

        // Gửi thông báo cho đối phương kèm đề xuất
        $otherUserId = ($ticket['user_id'] == $userId) ? $ticket['opp_user_id'] : $ticket['user_id'];
        $msg = "📌 [ĐỀ XUẤT ĐỔI SÂN] Đối thủ {$ticket['user_name']} vừa đề xuất đổi sân thi đấu sang '{$pitch['name']}' ({$pitch['location']} - " . number_format($pitch['price_per_hour'], 0, ',', '.') . "đ/h). Hãy vào trang Ghép Trận Thông Minh để duyệt đề xuất!";
        $msgSql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
        $this->conn->prepare($msgSql)->execute([$userId, $otherUserId, $msg]);

        return [
            'success' => true,
            'message' => 'Đã gửi đề xuất đổi sân sang: ' . $pitch['name'] . '. Đang chờ đối thủ đồng ý.',
            'pitch_name' => $pitch['name']
        ];
    }

    public function acceptSwitchPitch($ticketId, $userId) {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket || ($ticket['user_id'] != $userId && $ticket['opp_user_id'] != $userId)) {
            return ['success' => false, 'message' => 'Lệnh tìm trận không hợp lệ.'];
        }

        if (empty($ticket['proposed_pitch_id'])) {
            return ['success' => false, 'message' => 'Hiện không có đề xuất đổi sân nào cần duyệt.'];
        }

        if ($ticket['proposed_by_user_id'] == $userId) {
            return ['success' => false, 'message' => 'Bạn không thể tự duyệt đề xuất do chính mình tạo ra.'];
        }

        $newPitchId = (int)$ticket['proposed_pitch_id'];
        $pStmt = $this->conn->prepare("SELECT * FROM pitches WHERE id = ? LIMIT 1");
        $pStmt->execute([$newPitchId]);
        $pitch = $pStmt->fetch(PDO::FETCH_ASSOC);

        if (!$pitch) {
            return ['success' => false, 'message' => 'Sân đề xuất không tồn tại.'];
        }

        // Chính thức đổi sang sân mới cho cả 2 ticket, reset đề xuất và reset xác nhận 50%
        $up = "UPDATE " . $this->table_name . " 
                SET matched_pitch_id = ?, proposed_pitch_id = NULL, proposed_by_user_id = NULL,
                    user_confirmed = 0, matched_user_confirmed = 0 
                WHERE id IN (?, ?)";
        $stmt = $this->conn->prepare($up);
        $stmt->execute([$newPitchId, $ticket['id'], $ticket['matched_ticket_id']]);

        // Gửi thông báo đồng thuận cho cả hai bên
        $otherUserId = ($ticket['user_id'] == $userId) ? $ticket['opp_user_id'] : $ticket['user_id'];
        $msg = "🎉 [ĐỒNG THUẬN ĐỔI SÂN THÀNH CÔNG] Hai bên đã cùng đồng ý chuyển địa điểm thi đấu sang '{$pitch['name']}' ({$pitch['location']}). Vui lòng kiểm tra lại tiền sân chia đôi (50/50) và bấm Xác nhận!";
        $msgSql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
        $this->conn->prepare($msgSql)->execute([$userId, $otherUserId, $msg]);
        $this->conn->prepare($msgSql)->execute([$otherUserId, $userId, $msg]);

        return [
            'success' => true,
            'message' => 'Đã đồng ý chuyển sang sân: ' . $pitch['name'],
            'pitch_name' => $pitch['name']
        ];
    }

    public function rejectSwitchPitch($ticketId, $userId) {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket || ($ticket['user_id'] != $userId && $ticket['opp_user_id'] != $userId)) {
            return ['success' => false, 'message' => 'Lệnh tìm trận không hợp lệ.'];
        }

        $proposerId = $ticket['proposed_by_user_id'] ?: (($ticket['user_id'] == $userId) ? $ticket['opp_user_id'] : $ticket['user_id']);

        $up = "UPDATE " . $this->table_name . " 
                SET proposed_pitch_id = NULL, proposed_by_user_id = NULL 
                WHERE id IN (?, ?)";
        $stmt = $this->conn->prepare($up);
        $stmt->execute([$ticket['id'], $ticket['matched_ticket_id']]);

        // Gửi thông báo từ chối cho người đề xuất
        $msg = "ℹ️ [TỪ CHỐI ĐỔI SÂN] Đối thủ của bạn muốn giữ nguyên sân thi đấu ban đầu ({$ticket['pitch_name']}). Hai bên tiếp tục chuẩn bị cho trận đấu tại đây.";
        $msgSql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
        $this->conn->prepare($msgSql)->execute([$userId, $proposerId, $msg]);

        return [
            'success' => true,
            'message' => 'Đã từ chối đề xuất đổi sân. Trận đấu tiếp tục giữ sân ban đầu.'
        ];
    }

    public function switchMatchPitch($ticketId, $newPitchId, $userId) {
        return $this->proposeSwitchPitch($ticketId, $newPitchId, $userId);
    }
}
?>
