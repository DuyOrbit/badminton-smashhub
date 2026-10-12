<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Dashboard Chủ Sân</h1>
            <p class="mt-2 text-white/60">Quản lý sân cầu lông và doanh thu của bạn.</p>
        </div>
        <div class="flex space-x-3">
            <a href="index.php?action=owner_services" class="bg-[#121b21] border border-amber-300 text-amber-700 hover:bg-amber-50 px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Dịch Vụ Sân</a>
            <a href="index.php?action=owner_schedule" class="bg-blue-600 border border-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Lịch Đặt Sân</a>
            <a href="index.php?action=owner_pitches" class="bg-[#121b21] border border-white/20 text-white/80 hover:bg-[#0b1116] px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Quản Lý Sân</a>
            <a href="index.php?action=owner_reviews" class="bg-[#121b21] border border-green-300 text-green-700 hover:bg-green-500/20 px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Quản Lý Đánh Giá</a>
        </div>
    </div>

    <!-- Notifications -->
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'payment_approved'): ?>
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-none flex items-center">
            <svg class="w-5 h-5 text-emerald-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-emerald-800 font-bold text-sm">Xác nhận thu tiền mặt thành công! Trạng thái thanh toán đã được cập nhật.</span>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'reported'): ?>
        <div class="mb-6 bg-green-500/20 border-l-4 border-green-500 p-4 rounded-r-xl shadow-none flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-green-800 font-bold text-sm">Xác nhận kết quả trận đấu và cập nhật điểm xếp hạng thành công!</span>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'booking_confirmed'): ?>
        <div class="mb-6 bg-green-500/20 border-l-4 border-green-500 p-4 rounded-r-xl shadow-none flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-green-800 font-bold text-sm">Đã duyệt và chốt khóa giờ sân thi đấu thành công!</span>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'cancelled'): ?>
        <div class="mb-6 bg-blue-500/20 border-l-4 border-blue-500 p-4 rounded-r-xl shadow-none flex items-center">
            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-blue-800 font-bold text-sm">Đã hủy trận cáp kèo và giải phóng sân thành công!</span>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'failed'): ?>
        <div class="mb-6 bg-red-500/20 border-l-4 border-red-500 p-4 rounded-r-xl shadow-none flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-red-800 font-bold text-sm">Có lỗi xảy ra khi cập nhật kết quả. Vui lòng thử lại!</span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 shadow-none text-white">
            <p class="text-blue-100 font-medium mb-1">Tổng Số Sân Của Bạn</p>
            <p class="text-4xl font-black mb-4"><?php echo number_format($total_pitches); ?></p>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 shadow-none text-white">
            <p class="text-purple-100 font-medium mb-1">Tổng Lượt Đặt</p>
            <p class="text-4xl font-black mb-4"><?php echo number_format($total_bookings); ?></p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 shadow-none text-white">
            <p class="text-green-100 font-medium mb-1">Doanh Thu Ước Tính</p>
            <p class="text-3xl font-black mb-4"><?php echo number_format($total_revenue, 0, ',', '.'); ?>đ</p>
        </div>
    </div>

    <!-- Booking List -->
    <div class="bg-[#121b21] rounded-2xl shadow-none border border-white/5 overflow-hidden">
        <div class="px-6 py-5 border-b border-white/5 bg-[#0b1116]">
            <h3 class="text-lg leading-6 font-bold text-white">Đơn đặt sân mới nhất</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
                <thead class="bg-[#121b21]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Khách Hàng</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Sân</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Thời Gian Đá</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Tổng Tiền</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white/60 uppercase tracking-wider">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="bg-[#121b21] divide-y divide-white/5">
                    <?php if(count($bookings) > 0): ?>
                        <?php foreach($bookings as $b): ?>
                        <tr class="hover:bg-[#0b1116] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-white"><?php echo htmlspecialchars($b['customer_name']); ?></div>
                                <div class="text-sm text-white/60"><a href="tel:<?php echo htmlspecialchars($b['customer_phone']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($b['customer_phone']); ?></a></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($b['pitch_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm border border-white/10 bg-[#0b1116]/10 p-1.5 rounded text-center font-medium"><?php echo date('d/m/Y', strtotime($b['booking_date'])); ?><br><span class="text-green-600 font-bold"><?php echo substr($b['start_time'],0,5); ?> - <?php echo substr($b['end_time'],0,5); ?></span></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-white">
                                <?php echo number_format($b['total_price'], 0, ',', '.'); ?>đ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if($b['status'] == 'confirmed'): ?>
                                    <div class="flex flex-col gap-1 items-center">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">Đã Xác Nhận</span>
                                        <a href="index.php?action=owner_update_booking&id=<?php echo $b['id']; ?>&status=cancelled" onclick="return confirm('Xác nhận HỦY lịch này và HOÀN TIỀN cho khách?');" class="text-[10px] text-red-500 hover:text-red-700 font-bold underline mt-1">Hủy & Hoàn Tiền</a>
                                    </div>
                                <?php elseif($b['status'] == 'pending'): ?>
                                    <div class="flex flex-col gap-2 items-center">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800">Đang Chờ</span>
                                        <div class="flex gap-1 mt-1">
                                            <a href="index.php?action=owner_update_booking&id=<?php echo $b['id']; ?>&status=confirmed" class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded shadow-none focus:outline-none focus:ring-2 focus:ring-green-400">Duyệt</a>
                                            <a href="index.php?action=owner_update_booking&id=<?php echo $b['id']; ?>&status=cancelled" class="text-xs bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded shadow-none focus:outline-none focus:ring-2 focus:ring-red-400" onclick="return confirm('Bạn chắc chắn muốn hủy đơn này?');">Hủy</a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">Đã Hủy</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-white/60">Sân của bạn chưa có lượt đặt nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Matchmaking Integration -->
    <div class="mt-12 mb-8 border-b border-white/10 pb-5">
        <h2 class="text-2xl font-extrabold text-white tracking-tight">Quản Lý Cáp Kèo & Chấm Điểm</h2>
        <p class="mt-1 text-white/60 text-base">Xác nhận tỉ số sau trận đấu căng thẳng. Thu xếp hóa đơn Split Bill (chia đôi) của 2 đội!</p>
    </div>

    <div class="space-y-6 flex flex-col items-center mb-10">
        <?php if(count($matches) > 0): ?>
            <?php foreach($matches as $m): 
                $start = strtotime($m['start_time']);
                $end = strtotime($m['end_time']);
                $hours = ($end - $start) / 3600;
                $half_base = ($hours * $m['price_per_hour']) / 2;
                
                $hostDiscount = $ranking->getActiveDiscount($m['user_id']);
                $guestDiscount = $ranking->getActiveDiscount($m['opponent_id']);
                
                $hostFinalPrice = $half_base * ((100 - $hostDiscount) / 100);
                $guestFinalPrice = $half_base * ((100 - $guestDiscount) / 100);
            ?>
            <div class="bg-[#121b21] rounded-2xl shadow-none border border-white/5 overflow-hidden w-full relative">
                <!-- Status Badge -->
                <div class="absolute top-4 right-4 z-10">
                    <?php if($m['status'] == 'cancelled'): ?>
                        <span class="bg-red-100 text-red-800 text-xs font-black uppercase px-3 py-1.5 rounded-md border border-red-200 shadow-none">Đã Hủy Bỏ</span>
                    <?php elseif($m['match_result'] == 'pending'): ?>
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-black uppercase px-3 py-1.5 rounded-md border border-yellow-200 shadow-none animate-pulse">Chờ Chấm Điểm</span>
                    <?php else: ?>
                        <span class="bg-[#0b1116]/10 text-white/70 text-xs font-bold px-3 py-1.5 rounded-md border border-white/10">Đã Hoàn Tất</span>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col lg:flex-row">
                    <!-- Billing & Match Info -->
                    <div class="lg:w-1/3 bg-[#0b1116] p-6 border-r border-white/5 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-black text-white mb-1 text-blue-600"><?php echo htmlspecialchars($m['pitch_name']); ?></h3>
                            <p class="text-sm font-semibold text-white/70 mb-4"><?php echo date('d/m/Y', strtotime($m['play_date'])); ?> | <?php echo substr($m['start_time'],0,5); ?> - <?php echo substr($m['end_time'],0,5); ?></p>
                            
                            <div class="bg-[#121b21] rounded-xl p-4 border border-white/10 shadow-none space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-white/5">
                                    <h4 class="text-xs font-black text-white/60 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        Hóa Đơn Thu Tiền Mặt
                                    </h4>
                                    <span class="text-[11px] font-semibold text-white/40">Chia 50/50</span>
                                </div>

                                <!-- Chủ Nhà -->
                                <div class="p-3 rounded-xl border <?php echo $m['host_paid'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-amber-50/50 border-amber-200'; ?> space-y-2">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-black text-white/90 uppercase">Chủ Nhà (Đội 1)</span>
                                            <?php if($hostDiscount>0): ?>
                                                <span class="text-[10px] font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded uppercase">Giảm <?php echo $hostDiscount; ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-sm font-black text-white"><?php echo number_format($hostFinalPrice, 0, ',', '.'); ?>đ</span>
                                    </div>
                                    <div class="flex items-center justify-between pt-1 border-t <?php echo $m['host_paid'] ? 'border-emerald-100' : 'border-amber-100'; ?>">
                                        <?php if($m['host_paid']): ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-[#121b21] px-2 py-0.5 rounded-md border border-emerald-200 shadow-xs">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                Đã Thu Tiền (50%)
                                            </span>
                                            <span class="text-[10px] text-emerald-600 font-semibold">Hoàn tất</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-[#121b21] px-2 py-0.5 rounded-md border border-amber-300 shadow-xs">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                Chưa Thu Tiền
                                            </span>
                                            <a href="index.php?action=owner_approve_host_payment&id=<?php echo $m['id']; ?>" 
                                               onclick="return confirm('Xác nhận đã nhận đủ <?php echo number_format($hostFinalPrice, 0, ',', '.'); ?>đ tiền mặt từ Chủ Nhà?');"
                                               class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white px-2.5 py-1 rounded-lg shadow-none hover:shadow transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                Duyệt Thu Tiền
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Đội Khách -->
                                <div class="p-3 rounded-xl border <?php echo $m['guest_paid'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-amber-50/50 border-amber-200'; ?> space-y-2">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-black text-white/90 uppercase">Đội Khách (Đội 2)</span>
                                            <?php if($guestDiscount>0): ?>
                                                <span class="text-[10px] font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded uppercase">Giảm <?php echo $guestDiscount; ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-sm font-black text-white"><?php echo number_format($guestFinalPrice, 0, ',', '.'); ?>đ</span>
                                    </div>
                                    <div class="flex items-center justify-between pt-1 border-t <?php echo $m['guest_paid'] ? 'border-emerald-100' : 'border-amber-100'; ?>">
                                        <?php if($m['guest_paid']): ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-[#121b21] px-2 py-0.5 rounded-md border border-emerald-200 shadow-xs">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                Đã Thu Tiền (50%)
                                            </span>
                                            <span class="text-[10px] text-emerald-600 font-semibold">Hoàn tất</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-[#121b21] px-2 py-0.5 rounded-md border border-amber-300 shadow-xs">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                Chưa Thu Tiền
                                            </span>
                                            <a href="index.php?action=owner_approve_guest_payment&id=<?php echo $m['id']; ?>" 
                                               onclick="return confirm('Xác nhận đã nhận đủ <?php echo number_format($guestFinalPrice, 0, ',', '.'); ?>đ tiền mặt từ Đội Khách?');"
                                               class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white px-2.5 py-1 rounded-lg shadow-none hover:shadow transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                Duyệt Thu Tiền
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center pt-2 px-1 border-t border-white/5">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-white uppercase">TỔNG THU (Thực nhận)</span>
                                        <span class="text-[10px] text-white/40">Sau khi trừ giảm giá</span>
                                    </div>
                                    <span class="font-black text-lg text-emerald-600"><?php echo number_format($hostFinalPrice + $guestFinalPrice, 0, ',', '.'); ?>đ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Versus & Reporting -->
                    <div class="lg:w-2/3 p-6 flex flex-col justify-center">
                        <?php if($m['status'] != 'cancelled' && $m['booking_status'] == 'pending'): ?>
                            <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-xl relative overflow-hidden">
                                <div class="absolute right-0 top-0 w-16 h-16 bg-orange-100 rounded-bl-full z-0"></div>
                                <div class="relative z-10">
                                    <h4 class="text-orange-800 font-black flex items-center mb-1"><svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Phê Duyệt Chốt Sân Thức Thì</h4>
                                    <p class="text-sm text-orange-700 mb-3">Lịch của trận thi đấu này vẫn đang lơ lửng, chưa khóa trên Lịch Đặt Sân. Bạn cần duyệt để xác nhận khóa giờ.</p>
                                    <a href="index.php?action=owner_approve_match_booking&match_id=<?php echo $m['id']; ?>" class="inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg shadow-none text-sm" onclick="return confirm('Khóa khung giờ này trên Lịch chung cho 2 đội thi đấu?');">Duyệt Chốt Lịch Sân Trận Này</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center justify-between mb-8">
                            <!-- Host -->
                            <div class="flex flex-col items-center flex-1">
                                <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-blue-100 mb-2 shadow-none bg-[#0b1116]/20">
                                    <?php if(!empty($m['host_avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($m['host_avatar']); ?>" class="w-full h-full object-cover">
                                    <?php endif; ?>
                                </div>
                                <p class="text-lg font-bold text-white flex flex-col items-center">
                                    <?php echo htmlspecialchars($m['host_name']); ?>
                                    <span class="text-[10px] text-white bg-blue-600 uppercase px-2 py-0.5 rounded-full mt-1 shadow-none">Chủ Nhà</span>
                                </p>
                            </div>

                            <!-- VS Graphic -->
                            <div class="flex flex-col items-center px-4">
                                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-gray-800 to-gray-900 text-white flex items-center justify-center font-black text-2xl italic shadow-lg ring-4 ring-gray-100 z-10">
                                    VS
                                </div>
                            </div>

                            <!-- Guest -->
                            <div class="flex flex-col items-center flex-1">
                                <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-red-100 mb-2 shadow-none bg-[#0b1116]/20">
                                    <?php if(!empty($m['guest_avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($m['guest_avatar']); ?>" class="w-full h-full object-cover">
                                    <?php endif; ?>
                                </div>
                                <p class="text-lg font-bold text-white flex flex-col items-center">
                                    <?php echo htmlspecialchars($m['guest_name']); ?>
                                    <span class="text-[10px] text-white bg-red-600 uppercase px-2 py-0.5 rounded-full mt-1 shadow-none">Đội Khách</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <?php if($m['status'] == 'cancelled'): ?>
                                <div class="bg-red-500/20 text-red-700 rounded-xl p-4 text-center border border-red-200">
                                    <p class="font-bold text-sm">Trận Cáp Kèo đã bị HỦY</p>
                                    <p class="text-xs mt-1">Sân đã được tự động trả lại và giải phóng.</p>
                                </div>
                            <?php elseif($m['match_result'] == 'pending'): ?>
                                <form action="index.php?action=owner_report_result" method="POST" class="bg-[#0b1116] p-4 rounded-xl border border-white/10 mb-3">
                                    <input type="hidden" name="match_id" value="<?php echo $m['id']; ?>">
                                    <div class="flex items-center gap-2 mb-3 justify-center">
                                        <svg class="w-5 h-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p class="font-bold text-sm text-white/80 uppercase tracking-wide">Xác Nhận Kết Quả Kèo Đấu</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" name="match_result" value="host_won" onclick="return confirm('Chắc chắn CHỦ NHÀ Thắng?');" class="flex-1 bg-[#121b21] text-blue-700 hover:bg-blue-600 hover:text-white font-black py-3 rounded-lg border-2 border-blue-200 transition-all focus:ring-4 focus:ring-blue-100 uppercase tracking-wider text-[11px]">Chủ Thắng</button>
                                        <button type="submit" name="match_result" value="draw" onclick="return confirm('Chắc chắn kết quả HÒA NHAU?');" class="flex-1 bg-[#121b21] text-white/80 hover:bg-gray-600 hover:text-white font-black py-3 rounded-lg border-2 border-white/20 transition-all focus:ring-4 focus:ring-gray-100 uppercase tracking-wider text-[11px]">Hòa Nhau</button>
                                        <button type="submit" name="match_result" value="guest_won" onclick="return confirm('Chắc chắn ĐỘI KHÁCH Thắng?');" class="flex-1 bg-[#121b21] text-red-700 hover:bg-red-600 hover:text-white font-black py-3 rounded-lg border-2 border-red-200 transition-all focus:ring-4 focus:ring-red-100 uppercase tracking-wider text-[11px]">Khách Thắng</button>
                                    </div>
                                </form>
                                <button onclick="if(confirm('Chú ý: Hành động này HỦY kèo, giải phóng Sân và báo hoàn tiền cho cả 2 Đội. Bạn có chắc không?')) window.location.href='index.php?action=owner_cancel_match&id=<?php echo $m['id']; ?>'" class="w-full bg-[#121b21] border border-red-300 text-red-600 hover:bg-red-500/20 text-center py-2.5 rounded-xl text-sm font-bold transition-all">
                                    Hủy Kèo Khẩn Cấp (Sự Cố/Bùng Kèo)
                                </button>
                            <?php else: ?>
                                <div class="bg-gray-800 text-white rounded-xl p-4 text-center border-b-4 border-yellow-500">
                                    <p class="font-bold uppercase tracking-widest text-xs mb-1 text-white/40">Kết Quả Đã Được Duyệt</p>
                                    <p class="text-lg font-black text-yellow-400">
                                        <?php 
                                            if($m['match_result'] == 'host_won') echo "🏆 ĐỘI CHỦ NHÀ GIÀNH CHIẾN THẮNG (+30 Pts)";
                                            elseif($m['match_result'] == 'guest_won') echo "🏆 ĐỘI KHÁCH CƯỚP ĐIỂM THÀNH CÔNG (+30 Pts)";
                                            else echo "🤝 HAI ĐỘI BẤT PHÂN THẮNG BẠI (+10 Pts)";
                                        ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-12 bg-[#121b21] rounded-3xl border border-white/5 w-full shadow-none flex flex-col items-center">
                <svg class="h-16 w-16 text-gray-200 mb-4 drop-shadow-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3m0 0l3-3m-3 3V8" /></svg>
                <h3 class="text-xl font-black text-white/90 tracking-tight">Chưa có Cáp Kèo Thi Đấu</h3>
                <p class="mt-2 text-sm text-white/60 font-medium">Các trận đấu Cáp Kèo sẽ hiển thị ở đây khi hai đội thỏa thuận thi đấu tại chảo lửa của bạn.</p>
            </div>
        <?php endif; ?>
    </div>
</div>



