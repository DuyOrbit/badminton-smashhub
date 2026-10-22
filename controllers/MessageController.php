<?php
class MessageController {
        public function apiGetUnreadCount() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['count' => 0]);
            exit;
        }
        $database = new Database();
        $db = $database->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['count' => (int)$row['unread_count']]);
        exit;
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
        
        $database = new Database();
        $db = $database->getConnection();
        
        $msgModel = new Message($db);
        $contacts = $msgModel->getRecentContacts($_SESSION['user_id'])->fetchAll(PDO::FETCH_ASSOC);
        
        $search_error = null;
        if (isset($_GET['search_phone']) && !empty(trim($_GET['search_phone']))) {
            $phone = trim($_GET['search_phone']);
            $stmt = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
            $stmt->execute([$phone]);
            if ($stmt->rowCount() > 0) {
                $found = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($found['id'] != $_SESSION['user_id']) {
                    header("Location: index.php?action=chat&user=" . $found['id']);
                    exit;
                } else {
                    $search_error = "Bạn không thể chat với chính mình.";
                }
            } else {
                $search_error = "Không tìm thấy người dùng với số điện thoại này.";
            }
        }
        
        $active_user = null;
        $messages = [];
        
        if (isset($_GET['user'])) {
            require_once 'models/User.php';
            $userModel = new User($db);
            $active_user = $userModel->readOne($_GET['user']);
            if ($active_user) {
                $messages = $msgModel->getConversation($_SESSION['user_id'], $active_user['id'])->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $page_title = "Tin Nhắn";
        $content = 'user/chat.php';
        require_once 'views/layout.php';
    }

    public function apiSend() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || !isset($_POST['receiver_id']) || empty($_POST['content'])) {
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $msg = new Message($db);
        $msg->sender_id = $_SESSION['user_id'];
        $msg->receiver_id = $_POST['receiver_id'];
        $msg->content = $_POST['content'];
        
        if ($msg->send()) {
            require_once "models/Notification.php";
            $notif = new Notification($db);
            $senderName = htmlspecialchars(strip_tags($_SESSION['user_name'] ?? 'Một người dùng'), ENT_QUOTES, 'UTF-8');
            $cleanRaw = strip_tags($_POST['content'] ?? '');
            $contentStr = mb_substr($cleanRaw, 0, 40) . (mb_strlen($cleanRaw) > 40 ? '...' : '');
            $contentStr = htmlspecialchars($contentStr, ENT_QUOTES, 'UTF-8');
            $notif->create(
                $_POST['receiver_id'], 
                "💬 Tin nhắn mới từ " . $senderName, 
                $contentStr, 
                "index.php?action=chat&user=" . $_SESSION['user_id']
            );
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to send']);
        }
        exit;
    }

    public function apiGet() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || !isset($_GET['user'])) {
            echo json_encode([]);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $msgModel = new Message($db);
        $messages = $msgModel->getConversation($_SESSION['user_id'], $_GET['user'])->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($messages);
        exit;
    }
}
?>
