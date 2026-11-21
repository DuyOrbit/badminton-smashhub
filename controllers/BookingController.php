<?php
class BookingController {
    public function book() {
        $database = new Database();
        $db = $database->getConnection();

        $pitch_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$pitch_id) {
            header("Location: index.php?action=home&error=pitch_missing");
            exit;
        }
        
        $pitch = new Pitch($db);
        $pitchData = $pitch->readOne($pitch_id);

        if(!$pitchData) {
            header("Location: index.php?action=home&error=pitch_not_found");
            exit;
        }

        $currentUser = null;
        $discount = 0;
        if (isset($_SESSION['user_id'])) {
            $userModel = new User($db);
            $currentUser = $userModel->readOne($_SESSION['user_id']);
            
            $ranking = new Ranking($db);
            $discount = $ranking->getActiveDiscount($_SESSION['user_id']);
        }

        $reviewModel = new Review($db);
        $reviewsStmt = $reviewModel->readByPitch($pitch_id);
        $pitchReviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Badminton Services (Vợt, nước uống, cầu, căng cước)
        $serviceModel = new Service($db);
        $availableServices = $serviceModel->readByPitch($pitch_id);

        $page_title = "Đặt Sân - " . $pitchData['name'];
        $content = 'book.php';
        require_once 'views/layout.php';
    }

    public function store() {
        if($_POST) {
            $database = new Database();
            $db = $database->getConnection();

            $booking = new Booking($db);

            $pitch_id = (int)($_POST['pitch_id'] ?? 0);
            $booking_date = trim($_POST['booking_date'] ?? '');
            $start_time = trim($_POST['start_time'] ?? '');
            $end_time = trim($_POST['end_time'] ?? '');

            $pitch = new Pitch($db);
            $pitchData = $pitch->readOne($pitch_id);
            if (!$pitchData || ($pitchData['status'] ?? 'active') === 'locked') {
                header("Location: index.php?action=home&error=pitch_unavailable");
                exit;
            }

            $booking->pitch_id = $pitch_id;
            $booking->customer_name = htmlspecialchars(strip_tags($_POST['customer_name'] ?? ''));
            $booking->customer_phone = htmlspecialchars(strip_tags($_POST['customer_phone'] ?? ''));
            $booking->booking_date = $booking_date;
            $booking->start_time = $start_time;
            $booking->end_time = $end_time;
            $booking->user_id = $_SESSION['user_id'] ?? null;
            
            // 1. Kiểm tra thời gian bắt đầu và kết thúc
            $start = strtotime($start_time);
            $end = strtotime($end_time);
            $hours = ($end - $start) / 3600;
            if($hours <= 0) {
                 header("Location: index.php?action=book&id={$pitch_id}&error=invalid_time");
                 exit;
            }

            // 2. Chặn đặt ngày hoặc giờ trong quá khứ và bắt buộc đặt trước tối thiểu 60 phút
            $today = date('Y-m-d');
            $nowTime = date('H:i:s');
            $nowTimestamp = time();
            $bookingStartTimestamp = strtotime($booking_date . ' ' . $start_time);

            if ($booking_date < $today || ($bookingStartTimestamp < $nowTimestamp)) {
                header("Location: index.php?action=book&id={$pitch_id}&error=past_time");
                exit;
            }

            // Quy tắc hệ thống: Đặt trước tối thiểu 60 phút để sân chuẩn bị
            if (($bookingStartTimestamp - $nowTimestamp) < 3600) {
                header("Location: index.php?action=book&id={$pitch_id}&error=advance_booking_60min");
                exit;
            }

            // 3. Quy chuẩn khung giờ hoạt động chính xác (06:00 - 22:00)
            $start_hour = (int)date('H', $start);
            $end_hour = (int)date('H', $end);
            $end_min = (int)date('i', $end);
            if ($start_hour < 6 || $end_hour > 22 || ($end_hour == 22 && $end_min > 0)) {
                header("Location: index.php?action=book&id={$pitch_id}&error=outside_operating_hours");
                exit;
            }

            $base_price = $hours * $pitchData['price_per_hour'];
            
            $discount = 0;
            $ranking = new Ranking($db);
            if (isset($_SESSION['user_id'])) {
                $discount = $ranking->getActiveDiscount($_SESSION['user_id']);
            }

            // Dịch vụ đi kèm
            $services_selected = $_POST['services'] ?? [];
            $service_total = 0;
            $service_notes = [];
            
            if (!empty($services_selected) && is_array($services_selected)) {
                $serviceModel = new Service($db);
                foreach ($services_selected as $s_id => $qty) {
                    $qty = (int)$qty;
                    if ($qty > 0) {
                        $sItem = $serviceModel->readOne((int)$s_id);
                        if ($sItem) {
                            $cost = $qty * $sItem['price'];
                            $service_total += $cost;
                            $service_notes[] = "{$sItem['name']} x{$qty}";
                        }
                    }
                }
            }

            // Whitelist hình thức thanh toán
            $allowed_pm = ['cash', 'vietqr'];
            $payment_method = in_array($_POST['payment_method'] ?? '', $allowed_pm) ? $_POST['payment_method'] : 'cash';

            $booking->services_note = implode(', ', $service_notes);
            $booking->payment_method = $payment_method;
            $booking->payment_status = 'unpaid';
            $booking->checkin_code = 'CKIN' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
            $booking->total_price = ($base_price * ((100 - $discount) / 100)) + $service_total;

            // XỬ LÝ ĐỒNG THỜI AN TOÀN (CONCURRENCY-SAFE / ROW-LEVEL LOCKING TRONG TRANSACTION)
            $db->beginTransaction();
            try {
                // Khóa độc quyền (Exclusive Lock) dòng dữ liệu của Cụm sân trong InnoDB
                // Mọi request đặt sân đồng thời trên cụm sân này đều phải xếp hàng (serialize) lần lượt
                $lockStmt = $db->prepare("SELECT id, quantity, status FROM pitches WHERE id = ? FOR UPDATE");
                $lockStmt->execute([$pitch_id]);
                $lockedPitch = $lockStmt->fetch(PDO::FETCH_ASSOC);

                if (!$lockedPitch || ($lockedPitch['status'] ?? 'active') === 'locked') {
                    $db->rollBack();
                    header("Location: index.php?action=book&id={$pitch_id}&error=pitch_unavailable");
                    exit;
                }

                $pitchCapacity = (int)$lockedPitch['quantity'];

                // Kiểm tra số lượng sân con đang được sử dụng trong khung giờ này
                $overlapping = $booking->getConfirmedOverlappingCount($pitch_id, $booking_date, $start_time, $end_time);
                if ($overlapping >= $pitchCapacity) {
                    $db->rollBack();
                    header("Location: index.php?action=book&id={$pitch_id}&error=fully_booked");
                    exit;
                }

                // Tìm và chọn sân con còn trống an toàn tuyệt đối
                $occupiedCourts = $booking->getOccupiedCourts($pitch_id, $booking_date, $start_time, $end_time, $pitchCapacity);
                $requestedCourt = isset($_POST['court_number']) ? (int)$_POST['court_number'] : 1;
                $chosenCourt = null;

                if ($requestedCourt >= 1 && $requestedCourt <= $pitchCapacity && !in_array($requestedCourt, $occupiedCourts)) {
                    $chosenCourt = $requestedCourt;
                } else {
                    for ($c = 1; $c <= $pitchCapacity; $c++) {
                        if (!in_array($c, $occupiedCourts)) {
                            $chosenCourt = $c;
                            break;
                        }
                    }
                }

                // Nếu không còn bất kỳ sân con nào trống, hủy giao dịch và báo hết sân
                if ($chosenCourt === null) {
                    $db->rollBack();
                    header("Location: index.php?action=book&id={$pitch_id}&error=fully_booked");
                    exit;
                }
                $booking->court_number = $chosenCourt;

                $newBookingId = $booking->create();
                if($newBookingId) {
                    $db->commit();
                    // Store in session for success display
                    $_SESSION['last_booking'] = [
                        'booking_id' => $newBookingId,
                        'name' => $booking->customer_name,
                        'pitch_name' => $pitchData['name'],
                        'court_number' => $booking->court_number,
                        'date' => $booking->booking_date,
                        'time' => $booking->start_time . ' - ' . $booking->end_time,
                        'total_price' => $booking->total_price,
                        'payment_method' => $payment_method,
                        'payment_status' => 'unpaid',
                        'services_note' => $booking->services_note,
                        'checkin_code' => $booking->checkin_code
                    ];
                    header("Location: index.php?action=success");
                    exit;
                } else {
                    $db->rollBack();
                    header("Location: index.php?action=book&id={$pitch_id}&error=save_failed");
                    exit;
                }
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                error_log("Lỗi giao dịch đặt sân BookingController: " . $e->getMessage());
                header("Location: index.php?action=book&id={$pitch_id}&error=transaction_error");
                exit;
            }
        }
    }

    public function simulatePayment() {
        header('Content-Type: application/json; charset=UTF-8');
        $booking_id = $_POST['booking_id'] ?? $_GET['booking_id'] ?? null;
        if (!$booking_id) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng cung cấp mã đơn đặt sân (booking_id).'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $booking = new Booking($db);
        $bData = $booking->readOneWithPitchInfo($booking_id);

        if (!$bData) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn đặt sân trên hệ thống.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (($bData['status'] ?? '') === 'cancelled') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Đơn đặt sân đã bị hủy, không thể thực hiện thanh toán.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Security Check: Verify permission to pay for this booking (Anti-IDOR)
        $currentUserId = $_SESSION['user_id'] ?? null;
        $currentUserRole = $_SESSION['user_role'] ?? '';
        $isOwnerOfBooking = ($currentUserId && !empty($bData['user_id']) && (int)$bData['user_id'] === (int)$currentUserId);
        $isStaffOrOwner = in_array($currentUserRole, ['admin', 'staff', 'owner']);
        $isRecentSessionBooking = (isset($_SESSION['last_booking']) && (int)$_SESSION['last_booking']['booking_id'] === (int)$booking_id);

        if (!$isOwnerOfBooking && !$isStaffOrOwner && !$isRecentSessionBooking) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Lỗi bảo mật (403 Forbidden): Bạn không có quyền thao tác thanh toán trên đơn đặt sân này.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $updated = $booking->updatePaymentStatus($booking_id, 'paid', 'confirmed');
        if ($updated) {
            if (isset($_SESSION['last_booking']) && $_SESSION['last_booking']['booking_id'] == $booking_id) {
                $_SESSION['last_booking']['payment_status'] = 'paid';
            }
            echo json_encode([
                'success' => true,
                'message' => 'Mô phỏng thanh toán VietQR thành công! Đơn đặt sân đã được xác nhận thanh toán trực tuyến.',
                'payment_status' => 'paid',
                'booking_id' => $booking_id,
                'paid_at' => date('H:i:s d/m/Y')
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái đơn đặt.'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public function myBookings() {
        $database = new Database();
        $db = $database->getConnection();

        $booking = new Booking($db);
        $stmt = $booking->readByUser($_SESSION['user_id']);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $userReviews = [];
        $query = "SELECT * FROM reviews WHERE user_id = ?";
        $stmt_r = $db->prepare($query);
        $stmt_r->execute([$_SESSION['user_id']]);
        while($r = $stmt_r->fetch(PDO::FETCH_ASSOC)) {
            $userReviews[$r['booking_id']] = $r;
        }

        $page_title = "Lịch Sử Đặt Sân";
        $content = 'user/bookings.php';
        require_once 'views/layout.php';
    }

    public function storeReview() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id'])) {
            $database = new Database();
            $db = $database->getConnection();
            
            // Verify booking belongs to user
            $booking = new Booking($db);
            $bStmt = $booking->readByUser($_SESSION['user_id']);
            $bData = null;
            while($b = $bStmt->fetch(PDO::FETCH_ASSOC)) {
                if ($b['id'] == $_POST['booking_id']) {
                    $bData = $b;
                    break;
                }
            }

            if (!$bData) {
                header("Location: index.php?action=my_bookings&error=unauthorized_review");
                exit;
            }

            if ($bData['status'] !== 'completed') {
                header("Location: index.php?action=my_bookings&error=must_complete_to_review");
                exit;
            }

            $review = new Review($db);
            $review->booking_id = $bData['id'];
            $review->user_id = $_SESSION['user_id'];
            $review->pitch_id = $bData['pitch_id'];
            $review->rating = (int)$_POST['rating'];
            $review->comment = $_POST['comment'] ?? '';

            if ($review->create()) {
                header("Location: index.php?action=my_bookings&msg=review_added");
            } else {
                header("Location: index.php?action=my_bookings&error=review_failed");
            }
            exit;
        }
    }

    public function updateReview() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review_id'])) {
            $database = new Database();
            $db = $database->getConnection();
            
            $review = new Review($db);
            $review->id = $_POST['review_id'];
            $review->user_id = $_SESSION['user_id'];
            $review->rating = (int)$_POST['rating'];
            $review->comment = $_POST['comment'] ?? '';

            if ($review->update()) {
                header("Location: index.php?action=my_bookings&msg=review_updated");
            } else {
                header("Location: index.php?action=my_bookings&error=update_failed");
            }
            exit;
        }
    }

    public function deleteReview() {
        if (isset($_GET['id'])) {
            if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'])) {
                header("Location: index.php?action=my_bookings&error=csrf_invalid");
                exit;
            }

            $database = new Database();
            $db = $database->getConnection();
            
            $review = new Review($db);
            $review->id = $_GET['id'];
            $review->user_id = $_SESSION['user_id'];
            
            if ($review->delete()) {
                header("Location: index.php?action=my_bookings&msg=review_deleted");
            }
            exit;
        }
    }

    public function apiCheckAvailability() {
        header('Content-Type: application/json; charset=UTF-8');
        if(!isset($_GET['pitch_id']) || !isset($_GET['date'])) {
            echo json_encode(['error' => 'Missing parameters'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $pitch = new Pitch($db);
        $pitchData = $pitch->readOne($_GET['pitch_id']);
        if(!$pitchData) {
            echo json_encode(['error' => 'Pitch not found'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $totalCourts = (int)$pitchData['quantity'];
        $booking = new Booking($db);
        $slots = $booking->getDailyHourlyMatrix($_GET['pitch_id'], $_GET['date'], $totalCourts);

        if (isset($_GET['start']) && isset($_GET['end']) && $_GET['start'] !== '' && $_GET['end'] !== '') {
            $overlapping = $booking->getConfirmedOverlappingCount($_GET['pitch_id'], $_GET['date'], $_GET['start'], $_GET['end']);
            $occupiedCourts = $booking->getOccupiedCourts($_GET['pitch_id'], $_GET['date'], $_GET['start'], $_GET['end'], $totalCourts);
            $available = max(0, $totalCourts - $overlapping);
            echo json_encode([
                'booked' => $overlapping,
                'total' => $totalCourts,
                'available' => $available,
                'occupied_courts' => $occupiedCourts,
                'slots' => $slots
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'total' => $totalCourts,
            'slots' => $slots
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function apiAiSmartSearch() {
        header('Content-Type: application/json; charset=UTF-8');

        $inputRaw = file_get_contents('php://input');
        $jsonData = json_decode($inputRaw, true) ?: [];

        $prompt = $_POST['prompt'] ?? $jsonData['prompt'] ?? $_GET['prompt'] ?? '';
        $userLat = $_POST['user_lat'] ?? $jsonData['user_lat'] ?? $_GET['user_lat'] ?? null;
        $userLng = $_POST['user_lng'] ?? $jsonData['user_lng'] ?? $_GET['user_lng'] ?? null;

        if (empty(trim($prompt))) {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng nhập yêu cầu tìm sân (Ví dụ: Chiều nay 18h ở Bình Thạnh giá dưới 100k).'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        require_once 'models/AiSmartBooking.php';
        $aiModel = new AiSmartBooking($db);

        $parsed = $aiModel->parseNaturalLanguage($prompt);
        $rankedPitches = $aiModel->searchAndRankPitches($parsed, $userLat, $userLng, 4);
        $assistantMessage = $aiModel->generateAssistantMessage($parsed, $rankedPitches);

        echo json_encode([
            'success'         => true,
            'parsed'          => $parsed,
            'message'         => $assistantMessage,
            'recommendations' => $rankedPitches,
            'count'           => count($rankedPitches)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function apiAiChat() {
        header('Content-Type: application/json; charset=UTF-8');

        $inputRaw = file_get_contents('php://input');
        $jsonData = json_decode($inputRaw, true) ?: [];

        $message = $_POST['message'] ?? $jsonData['message'] ?? $_GET['message'] ?? '';
        $userLat = $_POST['user_lat'] ?? $jsonData['user_lat'] ?? $_GET['user_lat'] ?? null;
        $userLng = $_POST['user_lng'] ?? $jsonData['user_lng'] ?? $_GET['user_lng'] ?? null;

        if (empty(trim($message))) {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng nhập tin nhắn cho Trợ lý AI.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        require_once __DIR__ . '/../ai_engine/Predictor.php';
        $predictor = new Predictor($db);
        $result = $predictor->processMessage($message, $userLat, $userLng);

        echo json_encode([
            'success'    => true,
            'reply'      => $result['reply'],
            'intent'     => $result['intent'],
            'confidence' => $result['confidence'],
            'entities'   => $result['entities'] ?? [],
            'pitches'    => $result['pitches'] ?? []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
