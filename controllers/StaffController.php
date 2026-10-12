<?php
class StaffController {
    public function dashboard() {
        $database = new Database();
        $db = $database->getConnection();

        $bookingModel = new Booking($db);
        $pitchModel = new Pitch($db);

        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

        $assigned_pitch_id = $_SESSION['assigned_pitch_id'] ?? null;
        $owner_id = $_SESSION['owner_id'] ?? null;

        $assigned_pitch_name = 'Tất cả cụm sân hệ thống';
        if (!empty($assigned_pitch_id)) {
            $p_info = $pitchModel->readOne($assigned_pitch_id);
            if ($p_info) {
                $assigned_pitch_name = $p_info['name'];
            }
        }

        $bookings = $bookingModel->readAllForStaff($date, $keyword, $assigned_pitch_id, $owner_id);

        // Stats for selected date
        $total_today = count($bookings);
        $checked_in = 0;
        $unpaid_count = 0;
        $revenue_today = 0;

        foreach ($bookings as $b) {
            if ($b['status'] == 'completed') {
                $checked_in++;
            }
            if ($b['payment_status'] == 'unpaid' && $b['status'] != 'cancelled') {
                $unpaid_count++;
            }
            if ($b['status'] != 'cancelled') {
                $revenue_today += $b['total_price'];
            }
        }

        $page_title = "Bàn Trực & Check-in Tiếp Đón";
        $content = 'staff/dashboard.php';
        require_once 'views/layout.php';
    }

    public function checkin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['id'])) {
            $database = new Database();
            $db = $database->getConnection();

            $bookingModel = new Booking($db);
            $booking_id = $_POST['booking_id'] ?? $_GET['id'] ?? null;
            $code = $_POST['checkin_code'] ?? null;

            $assigned_pitch_id = $_SESSION['assigned_pitch_id'] ?? null;
            $owner_id = $_SESSION['owner_id'] ?? null;
            $role = $_SESSION['user_role'] ?? '';

            // Scope filter: Admin bypasses scope, staff/owner scoped to assigned pitch or owner's pitches
            $filter_pitch_id = ($role === 'admin') ? null : $assigned_pitch_id;
            $filter_owner_id = ($role === 'admin') ? null : $owner_id;

            if ($code) {
                $b = $bookingModel->findByCheckinCode($code, $filter_pitch_id, $filter_owner_id);
                if ($b) {
                    $booking_id = $b['id'];
                } else {
                    header("Location: index.php?action=staff_dashboard&error=unauthorized_or_not_found");
                    exit;
                }
            } else if ($booking_id) {
                // Read booking & verify pitch assignment authorization
                $b = $bookingModel->readOneWithPitchInfo($booking_id);
                if (!$b) {
                    header("Location: index.php?action=staff_dashboard&error=not_found");
                    exit;
                }
                if ($role !== 'admin') {
                    if (!empty($filter_pitch_id) && $b['pitch_id'] != $filter_pitch_id) {
                        header("Location: index.php?action=staff_dashboard&error=unauthorized_pitch");
                        exit;
                    }
                    if (!empty($filter_owner_id) && $b['owner_id'] != $filter_owner_id) {
                        header("Location: index.php?action=staff_dashboard&error=unauthorized_owner");
                        exit;
                    }
                }
            }

            if ($booking_id) {
                $bookingModel->checkin($booking_id);
                header("Location: index.php?action=staff_dashboard&msg=checkin_success");
                exit;
            }
        }
        header("Location: index.php?action=staff_dashboard");
        exit;
    }
}
?>
