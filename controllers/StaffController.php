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
        if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['id']) || isset($_GET['ticket_id']) || isset($_POST['ticket_id'])) {
            $database = new Database();
            $db = $database->getConnection();

            $bookingModel = new Booking($db);
            $booking_id = $_POST['booking_id'] ?? $_GET['id'] ?? null;
            $ticket_id = $_POST['ticket_id'] ?? $_GET['ticket_id'] ?? null;
            $code = $_POST['checkin_code'] ?? $_GET['checkin_code'] ?? null;
            $redirectTo = $_POST['redirect_to'] ?? $_GET['redirect_to'] ?? 'staff_dashboard';

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
                   || isset($_GET['is_ajax']) 
                   || isset($_POST['is_ajax']) 
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            $respond = function($success, $message, $extra = []) use ($isAjax, $redirectTo) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $param = $success ? 'msg=' . urlencode($extra['msg_code'] ?? 'checkin_success') : 'error=' . urlencode($extra['error_code'] ?? 'failed');
                $targetUrl = ($redirectTo === 'owner_schedule') 
                    ? "index.php?action=owner_schedule&$param" 
                    : "index.php?action=staff_dashboard&$param";
                header("Location: " . $targetUrl);
                exit;
            };

            $assigned_pitch_id = $_SESSION['assigned_pitch_id'] ?? null;
            $owner_id = $_SESSION['owner_id'] ?? null;
            $role = $_SESSION['user_role'] ?? '';

            // Scope filter: Admin bypasses scope, staff/owner scoped to assigned pitch or owner's pitches
            $filter_pitch_id = ($role === 'admin') ? null : $assigned_pitch_id;
            $filter_owner_id = ($role === 'admin') ? null : $owner_id;

            // 1. Xử lý Check-in theo ticket_id trực tiếp (của từng người chơi Smart Match AI)
            if ($ticket_id) {
                require_once 'models/MatchTicket.php';
                $mt = new MatchTicket($db);
                $res = $mt->checkinPlayer($ticket_id, $_SESSION['user_id'] ?? null);

                $bothChecked = false;
                $tCheck = $db->prepare("SELECT booking_id, matched_ticket_id FROM match_tickets WHERE id = ?");
                $tCheck->execute([$ticket_id]);
                if ($tRow = $tCheck->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($tRow['booking_id']) && !empty($tRow['matched_ticket_id'])) {
                        $oppCheck = $db->prepare("SELECT is_checked_in FROM match_tickets WHERE id = ?");
                        $oppCheck->execute([$tRow['matched_ticket_id']]);
                        $oppChecked = $oppCheck->fetchColumn();
                        if ($oppChecked == 1) {
                            $bothChecked = true;
                            $db->prepare("UPDATE bookings SET status = 'completed', payment_status = 'paid' WHERE id = ?")->execute([$tRow['booking_id']]);
                        } else {
                            $db->prepare("UPDATE bookings SET payment_status = 'deposited' WHERE id = ? AND payment_status != 'paid'")->execute([$tRow['booking_id']]);
                        }
                    }
                }

                $respond(true, $res['message'] ?? 'Check-in thành công!', [
                    'ticket_id' => $ticket_id,
                    'booking_id' => $tRow['booking_id'] ?? null,
                    'both_checked' => $bothChecked,
                    'msg_code' => 'checkin_success'
                ]);
            }

            // 2. Xử lý Check-in theo mã Code (nhập từ ô tìm kiếm hoặc quét barcode)
            if ($code) {
                $tCheck = $db->prepare("SELECT id, booking_id, matched_ticket_id FROM match_tickets WHERE checkin_code = ? LIMIT 1");
                $tCheck->execute([$code]);
                if ($ticketRow = $tCheck->fetch(PDO::FETCH_ASSOC)) {
                    require_once 'models/MatchTicket.php';
                    $mt = new MatchTicket($db);
                    $res = $mt->checkinPlayer($ticketRow['id'], $_SESSION['user_id'] ?? null);

                    $bothChecked = false;
                    if (!empty($ticketRow['booking_id']) && !empty($ticketRow['matched_ticket_id'])) {
                        $oppCheck = $db->prepare("SELECT is_checked_in FROM match_tickets WHERE id = ?");
                        $oppCheck->execute([$ticketRow['matched_ticket_id']]);
                        $oppChecked = $oppCheck->fetchColumn();
                        if ($oppChecked == 1) {
                            $bothChecked = true;
                            $db->prepare("UPDATE bookings SET status = 'completed', payment_status = 'paid' WHERE id = ?")->execute([$ticketRow['booking_id']]);
                        } else {
                            $db->prepare("UPDATE bookings SET payment_status = 'deposited' WHERE id = ? AND payment_status != 'paid'")->execute([$ticketRow['booking_id']]);
                        }
                    }

                    $respond(true, $res['message'] ?? 'Check-in thành công!', [
                        'ticket_id' => $ticketRow['id'],
                        'booking_id' => $ticketRow['booking_id'] ?? null,
                        'both_checked' => $bothChecked,
                        'msg_code' => 'checkin_success'
                    ]);
                }

                // Nếu là mã đơn booking truyền thống
                $b = $bookingModel->findByCheckinCode($code, $filter_pitch_id, $filter_owner_id);
                if ($b) {
                    $booking_id = $b['id'];
                } else {
                    $respond(false, 'Mã check-in không hợp lệ hoặc không có quyền truy cập.', [
                        'error_code' => 'unauthorized_or_not_found'
                    ]);
                }
            } else if ($booking_id) {
                $b = $bookingModel->readOneWithPitchInfo($booking_id);
                if (!$b) {
                    $respond(false, 'Không tìm thấy thông tin đơn đặt sân.', ['error_code' => 'not_found']);
                }
                if ($role !== 'admin') {
                    if (!empty($filter_pitch_id) && $b['pitch_id'] != $filter_pitch_id) {
                        $respond(false, 'Bạn không được phân quyền quản lý cụm sân này.', ['error_code' => 'unauthorized_pitch']);
                    }
                    if (!empty($filter_owner_id) && $b['owner_id'] != $filter_owner_id) {
                        $respond(false, 'Đơn này không thuộc cụm sân của bạn.', ['error_code' => 'unauthorized_owner']);
                    }
                }
            }

            if ($booking_id) {
                $bookingModel->checkin($booking_id);
                $respond(true, 'Xác nhận khách vào sân thành công!', [
                    'booking_id' => $booking_id,
                    'both_checked' => true,
                    'msg_code' => 'checkin_success'
                ]);
            }
        }
        $targetUrl = ($redirectTo === 'owner_schedule') ? "index.php?action=owner_schedule" : "index.php?action=staff_dashboard";
        header("Location: " . $targetUrl);
        exit;
    }
}
?>
