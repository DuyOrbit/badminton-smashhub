<?php
class NotificationController {
    public function apiGet() {
        header("Content-Type: application/json; charset=UTF-8");
        if (!isset($_SESSION["user_id"])) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $database = new Database();
        $db = $database->getConnection();
        require_once "models/Notification.php";
        $notif = new Notification($db);
        $stmt = $notif->getUnread($_SESSION["user_id"]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($notifications, JSON_UNESCAPED_UNICODE);
    }
    
    public function apiRead() {
        header("Content-Type: application/json; charset=UTF-8");
        if (!isset($_SESSION["user_id"]) || !isset($_POST["id"])) {
            echo json_encode(["success" => false], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $database = new Database();
        $db = $database->getConnection();
        require_once "models/Notification.php";
        $notif = new Notification($db);
        $notif->markAsRead($_POST["id"], $_SESSION["user_id"]);
        echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
    }
}
?>
