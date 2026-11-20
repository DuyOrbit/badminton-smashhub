<?php
class AuthController {
    public function forgotPassword() {
        $error = null;
        $tempPasswordGenerated = null;

        $maxAttempts = 5;
        $lockoutTime = 900; // 15 phut
        $now = time();
        $attempts = $_SESSION['forgot_attempts'] ?? ['count' => 0, 'last_time' => 0];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($attempts['count'] >= $maxAttempts && ($now - $attempts['last_time']) < $lockoutTime) {
                $remaining = ceil(($lockoutTime - ($now - $attempts['last_time'])) / 60);
                $error = "Ban da thu qua nhieu lan. Vui long thu lai sau {$remaining} phut.";
            } else {
                $username = trim($_POST['username'] ?? '');
                $phone = trim($_POST['phone'] ?? '');

                if (empty($username) || empty($phone)) {
                    $error = "Vui lòng cung cấp đầy đủ Tên đăng nhập và Số điện thoại đã đăng ký.";
                } else {
                    $database = new Database();
                    $db = $database->getConnection();

                    $stmt = $db->prepare("SELECT id, username, phone FROM users WHERE username = ? AND phone = ? LIMIT 1");
                    $stmt->execute([$username, $phone]);
                    $userFound = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($userFound) {
                        // Sinh mat khau tam ngau nhien co do phuc tap an toan
                        $tempPasswordGenerated = 'SH' . strtoupper(bin2hex(random_bytes(3))) . mt_rand(10, 99);
                        $hashed_password = password_hash($tempPasswordGenerated, PASSWORD_DEFAULT);
                        $upd = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $upd->execute([$hashed_password, $userFound['id']]);

                        // Reset bo dem lan thu khi thanh cong
                        unset($_SESSION['forgot_attempts']);
                    } else {
                        $attempts['count']++;
                        $attempts['last_time'] = $now;
                        $_SESSION['forgot_attempts'] = $attempts;
                        $remainingTries = max(0, $maxAttempts - $attempts['count']);
                        $error = "Xác minh thất bại: Tên đăng nhập và Số điện thoại không khớp. (Con {$remainingTries} lan thu)";
                    }
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
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['user_role'] = $user->role;
                $_SESSION['user_name'] = $user->name;
                $_SESSION['owner_id'] = $user->owner_id;
                $_SESSION['assigned_pitch_id'] = $user->assigned_pitch_id;
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }

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

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            $user->username = $username;
            $user->password = $password;
            $user->name = $name;
            $user->phone = $phone;
            
            // Validate do dai username, password va dinh dang so dien thoai
            if (strlen($username) < 3 || strlen($username) > 50) {
                $error = "Tên đăng nhập phải có từ 3 đến 50 ký tự.";
            } elseif (strlen($password) < 6) {
                $error = "Mật khẩu phải có tối thiểu 6 ký tự.";
            } elseif (!preg_match('/^(0[3|5|7|8|9])[0-9]{8}$/', $phone)) {
                $error = "Số điện thoại không đúng định dạng (10 chữ số, bắt đầu bằng 03, 05, 07, 08, 09).";
            } elseif ($user->usernameExists()) {
                $error = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.";
            } elseif ($user->phoneExists($phone)) {
                $error = "Số điện thoại này đã được sử dụng cho tài khoản khác.";
            } elseif ($user->create()) {
                // Khoi tao 600 Elo mac dinh theo chinh sach dang ky
                require_once 'models/Ranking.php';
                $season = Ranking::getCurrentSeason();
                $initEloStmt = $db->prepare("INSERT INTO user_scores (user_id, season, points, highest_points) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE points = points");
                $initEloStmt->execute([$user->id, $season, Ranking::DEFAULT_ELO, Ranking::DEFAULT_ELO]);

                // Auto login after registration
                $user->login();
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['user_role'] = $user->role;
                $_SESSION['user_name'] = $user->name;
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                
                header("Location: index.php");
                exit;
            } else {
                $error = "Không thể tạo tài khoản. Vui lòng thử lại.";
            }

            if (!empty($error)) {
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