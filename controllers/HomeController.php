<?php
class HomeController {
    public function index() {
        if (isset($_SESSION['user_role'])) {
            if ($_SESSION['user_role'] === 'admin') {
                header('Location: index.php?action=admin_dashboard');
                exit;
            } elseif ($_SESSION['user_role'] === 'owner') {
                header('Location: index.php?action=owner_dashboard');
                exit;
            } elseif ($_SESSION['user_role'] === 'staff') {
                header('Location: index.php?action=staff_dashboard');
                exit;
            }
        }

        $database = new Database();
        $db = $database->getConnection();

        $pitch = new Pitch($db);
        
        // Lấy các tham số tìm kiếm từ URL
        $keyword = isset($_GET['q']) ? $_GET['q'] : '';
        $max_price = isset($_GET['max_price']) ? $_GET['max_price'] : 0;
        $type = isset($_GET['type']) ? $_GET['type'] : '';

        // Nếu có tham số tìm kiếm thì gọi hàm search, ngược lại readAll
        if (!empty($keyword) || !empty($max_price) || !empty($type)) {
            $stmt = $pitch->search($keyword, $max_price, $type);
        } else {
            $stmt = $pitch->readAll();
        }
        
        $pitches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $page_title = "Trang Chủ";
        $content = 'home.php';
        require_once 'views/layout.php';
    }

    // --- BẢN ĐỒ TÌM KIẾM THEO BÁN KÍNH (RADIUS MAP SEARCH) ---
    public function mapSearch() {
        $database = new Database();
        $db = $database->getConnection();
        $pitch = new Pitch($db);

        // Vị trí mặc định: Quận Bình Thạnh, TP.HCM
        $user_lat = isset($_GET['lat']) ? (float)$_GET['lat'] : 10.812300;
        $user_lng = isset($_GET['lng']) ? (float)$_GET['lng'] : 106.711800;
        $radius = isset($_GET['radius']) ? (float)$_GET['radius'] : 8.0; // 8km mặc định
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;

        $pitches = $pitch->getPitchesWithinRadius($user_lat, $user_lng, $radius, $max_price, $type);

        $page_title = "Bản Đồ Tìm Sân Theo Bán Kính";
        $content = 'map_search.php';
        require_once 'views/layout.php';
    }

    public function apiPitchesMap() {
        header('Content-Type: application/json');
        $database = new Database();
        $db = $database->getConnection();
        $pitch = new Pitch($db);

        $user_lat = isset($_GET['lat']) ? (float)$_GET['lat'] : 10.812300;
        $user_lng = isset($_GET['lng']) ? (float)$_GET['lng'] : 106.711800;
        $radius = isset($_GET['radius']) ? (float)$_GET['radius'] : 8.0;
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;

        $pitches = $pitch->getPitchesWithinRadius($user_lat, $user_lng, $radius, $max_price, $type);

        echo json_encode([
            'user_lat' => $user_lat,
            'user_lng' => $user_lng,
            'radius_km' => $radius,
            'total_found' => count($pitches),
            'pitches' => $pitches
        ]);
        exit;
    }
}
?>
