<?php
// Simple MVC Router for Demo
session_start();
require_once 'config/database.php';

// Autoload controllers and models
spl_autoload_register(function ($class_name) {
    if (file_exists('controllers/' . $class_name . '.php')) {
        require_once 'controllers/' . $class_name . '.php';
    } else if (file_exists('models/' . $class_name . '.php')) {
        require_once 'models/' . $class_name . '.php';
    }
});

// Sinh mã CSRF Token chống giả mạo
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Hàm kiểm tra CSRF Token cho các thao tác quan trọng
function verifyCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die('Lỗi Bảo Mật: CSRF Token không hợp lệ. Hành động bị từ chối.');
        }
    }
}

// Validate session user integrity
if (isset($_SESSION['user_id'])) {
    try {
        $database_init = new Database();
        $db_init = $database_init->getConnection();
        if ($db_init) {
            $stmt_chk = $db_init->prepare("SELECT id, username, role, name, owner_id, assigned_pitch_id FROM users WHERE id = ? LIMIT 1");
            $stmt_chk->execute([(int)$_SESSION['user_id']]);
            $u_info = $stmt_chk->fetch(PDO::FETCH_ASSOC);
            if (!$u_info) {
                // Stale or deleted session ID -> cleanly purge
                unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], $_SESSION['owner_id'], $_SESSION['assigned_pitch_id']);
            } else {
                $_SESSION['user_name'] = $u_info['name'];
                $_SESSION['user_role'] = $u_info['role'];
                $_SESSION['owner_id'] = $u_info['owner_id'] ?? null;
                $_SESSION['assigned_pitch_id'] = $u_info['assigned_pitch_id'] ?? null;
            }
        }
    } catch (Exception $e) {}
}

// Simple Authorization Middleware
function requireRole($roles) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?action=login");
        exit;
    }
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    if (!in_array($_SESSION['user_role'] ?? '', $roles)) {
        header("Location: index.php?action=home&error=no_permission");
        exit;
    }
}

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: index.php?action=login");
        exit;
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : 'home';

