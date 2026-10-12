<?php
class UserController {
    public function profile() {
        $database = new Database();
        $db = $database->getConnection();

        $user = new User($db);
        $userData = $user->readOne($_SESSION['user_id']);
        if (!$userData) {
            unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);
            header("Location: index.php?action=login");
            exit;
        }
        
        $ranking = new Ranking($db);
        $currentSeason = Ranking::getCurrentSeason();
        $currentPoints = $ranking->getSeasonPoints($_SESSION['user_id'], $currentSeason);
        $currentRankName = $ranking->getRankName($currentPoints);
        $activeDiscount = $ranking->getActiveDiscount($_SESSION['user_id']);

        $page_title = "Hồ Sơ Cá Nhân";
        $content = 'user/profile.php';
        require_once 'views/layout.php';
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            $user = new User($db);
            $id = $_SESSION['user_id'];
            
            $data = [
                'name' => $_POST['name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'skill_level' => isset($_POST['skill_level']) ? (int)$_POST['skill_level'] : 3,
                'preferred_format' => $_POST['preferred_format'] ?? 'Đánh Đôi',
                'district' => $_POST['district'] ?? 'Bình Thạnh'
            ];

            if (!empty($_POST['new_password'])) {
                $data['password'] = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
            }

            // Handle avatar upload safely
            if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] == 0) {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $ext = strtolower(pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION));

                if (in_array($ext, $allowed_extensions)) {
                    $upload_dir = 'assets/uploads/avatars/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $target_file = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $target_file)) {
                        $data['avatar_url'] = $target_file;
                        $_SESSION['user_avatar'] = $target_file;
                    }
                }
            }

            if ($user->updateProfile($id, $data)) {
                // Update session name if changed
                $_SESSION['user_name'] = $data['name'];
                header("Location: index.php?action=user_profile&msg=updated");
            } else {
                header("Location: index.php?action=user_profile&error=failed");
            }
            exit;
        }
    }
}
?>
