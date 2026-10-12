<?php
// Interactive Visual Court Timeline Matrix & Walk-in POS Quick Booking View
$date = $_GET['date'] ?? date('Y-m-d');
$start_hour = 6;
$end_hour = 23;
$total_hours = $end_hour - $start_hour;

// Statistics counters
$total_bookings_count = 0;
$walkin_count = 0;
$online_count = 0;
$match_count = 0;
$revenue_day = 0;

if (isset($pitch_schedules) && is_array($pitch_schedules)) {
    foreach ($pitch_schedules as $ps) {
        foreach ($ps['bookings'] as $b) {
            $total_bookings_count++;
            $b_type = $b['booking_type'] ?? 'online_app';
            if ($b_type === 'walk_in') {
                $walkin_count++;
            } else {
                $online_count++;
            }
            if (strpos($b['services_note'] ?? '', 'Kèo Ghép') !== false || strpos($b['services_note'] ?? '', 'Smart Match') !== false) {
                $match_count++;
            }
            if (in_array($b['status'], ['confirmed', 'completed'])) {
                $revenue_day += (float)$b['total_price'];
            }
        }
    }
}
?>

<div class="max-w-[1400px] mx-auto space-y-6 animate-fade-in">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-[#121b21] p-6 rounded-3xl border border-white/10 shadow-xl">
        <div>
            <div class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#d8ff3e] mb-1">
                <span class="h-2 w-2 rounded-full bg-[#d8ff3e] animate-ping"></span>
                BÀN TRỰC LỄ TÂN & SƠ ĐỒ THỜI GIAN THỰC (POS & TIMELINE MATRIX)
            </div>
            <h1 class="text-3xl font-black uppercase tracking-tight text-white flex items-center gap-3">
                <span>📅 Lịch Hoạt Động Cụm Sân</span>
                <span class="text-xs bg-[#d8ff3e]/20 text-[#d8ff3e] border border-[#d8ff3e]/30 px-2.5 py-1 rounded-full font-bold">REAL-TIME GRID</span>
            </h1>
            <p class="mt-1 text-xs text-[#71808a]">Xem trực quan ca trống/kín theo từng giờ & Đặt ca tại quầy cho khách vãng lai siêu tốc.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button onclick="openPosModal()" class="flex items-center gap-2 bg-[#d8ff3e] text-[#0b1116] px-5 py-3 rounded-2xl font-black text-xs uppercase tracking-wider shadow-[3px_3px_0_#73940f] hover:translate-x-0.5 hover:translate-y-0.5 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                ⚡ Tạo Đơn Tại Quầy (POS)
            </button>
            <a href="index.php?action=owner_pitches" class="bg-[#0b1116] border border-white/10 text-white/80 hover:text-white hover:bg-white/5 px-4 py-3 rounded-2xl text-xs font-bold transition">Quản Lý Cụm Sân</a>
            <a href="index.php?action=owner_dashboard" class="bg-[#0b1116] border border-white/10 text-white/80 hover:text-white hover:bg-white/5 px-4 py-3 rounded-2xl text-xs font-bold transition">Dashboard</a>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'walkin_created'): ?>
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🎟️</span>
                    <div>
                        <h4 class="font-bold text-sm">ĐÃ KHÓA SÂN & ĐẶT ĐƠN TẠI QUẦY THÀNH CÔNG!</h4>
                        <p class="text-xs text-emerald-100">Ca thi đấu đã được cập nhật trực tiếp lên hệ thống real-time. Đã phòng ngừa $100\%$ trùng ca.</p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold text-lg">&times;</button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-4 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <strong class="font-bold text-sm">Không thể khóa ca!</strong>
                    <span class="text-xs block opacity-90">
                        <?php 
                        if ($_GET['error'] === 'overlapped') echo 'Khung giờ này đã có khách khác đặt trước trên hệ thống!';
                        else echo 'Đã xảy ra lỗi khi tạo đơn. Vui lòng kiểm tra lại.';
                        ?>
                    </span>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Control Bar: Date Selector & Quick Stats -->
    <div class="grid gap-4 md:grid-cols-4">
        <!-- Date Selector Card -->
        <div class="md:col-span-2 bg-[#121b21] p-4 rounded-2xl border border-white/10 flex items-center justify-between gap-4">
            <form action="index.php" method="GET" id="dateForm" class="flex items-center gap-3 w-full">
                <input type="hidden" name="action" value="owner_schedule">
                <label for="date" class="text-xs font-black uppercase text-[#71808a] flex-shrink-0">Chọn ngày:</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" 
                       class="bg-[#0b1116] border border-white/10 text-white text-xs font-bold rounded-xl px-3 py-2.5 focus:border-[#d8ff3e] outline-none flex-grow"
                       onchange="document.getElementById('dateForm').submit()">
                <div class="flex items-center gap-1">
                    <a href="index.php?action=owner_schedule&date=<?php echo date('Y-m-d'); ?>" class="bg-[#0b1116] border border-white/10 hover:border-[#d8ff3e] text-white px-3 py-2 rounded-xl text-[11px] font-bold transition">Hôm nay</a>
                    <a href="index.php?action=owner_schedule&date=<?php echo date('Y-m-d', strtotime('+1 day')); ?>" class="bg-[#0b1116] border border-white/10 hover:border-[#d8ff3e] text-white px-3 py-2 rounded-xl text-[11px] font-bold transition">Ngày mai</a>
                </div>
            </form>
        </div>

        <!-- Metrics Cards -->
        <div class="bg-[#121b21] p-4 rounded-2xl border border-white/10 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-black uppercase text-[#71808a]">TỔNG ĐƠN / DOANH THU NGÀY</div>
                <div class="text-xl font-black text-white mt-0.5"><?php echo $total_bookings_count; ?> <span class="text-xs text-[#84939b] font-normal">đơn</span></div>
            </div>
            <div class="text-right">
                <div class="text-[10px] font-black uppercase text-[#d8ff3e]">TỔNG THU</div>
                <div class="text-sm font-black text-[#d8ff3e]"><?php echo number_format($revenue_day); ?>đ</div>
            </div>
        </div>

        <div class="bg-[#121b21] p-4 rounded-2xl border border-white/10 flex items-center justify-around text-center">
            <div>
                <div class="text-[9px] font-black text-blue-400 uppercase">🎟️ TẠI QUẦY</div>
                <div class="text-lg font-black text-blue-400"><?php echo $walkin_count; ?></div>
            </div>
            <div class="h-6 w-px bg-white/10"></div>
            <div>
                <div class="text-[9px] font-black text-rose-400 uppercase">📲 APP ONLINE</div>
                <div class="text-lg font-black text-rose-400"><?php echo $online_count; ?></div>
            </div>
            <div class="h-6 w-px bg-white/10"></div>
            <div>
                <div class="text-[9px] font-black text-amber-400 uppercase">⚡ SMART MATCH</div>
                <div class="text-lg font-black text-amber-400"><?php echo $match_count; ?></div>
            </div>
        </div>
    </div>

    <!-- Color Legend Bar -->
    <div class="bg-[#121b21] px-5 py-3 rounded-2xl border border-white/10 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 text-white/60 font-medium">
            <span class="font-bold text-white uppercase text-[10px] tracking-wider">CHÚ THÍCH TRẠNG THÁI:</span>
        </div>
        <div class="flex flex-wrap items-center gap-4 text-[11px] font-bold">
            <div class="flex items-center gap-1.5 cursor-pointer" onclick="openPosModal()">
                <span class="w-3.5 h-3.5 rounded bg-[#10181e] border border-[#d8ff3e]/40 shadow-[0_0_8px_rgba(216,255,62,0.2)]"></span>
                <span class="text-[#d8ff3e]">🟩 Ô Trống (Click Đặt Nhanh)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded bg-blue-600 border border-blue-400"></span>
                <span class="text-blue-300">🟦 Đặt Tại Quầy (Walk-in)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded bg-rose-600 border border-rose-400"></span>
                <span class="text-rose-300">🟥 Đặt Online App</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded bg-amber-500 border border-amber-300"></span>
                <span class="text-amber-300">🟨 Smart Match AI</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded bg-purple-600 border border-purple-400"></span>
                <span class="text-purple-300">🟪 Lịch Cố Định Tháng</span>
            </div>
        </div>
    </div>

    <!-- Main Visual Timeline Grid Table -->
    <div class="bg-[#121b21] rounded-3xl border border-white/10 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <div style="min-width: 1200px;" class="p-6">
                <!-- Timeline Hours Header (06:00 to 23:00) -->
                <div class="flex ml-56 mb-4 border-b border-white/10 pb-3 relative h-6">
                    <?php for ($h = $start_hour; $h <= $end_hour; $h++): ?>
                        <div class="absolute text-[11px] font-black tracking-tighter text-[#71808a] -ml-4" 
                             style="left: <?php echo (($h - $start_hour) / $total_hours) * 100; ?>%;">
                            <?php echo sprintf('%02d:00', $h); ?>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- Pitches Rows -->
                <div class="space-y-4">
                    <?php if (!empty($pitch_schedules)): ?>
                        <?php foreach ($pitch_schedules as $ps): 
                            $pitch = $ps['pitch'];
                            $pitch_bookings = $ps['bookings'];
                            
                            // Sort bookings by start time
                            usort($pitch_bookings, function($a, $b) {
                                return strcmp($a['start_time'], $b['start_time']);
                            });

                            // Calculate visual tracks for overlapping items
                            $tracks = [];
                            $pitch_bookings_mapped = [];
                            foreach ($pitch_bookings as $b) {
                                $b_start = strtotime($date . ' ' . $b['start_time']);
                                $b_end = strtotime($date . ' ' . $b['end_time']);
                                
                                $placed = false;
                                foreach ($tracks as $track_index => $track_end) {
                                    if ($b_start >= $track_end) {
                                        $tracks[$track_index] = $b_end;
                                        $b['track'] = $track_index;
                                        $placed = true;
                                        break;
                                    }
                                }
                                if (!$placed) {
                                    $track_index = count($tracks);
                                    $tracks[$track_index] = $b_end;
                                    $b['track'] = $track_index;
                                }
                                $b['left_pct'] = max(0, (($b_start - strtotime($date . ' 06:00:00')) / ($total_hours * 3600)) * 100);
                                $b['width_pct'] = min(100 - $b['left_pct'], (($b_end - $b_start) / ($total_hours * 3600)) * 100);
                                $pitch_bookings_mapped[] = $b;
                            }
                            
                            $num_tracks = max(1, count($tracks));
                            $row_height = max(70, $num_tracks * 52 + 10);
                        ?>
                        <div class="flex items-stretch border border-white/10 rounded-2xl bg-[#0b1116] relative overflow-hidden group">
                            <!-- Pitch Info Card (Left Column) -->
                            <div class="w-56 flex-shrink-0 bg-[#10181e] border-r border-white/10 p-4 z-10 flex flex-col justify-between shadow-lg">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-black text-white text-sm truncate" title="<?php echo htmlspecialchars($pitch['name']); ?>">
                                            <?php echo htmlspecialchars($pitch['name']); ?>
                                        </h4>
                                    </div>
                                    <div class="text-[10px] font-bold text-[#d8ff3e] tracking-wider uppercase">
                                        <?php echo number_format($pitch['price_per_hour']); ?>đ/h
                                    </div>
                                    <div class="text-[10px] font-medium text-[#71808a] mt-1 truncate">
                                        <?php echo htmlspecialchars($pitch['type']); ?> · Sức chứa: <?php echo $pitch['quantity']; ?> sân
                                    </div>
                                </div>
                                <button onclick="openPosModal(<?php echo $pitch['id']; ?>, '<?php echo addslashes($pitch['name']); ?>', <?php echo $pitch['price_per_hour']; ?>)" 
                                        class="mt-3 w-full bg-[#121b21] hover:bg-[#d8ff3e] text-white/80 hover:text-[#0b1116] border border-white/10 hover:border-[#d8ff3e] py-1.5 px-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition">
                                    + Đặt Ca Tại Quầy
                                </button>
                            </div>
                            
                            <!-- Interactive Timeline Grid Area -->
                            <div class="flex-grow relative bg-[#0b1116] overflow-hidden" style="height: <?php echo $row_height; ?>px;">
                                <!-- Grid Column Lines & Clickable Empty Hourly Slots -->
                                <?php for ($h = $start_hour; $h < $end_hour; $h++): 
                                    $h_str = sprintf('%02d:00', $h);
                                    $h_next_str = sprintf('%02d:00', $h + 1);
                                    $slot_left = (($h - $start_hour) / $total_hours) * 100;
                                    $slot_width = (1 / $total_hours) * 100;
                                ?>
                                    <!-- Interactive Cell Background -->
                                    <div onclick="openPosModal(<?php echo $pitch['id']; ?>, '<?php echo addslashes($pitch['name']); ?>', <?php echo $pitch['price_per_hour']; ?>, '<?php echo $h_str; ?>', '<?php echo $h_next_str; ?>')"
                                         class="absolute top-0 bottom-0 border-l border-white/5 hover:bg-[#d8ff3e]/10 transition cursor-pointer group/cell"
                                         style="left: <?php echo $slot_left; ?>%; width: <?php echo $slot_width; ?>%;"
                                         title="Khung giờ <?php echo $h_str; ?> - <?php echo $h_next_str; ?> (Bấm để tạo đơn tại quầy)">
                                        <span class="opacity-0 group-hover/cell:opacity-100 absolute top-2 left-2 text-[9px] font-black text-[#d8ff3e] bg-[#0b1116]/80 px-1.5 py-0.5 rounded border border-[#d8ff3e]/30 pointer-events-none">
                                            +Đặt <?php echo $h_str; ?>
                                        </span>
                                    </div>
                                <?php endfor; ?>

                                <!-- Render Booked Blocks -->
                                <?php foreach ($pitch_bookings_mapped as $b): 
                                    if ($b['pitch_id'] != $pitch['id']) continue;

                                    $b_type = $b['booking_type'] ?? 'online_app';
                                    $is_match = strpos($b['services_note'] ?? '', 'Kèo Ghép') !== false || strpos($b['services_note'] ?? '', 'Smart Match') !== false;

                                    // Dynamic Badge Styling
                                    if ($b_type === 'walk_in') {
                                        $block_style = "bg-blue-600/90 hover:bg-blue-600 text-white border-blue-400/60 shadow-[0_0_12px_rgba(37,99,235,0.4)]";
                                        $badge_icon = "🎟️ TẠI QUẦY";
                                    } elseif ($b_type === 'fixed_monthly') {
                                        $block_style = "bg-purple-600/90 hover:bg-purple-600 text-white border-purple-400/60 shadow-[0_0_12px_rgba(147,51,234,0.4)]";
                                        $badge_icon = "🟪 CỐ ĐỊNH";
                                    } elseif ($is_match) {
                                        $block_style = "bg-amber-500/90 hover:bg-amber-500 text-white border-amber-300/60 shadow-[0_0_12px_rgba(245,158,11,0.4)]";
                                        $badge_icon = "⚡ MATCH AI";
                                    } else {
                                        $block_style = "bg-rose-600/90 hover:bg-rose-600 text-white border-rose-400/60 shadow-[0_0_12px_rgba(225,29,72,0.4)]";
                                        $badge_icon = "📲 ONLINE APP";
                                    }

                                    $track = $b['track'] ?? 0;
                                    $top_pos = $track * 48 + 8;
                                    $b_json = htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8');
                                ?>
                                    <div onclick="event.stopPropagation(); openDetailModal(<?php echo $b_json; ?>)" 
                                         class="absolute rounded-xl border p-2 text-xs font-bold overflow-hidden cursor-pointer transition-all hover:scale-[1.01] hover:z-20 shadow-md <?php echo $block_style; ?>"
                                         style="left: <?php echo $b['left_pct']; ?>%; width: <?php echo $b['width_pct']; ?>%; top: <?php echo $top_pos; ?>px; height: 42px; min-width: 25px;">
                                        <div class="flex items-center justify-between gap-1">
                                            <div class="truncate text-[11px] font-black leading-tight"><?php echo htmlspecialchars($b['customer_name']); ?></div>
                                            <span class="text-[8px] font-black uppercase px-1 py-0.2 rounded bg-black/30 flex-shrink-0"><?php echo $badge_icon; ?></span>
                                        </div>
                                        <div class="flex items-center justify-between text-[9px] opacity-90 mt-0.5 font-mono">
                                            <span><?php echo substr($b['start_time'], 0, 5) . '-' . substr($b['end_time'], 0, 5); ?></span>
                                            <span class="font-bold"><?php echo htmlspecialchars($b['checkin_code'] ?? ''); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12 text-[#71808a] bg-[#0b1116] rounded-2xl border-dashed border-2 border-white/10">
                            Chưa có dữ liệu cụm sân nào để hiển thị lịch.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: POS QUICK BOOKING (TẠO ĐƠN TẠI QUẦY) -->
<!-- ========================================== -->
<div id="posModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden animate-fade-in">
    <div class="bg-[#121b21] border border-white/15 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative space-y-6">
        <!-- Close Button -->
        <button onclick="closePosModal()" class="absolute top-5 right-5 text-white/50 hover:text-white text-xl font-bold p-1">&times;</button>
        
        <!-- Modal Header -->
        <div class="flex items-center gap-3 border-b border-white/10 pb-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#d8ff3e] text-[#0b1116] font-black text-2xl shadow-[0_0_15px_rgba(216,255,62,0.3)]">
                ⚡
            </div>
            <div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight">Tạo Đơn Khóa Sân Tại Quầy (POS)</h3>
                <p class="text-xs text-[#71808a]">Khóa ca trực tiếp cho khách vãng lai · Đồng bộ Real-time chống trùng ca.</p>
            </div>
        </div>

        <!-- POS Form -->
        <form action="index.php?action=create_walkin_booking" method="POST" id="posForm" class="space-y-4">
            <input type="hidden" name="booking_type" value="walk_in">

            <!-- Pitch Select & Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Chọn Sân Thi Đấu</label>
                    <select name="pitch_id" id="pos_pitch_id" onchange="updatePosPrice()" required class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                        <?php if(!empty($pitch_schedules)): ?>
                            <?php foreach($pitch_schedules as $ps): $p = $ps['pitch']; ?>
                                <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price_per_hour']; ?>">
                                    <?php echo htmlspecialchars($p['name']); ?> (<?php echo number_format($p['price_per_hour']); ?>đ/h)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Ngày Thi Đấu</label>
                    <input type="date" name="booking_date" id="pos_booking_date" value="<?php echo htmlspecialchars($date); ?>" required 
                           class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                </div>
            </div>

            <!-- Start Time & End Time -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Giờ Bắt Đầu</label>
                    <select name="start_time" id="pos_start_time" onchange="updatePosPrice()" class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                        <?php for($h = 6; $h <= 22; $h++): $h_str = sprintf('%02d:00', $h); ?>
                            <option value="<?php echo $h_str; ?>"><?php echo $h_str; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Giờ Kết Thúc</label>
                    <select name="end_time" id="pos_end_time" onchange="updatePosPrice()" class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                        <?php for($h = 7; $h <= 23; $h++): $h_str = sprintf('%02d:00', $h); ?>
                            <option value="<?php echo $h_str; ?>"><?php echo $h_str; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Tên Khách Hàng</label>
                    <input type="text" name="customer_name" id="pos_customer_name" value="Khách vãng lai" required class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Số Điện Thoại</label>
                    <input type="text" name="customer_phone" id="pos_customer_phone" placeholder="0901234567" value="0900000000" class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                </div>
            </div>

            <!-- Payment Method & Status -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Hình Thức Thanh Toán</label>
                    <select name="payment_method" class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                        <option value="cash">💵 Tiền Mặt Tại Quầy</option>
                        <option value="vietqr">📲 Chuyển Khoản VietQR</option>
                        <option value="app_wallet">💳 Ví Điểm Thành Viên</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Trạng Thái Thu Tiền</label>
                    <select name="payment_status" class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                        <option value="paid">✅ Đã Thu Đủ Tiền</option>
                        <option value="unpaid">⏳ Khách Chưa Trả (Thu sau)</option>
                    </select>
                </div>
            </div>

            <!-- Quick Services Addon Checkboxes -->
            <div>
                <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1.5">Dịch Vụ Mua Kèm Tại Quầy (Ghi Chú Đơn)</label>
                <div class="grid grid-cols-2 gap-2 text-xs font-medium text-white/80">
                    <label class="flex items-center gap-2 bg-[#0b1116] p-2 rounded-xl border border-white/10 cursor-pointer hover:border-[#d8ff3e]">
                        <input type="checkbox" onchange="toggleServiceAddon('2 Nước suối (+20k)', 20000, this)" class="accent-[#d8ff3e]">
                        <span>🥤 2 Nước suối (+20k)</span>
                    </label>
                    <label class="flex items-center gap-2 bg-[#0b1116] p-2 rounded-xl border border-white/10 cursor-pointer hover:border-[#d8ff3e]">
                        <input type="checkbox" onchange="toggleServiceAddon('1 Revive chanh muối (+15k)', 15000, this)" class="accent-[#d8ff3e]">
                        <span>⚡ Revive (+15k)</span>
                    </label>
                    <label class="flex items-center gap-2 bg-[#0b1116] p-2 rounded-xl border border-white/10 cursor-pointer hover:border-[#d8ff3e]">
                        <input type="checkbox" onchange="toggleServiceAddon('Thuê 2 vợt Yonex (+40k)', 40000, this)" class="accent-[#d8ff3e]">
                        <span>🏸 Thuê 2 Vợt (+40k)</span>
                    </label>
                    <label class="flex items-center gap-2 bg-[#0b1116] p-2 rounded-xl border border-white/10 cursor-pointer hover:border-[#d8ff3e]">
                        <input type="checkbox" onchange="toggleServiceAddon('1 Ống cầu Hải Yến S90 (+220k)', 220000, this)" class="accent-[#d8ff3e]">
                        <span>🔴 Ống Cầu (+220k)</span>
                    </label>
                </div>
                <input type="hidden" name="services_note" id="pos_services_note" value="Đơn đặt trực tiếp tại quầy Lễ tân">
            </div>

            <!-- Price Summary & Submit -->
            <div class="pt-2 border-t border-white/10 flex items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-black text-[#71808a] uppercase block">TỔNG TIỀN THANH TOÁN</span>
                    <span id="pos_total_display" class="text-2xl font-black text-[#d8ff3e]">0đ</span>
                    <input type="hidden" name="total_price" id="pos_total_price" value="0">
                </div>

                <button type="submit" class="flex items-center gap-2 bg-[#d8ff3e] text-[#0b1116] px-6 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider shadow-[4px_4px_0_#73940f] hover:translate-x-0.5 hover:translate-y-0.5 transition cursor-pointer">
                    <span>⚡ XÁC NHẬN KHÓA SÂN & IN POS</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: BOOKING DETAIL POPUP -->
<!-- ========================================== -->
<div id="detailModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden animate-fade-in">
    <div class="bg-[#121b21] border border-white/15 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative space-y-5">
        <button onclick="closeDetailModal()" class="absolute top-5 right-5 text-white/50 hover:text-white text-xl font-bold p-1">&times;</button>

        <div class="flex items-center gap-3 border-b border-white/10 pb-4">
            <span class="text-3xl">📋</span>
            <div>
                <h3 class="text-lg font-black text-white uppercase" id="det_title">Chi Tiết Đơn Đặt Sân</h3>
                <p class="text-xs text-[#71808a]" id="det_code">Mã check-in: -</p>
            </div>
        </div>

        <div class="space-y-3 text-xs text-white/90">
            <div class="flex justify-between py-1.5 border-b border-white/5">
                <span class="text-[#71808a] font-bold uppercase">Khách Hàng:</span>
                <span class="font-black text-white" id="det_customer">-</span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-white/5">
                <span class="text-[#71808a] font-bold uppercase">Số Điện Thoại:</span>
                <span class="font-mono font-bold text-white" id="det_phone">-</span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-white/5">
                <span class="text-[#71808a] font-bold uppercase">Khung Giờ Thi Đấu:</span>
                <span class="font-mono font-bold text-[#d8ff3e]" id="det_time">-</span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-white/5">
                <span class="text-[#71808a] font-bold uppercase">Tổng Tiền Sân:</span>
                <span class="font-black text-white" id="det_price">-</span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-white/5">
                <span class="text-[#71808a] font-bold uppercase">Loại Đơn & Kênh:</span>
                <span class="font-bold text-cyan-400" id="det_type">-</span>
            </div>
            <div class="py-1.5 border-b border-white/5">
                <span class="text-[#71808a] font-bold uppercase block mb-1">Ghi Chú Dịch Vụ:</span>
                <div class="bg-[#0b1116] p-2.5 rounded-xl text-white/80 font-mono text-[11px]" id="det_note">-</div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a id="det_checkin_link" href="#" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition">
                ✅ Xác Nhận Check-in Sân
            </a>
            <button onclick="closeDetailModal()" class="bg-[#0b1116] border border-white/10 text-white/80 hover:text-white px-4 py-2.5 rounded-xl text-xs font-bold">
                Đóng
            </button>
        </div>
    </div>
</div>

<!-- Scripts for POS Price Calculation & Modal Toggle -->
<script>
let serviceAddons = {};

function openPosModal(pitchId = null, pitchName = null, pitchPrice = null, startTime = '18:00', endTime = '19:00') {
    document.getElementById('posModal').classList.remove('hidden');
    
    if (pitchId) {
        document.getElementById('pos_pitch_id').value = pitchId;
    }
    if (startTime) {
        document.getElementById('pos_start_time').value = startTime;
    }
    if (endTime) {
        document.getElementById('pos_end_time').value = endTime;
    }
    
    updatePosPrice();
}

function closePosModal() {
    document.getElementById('posModal').classList.add('hidden');
}

function openDetailModal(b) {
    document.getElementById('detailModal').classList.remove('hidden');
    document.getElementById('det_title').textContent = b.customer_name + ' (Đơn #' + b.id + ')';
    document.getElementById('det_code').textContent = 'Mã Check-in: ' + (b.checkin_code || 'CKIN...');
    document.getElementById('det_customer').textContent = b.customer_name;
    document.getElementById('det_phone').textContent = b.customer_phone || 'N/A';
    document.getElementById('det_time').textContent = b.booking_date + ' (' + b.start_time.substr(0,5) + ' - ' + b.end_time.substr(0,5) + ')';
    document.getElementById('det_price').textContent = Number(b.total_price).toLocaleString('vi-VN') + 'đ';
    document.getElementById('det_type').textContent = (b.booking_type === 'walk_in' ? '🎟️ ĐẶT TẠI QUẦY' : '📲 ĐẶT TRÊN APP ONLINE');
    document.getElementById('det_note').textContent = b.services_note || 'Không có ghi chú';

    document.getElementById('det_checkin_link').href = 'index.php?action=staff_checkin&id=' + b.id;
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

function toggleServiceAddon(name, price, checkbox) {
    if (checkbox.checked) {
        serviceAddons[name] = price;
    } else {
        delete serviceAddons[name];
    }
    updatePosPrice();
}

function updatePosPrice() {
    const pitchSelect = document.getElementById('pos_pitch_id');
    if (!pitchSelect || pitchSelect.options.length === 0) return;

    const selectedOpt = pitchSelect.options[pitchSelect.selectedIndex];
    const pricePerHour = parseFloat(selectedOpt.getAttribute('data-price') || 0);

    const startTimeStr = document.getElementById('pos_start_time').value;
    const endTimeStr = document.getElementById('pos_end_time').value;

    const startHour = parseInt(startTimeStr.split(':')[0], 10);
    const endHour = parseInt(endTimeStr.split(':')[0], 10);

    let durationHours = endHour - startHour;
    if (durationHours <= 0) durationHours = 1;

    let basePrice = durationHours * pricePerHour;

    let addonPrice = 0;
    let addonNotes = [];
    for (let k in serviceAddons) {
        addonPrice += serviceAddons[k];
        addonNotes.push(k);
    }

    const totalPrice = basePrice + addonPrice;

    document.getElementById('pos_total_display').textContent = totalPrice.toLocaleString('vi-VN') + 'đ';
    document.getElementById('pos_total_price').value = totalPrice;

    let noteText = 'Đơn đặt trực tiếp tại quầy Lễ tân';
    if (addonNotes.length > 0) {
        noteText += ' | Kèm dịch vụ: ' + addonNotes.join(', ');
    }
    document.getElementById('pos_services_note').value = noteText;
}

// Initial trigger
document.addEventListener("DOMContentLoaded", function() {
    updatePosPrice();
});
</script>
