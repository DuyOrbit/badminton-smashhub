<?php
class NotificationController {
    public function apiGet() {
        header("Content-Type: application/json");
        if (!isset($_SESSION["user_id"])) {
            echo json_encode([]);
            exit;
        }
        $database = new Database();
        $db = $database->getConnection();
        require_once "models/Notification.php";
        $notif = new Notification($db);
        $stmt = $notif->getUnread($_SESSION["user_id"]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($notifications);
    }
    
    public function apiRead() {
        header("Content-Type: application/json");
        if (!isset($_SESSION["user_id"]) || !isset($_POST["id"])) {
            echo json_encode(["success" => false]);
            exit;
        }
        $database = new Database();
        $db = $database->getConnection();
        require_once "models/Notification.php";
        $notif = new Notification($db);
        $notif->markAsRead($_POST["id"], $_SESSION["user_id"]);
        echo json_encode(["success" => true]);
    }
}
?>
