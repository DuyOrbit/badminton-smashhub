<?php
class BookingController {
    public function book() {
        $database = new Database();
        $db = $database->getConnection();

        $pitch_id = isset($_GET['id']) ? $_GET['id'] : die('Chưa chọn sân.');
        
        $pitch = new Pitch($db);
        $pitchData = $pitch->readOne($pitch_id);

        if(!$pitchData) {
            die('Sân không tồn tại.');
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

            $booking->pitch_id = $_POST['pitch_id'];
            $booking->customer_name = $_POST['customer_name'];
            $booking->customer_phone = $_POST['customer_phone'];
            $booking->booking_date = $_POST['booking_date'];
            $booking->start_time = $_POST['start_time'];
            $booking->end_time = $_POST['end_time'];
            $booking->user_id = $_SESSION['user_id'] ?? null;
            
            // Calculate total price (simplified)
            $start = strtotime($_POST['start_time']);
            $end = strtotime($_POST['end_time']);
            $hours = ($end - $start) / 3600;
            if($hours <= 0) {
                 die("Thời gian kết thúc phải sau thời gian bắt đầu.");
            }

            $pitch = new Pitch($db);
            $pitchData = $pitch->readOne($_POST['pitch_id']);
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
            
            if (!empty($services_selected)) {
                $serviceModel = new Service($db);
                foreach ($services_selected as $s_id => $qty) {
                    $qty = (int)$qty;
                    if ($qty > 0) {
                        $sItem = $serviceModel->readOne($s_id);
                        if ($sItem) {
                            $cost = $qty * $sItem['price'];
                            $service_total += $cost;
                            $service_notes[] = "{$sItem['name']} x{$qty}";
                        }
                    }
                }
            }

            $payment_method = $_POST['payment_method'] ?? 'cash';
            $booking->services_note = implode(', ', $service_notes);
            $booking->payment_method = $payment_method;
            $booking->payment_status = 'unpaid';
            $booking->checkin_code = 'CKIN' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
            $booking->total_price = ($base_price * ((100 - $discount) / 100)) + $service_total;

            // Check if full
            $overlapping = $booking->getConfirmedOverlappingCount($_POST['pitch_id'], $_POST['booking_date'], $_POST['start_time'], $_POST['end_time']);
            
            if ($overlapping >= $pitchData['quantity']) {
                die("<h3 style='color:red;'>Rất tiếc! Sân đã kín lịch đã được xác nhận vào khung giờ này. Vui lòng chọn khung giờ khác.</h3><br><a href='javascript:history.back()'>Quay lại</a>");
            }

            $newBookingId = $booking->create();
            if($newBookingId) {
                // Store in session for success display
                $_SESSION['last_booking'] = [
                    'booking_id' => $newBookingId,
                    'name' => $_POST['customer_name'],
                    'pitch_name' => $pitchData['name'],
                    'date' => $_POST['booking_date'],
                    'time' => $_POST['start_time'] . ' - ' . $_POST['end_time'],
                    'total_price' => $booking->total_price,
                    'payment_method' => $payment_method,
                    'services_note' => $booking->services_note,
                    'checkin_code' => $booking->checkin_code
                ];
                header("Location: index.php?action=success");
                exit;
            } else {
                echo "Lỗi: Không thể lưu thông tin đặt sân.";
            }
        }
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
                die("Bạn không có quyền đánh giá sân này.");
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
        header('Content-Type: application/json');
        if(!isset($_GET['pitch_id']) || !isset($_GET['date']) || !isset($_GET['start']) || !isset($_GET['end'])) {
            echo json_encode(['error' => 'Missing parameters']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $pitch = new Pitch($db);
        $pitchData = $pitch->readOne($_GET['pitch_id']);
        if(!$pitchData) {
            echo json_encode(['error' => 'Pitch not found']);
            exit;
        }

        $booking = new Booking($db);
        $overlapping = $booking->getConfirmedOverlappingCount($_GET['pitch_id'], $_GET['date'], $_GET['start'], $_GET['end']);

        $available = max(0, $pitchData['quantity'] - $overlapping);
        
        echo json_encode([
            'booked' => $overlapping,
            'total' => $pitchData['quantity'],
            'available' => $available
        ]);
        exit;
    }
}
?>
