<?php
class Ranking {
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
        if ($hostRating <= 0) $hostRating = 100;
        if ($guestRating <= 0) $guestRating = 100;

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
            return $row['points'];
        }
        return 0;
    }

    public function getSeasonHighestPoints($user_id, $season) {
        $query = "SELECT highest_points, points FROM user_scores WHERE user_id = ? AND season = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $season]);
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return max((int)$row['highest_points'], (int)$row['points']);
        }
        return 0;
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
