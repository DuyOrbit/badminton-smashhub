<?php
class AuthController {
    public function forgotPassword() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['username'])) {
            $database = new Database();
            $db = $database->getConnection();
            $username = trim($_POST['username']);
            $phone = trim($_POST['phone'] ?? '');

            if (empty($phone)) {
                $error = "Vui lòng cung cấp số điện thoại đã đăng ký để xác minh quyền sở hữu tài khoản.";
            } else {
                $stmt = $db->prepare("SELECT id, username, phone FROM users WHERE username = ? AND phone = ? LIMIT 1");
                $stmt->execute([$username, $phone]);
                $userFound = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userFound) {
                    $newPass = !empty($_POST['new_password']) ? $_POST['new_password'] : '123456';
                    $hashed_password = password_hash($newPass, PASSWORD_DEFAULT);
                    $upd = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $upd->execute([$hashed_password, $userFound['id']]);

                    header("Location: index.php?action=login&msg=reset_success");
                    exit;
                } else {
                    $error = "Xác minh thất bại: Tên đăng nhập và Số điện thoại không khớp với hồ sơ người dùng.";
                }
            }
        }
        
        $page_title = "Quên Mật Khẩu";
        $content = "auth/forgot_password.php";
        require_once "views/layout.php";
    }
    public function login() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();
            $user = new User($db);

            $user->username = $_POST['username'];
            $user->password = $_POST['password'];

            if($user->login()) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['user_role'] = $user->role;
                $_SESSION['user_name'] = $user->name;
                $_SESSION['owner_id'] = $user->owner_id;
                $_SESSION['assigned_pitch_id'] = $user->assigned_pitch_id;

                if($user->role == 'admin') {
                    header("Location: index.php?action=admin_dashboard");
                } else if($user->role == 'owner') {
                    header("Location: index.php?action=owner_dashboard");
                } else if($user->role == 'staff') {
                    header("Location: index.php?action=staff_dashboard");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = "Tài khoản hoặc mật khẩu không chính xác.";
                $content = 'auth/login.php';
                require_once 'views/layout.php';
                return;
            }
        }
        
        $page_title = "Đăng Nhập";
        $content = 'auth/login.php';
        require_once 'views/layout.php';
    }

    public function register() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();
            $user = new User($db);

            $user->username = $_POST['username'];
            $user->password = $_POST['password'];
            $user->name = $_POST['name'];
            $user->phone = $_POST['phone'];
            
            // Check if username exists
            if($user->usernameExists()) {
                $error = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.";
                $content = 'auth/register.php';
                require_once 'views/layout.php';
                return;
            }

            if($user->create()) {
                // Auto login after registration
                $user->login(); // using the same credentials that were just posted
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['user_role'] = $user->role;
                $_SESSION['user_name'] = $user->name;
                
                header("Location: index.php");
                exit;
            } else {
                $error = "Không thể tạo tài khoản. Vui lòng thử lại.";
                $content = 'auth/register.php';
                require_once 'views/layout.php';
                return;
            }
        }

        $page_title = "Đăng Ký";
        $content = 'auth/register.php';
        require_once 'views/layout.php';
    }

    public function logout() {
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
?>