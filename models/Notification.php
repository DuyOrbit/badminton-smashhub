<?php
class Notification {
    private $conn;
    private $table_name = "notifications";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $title, $content, $link = null) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, title, content, link) VALUES (:user_id, :title, :content, :link)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":link", $link);
        return $stmt->execute();
    }

    public function getUnread($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt;
    }

    public function markAsRead($id, $user_id) {
        $query = "UPDATE " . $this->table_name . " SET is_read = 1 WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }
}
?>
