<?php
class AdminController {
    public function pitches() {
        $database = new Database();
        $db = $database->getConnection();
        
        $pitch = new Pitch($db);
        $stmt = $pitch->readAll();
        $pitches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $page_title = "Quản Lý Cụm Sân";
        $content = "admin/pitches.php";
        require_once "views/layout.php";
    }

    public function togglePitchStatus() {
        if(isset($_GET["id"]) && isset($_GET["status"])) {
            if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'])) {
                header("Location: index.php?action=admin_pitches&error=csrf_invalid");
                exit;
            }
            $database = new Database();
            $db = $database->getConnection();
            $status = $_GET["status"] == "locked" ? "locked" : "active";
            $stmt = $db->prepare("UPDATE pitches SET status = ? WHERE id = ?");
            $stmt->execute([$status, $_GET["id"]]);
            header("Location: index.php?action=admin_pitches&msg=updated");
            exit;
        }
    }

    public function exportBookings() {
        if (!isset($_SESSION["user_id"])) exit;
        
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT b.id, u.name as user_name, p.name as pitch_name, b.booking_date, b.start_time, b.end_time, b.total_price, b.status 
                  FROM bookings b 
                  LEFT JOIN users u ON b.user_id = u.id 
                  LEFT JOIN pitches p ON b.pitch_id = p.id 
                  ORDER BY b.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=admin_bookings_export.csv");
        $output = fopen("php://output", "w");
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ["ID", "Khach Hang", "San", "Ngay", "Gio Bat Dau", "Gio Ket Thuc", "Tong Tien", "Trang Thai"]);
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    public function dashboard() {
        $database = new Database();
        $db = $database->getConnection();

        // Get stats
        $stmt_users = $db->query("SELECT COUNT(*) FROM users");
        $total_users = $stmt_users->fetchColumn();

        $stmt_pitches = $db->query("SELECT COUNT(*) FROM pitches");
        $total_pitches = $stmt_pitches->fetchColumn();

        $stmt_bookings = $db->query("SELECT COUNT(*) FROM bookings");
        $total_bookings = $stmt_bookings->fetchColumn();

        // Get latest bookings
        $booking = new Booking($db);
        $stmt_latest = $booking->readAll();
        $bookings = $stmt_latest->fetchAll(PDO::FETCH_ASSOC);

        $page_title = "Quản Trị Hệ Thống";
        $content = 'admin/dashboard.php';
        require_once 'views/layout.php';
    }

    public function users() {
        $database = new Database();
        $db = $database->getConnection();
        
        $userModel = new User($db);
        $stmt = $userModel->readAll();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Danh sách Chủ sân (owner) để phân công nhân viên
        $stmt_owners = $db->query("SELECT id, name, username FROM users WHERE role = 'owner' ORDER BY name ASC");
        $owners = $stmt_owners->fetchAll(PDO::FETCH_ASSOC);

        // Danh sách Cụm sân (pitches) kèm tên chủ sân
        $stmt_pitches = $db->query("SELECT p.id, p.name, p.owner_id, u.name as owner_name FROM pitches p LEFT JOIN users u ON p.owner_id = u.id ORDER BY p.name ASC");
        $pitches = $stmt_pitches->fetchAll(PDO::FETCH_ASSOC);

        $page_title = "Quản Lý Người Dùng & Phân Quyền";
        $content = 'admin/users.php';
        require_once 'views/layout.php';
    }

    public function createUser() {
        $database = new Database();
        $db = $database->getConnection();

        // Danh sách Chủ sân (owner)
        $stmt_owners = $db->query("SELECT id, name, username FROM users WHERE role = 'owner' ORDER BY name ASC");
        $owners = $stmt_owners->fetchAll(PDO::FETCH_ASSOC);

        // Danh sách Cụm sân (pitches) kèm thông tin chủ
        $stmt_pitches = $db->query("SELECT p.id, p.name, p.owner_id, u.name as owner_name FROM pitches p LEFT JOIN users u ON p.owner_id = u.id ORDER BY p.name ASC");
        $pitches = $stmt_pitches->fetchAll(PDO::FETCH_ASSOC);

        $page_title = "Tạo Tài Khoản Mới";
        $content = 'admin/create_user.php';
        require_once 'views/layout.php';
    }

    public function storeUser() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();
            $user = new User($db);

            $user->username = trim($_POST['username'] ?? '');
            $user->password = $_POST['password'] ?? '123456';
            $user->name = trim($_POST['name'] ?? '');
            $user->phone = trim($_POST['phone'] ?? '');
            $user->role = $_POST['role'] ?? 'user';

            // Xử lý phân công cho Nhân Viên Thu Ngân
            if ($user->role === 'staff') {
                $user->owner_id = !empty($_POST['owner_id']) ? (int)$_POST['owner_id'] : null;
                $user->assigned_pitch_id = !empty($_POST['assigned_pitch_id']) ? (int)$_POST['assigned_pitch_id'] : null;

                // Tự động tìm chủ sân nếu Admin chỉ chọn Cụm sân
                if (!empty($user->assigned_pitch_id) && empty($user->owner_id)) {
                    $stmt_p = $db->prepare("SELECT owner_id FROM pitches WHERE id = ?");
                    $stmt_p->execute([$user->assigned_pitch_id]);
                    $p_row = $stmt_p->fetch(PDO::FETCH_ASSOC);
                    if ($p_row && !empty($p_row['owner_id'])) {
                        $user->owner_id = (int)$p_row['owner_id'];
                    }
                }
            } else {
                $user->owner_id = null;
                $user->assigned_pitch_id = null;
            }

            if ($user->usernameExists()) {
                header("Location: index.php?action=admin_create_user&error=exists");
                exit;
            }

            if ($user->create()) {
                header("Location: index.php?action=admin_users&msg=created");
            } else {
                header("Location: index.php?action=admin_create_user&error=failed");
            }
            exit;
        }
    }

    public function assignStaff() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
            $database = new Database();
            $db = $database->getConnection();

            $user_id = (int)$_POST['user_id'];
            $owner_id = !empty($_POST['owner_id']) ? (int)$_POST['owner_id'] : null;
            $pitch_id = !empty($_POST['assigned_pitch_id']) ? (int)$_POST['assigned_pitch_id'] : null;

            // Tự động suy luận chủ sân nếu chọn cụm sân
            if (!empty($pitch_id) && empty($owner_id)) {
                $stmt_p = $db->prepare("SELECT owner_id FROM pitches WHERE id = ?");
                $stmt_p->execute([$pitch_id]);
                $p_row = $stmt_p->fetch(PDO::FETCH_ASSOC);
                if ($p_row && !empty($p_row['owner_id'])) {
                    $owner_id = (int)$p_row['owner_id'];
                }
            }

            $userModel = new User($db);
            if ($userModel->assignStaffToPitch($user_id, $owner_id, $pitch_id)) {
                header("Location: index.php?action=admin_users&msg=assigned");
            } else {
                header("Location: index.php?action=admin_users&error=assign_failed");
            }
            exit;
        }
        header("Location: index.php?action=admin_users");
        exit;
    }

    public function updateRole() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['role'])) {
            $database = new Database();
            $db = $database->getConnection();
            $userModel = new User($db);

            $targetId = (int)$_POST['user_id'];
            $newRole = $_POST['role'];

            // Do not allow the current admin to demote their own account
            if ($targetId == $_SESSION['user_id'] && $newRole != 'admin') {
                header("Location: index.php?action=admin_users&error=cannot_demote_self");
                exit;
            }

            if ($userModel->updateRole($targetId, $newRole)) {
                header("Location: index.php?action=admin_users&msg=role_updated");
            } else {
                header("Location: index.php?action=admin_users&error=failed");
            }
            exit;
        }
        header("Location: index.php?action=admin_users");
        exit;
    }

    public function deleteUser() {
        if (isset($_GET['id'])) {
            $targetId = (int)$_GET['id'];

            // Do not allow admin to delete their own account
            if ($targetId == $_SESSION['user_id']) {
                header("Location: index.php?action=admin_users&error=cannot_delete_self");
                exit;
            }

            if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'])) {
                header("Location: index.php?action=admin_users&error=csrf_invalid");
                exit;
            }

            $database = new Database();
            $db = $database->getConnection();
            $userModel = new User($db);

            if ($userModel->delete($targetId)) {
                header("Location: index.php?action=admin_users&msg=deleted");
            } else {
                header("Location: index.php?action=admin_users&error=failed");
            }
            exit;
        }
        header("Location: index.php?action=admin_users");
        exit;
    }
}
?>