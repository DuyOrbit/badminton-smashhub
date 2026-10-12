<?php
// Home View matching SMASHHUB UI/UX Design System
$upcoming_bookings = [];
if (isset($_SESSION['user_id']) && class_exists('Database') && class_exists('Booking')) {
    try {
        $db_b = (new Database())->getConnection();
        $bk_model = new Booking($db_b);
        $stmt_b = $bk_model->readByUser($_SESSION['user_id']);
        $all_b = $stmt_b->fetchAll(PDO::FETCH_ASSOC);
        $today = date('Y-m-d');
        foreach ($all_b as $b) {
            if ($b['booking_date'] >= $today && in_array($b['status'], ['confirmed', 'pending'])) {
                $upcoming_bookings[] = $b;
            }
        }
        if (empty($upcoming_bookings) && !empty($all_b)) {
            $upcoming_bookings = array_slice($all_b, 0, 2);
        }
    } catch (Exception $e) {}
}

$is_logged_in = isset($_SESSION['user_id']);
$user_streak = 0;
if ($is_logged_in && isset($db_b)) {
    try {
        $stmt_m = $db_b->prepare("SELECT host_id, guest_id, match_result FROM matchmaking WHERE (host_id = ? OR guest_id = ?) AND status = 'completed' ORDER BY updated_at DESC LIMIT 10");
        $stmt_m->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
        $recent_matches = $stmt_m->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recent_matches as $rm) {
            $is_host = ($rm['host_id'] == $_SESSION['user_id']);
            if (($is_host && $rm['match_result'] === 'host_won') || (!$is_host && $rm['match_result'] === 'guest_won')) {
                $user_streak++;
            } else {
                break;
            }
        }
    } catch (Exception $e) {}
}

// Fallback defaults for user metrics
$user_elo_pts = $user_elo_pts ?? 600;
$user_badge_text = $user_badge_text ?? 'GUEST PLAYER';
$user_rep_val = $user_rep_val ?? 100;

// Accent gradients for pitch cards to mirror uxui
$accents = [
    'from-[#d8ff3e]/40 via-[#10181e]/80 to-[#121b21]',
    'from-[#52e0ff]/40 via-[#10181e]/80 to-[#121b21]',
    'from-[#ffb84d]/40 via-[#10181e]/80 to-[#121b21]',
    'from-[#b98aee]/40 via-[#10181e]/80 to-[#121b21]',
];
?>

