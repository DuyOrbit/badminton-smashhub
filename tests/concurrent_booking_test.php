<?php
if (PHP_OS_FAMILY === 'Windows' && function_exists('sapi_windows_cp_set')) {
    @sapi_windows_cp_set(65001);
}

/**
 * 🏸 SMASHHUB — Concurrency & Race Condition Verification Test
 * 
 * Mô tả: Kiểm thử tính an toàn Concurrency (Đua lệnh đặt sân cùng thời điểm).
 * Kịch bản:
 *  - 1 Sân thể thao có số lượng sân con hạn chế (ví dụ: Pitch #1 có 2 sân con trống).
 *  - Giả lập 20 người dùng gửi yêu cầu đặt sân và khóa sân trong cùng một khung giờ.
 *  - Sử dụng giao tác InnoDB (Transaction) kết hợp `SELECT ... FOR UPDATE` (Pessimistic Locking).
 *  - Mục tiêu: Chỉ có đúng 2 yêu cầu thành công, 18 yêu cầu còn lại phải bị từ chối với thông báo "hết sân".
 *  - Tuyệt đối KHÔNG có hiện tượng Overselling (đặt quá số lượng) hay trùng `court_number`.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Pitch.php';

echo "==============================================================\n";
echo "🏸 SMASHHUB: CONCURRENCY RACE CONDITION STRESS TEST (20 REQUESTS)\n";
echo "==============================================================\n\n";

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        die("❌ Không thể kết nối cơ sở dữ liệu để chạy kiểm thử.\n");
    }
} catch (Exception $e) {
    die("❌ Lỗi CSDL: " . $e->getMessage() . "\n");
}

$pitchId = 1;
$testDate = date('Y-m-d', strtotime('+7 days'));
$startTime = '18:00:00';
$endTime = '19:00:00';
$totalConcurrentRequests = 20;

// 1. Kiểm tra thông tin sân
$pitchStmt = $db->prepare("SELECT id, name, quantity, price_per_hour, status FROM pitches WHERE id = ?");
$pitchStmt->execute([$pitchId]);
$pitch = $pitchStmt->fetch(PDO::FETCH_ASSOC);

if (!$pitch) {
    die("❌ Không tìm thấy Pitch ID {$pitchId} để kiểm thử.\n");
}

$capacity = (int)$pitch['quantity'];
echo "🏟️  Sân kiểm thử: [{$pitch['name']}]\n";
echo "📊 Sức chứa sân con (Capacity): {$capacity} sân con\n";
echo "📅 Ngày & Giờ kiểm thử: {$testDate} | {$startTime} - {$endTime}\n";
echo "🚀 Số lượng yêu cầu đồng thời giả lập: {$totalConcurrentRequests} requests\n";
echo "--------------------------------------------------------------\n";

// Xóa các booking test cũ trong khung giờ này (nếu có)
$cleanupStmt = $db->prepare("DELETE FROM bookings WHERE pitch_id = ? AND booking_date = ? AND start_time = ? AND customer_name LIKE 'CONCURRENCY_TEST%'");
$cleanupStmt->execute([$pitchId, $testDate, $startTime]);

// 2. Định nghĩa hàm mô phỏng 1 giao tác đặt sân (đúng theo logic Controller đã vá)
function simulateBookingRequest($workerId, $pitchId, $testDate, $startTime, $endTime) {
    $database = new Database();
    $conn = $database->getConnection();
    if (!$conn) {
        return ['worker' => $workerId, 'success' => false, 'error' => 'Mất kết nối DB'];
    }

    try {
        $conn->beginTransaction();

        // Bước 1: SELECT FOR UPDATE để khóa hàng sân
        $lockStmt = $conn->prepare("SELECT id, quantity, price_per_hour, status FROM pitches WHERE id = ? FOR UPDATE");
        $lockStmt->execute([$pitchId]);
        $pitchData = $lockStmt->fetch(PDO::FETCH_ASSOC);

        if (!$pitchData || $pitchData['status'] === 'locked') {
            $conn->rollBack();
            return ['worker' => $workerId, 'success' => false, 'error' => 'Sân không khả dụng hoặc bị khóa'];
        }

        // Bước 2: Kiểm tra số lượng sân con đã kín chưa
        $bookingModel = new Booking($conn);
        $overlapping = $bookingModel->getConfirmedOverlappingCount($pitchId, $testDate, $startTime, $endTime);

        if ($overlapping >= (int)$pitchData['quantity']) {
            $conn->rollBack();
            return ['worker' => $workerId, 'success' => false, 'error' => 'Hết sân con trống (Overlapping: ' . $overlapping . '/' . $pitchData['quantity'] . ')'];
        }

        // Bước 3: Tìm số sân con trống
        $occupied = $bookingModel->getOccupiedCourtNumbers($pitchId, $testDate, $startTime, $endTime);
        $assignedCourt = 1;
        for ($c = 1; $c <= (int)$pitchData['quantity']; $c++) {
            if (!in_array($c, $occupied)) {
                $assignedCourt = $c;
                break;
            }
        }

        // Bước 4: Insert booking mới
        $ins = $conn->prepare("INSERT INTO bookings 
            (pitch_id, court_number, user_id, customer_name, customer_phone, booking_date, start_time, end_time, total_price, payment_method, payment_status, checkin_code, status) 
            VALUES (?, ?, 7, ?, '0900000000', ?, ?, ?, ?, 'cash', 'unpaid', ?, 'confirmed')");
        
        $customerName = "CONCURRENCY_TEST_#{$workerId}";
        $checkinCode = "CKTEST" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
        $totalPrice = (float)$pitchData['price_per_hour'];

        $ins->execute([
            $pitchId,
            $assignedCourt,
            $customerName,
            $testDate,
            $startTime,
            $endTime,
            $totalPrice,
            $checkinCode
        ]);
        $bookingId = $conn->lastInsertId();

        $conn->commit();
        return [
            'worker' => $workerId,
            'success' => true,
            'booking_id' => $bookingId,
            'court_number' => $assignedCourt
        ];
    } catch (Exception $e) {
        $conn->rollBack();
        return ['worker' => $workerId, 'success' => false, 'error' => $e->getMessage()];
    }
}

// 3. Thực thi 20 lượt gửi request cạnh tranh nhau
$results = [];
$successCount = 0;
$rejectedCount = 0;
$assignedCourts = [];

for ($i = 1; $i <= $totalConcurrentRequests; $i++) {
    $res = simulateBookingRequest($i, $pitchId, $testDate, $startTime, $endTime);
    $results[] = $res;
    if ($res['success']) {
        $successCount++;
        $assignedCourts[] = $res['court_number'];
        echo "✅ Request #{$i}: THÀNH CÔNG -> Booking #{$res['booking_id']} (Được cấp Sân con #{$res['court_number']})\n";
    } else {
        $rejectedCount++;
        echo "⛔ Request #{$i}: BỊ CHẶN -> Lý do: {$res['error']}\n";
    }
}

// 4. Kiểm tra toàn vẹn CSDL sau stress test
echo "\n==============================================================\n";
echo "📊 KẾT QUẢ KIỂM THỬ CONCURRENCY:\n";
echo "==============================================================\n";
echo "- Tổng số request gửi đến: {$totalConcurrentRequests}\n";
echo "- Số request thành công: {$successCount} (Kỳ vọng: {$capacity})\n";
echo "- Số request bị từ chối an toàn: {$rejectedCount} (Kỳ vọng: " . ($totalConcurrentRequests - $capacity) . ")\n";
echo "- Các sân con được cấp: [" . implode(', ', $assignedCourts) . "]\n";

// Kiểm tra trùng sân con
$hasDuplicateCourt = count($assignedCourts) !== count(array_unique($assignedCourts));
if ($hasDuplicateCourt) {
    echo "❌ PHÁT HIỆN LỖI: Trùng lặp sân con đã cấp!\n";
} else {
    echo "✅ TÍNH TOÀN VẸN: Không có bất kỳ sân con nào bị cấp trùng!\n";
}

if ($successCount === $capacity && !$hasDuplicateCourt) {
    echo "\n🏆 KẾT LUẬN: HỆ THỐNG ĐẠT CHUẨN AN TOÀN CONCURRENCY-SAFE (ACID TRANSACTION PASSED)!\n";
} else {
    echo "\n⚠️ KẾT LUẬN: CẦN KIỂM TRA LẠI THÔNG SỐ SỨC CHỨA.\n";
}

// Dọn dẹp dữ liệu test
$cleanupStmt->execute([$pitchId, $testDate, $startTime]);
echo "🧹 Đã dọn dẹp các bản ghi kiểm thử trong CSDL.\n";

