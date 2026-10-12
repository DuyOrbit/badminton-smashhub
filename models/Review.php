<?php
class Review {
    private $conn;
    private $table_name = "reviews";

    public $id;
    public $booking_id;
    public $user_id;
    public $pitch_id;
    public $rating;
    public $comment;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET booking_id=:booking_id, user_id=:user_id, pitch_id=:pitch_id, 
                      rating=:rating, comment=:comment";

        $stmt = $this->conn->prepare($query);

        $this->comment = htmlspecialchars(strip_tags($this->comment));

        $stmt->bindParam(":booking_id", $this->booking_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":pitch_id", $this->pitch_id);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":comment", $this->comment);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET rating = :rating, comment = :comment 
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $this->comment = htmlspecialchars(strip_tags($this->comment));

        $stmt->bindParam(':rating', $this->rating);
        $stmt->bindParam(':comment', $this->comment);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->user_id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readByPitch($pitch_id) {
        $query = "SELECT r.*, u.name as customer_name 
                  FROM " . $this->table_name . " r
                  INNER JOIN users u ON r.user_id = u.id
                  WHERE r.pitch_id = ?
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $pitch_id);
        $stmt->execute();
        return $stmt;
    }

    public function readByOwner($owner_id) {
        $query = "SELECT r.*, u.name as customer_name, p.name as pitch_name, b.booking_date 
                  FROM " . $this->table_name . " r
                  INNER JOIN users u ON r.user_id = u.id
                  INNER JOIN pitches p ON r.pitch_id = p.id
                  INNER JOIN bookings b ON r.booking_id = b.id
                  WHERE p.owner_id = ?
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $owner_id);
        $stmt->execute();
        return $stmt;
    }

    public function getReviewByBooking($booking_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE booking_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $booking_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