<div class="space-y-8 animate-fade-in">
    <!-- Top Hero Row -->
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <div class="mb-2 flex items-center gap-2 text-[11px] font-black uppercase tracking-[.18em] text-[#d8ff3e]">
                <span class="h-2 w-2 animate-pulse rounded-full bg-[#ff7652]"></span>
                <?php echo date('l · d F Y'); ?> • SÂN CẦU HOẠT ĐỘNG TOÀN DIỆN
            </div>
            <h1 class="text-4xl font-black uppercase italic leading-none tracking-[-.06em] sm:text-5xl lg:text-6xl">
                Ready to <span class="text-[#d8ff3e]">rally?</span>
            </h1>
            <p class="mt-3 text-[13px] sm:text-[14px] text-[#7c8b93] max-w-xl">
                Sân chuẩn BWF. Trận cầu đỉnh cao. Tích hợp AI ghép trận theo Elo và bản đồ bán kính siêu tốc.
            </p>
        </div>
        
        <!-- CTA Quick Actions -->
        <div class="flex flex-wrap items-center gap-3">
            <a href="#pitches" class="flex items-center gap-2 rounded-xl bg-[#d8ff3e] px-5 py-3.5 text-[12px] font-black uppercase tracking-wide text-[#10181e] shadow-[4px_4px_0_#6d8a16] transition hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0_#6d8a16]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Đặt sân ngay
            </a>
            <a href="index.php?action=smart_match" class="flex items-center gap-2 rounded-xl border border-[#ff7652]/30 bg-[#ff7652]/10 px-4 py-3.5 text-[12px] font-black uppercase tracking-wide text-[#ff7652] hover:bg-[#ff7652]/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Smart Match AI
            </a>
            <a href="index.php?action=map_search" class="flex items-center gap-2 rounded-xl border border-white/10 bg-[#0b1116]/5 px-4 py-3.5 text-[12px] font-black uppercase tracking-wide text-white hover:bg-[#0b1116]/10 transition">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Bản đồ sân
            </a>
        </div>
    </div>

    <!-- 3 Metrics / Feature Highlight Cards Row -->
    <?php if ($is_logged_in): ?>
        <!-- TRẠNG THÁI ĐÃ ĐĂNG NHẬP: CHỈ SỐ CÁ NHÂN CỦA LÔNG THỦ -->
        <div class="grid gap-4 md:grid-cols-[1.7fr_1fr_1fr]">
            <!-- Metric 1: Hot Streak Cá Nhân -->
            <div class="relative overflow-hidden rounded-2xl border border-[#d8ff3e]/20 bg-[#17231d] p-5">
                <div class="absolute -right-8 -top-12 h-40 w-40 rounded-full bg-[#d8ff3e]/10 blur-2xl"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-wider text-[#d8ff3e]">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.48 12.35c-1.57-4.08-7.16-4.3-5.81-10.23.1-.44-.34-.78-.71-.56C8.84 3.9 4 9.17 4 14.5c0 4.41 3.59 8 8 8s8-3.59 8-8c0-.75-.12-1.46-.52-2.15zM12 20.5c-3.31 0-6-2.69-6-6 0-2.3 1.34-4.31 3.32-5.32-.4 2.76 1.83 4.82 4.68 4.82 1.3 0 2.47-.53 3.33-1.39C16.89 15.69 14.7 20.5 12 20.5z"/></svg>
                            Hot streak của bạn
                        </div>
                        <div class="mt-2 text-3xl font-black text-white">
                            <?php echo str_pad($user_streak, 2, '0', STR_PAD_LEFT); ?> <span class="text-lg text-[#81908b]">trận liên tiếp</span>
                        </div>
                        <p class="mt-1 text-[11px] text-[#82918b]">
                            <?php echo $user_streak > 0 ? 'Bạn đang ở phong độ đỉnh cao của mùa giải!' : 'Hãy ghép trận và giành chiến thắng để thiết lập chuỗi thắng!'; ?>
                        </p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#d8ff3e] text-[#17231d] shadow-[0_0_20px_rgba(216,255,62,0.4)]">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.48 12.35c-1.57-4.08-7.16-4.3-5.81-10.23.1-.44-.34-.78-.71-.56C8.84 3.9 4 9.17 4 14.5c0 4.41 3.59 8 8 8s8-3.59 8-8c0-.75-.12-1.46-.52-2.15z"/></svg>
                    </div>
                </div>
                <!-- Streak Bars -->
                <div class="mt-5 flex gap-1.5">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <span class="h-1.5 flex-1 rounded-full <?php echo $i <= $user_streak ? 'bg-[#d8ff3e]' : 'bg-[#0b1116]/30'; ?>"></span>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Metric 2: Elo Rating Cá Nhân -->
            <div class="rounded-2xl border border-white/8 bg-[#121b21] p-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#b98aee]/15 text-[#c89aff]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                </div>
                <div class="mt-4 text-[10px] font-black tracking-widest text-[#71818a] uppercase">ELO RATING CỦA BẠN</div>
                <div class="mt-1 text-3xl font-black text-white"><?php echo number_format($user_elo_pts); ?></div>
                <div class="mt-1 text-[10px] font-bold text-[#d8ff3e]"><?php echo $user_badge_text; ?></div>
            </div>

            <!-- Metric 3: Điểm Uy Tín Cá Nhân -->
            <div class="rounded-2xl border border-white/8 bg-[#121b21] p-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#52e0ff]/15 text-[#52e0ff]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div class="mt-4 text-[10px] font-black tracking-widest text-[#71818a] uppercase">ĐIỂM UY TÍN FAIR-PLAY</div>
                <div class="mt-1 text-3xl font-black text-white"><?php echo $user_rep_val; ?><span class="text-lg text-[#71818a]">/100</span></div>
                <div class="mt-1 text-[10px] font-bold text-cyan-400">
                    <?php echo ($user_rep_val >= 90) ? 'Top 5% Vận động viên mẫu mực' : (($user_rep_val >= 70) ? 'Uy tín tốt · Sẵn sàng ghép' : 'Cảnh báo vi phạm'); ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- TRẠNG THÁI KHÁCH VÃNG LAI (CHƯA ĐĂNG NHẬP): GIỚI THIỆU TÍNH NĂNG NỔI BẬT HỆ THỐNG -->
        <div class="grid gap-4 md:grid-cols-[1.7fr_1fr_1fr]">
            <!-- Feature 1: Ghép Kèo AI Cân Trình (1.7fr) -->
            <div class="relative overflow-hidden rounded-2xl border border-[#d8ff3e]/30 bg-gradient-to-br from-[#17231d] via-[#121b21] to-[#0b1116] p-5 shadow-lg">
                <div class="absolute -right-10 -top-12 h-44 w-44 rounded-full bg-[#d8ff3e]/15 blur-3xl pointer-events-none"></div>
                <div class="relative flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-wider text-[#d8ff3e]">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Công Nghệ Bắt Cặp AI Độc Quyền
                        </div>
                        <div class="mt-2 text-2xl sm:text-3xl font-black text-white">
                            Ghép Kèo AI <span class="text-[#d8ff3e]">Cân Trình</span>
                        </div>
                        <p class="mt-1.5 text-xs text-[#82918b] leading-relaxed max-w-lg">
                            Tự động bắt cặp đối thủ có điểm Elo tương đương trong bán kính GPS gần bạn. Không lo lệch trình, không sợ bùng kèo!
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-white/5 flex flex-wrap items-center gap-3">
                        <a href="index.php?action=register" class="inline-flex items-center gap-1.5 rounded-xl bg-[#d8ff3e] px-4 py-2.5 text-xs font-black uppercase text-[#10181e] shadow-[3px_3px_0_#6d8a16] transition hover:translate-x-0.5 hover:translate-y-0.5">
                            <span>⚡</span> Đăng Ký Nhận 600 Elo Khởi Điểm
                        </a>
                        <a href="index.php?action=smart_match" class="inline-flex items-center gap-1 text-xs font-bold text-white/70 hover:text-white transition">
                            Tìm hiểu Smart Match →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Feature 2: Mạng Lưới Sân BWF (1fr) -->
            <div class="rounded-2xl border border-white/8 bg-[#121b21] p-5 flex flex-col justify-between hover:border-white/20 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#b98aee]/15 text-[#c89aff]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-[#b98aee] bg-[#b98aee]/10 px-2 py-0.5 rounded-full border border-[#b98aee]/20">BÁN KÍNH GPS</span>
                    </div>
                    <div class="mt-3 text-[10px] font-black tracking-widest text-[#71818a] uppercase">Mạng Lưới Sân Bãi</div>
                    <div class="mt-1 text-2xl font-black text-white">100% Sân Thảm BWF</div>
                    <p class="mt-1 text-xs text-[#82918b] leading-relaxed">
                        Định vị GPS quét sân trống siêu tốc, đặt lịch trực tuyến và check-in QR code thuận tiện.
                    </p>
                </div>
                <div class="mt-3 pt-2 border-t border-white/5">
                    <a href="index.php?action=map_search" class="text-xs font-bold text-[#b98aee] hover:underline flex items-center gap-1">
                        Xem bản đồ sân gần bạn →
                    </a>
                </div>
            </div>

            <!-- Feature 3: Bảo Chứng Fair-Play (1fr) -->
            <div class="rounded-2xl border border-white/8 bg-[#121b21] p-5 flex flex-col justify-between hover:border-white/20 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#52e0ff]/15 text-[#52e0ff]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-cyan-400 bg-cyan-400/10 px-2 py-0.5 rounded-full border border-cyan-400/20">VĂN MINH 100%</span>
                    </div>
                    <div class="mt-3 text-[10px] font-black tracking-widest text-[#71818a] uppercase">Bảo Chứng Kèo Đấu</div>
                    <div class="mt-1 text-2xl font-black text-white">Chống Bùng Sân</div>
                    <p class="mt-1 text-xs text-[#82918b] leading-relaxed">
                        Hệ thống Uy Tín Fair-Play phạt nghiêm người bùng kèo và tự động hoàn cọc minh bạch.
                    </p>
                </div>
                <div class="mt-3 pt-2 border-t border-white/5">
                    <a href="index.php?action=login" class="text-xs font-bold text-cyan-400 hover:underline flex items-center gap-1">
                        Đăng nhập trải nghiệm →
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Search & Filter Bar -->
    <div id="search-bar" class="rounded-2xl border border-white/8 bg-[#121b21] p-4 sm:p-5">
        <form action="index.php" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <input type="hidden" name="action" value="home">
            
            <!-- Keyword -->
            <div class="flex-grow w-full md:w-auto">
                <label for="q" class="block text-[11px] font-black text-[#71818a] uppercase tracking-widest mb-2 ml-1">Tên sân / Địa chỉ</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#71818a]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="q" id="q" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" class="block w-full pl-10 pr-4 py-3 bg-[#0b1116] border border-white/10 text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-[13px] font-medium" placeholder="Bạn muốn đánh cầu ở cụm sân nào?">
                </div>
            </div>

            <!-- Floor Type -->
            <div class="w-full md:w-56 flex-shrink-0">
                <label for="type" class="block text-[11px] font-black text-[#71818a] uppercase tracking-widest mb-2 ml-1">Loại Mặt Thảm</label>
                <div class="relative">
                    <select name="type" id="type" class="block w-full pl-3.5 pr-8 py-3 bg-[#0b1116] border border-white/10 text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-[13px] font-medium appearance-none cursor-pointer">
                        <option value="">Tất cả các loại thảm</option>
                        <option value="Thảm PVC Tiêu Chuẩn BWF" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Thảm PVC Tiêu Chuẩn BWF') ? 'selected' : ''; ?>>Thảm BWF Tiêu Chuẩn</option>
                        <option value="Thảm PVC Yonex" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Thảm PVC Yonex') ? 'selected' : ''; ?>>Thảm PVC Yonex</option>
                        <option value="Sân Gỗ Thi Đấu" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Sân Gỗ Thi Đấu') ? 'selected' : ''; ?>>Sân Gỗ Thi Đấu</option>
                        <option value="Thảm Alite" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Thảm Alite') ? 'selected' : ''; ?>>Thảm Alite</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-[#71818a]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Max Price -->
            <div class="w-full md:w-48 flex-shrink-0">
                <label for="max_price" class="block text-[11px] font-black text-[#71818a] uppercase tracking-widest mb-2 ml-1">Giá tối đa / giờ</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#d8ff3e] font-black text-xs">₫</div>
                    <input type="number" name="max_price" id="max_price" step="10000" min="0" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>" class="block w-full pl-7 pr-3 py-3 bg-[#0b1116] border border-white/10 text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-[13px] font-medium" placeholder="VD: 100000">
                </div>
            </div>

            <!-- Search Action Buttons -->
            <div class="w-full md:w-auto flex-shrink-0 flex gap-2">
                <button type="submit" class="w-full md:w-auto px-6 py-3 bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] font-black rounded-xl shadow-[3px_3px_0_#73940f] transition flex items-center justify-center gap-2 text-[12px] uppercase">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span>Lọc Sân</span>
                </button>
                <a href="index.php?action=map_search" class="hidden sm:flex px-4 py-3 rounded-xl border border-cyan-500/30 bg-cyan-500/10 text-cyan-300 hover:bg-cyan-500/20 text-[12px] font-black uppercase items-center gap-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    <span>Bán Kính</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Main Grid: Left Hot Courts, Right Widgets -->
    <div id="pitches" class="mt-9 grid gap-8 xl:grid-cols-[1.45fr_.75fr]">
        <!-- Left: Hot Courts (Sân Đang Hot) -->
        <div>
            <div class="mb-5 flex items-end justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black uppercase italic tracking-tight text-white">Sân đang hot</h2>
                        <span class="rounded bg-[#ff7652] px-2 py-0.5 text-[9px] font-black uppercase text-white">BWF Standards</span>
                    </div>
                    <p class="mt-1 text-[12px] text-[#708089]">Được cộng đồng lông thủ đánh giá cao nhất hôm nay</p>
                </div>
                <a href="index.php?action=map_search" class="text-[11px] font-black uppercase text-[#d8ff3e] hover:underline flex items-center gap-1">
                    Xem bản đồ
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>

            <!-- Courts Grid -->
            <?php if (empty($pitches)): ?>
                <div class="rounded-2xl border border-white/8 bg-[#121b21] p-12 text-center">
                    <div class="text-4xl mb-3">🏸</div>
                    <h3 class="text-lg font-black text-white">Không tìm thấy sân cầu lông nào</h3>
                    <p class="text-xs text-[#708089] mt-1 max-w-sm mx-auto">Không có kết quả nào phù hợp với bộ lọc của bạn. Hãy thử thay đổi mức giá hoặc loại thảm.</p>
                    <a href="index.php" class="mt-4 inline-block px-5 py-2.5 rounded-xl bg-[#d8ff3e] text-[#0b1116] text-xs font-black uppercase shadow-[2px_2px_0_#73940f]">Xóa bộ lọc</a>
                </div>
            <?php else: ?>
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($pitches as $i => $court): 
                        $accent = $accents[$i % count($accents)];
                        $rating = '4.9';
                        if ($i % 3 == 1) $rating = '4.8';
                        if ($i % 3 == 2) $rating = '4.7';
                    ?>
                        <article class="group relative overflow-hidden rounded-2xl border border-white/8 bg-[#121b21] transition hover:-translate-y-1.5 hover:border-[#d8ff3e]/40 flex flex-col">
                            <!-- Card Header Image & Overlay -->
                            <div class="relative h-36 bg-gradient-to-br <?php echo $accent; ?> overflow-hidden">
                                <?php if(!empty($court['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($court['image_url']); ?>" alt="<?php echo htmlspecialchars($court['name']); ?>" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-60 transition-transform duration-500 group-hover:scale-110">
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-gradient-to-t from-[#121b21] via-transparent to-transparent"></div>
                                <div class="relative p-3.5 flex justify-between items-start">
                                    <span class="rounded bg-black/50 backdrop-blur-md px-2 py-1 text-[9px] font-black uppercase text-white border border-white/10">
                                        <?php echo htmlspecialchars($court['type']); ?>
                                    </span>
                                    <span class="rounded bg-[#d8ff3e] text-[#0b1116] px-2 py-0.5 text-[9px] font-black uppercase shadow-sm">
                                        <?php echo ($court['quantity'] ?? 6); ?> sân
                                    </span>
                                </div>
                                <div class="absolute bottom-2.5 left-3.5 flex items-center gap-2">
                                    <span class="rounded bg-black/60 backdrop-blur-md px-2 py-0.5 text-[10px] font-black text-white flex items-center border border-white/10">
                                        <svg class="w-3 h-3 text-[#ffd45b] fill-current mr-1" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <?php echo $rating; ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-4 flex-1 flex flex-col">
                                <h3 class="font-black text-[15px] text-white truncate" title="<?php echo htmlspecialchars($court['name']); ?>">
                                    <?php echo htmlspecialchars($court['name']); ?>
                                </h3>
                                <div class="mt-1.5 flex items-start gap-1 text-[11px] text-[#7f8e95] line-clamp-1">
                                    <svg class="w-3.5 h-3.5 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                    <span><?php echo htmlspecialchars($court['location']); ?></span>
                                </div>

                                <div class="mt-4 pt-3 border-t border-white/5 flex items-end justify-between">
                                    <div>
                                        <div class="text-[10px] font-bold text-[#71818a] uppercase">Chi phí thuê</div>
                                        <div class="text-[11px] text-[#71818a]">
                                            từ <b class="text-[#d8ff3e] text-base font-black"><?php echo number_format($court['price_per_hour'], 0, ',', '.'); ?>đ</b><span class="text-[10px]">/h</span>
                                        </div>
                                    </div>
                                    <a href="index.php?action=book&id=<?php echo $court['id']; ?>" class="rounded-xl bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] px-3.5 py-2 text-[11px] font-black uppercase tracking-wider shadow-[2px_2px_0_#73940f] transition flex items-center gap-1">
                                        <span>Đặt</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Sidebar Widgets (Smart Match & Map Teaser) -->
        <div class="space-y-6">
            <!-- Widget 1: Smart Match (AI) -->
            <aside class="rounded-2xl border border-white/8 bg-[#121b21] p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#d8ff3e]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.4 7.2h7.6l-6 4.8 2.4 7.2-6-4.8-6 4.8 2.4-7.2-6-4.8h7.6z"/></svg>
                            <h2 class="text-lg font-black uppercase italic tracking-tight text-white">Smart Match</h2>
                        </div>
                        <p class="mt-1 text-[11px] text-[#71818a]">Trận đấu phù hợp với bạn theo Elo</p>
                    </div>
                    <a href="index.php?action=smart_match" class="text-[#71818a] hover:text-[#d8ff3e] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>

                <!-- Match Recommendation Box -->
                <div class="mt-4 rounded-xl border border-[#d8ff3e]/20 bg-[#d8ff3e]/5 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-[#d8ff3e]">98% MATCH • BẬC ELO</span>
                        <span class="text-[10px] font-bold text-[#80908a]">Tối nay · Khung Vàng</span>
                    </div>
                    <h3 class="mt-2 text-[14px] font-black text-white">Đánh Kèo Đôi Cùng Trình Độ</h3>
                    <p class="text-[11px] text-[#82918d] mt-1 leading-relaxed">
                        Thuật toán tự động tìm đối thủ có chênh lệch $\Delta Elo \le 100$, hỗ trợ cưa đôi 50/50 tiền sân.
                    </p>
                    <div class="mt-3.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="flex -space-x-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-[#17231d] bg-[#f3a7a0] text-[8px] font-black text-[#10181e]">MT</span>
                                <span class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-[#17231d] bg-[#a6d6ed] text-[8px] font-black text-[#10181e]">TD</span>
                                <span class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-[#17231d] bg-[#d7c0ee] text-[8px] font-black text-[#10181e]">+1</span>
                            </div>
                            <span class="text-[10px] text-[#82918d]">Đang trong hàng đợi</span>
                        </div>
                    </div>
                    <a href="index.php?action=smart_match" class="mt-4 block w-full text-center rounded-lg bg-[#d8ff3e] hover:bg-[#c6f030] py-2.5 text-[11px] font-black uppercase text-[#10181e] shadow-[2px_2px_0_#73940f] transition">
                        Vào Hàng Chờ Tìm Trận (AI)
                    </a>
                </div>

                <a href="index.php?action=matchmaking" class="mt-3.5 flex items-center justify-center gap-1 text-[11px] font-black uppercase text-[#d8ff3e] hover:underline">
                    Xem Bảng Kèo Mở
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </aside>

            <!-- Widget 2: Radius Map Search Teaser -->
            <aside class="rounded-2xl border border-white/8 bg-[#121b21] p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            <h2 class="text-lg font-black uppercase italic tracking-tight text-white">Bản Đồ Bán Kính</h2>
                        </div>
                        <p class="mt-1 text-[11px] text-[#71818a]">Tìm sân quanh vị trí GPS của bạn</p>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-cyan-500/20 bg-cyan-500/5 p-4">
                    <p class="text-[12px] text-[#94a3b8] leading-relaxed">
                        Định vị GPS tự động và quét tất cả cụm sân trong bán kính từ <b class="text-cyan-300">1km đến 15km</b> với công thức Haversine chính xác.
                    </p>
                    <a href="index.php?action=map_search" class="mt-4 block w-full text-center rounded-lg bg-cyan-400 hover:bg-cyan-300 py-2.5 text-[11px] font-black uppercase text-[#0b1116] shadow-[2px_2px_0_#0e7490] transition">
                        Mở Bản Đồ Quét Bán Kính
                    </a>
                </div>
            </aside>
        </div>
    </div>

    <!-- Bottom Section: Lịch Sắp Tới (Upcoming Bookings) -->
    <div class="mt-8 rounded-2xl border border-white/8 bg-[#121b21] p-5 sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black uppercase italic tracking-tight text-white">Lịch sắp tới</h2>
                <p class="mt-1 text-[12px] text-[#708089]">Đừng bỏ lỡ những trận cầu hấp dẫn của bạn</p>
            </div>
            <a href="index.php?action=my_bookings" class="rounded-lg border border-white/10 p-2 text-[#819098] hover:text-white hover:bg-[#0b1116]/5 transition" title="Xem tất cả lịch">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </a>
        </div>

        <div class="mt-5 flex flex-col gap-3 md:flex-row">
            <?php if (!empty($upcoming_bookings)): ?>
                <?php foreach ($upcoming_bookings as $bk): 
                    $dayName = 'TH ' . (date('N', strtotime($bk['booking_date'])) + 1);
                    if ($dayName == 'TH 8') $dayName = 'CN';
                    $dateNum = date('d', strtotime($bk['booking_date']));
                    $timeStr = substr($bk['start_time'], 0, 5) . ' – ' . substr($bk['end_time'], 0, 5);
                ?>
                    <div class="flex flex-1 items-center gap-4 rounded-xl border border-white/6 bg-[#0d151b] p-3.5 hover:border-white/20 transition">
                        <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-[#d8ff3e] text-[#10181e] font-black">
                            <span class="text-[9px]"><?php echo $dayName; ?></span>
                            <b class="text-xl leading-5"><?php echo $dateNum; ?></b>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-[13px] font-black text-white"><?php echo htmlspecialchars($bk['pitch_name'] ?? 'Cụm Sân Cầu Lông'); ?></h3>
                            <div class="mt-1 flex items-center gap-1.5 text-[10px] text-[#7d8c94]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span><?php echo $timeStr; ?></span>
                                <span class="mx-1">·</span>
                                <span class="font-bold text-amber-400"><?php echo htmlspecialchars($bk['checkin_code'] ?? 'MÃ SÂN'); ?></span>
                            </div>
                        </div>
                        <span class="hidden sm:block rounded bg-[#d8ff3e]/10 px-2.5 py-1 text-[9px] font-black text-[#d8ff3e] uppercase border border-[#d8ff3e]/20">
                            <?php echo ($bk['status'] == 'confirmed') ? 'ĐÃ XÁC NHẬN' : 'CHỜ DUYỆT'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-4 rounded-xl border border-white/6 bg-[#0d151b] p-4 text-[#7d8c94]">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#0b1116]/5 text-[#d8ff3e]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="text-[12px]">
                            <p class="font-bold text-white">Bạn chưa có lịch đặt sân nào sắp tới.</p>
                            <p class="text-[11px] text-[#71818a]">Khởi động đam mê bằng việc chọn một sân đấu hoặc ghép trận cùng cộng đồng ngay hôm nay!</p>
                        </div>
                    </div>
                    <a href="#pitches" class="px-4 py-2 rounded-lg bg-[#d8ff3e] text-[#10181e] font-black text-[11px] uppercase tracking-wider shadow-[2px_2px_0_#73940f] hover:translate-x-0.5 transition flex-shrink-0">
                        Đặt sân ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
