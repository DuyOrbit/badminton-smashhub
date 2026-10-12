<?php
class OwnerController {
        public function exportBookings() {
        if (!isset($_SESSION["user_id"])) exit;
        
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT b.id, u.name as user_name, p.name as pitch_name, b.booking_date, b.start_time, b.end_time, b.total_price, b.status 
                  FROM bookings b 
                  LEFT JOIN users u ON b.user_id = u.id 
                  LEFT JOIN pitches p ON b.pitch_id = p.id 
                  WHERE p.owner_id = ? 
                  ORDER BY b.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION["user_id"]]);
        
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=owner_bookings_export.csv");
        $output = fopen("php://output", "w");
        // Windows excel uses UTF-8 BOM
        fputs($output, "﻿");
        fputcsv($output, ["ID", "Khach Hang", "San", "Ngay", "Gio Bat Dau", "Gio Ket Thuc", "Tong Tien", "Trang Thai"]);
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
    public function dashboard() {
        $database = new Database();
        $db = $database->getConnection();
        
        $owner_id = $_SESSION['user_id'];

        $pitch = new Pitch($db);
        $stmt_pitches = $pitch->readByOwner($owner_id);
        $total_pitches = $stmt_pitches->rowCount();

        $booking = new Booking($db);
        $stmt_bookings = $booking->readByOwner($owner_id);
        $bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);
        $total_bookings = count($bookings);
        
        $total_revenue = 0;
        foreach($bookings as $b) {
            if($b['status'] == 'confirmed' || $b['status'] == 'pending') {
                $total_revenue += $b['total_price'];
            }
        }

        // --- MATCHMAKING LOGIC ---
        $matchModel = new Matchmaking($db);
        $matches = $matchModel->getMatchesForOwner($_SESSION['user_id'])->fetchAll(PDO::FETCH_ASSOC);

        // Lọc Booking thông thường khỏi bảng (giấu các booking sinh ra từ Cáp Kèo)
        $regular_bookings = [];
        foreach($bookings as $b) {
            $is_match = false;
            foreach($matches as $m) {
                if ($b['pitch_id'] == $m['pitch_id'] && $b['booking_date'] == $m['play_date'] && $b['start_time'] == $m['start_time']) {
                    $is_match = true;
                    break;
                }
            }
            if (!$is_match) {
                $regular_bookings[] = $b;
            }
        }
        $bookings = $regular_bookings;
        
        // Cần Ranking model cho Dashboard
        require_once 'models/Ranking.php';
        $ranking = new Ranking($db);

        // Thống kê Doanh thu 7 ngày và Phân bổ khung giờ cho Chart.js
        $revenue_stats = $booking->getWeeklyRevenueStats($owner_id);
        $occupancy_stats = $booking->getHourlyOccupancyStats($owner_id);

        $page_title = "Dashboard Chủ Sân";
        $content = 'owner/dashboard.php';
        require_once 'views/layout.php';
    }

    public function approveMatchBooking() {
        if(isset($_GET['match_id'])) {
            $database = new Database();
            $db = $database->getConnection();
            $query = "SELECT m.* FROM matchmaking m WHERE id = ?";
            $s = $db->prepare($query);
            $s->execute([$_GET['match_id']]);
            if($m = $s->fetch(PDO::FETCH_ASSOC)) {
                $upd = "UPDATE bookings SET status = 'confirmed' WHERE pitch_id = ? AND booking_date = ? AND start_time = ?";
                $u = $db->prepare($upd);
                $u->execute([$m['pitch_id'], $m['play_date'], $m['start_time']]);
                header("Location: index.php?action=owner_dashboard&msg=booking_confirmed");
                exit;
            }
        }
        header("Location: index.php?action=owner_dashboard");
        exit;
    }

    public function reportResult() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['match_id'])) {
            $database = new Database();
            $db = $database->getConnection();
            $matchModel = new Matchmaking($db);
            
            if ($matchModel->reportResult($_POST['match_id'], $_POST['match_result'], $_SESSION['user_id'])) {
                header("Location: index.php?action=owner_dashboard&msg=reported");
            } else {
                header("Location: index.php?action=owner_dashboard&error=failed");
            }
            exit;
        }
    }

    public function approveHostPayment() {
        if (isset($_GET['id'])) {
            $database = new Database();
            $db = $database->getConnection();
            $query = "UPDATE matchmaking SET host_paid = 1 WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_GET['id']]);
            $redirect = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'owner_matches') !== false) 
                ? "index.php?action=owner_matches&msg=payment_approved" 
                : "index.php?action=owner_dashboard&msg=payment_approved";
            header("Location: " . $redirect);
            exit;
        }
    }

    public function approveGuestPayment() {
        if (isset($_GET['id'])) {
            $database = new Database();
            $db = $database->getConnection();
            $query = "UPDATE matchmaking SET guest_paid = 1 WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_GET['id']]);
            $redirect = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'owner_matches') !== false) 
                ? "index.php?action=owner_matches&msg=payment_approved" 
                : "index.php?action=owner_dashboard&msg=payment_approved";
            header("Location: " . $redirect);
            exit;
        }
    }

    public function cancelMatch() {
        if (isset($_GET['id'])) {
            $database = new Database();
            $db = $database->getConnection();
            $match = new Matchmaking($db);
            if($match->cancelMatchByOwner($_GET['id'], $_SESSION['user_id'])) {
                header("Location: index.php?action=owner_dashboard&msg=cancelled");
            } else {
                header("Location: index.php?action=owner_dashboard&error=cancel_failed");
            }
            exit;
        }
    }

    public function schedule() {
        $database = new Database();
        $db = $database->getConnection();
        
        $owner_id = $_SESSION['user_id'];
        $date = $_GET['date'] ?? date('Y-m-d');

        $pitch = new Pitch($db);
        $stmt_pitches = $pitch->readByOwner($owner_id);
        $pitches = $stmt_pitches->fetchAll(PDO::FETCH_ASSOC);

        $booking = new Booking($db);
        $bookings = $booking->readScheduleByOwner($owner_id, $date);

        // Map bookings to pitches
        $pitch_schedules = [];
        foreach($pitches as $p) {
            $pitch_schedules[$p['id']] = [
                'pitch' => $p,
                'bookings' => []
            ];
        }

        foreach($bookings as $b) {
            if(isset($pitch_schedules[$b['pitch_id']])) {
                $pitch_schedules[$b['pitch_id']]['bookings'][] = $b;
            }
        }

        $page_title = "Lịch Đặt Sân";
        $content = 'owner/schedule.php';
        require_once 'views/layout.php';
    }

    public function pitches() {
        $database = new Database();
        $db = $database->getConnection();
        
        $owner_id = $_SESSION['user_id'];

        $pitch = new Pitch($db);
        $stmt = $pitch->readByOwner($owner_id);
        $pitches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $page_title = "Quản Lý Sân";
        $content = 'owner/pitches.php';
        require_once 'views/layout.php';
    }

    public function addPitch() {
        $page_title = "Thêm Sân Bóng Mới";
        $content = 'owner/add_pitch.php';
        require_once 'views/layout.php';
    }

    public function storePitch() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();
            
            $pitch = new Pitch($db);
            
            $data = [
                'owner_id' => $_SESSION['user_id'],
                'name' => $_POST['name'] ?? '',
                'location' => $_POST['location'] ?? '',
                'district' => $_POST['district'] ?? '',
                'city' => $_POST['city'] ?? 'TP.HCM',
                'type' => $_POST['type'] ?? '',
                'quantity' => $_POST['quantity'] ?? 1,
                'price_per_hour' => $_POST['price_per_hour'] ?? 0,
                'description' => $_POST['description'] ?? '',
                'image_url' => $_POST['image_url'] ?? '',
                'latitude' => $_POST['latitude'] ?? null,
                'longitude' => $_POST['longitude'] ?? null
            ];

            // Handle file upload if provided safely
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed_extensions)) {
                    $upload_dir = 'assets/uploads/pitches/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $target_file = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
                        $data['image_url'] = $target_file;
                    }
                }
            }
            
            if ($pitch->create($data)) {
                header("Location: index.php?action=owner_pitches&msg=added");
            } else {
                header("Location: index.php?action=owner_add_pitch&error=1");
            }
            exit;
        }
    }

    public function editPitch() {
        if (!isset($_GET['id'])) {
            header("Location: index.php?action=owner_pitches");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        
        $pitch = new Pitch($db);
        $pitch_data = $pitch->readOne($_GET['id']);

        if (!$pitch_data || $pitch_data['owner_id'] != $_SESSION['user_id']) {
            header("Location: index.php?action=owner_pitches");
            exit;
        }

        $page_title = "Sửa Sân Bóng";
        $content = 'owner/edit_pitch.php';
        require_once 'views/layout.php';
    }

    public function updatePitch() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
            $database = new Database();
            $db = $database->getConnection();
            
            $pitch = new Pitch($db);
            $id = $_POST['id'];
            
            // Verify ownership first
            $pitch_data = $pitch->readOne($id);
            if (!$pitch_data || $pitch_data['owner_id'] != $_SESSION['user_id']) {
                header("Location: index.php?action=owner_pitches");
                exit;
            }
            
            $data = [
                'owner_id' => $_SESSION['user_id'],
                'name' => $_POST['name'] ?? '',
                'location' => $_POST['location'] ?? '',
                'district' => $_POST['district'] ?? '',
                'city' => $_POST['city'] ?? 'TP.HCM',
                'type' => $_POST['type'] ?? '',
                'quantity' => $_POST['quantity'] ?? 1,
                'price_per_hour' => $_POST['price_per_hour'] ?? 0,
                'description' => $_POST['description'] ?? '',
                'image_url' => $_POST['image_url'] ?? '',
                'latitude' => $_POST['latitude'] ?? null,
                'longitude' => $_POST['longitude'] ?? null
            ];

            // Handle file upload if provided safely
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed_extensions)) {
                    $upload_dir = 'assets/uploads/pitches/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $target_file = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
                        $data['image_url'] = $target_file;
                    }
                }
            }
            
            if ($pitch->update($data, $id)) {
                header("Location: index.php?action=owner_pitches&msg=updated");
            } else {
                header("Location: index.php?action=owner_edit_pitch&id=$id&error=1");
            }
            exit;
        }
    }

    public function deletePitch() {
        if (isset($_GET['id'])) {
            $database = new Database();
            $db = $database->getConnection();
            $pitch = new Pitch($db);
            $pitchId = (int)$_GET['id'];
            $ownerId = (int)$_SESSION['user_id'];

            if ($pitch->delete($pitchId, $ownerId)) {
                header("Location: index.php?action=owner_pitches&msg=deleted");
            } else {
                header("Location: index.php?action=owner_pitches&error=delete_failed");
            }
            exit;
        }
        header("Location: index.php?action=owner_pitches");
        exit;
    }

    public function updateBookingStatus() {
        if(isset($_GET['id']) && isset($_GET['status'])) {
            $database = new Database();
            $db = $database->getConnection();
            
            $booking = new Booking($db);
            $bookingId = $_GET['id'];
            $newStatus = $_GET['status'];

            if ($newStatus == 'confirmed') {
                // Fetch current booking data to check overlapping
                $query = "SELECT pitch_id, booking_date, start_time, end_time FROM bookings WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $bookingId);
                $stmt->execute();
                $bData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($bData) {
                    $pitch = new Pitch($db);
                    $pitchData = $pitch->readOne($bData['pitch_id']);

                    $overlapping = $booking->getConfirmedOverlappingCount($bData['pitch_id'], $bData['booking_date'], $bData['start_time'], $bData['end_time'], false);
                    
                    if ($overlapping >= $pitchData['quantity']) {
                        // Sân đã đầy, báo lỗi
                        die("<h3 style='color:red;'>Không thể xác nhận: Sân đã đạt tối đa số lượng (Tối đa: {$pitchData['quantity']}) vào khung giờ này.</h3><br><a href='index.php?action=owner_dashboard'>Quay lại</a>");
                    }
                }
            }

            // In a real app we should verify if this booking belongs to a pitch owned by this owner
            $booking->updateStatus($bookingId, $newStatus);

            if ($newStatus == 'cancelled') {
                $q = "SELECT user_id, booking_date, start_time FROM bookings WHERE id = ? AND user_id IS NOT NULL";
                $s = $db->prepare($q);
                $s->execute([$bookingId]);
                if($bData = $s->fetch(PDO::FETCH_ASSOC)) {
                    $owner_id = $_SESSION['user_id'];
                    $content = "Lịch đặt sân của bạn vào ngày " . date('d/m', strtotime($bData['booking_date'])) . " lúc " . substr($bData['start_time'], 0, 5) . " đã bị Chủ sân HỦY. Tiền sân (nếu đã thanh toán) sẽ được Hoàn lại (Refund) tự động vào ví của bạn.";
                    $msg_q = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
                    $msg_s = $db->prepare($msg_q);
                    $msg_s->execute([$owner_id, $bData['user_id'], $content]);
                }
            }
        }
        header("Location: index.php?action=owner_dashboard");
        exit;
    }

    public function reviews() {
        $database = new Database();
        $db = $database->getConnection();
        
        $owner_id = $_SESSION['user_id'];

        $review = new Review($db);
        $stmt_reviews = $review->readByOwner($owner_id);
        $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

        $page_title = "Đánh Giá Của Khách";
        $content = 'owner/reviews.php';
        require_once 'views/layout.php';
    }

    public function services() {
        $database = new Database();
        $db = $database->getConnection();

        $owner_id = $_SESSION['user_id'];
        $pitch = new Pitch($db);
        $ownerPitches = $pitch->readByOwner($owner_id)->fetchAll(PDO::FETCH_ASSOC);

        $service = new Service($db);
        $services = $service->readByPitch();

        $page_title = "Quản Lý Dịch Vụ Sân Cầu Lông";
        $content = 'owner/services.php';
        require_once 'views/layout.php';
    }

    public function storeService() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            $service = new Service($db);
            $data = [
                'pitch_id' => !empty($_POST['pitch_id']) ? $_POST['pitch_id'] : null,
                'name' => $_POST['name'],
                'category' => $_POST['category'] ?? 'beverage',
                'price' => $_POST['price'],
                'unit' => $_POST['unit'] ?? 'Cái',
                'stock' => $_POST['stock'] ?? 100
            ];

            $service->create($data);
            header("Location: index.php?action=owner_services&msg=created");
            exit;
        }
    }

    public function deleteService() {
        if (isset($_GET['id'])) {
            $database = new Database();
            $db = $database->getConnection();

            $service = new Service($db);
            $service->delete($_GET['id']);
            header("Location: index.php?action=owner_services&msg=deleted");
            exit;
        }
    }

    public function createWalkinBooking() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            $booking = new Booking($db);
            $pitch_id = $_POST['pitch_id'] ?? null;
            $customer_name = $_POST['customer_name'] ?? 'Khách vãng lai';
            $customer_phone = $_POST['customer_phone'] ?? '0900000000';
            $booking_date = $_POST['booking_date'] ?? date('Y-m-d');
            $start_time = $_POST['start_time'] ?? '18:00';
            $end_time = $_POST['end_time'] ?? '19:00';
            $payment_method = $_POST['payment_method'] ?? 'cash';
            $payment_status = $_POST['payment_status'] ?? 'paid';
            $booking_type = $_POST['booking_type'] ?? 'walk_in';
            $services_note = $_POST['services_note'] ?? 'Đơn đặt trực tiếp tại quầy Lễ tân';

            if (strlen($start_time) == 5) $start_time .= ':00';
            if (strlen($end_time) == 5) $end_time .= ':00';

            // Check overlap
            $pitchModel = new Pitch($db);
            $pitchData = $pitchModel->readOne($pitch_id);
            if (!$pitchData) {
                header("Location: index.php?action=owner_schedule&date={$booking_date}&error=invalid_pitch");
                exit;
            }

            $overlapping = $booking->getConfirmedOverlappingCount($pitch_id, $booking_date, $start_time, $end_time, true);
            if ($overlapping >= $pitchData['quantity']) {
                header("Location: index.php?action=owner_schedule&date={$booking_date}&error=overlapped");
                exit;
            }

            // Calculate price based on duration
            $start_dt = new DateTime($booking_date . ' ' . $start_time);
            $end_dt = new DateTime($booking_date . ' ' . $end_time);
            $hours = max(0.5, ($end_dt->getTimestamp() - $start_dt->getTimestamp()) / 3600);
            $total_price = $_POST['total_price'] ?? ($hours * $pitchData['price_per_hour']);

            $booking->pitch_id = $pitch_id;
            $booking->user_id = null;
            $booking->customer_name = $customer_name;
            $booking->customer_phone = $customer_phone;
            $booking->booking_date = $booking_date;
            $booking->start_time = $start_time;
            $booking->end_time = $end_time;
            $booking->total_price = $total_price;
            $booking->status = 'confirmed';
            $booking->payment_method = $payment_method;
            $booking->payment_status = $payment_status;
            $booking->services_note = $services_note;
            $booking->booking_type = $booking_type;
            $booking->created_by_user_id = $_SESSION['user_id'] ?? null;

            if ($booking->create()) {
                header("Location: index.php?action=owner_schedule&date={$booking_date}&msg=walkin_created");
            } else {
                header("Location: index.php?action=owner_schedule&date={$booking_date}&error=create_failed");
            }
            exit;
        }
    }

    public function staff() {
        $database = new Database();
        $db = $database->getConnection();
        $owner_id = $_SESSION['user_id'];

        $pitchModel = new Pitch($db);
        $pitches = $pitchModel->readByOwner($owner_id)->fetchAll(PDO::FETCH_ASSOC);

        $userModel = new User($db);
        $staff_list = $userModel->readStaffByOwner($owner_id);

        $page_title = "Quản Lý Nhân Viên Cụm Sân";
        $content = 'owner/staff.php';
        require_once 'views/layout.php';
    }

    public function createStaff() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            $user = new User($db);
            $user->username = $_POST['username'] ?? '';
            $user->password = $_POST['password'] ?? '123456';
            $user->name = $_POST['name'] ?? '';
            $user->phone = $_POST['phone'] ?? '';
            $user->role = 'staff';
            $user->owner_id = $_SESSION['user_id'];
            $user->assigned_pitch_id = !empty($_POST['assigned_pitch_id']) ? $_POST['assigned_pitch_id'] : null;

            if ($user->usernameExists()) {
                header("Location: index.php?action=owner_staff&error=username_exists");
                exit;
            }

            if ($user->create()) {
                header("Location: index.php?action=owner_staff&msg=created");
            } else {
                header("Location: index.php?action=owner_staff&error=create_failed");
            }
            exit;
        }
    }

    public function assignStaff() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['staff_id'])) {
            $database = new Database();
            $db = $database->getConnection();

            $userModel = new User($db);
            $staff_id = (int)$_POST['staff_id'];
            $pitch_id = !empty($_POST['assigned_pitch_id']) ? (int)$_POST['assigned_pitch_id'] : null;
            $owner_id = $_SESSION['user_id'];

            if ($userModel->assignStaffToPitch($staff_id, $owner_id, $pitch_id)) {
                header("Location: index.php?action=owner_staff&msg=assigned");
            } else {
                header("Location: index.php?action=owner_staff&error=assign_failed");
            }
            exit;
        }
    }

    public function removeStaff() {
        if (isset($_GET['id'])) {
            $database = new Database();
            $db = $database->getConnection();

            $userModel = new User($db);
            $staff_id = (int)$_GET['id'];

            if ($userModel->removeStaffAssignment($staff_id)) {
                header("Location: index.php?action=owner_staff&msg=removed");
            } else {
                header("Location: index.php?action=owner_staff&error=remove_failed");
            }
            exit;
        }
    }
}
?>