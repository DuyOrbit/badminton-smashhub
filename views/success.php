<?php
$page_title = "Đặt Sân Thành Công";
$bookingData = isset($_SESSION['last_booking']) ? $_SESSION['last_booking'] : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PitchConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#0b1116]/5 flex flex-col min-h-screen">

    <div class="flex-grow flex items-center justify-center p-4">
        <div class="bg-[#0b1116] rounded-3xl shadow-none max-w-lg w-full p-8 md:p-10 text-center relative overflow-hidden">
            <!-- Top Decoration -->
            <div class="absolute top-0 left-0 w-full h-3 bg-gradient-to-r from-green-400 to-green-600"></div>
            
            <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-500/10 mb-6">
                <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h2 class="text-3xl font-black text-white mb-2">Đặt Sân Thành Công!</h2>
            <p class="text-white/60 mb-6 font-medium">Yêu cầu của bạn đã được hệ thống BadmintonConnect ghi nhận.</p>

            <?php if($bookingData): ?>
            <!-- Check-in Code Card -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-4 rounded-2xl mb-6 shadow-lg text-center">
                <span class="text-xs uppercase tracking-widest font-bold text-green-100">Mã Check-in Nhận Sân</span>
                <div class="text-3xl font-black tracking-widest my-1 font-mono text-white"><?php echo htmlspecialchars($bookingData['checkin_code'] ?? 'CKIN-XXXX'); ?></div>
                <p class="text-[11px] text-green-100">Đưa mã này cho nhân viên bàn trực để vào sân nhanh chóng</p>
            </div>

            <div class="bg-[#0b1116]/5 rounded-2xl p-6 text-left mb-6 border border-white/5 shadow-inner">
                <h3 class="text-xs font-bold text-white/40 uppercase tracking-wider mb-4 border-b border-white/10 pb-2">Hóa Đơn Chi Tiết</h3>
                <ul class="space-y-3">
                    <li class="flex justify-between items-center"><span class="text-white/60 text-sm">Họ và tên</span> <span class="font-bold text-white"><?php echo htmlspecialchars($bookingData['name']); ?></span></li>
                    <li class="flex justify-between items-center"><span class="text-white/60 text-sm">Tên sân</span> <span class="font-bold text-white"><?php echo htmlspecialchars($bookingData['pitch_name']); ?></span></li>
                    <li class="flex justify-between items-center"><span class="text-white/60 text-sm">Ngày chơi cầu</span> <span class="font-bold text-white"><?php echo date('d/m/Y', strtotime($bookingData['date'])); ?></span></li>
                    <li class="flex justify-between items-center"><span class="text-white/60 text-sm">Khung giờ</span> <span class="font-bold text-white bg-[#0b1116]/10 px-2 py-1 rounded text-xs"><?php echo htmlspecialchars($bookingData['time']); ?></span></li>
                    
                    <?php if(!empty($bookingData['services_note'])): ?>
                    <li class="flex justify-between items-start pt-1">
                        <span class="text-white/60 text-sm">Dịch vụ thêm</span>
                        <span class="font-bold text-white text-right text-xs max-w-[200px]"><?php echo htmlspecialchars($bookingData['services_note']); ?></span>
                    </li>
                    <?php endif; ?>

                    <li class="flex justify-between items-center pt-1">
                        <span class="text-white/60 text-sm">Hình thức thanh toán</span>
                        <span class="font-bold text-xs px-2 py-1 rounded <?php echo ($bookingData['payment_method'] ?? '') == 'vietqr' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                            <?php echo ($bookingData['payment_method'] ?? '') == 'vietqr' ? '📱 VietQR / Chuyển khoản' : '💵 Tiền mặt tại sân'; ?>
                        </span>
                    </li>
                    
                    <li class="flex justify-between items-center pt-4 mt-2 border-t border-white/10 border-dashed">
                        <span class="font-bold text-white">Tổng Thanh Toán</span> 
                        <span class="font-black text-green-400 text-2xl"><?php echo number_format($bookingData['total_price'], 0, ',', '.'); ?>đ</span>
                    </li>
                </ul>
            </div>
            
            <!-- Payment QR Section -->
            <?php if(($bookingData['payment_method'] ?? '') == 'vietqr'): ?>
            <div class="mb-6 p-4 border-2 border-dashed border-blue-200 rounded-2xl bg-blue-500/10/50">
                 <p class="text-sm text-blue-900 mb-3 font-bold">Thanh Toán Trực Tuyến Qua VietQR</p>
                 <div class="inline-block p-2 bg-[#0b1116] rounded-xl shadow-none border border-white/5">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=BADMINTON_<?php echo urlencode($bookingData['checkin_code'] ?? 'CKIN'); ?>_<?php echo $bookingData['total_price']; ?>" alt="VietQR Code" class="h-40 w-40 object-contain mx-auto">
                 </div>
                 <p class="text-xs text-blue-700 mt-3 font-medium">Nội dung chuyển khoản: <span class="font-mono font-bold"><?php echo htmlspecialchars($bookingData['checkin_code'] ?? 'CKIN'); ?></span></p>
            </div>
            <?php else: ?>
            <div class="mb-6 p-4 border border-green-200 rounded-2xl bg-green-500/10/60 text-center">
                 <p class="text-xs text-green-800 font-bold">💵 Bạn đã chọn thanh toán tiền mặt tại quầy trực tiếp đón.</p>
                 <p class="text-[11px] text-green-400 mt-1">Vui lòng thanh toán cho nhân viên khi check-in vào sân.</p>
            </div>
            <?php endif; ?>

            <?php 
                // Clear session after displaying
                unset($_SESSION['last_booking']);
            endif; 
            ?>

            <div class="flex gap-3">
                <a href="index.php?action=my_bookings" class="w-1/2 inline-flex items-center justify-center bg-[#0b1116]/10 hover:bg-[#0b1116]/10 text-white font-bold py-3.5 px-4 rounded-xl text-sm transition-colors">
                    Xem Lịch Sử
                </a>
                <a href="index.php" class="w-1/2 inline-flex items-center justify-center bg-gray-900 hover:bg-gray-800 text-white font-bold py-3.5 px-4 rounded-xl text-sm transition-colors shadow-none">
                    Trang Chủ
                </a>
            </div>
        </div>
    </div>
</body>
</html>
