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
        $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
        $district = isset($_GET['district']) ? trim($_GET['district']) : '';
        $type = isset($_GET['type']) ? trim($_GET['type']) : '';
        $min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (float)$_GET['min_price'] : 0;
        $max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (float)$_GET['max_price'] : 0;
        $price_preset = isset($_GET['price_preset']) ? trim($_GET['price_preset']) : '';
        $amenity = isset($_GET['amenity']) ? $_GET['amenity'] : '';
        $sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';

        // Tự động map các mốc khoảng giá nhanh (Price Presets)
        if (!empty($price_preset)) {
            if ($price_preset === 'under_80k') {
                $max_price = 80000;
                $min_price = 0;
            } elseif ($price_preset === '80k_100k') {
                $min_price = 80000;
                $max_price = 100000;
            } elseif ($price_preset === 'above_100k') {
                $min_price = 100000;
                $max_price = 0;
            }
        }

        // Kiểm tra xem có bất kỳ điều kiện lọc nào đang hoạt động không
        $has_filter = (!empty($keyword) || (!empty($district) && $district !== 'all') || (!empty($type) && $type !== 'all') || $min_price > 0 || $max_price > 0 || !empty($amenity) || !empty($sort) || !empty($price_preset));

        if ($has_filter) {
            $stmt = $pitch->search($keyword, $max_price, $type, $district, $amenity, $min_price, $sort);
        } else {
            $stmt = $pitch->readAll();
        }
        
        $pitches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $districts_count = $pitch->getActiveDistrictsWithCount();

        $page_title = "Trang Chủ - Tìm Kiếm Sân Cầu Lông & Cáp Kèo AI";
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
        header('Content-Type: application/json; charset=UTF-8');
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
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
