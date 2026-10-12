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
            <div id="vietqr-container" class="mb-6 p-5 border-2 border-dashed border-cyan-500/40 rounded-2xl bg-cyan-950/20 text-center relative overflow-hidden transition-all duration-300">
                 <div class="flex items-center justify-between mb-3">
                     <span class="text-xs font-black tracking-wider text-cyan-400 uppercase">Thanh Toán Trực Tuyến VietQR</span>
                     <span id="payment-status-badge" class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 animate-pulse">
                         ⏳ Chờ Quét Mã
                     </span>
                 </div>

                 <div class="inline-block p-3 bg-white rounded-2xl shadow-xl border border-white/10 my-1">
                    <img id="qr-image" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=BADMINTON_<?php echo urlencode($bookingData['checkin_code'] ?? 'CKIN'); ?>_<?php echo $bookingData['total_price']; ?>" alt="VietQR Code" class="h-36 w-36 object-contain mx-auto">
                 </div>

                 <p class="text-xs text-white/70 mt-3 font-medium">Nội dung chuyển khoản: <span class="font-mono font-black text-[#d8ff3e]"><?php echo htmlspecialchars($bookingData['checkin_code'] ?? 'CKIN'); ?></span></p>

                 <!-- Sandbox Simulation Button for Defense Demo -->
                 <div class="mt-4 pt-3 border-t border-white/10">
                     <button type="button" id="btn-simulate-pay" onclick="simulateQrPayment(<?php echo (int)($bookingData['booking_id'] ?? 0); ?>)" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black py-2.5 px-4 text-xs uppercase tracking-wider shadow-lg transition transform hover:-translate-y-0.5 cursor-pointer">
                         <span>⚡</span> Mô Phỏng Quét QR Thành Công (Sandbox Demo)
                     </button>
                     <p class="text-[10px] text-white/40 mt-1.5 italic">* Dành cho buổi bảo vệ đồ án: Bấm để giả lập giao dịch ngân hàng thành công tức thì</p>
                 </div>
            </div>
            <?php else: ?>
            <div class="mb-6 p-4 border border-green-500/30 rounded-2xl bg-green-500/10 text-center">
                 <p class="text-xs text-green-400 font-bold">💵 Bạn đã chọn thanh toán tiền mặt tại quầy trực tiếp.</p>
                 <p class="text-[11px] text-white/60 mt-1">Vui lòng thanh toán cho nhân viên khi check-in vào sân.</p>
            </div>
            <?php endif; ?>

            <?php 
                // Clear session after displaying
                unset($_SESSION['last_booking']);
            endif; 
            ?>

            <div class="flex gap-3">
                <a href="index.php?action=my_bookings" class="w-1/2 inline-flex items-center justify-center bg-[#0b1116]/10 hover:bg-[#0b1116]/20 border border-white/10 text-white font-bold py-3.5 px-4 rounded-xl text-sm transition-colors">
                    Xem Lịch Sử
                </a>
                <a href="index.php" class="w-1/2 inline-flex items-center justify-center bg-[#d8ff3e] hover:bg-[#c2ea2d] text-slate-950 font-black py-3.5 px-4 rounded-xl text-sm transition-colors shadow-lg">
                    Về Trang Chủ
                </a>
            </div>
        </div>
    </div>

    <script>
    function simulateQrPayment(bookingId) {
        if (!bookingId) {
            alert('Không tìm thấy mã đơn đặt sân.');
            return;
        }

        const btn = document.getElementById('btn-simulate-pay');
        const badge = document.getElementById('payment-status-badge');
        const container = document.getElementById('vietqr-container');

        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> Đang xác thực giao dịch ngân hàng...';

        fetch('index.php?action=api_simulate_payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_id=' + encodeURIComponent(bookingId)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Success UI transformation
                badge.className = 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40';
                badge.innerHTML = '✅ ĐÃ THANH TOÁN (PAID)';

                container.className = 'mb-6 p-5 border-2 border-emerald-500/60 rounded-2xl bg-emerald-950/30 text-center relative overflow-hidden transition-all duration-300 shadow-[0_0_30px_rgba(16,185,129,0.2)]';

                btn.className = 'w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500/30 text-emerald-300 font-bold py-2.5 px-4 text-xs uppercase cursor-default';
                btn.innerHTML = '🎉 Giao Dịch Thành Công lúc ' + (data.paid_at || '');

                alert('🎉 XÁC NHẬN THANH TOÁN THÀNH CÔNG!\n\nHệ thống đã nhận được tiền qua VietQR. Mã đơn và lịch thi đấu của bạn đã được chốt chính thức.');
            } else {
                alert('Có lỗi: ' + (data.message || 'Không thể cập nhật'));
                btn.disabled = false;
                btn.innerHTML = '<span>⚡</span> Thử lại mô phỏng';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi kết nối máy chủ!');
            btn.disabled = false;
            btn.innerHTML = '<span>⚡</span> Thử lại';
        });
    }
    </script>
</body>
</html>