// Basic routing
switch ($action) {
    case 'home':
        $controller = new HomeController();
        $controller->index();
        break;

    // Radius Map Search Routes
    case 'map_search':
        $controller = new HomeController();
        $controller->mapSearch();
        break;
    case 'api_pitches_map':
        $controller = new HomeController();
        $controller->apiPitchesMap();
        break;

    // Community Clubs Routes
    case 'clubs':
        $controller = new ClubController();
        $controller->index();
        break;
    case 'club_create':
        requireAuth();
        $controller = new ClubController();
        $controller->create();
        break;
    case 'club_store':
        requireAuth();
        $controller = new ClubController();
        $controller->store();
        break;
    case 'club_detail':
        $controller = new ClubController();
        $controller->detail();
        break;
    case 'club_join':
        requireAuth();
        $controller = new ClubController();
        $controller->join();
        break;
    case 'club_leave':
        requireAuth();
        $controller = new ClubController();
        $controller->leave();
        break;
    case 'club_post':
        requireAuth();
        $controller = new ClubController();
        $controller->storePost();
        break;
    case 'club_toggle_recruitment':
        requireAuth();
        $controller = new ClubController();
        $controller->toggleRecruitment();
        break;
        
    // User Profile Routes
    case 'user_profile':
        requireAuth();
        $controller = new UserController();
        $controller->profile();
        break;
    case 'update_profile':
        requireAuth();
        $controller = new UserController();
        $controller->updateProfile();
        break;

    // Matchmaking Routes
    case 'matchmaking':
        $controller = new MatchmakingController();
        $controller->index();
        break;
    case 'my_matches':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->myMatches();
        break;
    case 'create_match':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->store();
        break;
    case 'close_match':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->closeMatch();
        break;
    case 'accept_match':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->acceptMatch();
        break;
    case 'cancel_match':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->cancelMatch();
        break;

    // Smart Matchmaking Queue Routes (Lập Lệnh Tìm Trận)
    case 'smart_match':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->smartMatch();
        break;
    case 'store_ticket':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->storeTicket();
        break;
    case 'confirm_ticket':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->confirmTicket();
        break;
    case 'cancel_ticket':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->cancelTicket();
        break;
    case 'api_check_ticket':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->apiCheckTicket();
        break;
    case 'report_noshow':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->reportNoShow();
        break;
    case 'switch_pitch':
        requireAuth();
        $controller = new MatchmakingController();
        $controller->switchPitch();
        break;

    // Chat Routes
    case 'chat':
        $controller = new MessageController();
        $controller->index();
        break;
    case 'api_send_message':
        $controller = new MessageController();
        $controller->apiSend();
        break;
    case 'api_get_notifications':
        require_once 'controllers/NotificationController.php';
        $controller = new NotificationController();
        $controller->apiGet();
        break;
    case 'api_read_notification':
        require_once 'controllers/NotificationController.php';
        $controller = new NotificationController();
        $controller->apiRead();
        break;
        case 'api_get_unread_chat_count':
        require_once 'controllers/MessageController.php';
        $controller = new MessageController();
        $controller->apiGetUnreadCount();
        break;
    case 'api_get_messages':
        $controller = new MessageController();
        $controller->apiGet();
        break;
    
    // Auth Routes
    case 'forgot_password':
        $controller = new AuthController();
        $controller->forgotPassword();
        break;
    case 'reset_password':
        $controller = new AuthController();
        $controller->resetPassword();
        break;
    case 'login':
        $controller = new AuthController();
        $controller->login();
        break;
    case 'register':
        $controller = new AuthController();
        $controller->register();
        break;
    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;

    // Booking Routes
    case 'api_check_availability':
        $controller = new BookingController();
        $controller->apiCheckAvailability();
        break;
    case 'api_simulate_payment':
        $controller = new BookingController();
        $controller->simulatePayment();
        break;
    case 'book':
        requireAuth();
        $controller = new BookingController();
        $controller->book();
        break;
    case 'store_booking':
        requireAuth();
        $controller = new BookingController();
        $controller->store();
        break;
    case 'my_bookings':
        requireAuth();
        $controller = new BookingController();
        $controller->myBookings();
        break;
    case 'store_review':
        requireAuth();
        $controller = new BookingController();
        $controller->storeReview();
        break;
    case 'update_review':
        requireAuth();
        $controller = new BookingController();
        $controller->updateReview();
        break;
    case 'delete_review':
        requireAuth();
        $controller = new BookingController();
        $controller->deleteReview();
        break;
    
    // Admin Routes
    case 'admin_pitches':
        requireRole(['admin']);
        $controller = new AdminController();
        $controller->pitches();
        break;
    case 'admin_toggle_pitch':
    case 'admin_toggle_pitch_status':
        requireRole(['admin']);
        $controller = new AdminController();
        $controller->togglePitchStatus();
        break;
    case 'admin_export_bookings':
        requireRole(['admin']);
        $controller = new AdminController();
        $controller->exportBookings();
        break;
    case 'admin_dashboard':
        requireRole('admin');
        $controller = new AdminController();
        $controller->dashboard();
        break;
    case 'admin_users':
        requireRole('admin');
        $controller = new AdminController();
        $controller->users();
        break;
    case 'admin_create_user':
        requireRole('admin');
        $controller = new AdminController();
        $controller->createUser();
        break;
    case 'admin_store_user':
        requireRole('admin');
        verifyCsrfToken();
        $controller = new AdminController();
        $controller->storeUser();
        break;
    case 'admin_update_role':
        requireRole('admin');
        verifyCsrfToken();
        $controller = new AdminController();
        $controller->updateRole();
        break;
    case 'admin_assign_staff':
        requireRole('admin');
        verifyCsrfToken();
        $controller = new AdminController();
        $controller->assignStaff();
        break;
    case 'admin_delete_user':
        requireRole('admin');
        $controller = new AdminController();
        $controller->deleteUser();
        break;

    // Owner Routes
    case 'owner_export_bookings':
        requireRole(['owner']);
        $controller = new OwnerController();
        $controller->exportBookings();
        break;
    case 'owner_dashboard':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->dashboard();
        break;
    case 'owner_schedule':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->schedule();
        break;
    case 'owner_staff':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->staff();
        break;
    case 'owner_create_staff':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->createStaff();
        break;
    case 'owner_assign_staff':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->assignStaff();
        break;
    case 'owner_remove_staff':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->removeStaff();
        break;
    case 'create_walkin_booking':
        requireRole(['owner', 'staff']);
        $controller = new OwnerController();
        $controller->createWalkinBooking();
        break;
    case 'owner_pitches':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->pitches();
        break;
    case 'owner_add_pitch':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->addPitch();
        break;
    case 'owner_store_pitch':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->storePitch();
        break;
    case 'owner_edit_pitch':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->editPitch();
        break;
    case 'owner_update_pitch':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->updatePitch();
        break;
    case 'owner_delete_pitch':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->deletePitch();
        break;
    case 'owner_update_booking':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->updateBookingStatus();
        break;
    case 'owner_reviews':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->reviews();
        break;
    case 'owner_approve_match_booking':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->approveMatchBooking();
        break;
    case 'owner_report_result':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->reportResult();
        break;
    case 'owner_approve_host_payment':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->approveHostPayment();
        break;
    case 'owner_approve_guest_payment':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->approveGuestPayment();
        break;
    case 'owner_cancel_match':
        requireRole('owner');
        $controller = new OwnerController();
        $controller->cancelMatch();
        break;

    // Staff Routes
    case 'staff_dashboard':
        requireRole(['staff', 'owner', 'admin']);
        $controller = new StaffController();
        $controller->dashboard();
        break;
    case 'staff_checkin':
        requireRole(['staff', 'owner', 'admin']);
        $controller = new StaffController();
        $controller->checkin();
        break;

    // Owner Services Management
    case 'owner_services':
        requireRole(['owner', 'admin']);
        $controller = new OwnerController();
        $controller->services();
        break;
    case 'owner_store_service':
        requireRole(['owner', 'admin']);
        $controller = new OwnerController();
        $controller->storeService();
        break;
    case 'owner_delete_service':
        requireRole(['owner', 'admin']);
        $controller = new OwnerController();
        $controller->deleteService();
        break;

    case 'success':
        require 'views/success.php';
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
?>




