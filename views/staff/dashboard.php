<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header & Alerts -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white flex items-center gap-2">
                <span>🎟️</span> Bàn Trực & Check-in Tiếp Đón
            </h1>
            <p class="mt-1 text-white/60 text-sm">Quản lý tiếp đón khách hàng, kiểm tra mã check-in và thu tiền tại quầy.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-500/20 text-green-300 border border-green-500/30">
                👤 Lễ tân: <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </span>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-[#d8ff3e]/20 text-[#d8ff3e] border border-[#d8ff3e]/30">
                🏟️ Cụm sân phụ trách: <?php echo htmlspecialchars($assigned_pitch_name ?? 'Tất cả cụm sân'); ?>
            </span>
        </div>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'checkin_success'): ?>
        <div class="bg-green-500/100/10 border-l-4 border-green-500 p-4 rounded-r-xl flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="text-green-800 font-bold text-sm">Check-in thành công! Khách hàng đã được xác nhận vào sân và thanh toán.</span>
            </div>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="bg-red-500/10 border-l-4 border-red-500 p-4 rounded-r-xl flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-red-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-red-300 font-bold text-sm">
                    <?php 
                        if ($_GET['error'] == 'unauthorized_or_not_found' || $_GET['error'] == 'unauthorized_pitch' || $_GET['error'] == 'unauthorized_owner') {
                            echo "⚠️ Từ chối Check-in: Mã hoặc Đơn đặt này KHÔNG thuộc Cụm Sân bạn đang được phân công phụ trách!";
                        } else {
                            echo "Không tìm thấy mã Check-in hoặc số điện thoại này trong hệ thống!";
                        }
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Check-in Bar -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-3xl p-6 md:p-8 text-white shadow-none">
        <div class="max-w-3xl">
            <h2 class="text-xl font-black mb-2">⚡ Check-in Nhanh Bằng Mã Hoặc SĐT</h2>
            <p class="text-green-100 text-xs mb-4">Nhập mã check-in (ví dụ: CKIN...) hoặc Số điện thoại người đặt để hoàn tất tiếp đón trong 1 click.</p>
            
            <form action="index.php?action=staff_checkin" method="POST" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="checkin_code" required placeholder="Nhập mã Check-in hoặc Số Điện Thoại..." class="flex-grow px-5 py-3.5 rounded-2xl text-white font-bold placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-green-300 shadow-inner text-sm uppercase">
                <button type="submit" class="bg-gray-900 hover:bg-black text-white font-black px-7 py-3.5 rounded-2xl text-sm transition-all shadow-lg flex-shrink-0 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Xác Nhận Check-in
                </button>
            </form>
        </div>
    </div>

    <!-- Stat Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <div class="bg-[#0b1116] p-5 rounded-2xl border border-white/5 shadow-none">
            <p class="text-xs font-bold text-white/40 uppercase tracking-wider">Tổng Lịch Ngày</p>
            <p class="text-3xl font-black text-white mt-2"><?php echo $total_today; ?></p>
        </div>

        <div class="bg-[#0b1116] p-5 rounded-2xl border border-white/5 shadow-none">
            <p class="text-xs font-bold text-green-400 uppercase tracking-wider">Đã Check-in Sân</p>
            <p class="text-3xl font-black text-green-400 mt-2"><?php echo $checked_in; ?></p>
        </div>

        <div class="bg-[#0b1116] p-5 rounded-2xl border border-white/5 shadow-none">
            <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Chưa Thanh Toán</p>
            <p class="text-3xl font-black text-amber-600 mt-2"><?php echo $unpaid_count; ?></p>
        </div>

        <div class="bg-[#0b1116] p-5 rounded-2xl border border-white/5 shadow-none">
            <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Dự Kiến Thu</p>
            <p class="text-2xl font-black text-blue-600 mt-2"><?php echo number_format($revenue_today, 0, ',', '.'); ?>đ</p>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-[#0b1116] p-4 rounded-2xl border border-white/10 shadow-none flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="index.php" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="hidden" name="action" value="staff_dashboard">
            
            <div class="flex items-center gap-2">
                <label for="date" class="text-xs font-bold text-gray-700">Ngày:</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-green-500" onchange="this.form.submit()">
            </div>

            <div class="flex items-center gap-2 flex-grow sm:flex-grow-0">
                <input type="text" name="search" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Tìm tên / sđt / mã..." class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 w-48">
                <button type="submit" class="bg-[#0b1116]/10 hover:bg-[#0b1116]/10 text-gray-700 px-3.5 py-2 rounded-xl text-sm font-bold transition-colors">Lọc</button>
            </div>
        </form>

        <div class="flex items-center gap-2 text-xs font-medium text-white/60">
            <span class="w-3 h-3 rounded-full bg-green-500/100/100 inline-block"></span> Đã check-in
            <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block ml-2"></span> Chờ check-in
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="bg-[#0b1116] rounded-3xl border border-white/10 shadow-none overflow-hidden">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="font-extrabold text-white text-lg">Danh Sách Lịch Đặt (Ngày <?php echo date('d/m/Y', strtotime($date)); ?>)</h3>
            <span class="text-xs text-white/40 font-bold"><?php echo count($bookings); ?> đơn</span>
        </div>

        <?php if(count($bookings) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-white/80">
                    <thead class="bg-[#0b1116]/5 text-gray-700 uppercase font-black text-[11px] tracking-wider border-b border-white/10">
                        <tr>
                            <th class="py-3.5 px-6">Mã Check-in</th>
                            <th class="py-3.5 px-6">Khách Hàng</th>
                            <th class="py-3.5 px-6">Sân Cầu</th>
                            <th class="py-3.5 px-6">Khung Giờ</th>
                            <th class="py-3.5 px-6">Dịch Vụ Kèm</th>
                            <th class="py-3.5 px-6">Tổng Tiền</th>
                            <th class="py-3.5 px-6">Thanh Toán</th>
                            <th class="py-3.5 px-6 text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($bookings as $b): ?>
                        <tr class="hover:bg-[#0b1116]/5/80 transition-colors <?php echo $b['status'] == 'completed' ? 'bg-green-500/100/10/30' : ''; ?>">
                            <td class="py-4 px-6">
                                <span class="font-mono font-bold text-xs bg-[#0b1116]/10 text-white px-2.5 py-1 rounded-lg border border-white/10">
                                    <?php echo htmlspecialchars($b['checkin_code'] ?? 'CKIN-'.$b['id']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-white"><?php echo htmlspecialchars($b['customer_name']); ?></div>
                                <div class="text-xs text-white/40 font-mono"><?php echo htmlspecialchars($b['customer_phone']); ?></div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-medium text-white block"><?php echo htmlspecialchars($b['pitch_name']); ?></span>
                                <span class="text-[11px] text-white/40"><?php echo htmlspecialchars($b['pitch_type']); ?></span>
                            </td>
                            <td class="py-4 px-6 font-bold text-white">
                                <?php echo date('H:i', strtotime($b['start_time'])); ?> - <?php echo date('H:i', strtotime($b['end_time'])); ?>
                            </td>
                            <td class="py-4 px-6 text-xs text-white/80 max-w-[180px]">
                                <?php echo !empty($b['services_note']) ? htmlspecialchars($b['services_note']) : '<span class="text-gray-300 italic">Không có</span>'; ?>
                            </td>
                            <td class="py-4 px-6 font-black text-white">
                                <?php echo number_format($b['total_price'], 0, ',', '.'); ?>đ
                            </td>
                            <td class="py-4 px-6">
                                <?php if($b['payment_status'] == 'paid'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-green-100 text-green-800">Đã TT</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                                        <?php echo $b['payment_method'] == 'vietqr' ? 'Chờ CK' : 'Chưa thu'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <?php if($b['status'] == 'completed'): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-500/100/10 text-green-700 border border-green-200">
                                        ✓ Đã Vào Sân
                                    </span>
                                <?php else: ?>
                                    <a href="index.php?action=staff_checkin&id=<?php echo $b['id']; ?>" onclick="return confirm('Xác nhận khách hàng đã đến nhận sân và thanh toán?')" class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold bg-green-600 hover:bg-green-700 text-white shadow-none transition-all transform hover:scale-105">
                                        Check-in Ngay
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-[#0b1116]/10 rounded-full flex items-center justify-center mx-auto text-2xl text-white/40 mb-3">🏸</div>
                <h4 class="font-bold text-gray-700 mb-1">Không có lịch đặt nào trong ngày này</h4>
                <p class="text-xs text-white/40">Thử chọn ngày khác hoặc xóa bộ lọc tìm kiếm.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
