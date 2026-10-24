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
            
            <form action="index.php?action=staff_checkin" method="POST" onsubmit="return handleQuickCheckin(event, this);" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="checkin_code" required placeholder="Nhập mã Check-in hoặc Số Điện Thoại..." class="flex-grow px-5 py-3.5 rounded-2xl text-white font-bold placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-green-300 shadow-inner text-sm uppercase">
                <button type="submit" id="btn_quick_checkin" class="bg-gray-900 hover:bg-black text-white font-black px-7 py-3.5 rounded-2xl text-sm transition-all shadow-lg flex-shrink-0 flex items-center justify-center gap-2">
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
            <p id="stat_checked_in" class="text-3xl font-black text-green-400 mt-2"><?php echo $checked_in; ?></p>
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
                        <tr id="booking_row_<?php echo $b['id']; ?>" class="hover:bg-[#0b1116]/5/80 transition-colors <?php echo $b['status'] == 'completed' ? 'bg-green-500/10' : ''; ?>">
                            <td class="py-4 px-6">
                                <?php if(!empty($b['is_match'])): ?>
                                    <div class="flex flex-col gap-1">
                                        <?php if(!empty($b['host_checkin_code'])): ?>
                                            <span class="font-mono font-bold text-[10px] bg-blue-950/80 text-blue-300 px-2 py-0.5 rounded border border-blue-500/30 whitespace-nowrap" title="Mã check-in Chủ Nhà">
                                                👤 <?= htmlspecialchars($b['host_checkin_code']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if(!empty($b['guest_checkin_code'])): ?>
                                            <span class="font-mono font-bold text-[10px] bg-rose-950/80 text-rose-300 px-2 py-0.5 rounded border border-rose-500/30 whitespace-nowrap" title="Mã check-in Đội Khách">
                                                👤 <?= htmlspecialchars($b['guest_checkin_code']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if(empty($b['host_checkin_code']) && empty($b['guest_checkin_code'])): ?>
                                            <span class="font-mono font-bold text-xs bg-[#0b1116]/10 text-white px-2.5 py-1 rounded-lg border border-white/10">
                                                <?= htmlspecialchars($b['checkin_code'] ?? 'CKIN-'.$b['id']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="font-mono font-bold text-xs bg-[#0b1116]/10 text-white px-2.5 py-1 rounded-lg border border-white/10">
                                        <?php echo htmlspecialchars($b['checkin_code'] ?? 'CKIN-'.$b['id']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6">
                                <?php if(!empty($b['is_match'])): ?>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-300 border border-blue-500/30">Đội 1</span>
                                            <span class="font-bold text-white text-xs"><?= htmlspecialchars($b['match_host_name'] ?: 'Chủ Nhà') ?></span>
                                            <span class="text-[10px] text-white/50 font-mono"><?= htmlspecialchars($b['match_host_phone']) ?></span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300 border border-rose-500/30">Đội 2</span>
                                            <span class="font-bold text-white text-xs"><?= htmlspecialchars($b['match_guest_name'] ?: 'Đội Khách') ?></span>
                                            <span class="text-[10px] text-white/50 font-mono"><?= htmlspecialchars($b['match_guest_phone']) ?></span>
                                        </div>
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase tracking-wider">⚡ Ghép Kèo AI (50/50)</span>
                                    </div>
                                <?php else: ?>
                                    <div class="font-bold text-white"><?php echo htmlspecialchars($b['customer_name']); ?></div>
                                    <div class="text-xs text-white/40 font-mono"><?php echo htmlspecialchars($b['customer_phone']); ?></div>
                                <?php endif; ?>
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
                            <td class="py-4 px-6" id="payment_cell_<?php echo $b['id']; ?>">
                                <?php if(!empty($b['is_match'])): ?>
                                    <div class="flex flex-col gap-1 text-[11px] whitespace-nowrap">
                                        <span id="host_paid_<?= $b['id'] ?>" class="<?= !empty($b['host_paid']) ? 'text-emerald-400 font-bold' : 'text-amber-400' ?>">
                                            Đội 1: <?= !empty($b['host_paid']) ? '✓ Đã TT' : '⏳ Chưa thu' ?>
                                        </span>
                                        <span id="guest_paid_<?= $b['id'] ?>" class="<?= !empty($b['guest_paid']) ? 'text-emerald-400 font-bold' : 'text-amber-400' ?>">
                                            Đội 2: <?= !empty($b['guest_paid']) ? '✓ Đã TT' : '⏳ Chưa thu' ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <?php if($b['payment_status'] == 'paid'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-green-100 text-green-800">Đã TT</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                                            <?php echo $b['payment_method'] == 'vietqr' ? 'Chờ CK' : 'Chưa thu'; ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center" id="action_cell_<?php echo $b['id']; ?>">
                                <?php if(!empty($b['is_match'])): ?>
                                    <div class="flex flex-col gap-1.5 items-stretch min-w-[140px]">
                                        <!-- Host checkin action -->
                                        <div id="host_action_wrap_<?= $b['id'] ?>">
                                        <?php if(!empty($b['host_checkin']) || $b['status'] == 'completed'): ?>
                                            <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 w-full">
                                                ✓ <?= htmlspecialchars($b['match_host_name'] ?: 'Đội 1') ?>
                                            </span>
                                        <?php else: ?>
                                            <button type="button" onclick="return ajaxStaffCheckin(event, this, 'ticket', <?= $b['host_ticket_id'] ?>, <?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['match_host_name'] ?: 'Đội 1')) ?>', 'host')" class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow transition w-full">
                                                🎟️ Check-in: <?= htmlspecialchars($b['match_host_name'] ?: 'Đội 1') ?>
                                            </button>
                                        <?php endif; ?>
                                        </div>

                                        <!-- Guest checkin action -->
                                        <div id="guest_action_wrap_<?= $b['id'] ?>">
                                        <?php if(!empty($b['guest_checkin']) || $b['status'] == 'completed'): ?>
                                            <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30 w-full">
                                                ✓ <?= htmlspecialchars($b['match_guest_name'] ?: 'Đội 2') ?>
                                            </span>
                                        <?php else: ?>
                                            <button type="button" onclick="return ajaxStaffCheckin(event, this, 'ticket', <?= $b['guest_ticket_id'] ?>, <?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['match_guest_name'] ?: 'Đội 2')) ?>', 'guest')" class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-[10px] font-bold bg-blue-600 hover:bg-blue-700 text-white shadow transition w-full">
                                                🎟️ Check-in: <?= htmlspecialchars($b['match_guest_name'] ?: 'Đội 2') ?>
                                            </button>
                                        <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php if($b['status'] == 'completed'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-500/10 text-green-700 border border-green-200">
                                            ✓ Đã Vào Sân
                                        </span>
                                    <?php else: ?>
                                        <button type="button" onclick="return ajaxStaffCheckin(event, this, 'booking', <?= $b['id'] ?>, <?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['customer_name'])) ?>')" class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold bg-green-600 hover:bg-green-700 text-white shadow-none transition-all transform hover:scale-105">
                                            Check-in Ngay
                                        </button>
                                    <?php endif; ?>
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

<script>
function ajaxStaffCheckin(event, btn, type, targetId, bookingId, playerName, party = null) {
    event.preventDefault();
    if (!confirm('Xác nhận Check-in cho ' + playerName + '?')) return false;

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span> Đang xử lý...';

    const url = (type === 'ticket') 
        ? 'index.php?action=staff_checkin&ticket_id=' + targetId + '&is_ajax=1'
        : 'index.php?action=staff_checkin&id=' + targetId + '&is_ajax=1';

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('✅ ' + (data.message || 'Check-in thành công cho ' + playerName));
            
            // Swap button with checked-in badge
            const badgeBg = (party === 'guest') ? 'bg-blue-500/20 text-blue-300 border-blue-500/30' : 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
            const badgeHtml = `<span class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-[10px] font-bold ${badgeBg} border w-full">✓ ${playerName}</span>`;
            
            const parentWrap = btn.parentElement;
            if (parentWrap) {
                parentWrap.innerHTML = badgeHtml;
            } else {
                btn.outerHTML = badgeHtml;
            }

            // Update payment cell if split bill
            if (party === 'host') {
                const pEl = document.getElementById('host_paid_' + bookingId);
                if (pEl) { pEl.className = 'text-emerald-400 font-bold'; pEl.textContent = 'Đội 1: ✓ Đã TT'; }
            } else if (party === 'guest') {
                const pEl = document.getElementById('guest_paid_' + bookingId);
                if (pEl) { pEl.className = 'text-emerald-400 font-bold'; pEl.textContent = 'Đội 2: ✓ Đã TT'; }
            }

            // Update stat counter +1
            const statEl = document.getElementById('stat_checked_in');
            if (statEl) {
                let current = parseInt(statEl.textContent.trim()) || 0;
                statEl.textContent = current + 1;
            }

            // If both checked or whole booking checked, highlight row
            if (data.both_checked) {
                const row = document.getElementById('booking_row_' + bookingId);
                if (row) row.classList.add('bg-green-500/10');
            }
        } else {
            window.showToast('⚠️ ' + (data.message || 'Lỗi xử lý check-in.'), 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error(err);
        window.showToast('⚠️ Lỗi kết nối mạng khi gửi check-in.', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });

    return false;
}

function handleQuickCheckin(event, form) {
    event.preventDefault();
    const input = form.querySelector('input[name="checkin_code"]');
    const code = input ? input.value.trim() : '';
    if (!code) return false;

    const btn = document.getElementById('btn_quick_checkin');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span> Đang tìm & check-in...';

    const formData = new FormData(form);
    formData.append('is_ajax', '1');

    fetch('index.php?action=staff_checkin', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (data.success) {
            window.showToast('✅ ' + (data.message || 'Check-in thành công!'));
            input.value = '';
            
            // Increment stat counter
            const statEl = document.getElementById('stat_checked_in');
            if (statEl) {
                let current = parseInt(statEl.textContent.trim()) || 0;
                statEl.textContent = current + 1;
            }

            // If booking row is on page, highlight it
            if (data.booking_id) {
                const row = document.getElementById('booking_row_' + data.booking_id);
                if (row) {
                    row.classList.add('bg-green-500/20');
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => {
                        // refresh specific cells or reload after brief highlight if not on screen
                    }, 500);
                }
            }
        } else {
            window.showToast('⚠️ ' + (data.message || 'Mã không hợp lệ hoặc không tìm thấy.'), 'error');
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = originalText;
        window.showToast('⚠️ Lỗi kết nối máy chủ khi check-in.', 'error');
    });

    return false;
}
</script>
