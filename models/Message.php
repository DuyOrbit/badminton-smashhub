<?php
class Message {
    private $conn;
    private $table_name = "messages";

    public $id;
    public $sender_id;
    public $receiver_id;
    public $content;
    public $is_read;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function send() {
        $query = "INSERT INTO " . $this->table_name . " SET sender_id=:sender_id, receiver_id=:receiver_id, content=:content";
        $stmt = $this->conn->prepare($query);
        $this->content = htmlspecialchars(strip_tags($this->content));
        $stmt->bindParam(":sender_id", $this->sender_id);
        $stmt->bindParam(":receiver_id", $this->receiver_id);
        $stmt->bindParam(":content", $this->content);
        return $stmt->execute();
    }

    public function getConversation($user1, $user2) {
        $query = "SELECT m.*, u.avatar_url, u.name as sender_name 
                  FROM " . $this->table_name . " m
                  JOIN users u ON m.sender_id = u.id
                  WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                     OR (m.sender_id = ? AND m.receiver_id = ?)
                  ORDER BY m.created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user1, $user2, $user2, $user1]);
        
        // Mark as read immediately when fetched by the receiver
        $updateQ = "UPDATE " . $this->table_name . " SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?";
        $upStmt = $this->conn->prepare($updateQ);
        $upStmt->execute([$user2, $user1]);
        
        return $stmt;
    }

    public function getRecentContacts($user_id) {
        $query = "
            SELECT 
                u.id as contact_id, u.name, u.avatar_url,
                m.content as last_message, m.created_at, m.is_read, m.sender_id
            FROM users u
            JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
            WHERE (m.sender_id = ? OR m.receiver_id = ?) 
              AND u.id != ?
              AND m.id = (
                  SELECT MAX(id) FROM messages m2 
                  WHERE (m2.sender_id = u.id AND m2.receiver_id = ?) 
                     OR (m2.sender_id = ? AND m2.receiver_id = u.id)
              )
            ORDER BY m.created_at DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
        return $stmt;
    }
}
?>
