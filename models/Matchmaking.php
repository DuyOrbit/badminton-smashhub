<?php
class Matchmaking {
    private $conn;
    private $table_name = "matchmaking";

    public $id;
    public $user_id;
    public $pitch_id;
    public $play_date;
    public $start_time;
    public $end_time;
    public $format;
    public $skill_level;
    public $district;
    public $message;
    public $status;
    public $opponent_id;
    public $match_result;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, pitch_id=:pitch_id, play_date=:play_date, 
                      start_time=:start_time, end_time=:end_time, format=:format,
                      skill_level=:skill_level, district=:district, message=:message, status='open'";
        $stmt = $this->conn->prepare($query);
        $this->message = htmlspecialchars(strip_tags($this->message));
        $this->format = !empty($this->format) ? htmlspecialchars(strip_tags($this->format)) : 'Đánh Đôi';
        $this->skill_level = !empty($this->skill_level) ? intval($this->skill_level) : 3;
        $this->district = !empty($this->district) ? htmlspecialchars(strip_tags($this->district)) : '';

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":pitch_id", $this->pitch_id);
        $stmt->bindParam(":play_date", $this->play_date);
        $stmt->bindParam(":start_time", $this->start_time);
        $stmt->bindParam(":end_time", $this->end_time);
        $stmt->bindParam(":format", $this->format);
        $stmt->bindParam(":skill_level", $this->skill_level);
        $stmt->bindParam(":district", $this->district);
        $stmt->bindParam(":message", $this->message);
        return $stmt->execute();
    }

    // Thuật toán gợi ý ghép người chơi thông minh (Smart Recommendation)
    public function readAllOpen($currentUser = null) {
        $query = "SELECT m.*, u.name as user_name, u.avatar_url, u.skill_level as user_skill, u.district as user_district, 
                         p.name as pitch_name, p.image_url as pitch_image, p.price_per_hour, p.district as pitch_district
                  FROM " . $this->table_name . " m
                  INNER JOIN users u ON m.user_id = u.id
                  INNER JOIN pitches p ON m.pitch_id = p.id
                  WHERE m.status = 'open' AND m.opponent_id IS NULL
                  ORDER BY m.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Áp dụng thuật toán gợi ý ghép người chơi dựa trên 4 yếu tố từ đề tài:
        // 1. Thời gian (Time Similarity)
        // 2. Trình độ (Skill Similarity)
        // 3. Hình thức chơi (Format Similarity)
        // 4. Khu vực (Location Similarity)
        if ($currentUser && !empty($matches)) {
            $userSkill = (int)($currentUser['skill_level'] ?? 3);
            $userFormat = $currentUser['preferred_format'] ?? 'Đánh Đôi';
            $userDistrict = $currentUser['district'] ?? 'Bình Thạnh';

            foreach ($matches as &$m) {
                // Tiêu chí 1: Trình độ (30%)
                $matchSkill = (int)($m['skill_level'] ?? 3);
                $diffSkill = abs($userSkill - $matchSkill);
                $simSkill = max(0, 1 - ($diffSkill * 0.25)); // diff 0 => 1.0, diff 1 => 0.75, diff 2 => 0.5

                // Tiêu chí 2: Hình thức chơi (20%)
                $matchFormat = $m['format'] ?? 'Đánh Đôi';
                $simFormat = (strcasecmp($userFormat, $matchFormat) === 0) ? 1.0 : 0.6;

                // Tiêu chí 3: Khu vực (25%)
                $targetDistrict = !empty($m['district']) ? $m['district'] : $m['pitch_district'];
                $simDistrict = 0.5;
                if (!empty($targetDistrict) && strcasecmp(trim($targetDistrict), trim($userDistrict)) === 0) {
                    $simDistrict = 1.0;
                } elseif (in_array(trim($targetDistrict), ['Bình Thạnh', 'Gò Vấp', 'Phú Nhuận', 'Tân Bình'])) {
                    $simDistrict = 0.8;
                }

                // Tiêu chí 4: Thời gian thi đấu (25%)
                $simTime = 0.8;
                if (date('Y-m-d') === $m['play_date']) {
                    $simTime = 1.0; // cùng ngày hôm nay
                }

                $compositeScore = (0.30 * $simSkill) + (0.25 * $simDistrict) + (0.25 * $simTime) + (0.20 * $simFormat);
                $m['match_pct'] = (int)round($compositeScore * 100);
            }
            unset($m);

            // Sắp xếp các kèo có độ tương đồng cao nhất lên đầu
            usort($matches, function($a, $b) {
                return ($b['match_pct'] ?? 0) <=> ($a['match_pct'] ?? 0);
            });
        }

        return $matches;
    }

    public function acceptMatch($id, $opponent_id) {
        $query = "UPDATE " . $this->table_name . " SET status = 'closed', opponent_id = ? WHERE id = ? AND status = 'open' AND user_id != ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$opponent_id, $id, $opponent_id]);
    }

    public function readMyMatches($user_id) {
        $query = "SELECT m.*, 
                         u1.name as host_name, u1.avatar_url as host_avatar, 
                         u2.name as guest_name, u2.avatar_url as guest_avatar,
                         p.name as pitch_name, p.image_url as pitch_image, p.price_per_hour
                  FROM " . $this->table_name . " m
                  INNER JOIN users u1 ON m.user_id = u1.id
                  LEFT JOIN users u2 ON m.opponent_id = u2.id
                  INNER JOIN pitches p ON m.pitch_id = p.id
                  WHERE m.user_id = ? OR m.opponent_id = ?
                  ORDER BY m.play_date DESC, m.start_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $user_id]);
        return $stmt;
    }

    public function getMatchesForOwner($owner_id) {
        $query = "SELECT m.*, 
                         u1.name as host_name, u1.avatar_url as host_avatar, 
                         u2.name as guest_name, u2.avatar_url as guest_avatar,
                         p.name as pitch_name, p.image_url as pitch_image, p.price_per_hour,
                         b.status as booking_status
                  FROM " . $this->table_name . " m
                  INNER JOIN users u1 ON m.user_id = u1.id
                  INNER JOIN users u2 ON m.opponent_id = u2.id
                  INNER JOIN pitches p ON m.pitch_id = p.id
                  LEFT JOIN bookings b ON m.pitch_id = b.pitch_id AND m.play_date = b.booking_date AND m.start_time = b.start_time
                  WHERE p.owner_id = ? AND m.opponent_id IS NOT NULL
                  ORDER BY m.play_date DESC, m.start_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$owner_id]);
        return $stmt;
    }

    public function reportResult($id, $result, $owner_id) {
        $verify = "SELECT m.user_id, m.opponent_id, m.match_result FROM " . $this->table_name . " m
                   INNER JOIN pitches p ON m.pitch_id = p.id
                   WHERE m.id = ? AND p.owner_id = ?";
        $vStmt = $this->conn->prepare($verify);
        $vStmt->execute([$id, $owner_id]);
        if($row = $vStmt->fetch(PDO::FETCH_ASSOC)) {
            $query = "UPDATE " . $this->table_name . " SET match_result = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            if ($stmt->execute([$result, $id])) {
                require_once 'models/Ranking.php';
                $ranking = new Ranking($this->conn);
                $ranking->updateScore($row['user_id'], $row['opponent_id'], $result);
                return true;
            }
        }
        return false;
    }

    public function closeMatch($id, $user_id) {
        $query = "UPDATE " . $this->table_name . " SET status = 'closed' WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id, $user_id]);
    }

    public function completeMatch($id, $user_id) {
        return $this->closeMatch($id, $user_id);
    }

    public function cancelMatch($id, $user_id) {
        $query = "UPDATE " . $this->table_name . " SET status = 'open', opponent_id = NULL WHERE id = ? AND (user_id = ? OR opponent_id = ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id, $user_id, $user_id]);
    }

    public function cancelMatchByUser($id, $user_id) {
        return $this->cancelMatch($id, $user_id);
    }

    public function ownerCancelMatch($id, $owner_id) {
        $verify = "SELECT m.id FROM " . $this->table_name . " m
                   INNER JOIN pitches p ON m.pitch_id = p.id
                   WHERE m.id = ? AND p.owner_id = ?";
        $vStmt = $this->conn->prepare($verify);
        $vStmt->execute([$id, $owner_id]);
        if($vStmt->fetch()) {
            $query = "UPDATE " . $this->table_name . " SET status = 'cancelled' WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        }
        return false;
    }

    public function cancelMatchByOwner($id, $owner_id) {
        return $this->ownerCancelMatch($id, $owner_id);
    }

    public function updatePaymentStatus($id, $type, $owner_id) {
        $verify = "SELECT m.id FROM " . $this->table_name . " m
                   INNER JOIN pitches p ON m.pitch_id = p.id
                   WHERE m.id = ? AND p.owner_id = ?";
        $vStmt = $this->conn->prepare($verify);
        $vStmt->execute([$id, $owner_id]);
        if($vStmt->fetch()) {
            $column = ($type === 'host') ? 'host_paid' : 'guest_paid';
            $query = "UPDATE " . $this->table_name . " SET $column = 1 WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        }
        return false;
    }
}
?>
