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
        
        $host_points = 0;
        $guest_points = 0;
        
        if ($result == 'host_won') {
            $host_points = 30;
            $guest_points = -15;
        } elseif ($result == 'guest_won') {
            $host_points = -15;
            $guest_points = 30;
        } elseif ($result == 'draw') {
            $host_points = 10;
            $guest_points = 10;
        }
        
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
