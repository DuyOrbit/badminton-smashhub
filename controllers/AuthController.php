<?php
class AuthController {
        public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['username'])) {
            $database = new Database();
            $db = $database->getConnection();
            
            // Mock password reset: immediately reset to '123456'
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
            $hashed_password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt->execute([$hashed_password, $_POST['username']]);
            
            if ($stmt->rowCount() > 0) {
                header("Location: index.php?action=login&msg=reset_success");
                exit;
            } else {
                $error = "Không tìm thấy tên đăng nhập này trong hệ thống.";
            }
        }
        
        $page_title = "Quên mật khẩu";
        require_once "views/auth/forgot_password.php";
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