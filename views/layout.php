<?php
// Layout wrapper for BadmintonConnect / SmashHub
$clean_user_name = '';
$user_role = $_SESSION['user_role'] ?? 'guest';
$user_initials = 'KH';
$user_badge_text = 'GUEST PLAYER';
$user_elo_pts = 100;
$user_rep_val = 100;
$current_action = $_GET['action'] ?? 'home';

if (isset($_SESSION['user_id'])) {
    $clean_user_name = trim(preg_replace('/\s*\(.*?\)/', '', $_SESSION['user_name'] ?? 'Vận động viên'));
    $words = explode(' ', $clean_user_name);
    $user_initials = '';
    foreach (array_slice($words, -2) as $w) {
        $user_initials .= mb_substr($w, 0, 1, 'UTF-8');
    }
    if (empty($user_initials)) {
        $user_initials = 'VD';
    }
    $user_initials = mb_strtoupper($user_initials, 'UTF-8');

    // Retrieve Elo and Reputation if classes exist
    if (class_exists('Database')) {
        try {
            $db_inst = (new Database())->getConnection();
            if (class_exists('Ranking')) {
                $rk_inst = new Ranking($db_inst);
                $pts = $rk_inst->getSeasonHighestPoints($_SESSION['user_id'], Ranking::getCurrentSeason());
                if ($pts > 0) $user_elo_pts = $pts;
                $rk_name = $rk_inst->getRankName($user_elo_pts);
                if ($user_role === 'user') {
                    $user_badge_text = mb_strtoupper($rk_name, 'UTF-8') . ' · ' . $user_elo_pts . ' ELO';
                } else {
                    $role_names = ['staff' => 'NHÂN VIÊN SÂN', 'owner' => 'CHỦ CỤM SÂN', 'admin' => 'ADMIN TỔNG'];
                    $user_badge_text = $role_names[$user_role] ?? mb_strtoupper($user_role, 'UTF-8');
                }
            }
            if (class_exists('Reputation')) {
                $rp_inst = new Reputation($db_inst);
                $rep_data = $rp_inst->getUserReputation($_SESSION['user_id']);
                $user_rep_val = $rep_data['score'];
            }
        } catch (Exception $e) {}
    }
}
?>
<!DOCTYPE html>
<html lang="vi" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
    <script>
        window.csrfToken = "<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>";
        window.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.currentUserId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?>;
    </script>
    <title><?php echo htmlspecialchars($page_title ?? 'Trang Chủ'); ?> - SMASHHUB Badminton</title>
    
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        volt: '#d8ff3e',
                        coral: '#ff7652',
                        cyanAccent: '#52e0ff',
                        purpleAccent: '#b98aee',
                        darkbg: '#0b1116',
                        darksidebar: '#10181e',
                        darkheader: '#0d151b',
                        darkcard: '#121b21',
                        darkstreak: '#17231d',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #0b1116;
            color: #ffffff;
        }
        ::selection {
            background-color: #d8ff3e;
            color: #0b1116;
        }
        /* Custom sleek scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0b1116;
        }
        ::-webkit-scrollbar-thumb {
            background: #1c2730;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #2a3a47;
        }
        .brutal-shadow {
            box-shadow: 4px 4px 0 #73940f;
        }
        .brutal-btn-shadow {
            box-shadow: 4px 4px 0 #6d8a16;
        }
        .brutal-btn-shadow:hover {
            box-shadow: 2px 2px 0 #6d8a16;
            transform: translate(2px, 2px);
        }
        
        /* Base styles for form elements to prevent white-on-white text */
        input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]), 
        textarea, select {
            background-color: #121b21;
            color: #ffffff;
        }
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }
    </style>
</head>
<body class="bg-[#0b1116] text-white selection:bg-[#d8ff3e] selection:text-[#0b1116] min-h-screen flex flex-col antialiased">

    <!-- Mobile Sidebar Backdrop -->
    <div id="mobileBackdrop" onclick="closeSidebar()" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-40 hidden transition-opacity lg:hidden"></div>

    <!-- Fixed Left Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-[260px] flex-col border-r border-white/10 bg-[#10181e] px-5 py-6 transition-transform duration-300 -translate-x-full lg:translate-x-0">
        <!-- Logo Header -->
        <div class="flex items-center justify-between px-2">
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="relative flex h-11 w-11 -rotate-6 items-center justify-center rounded-xl bg-[#d8ff3e] text-[#0b1116] shadow-[4px_4px_0_#73940f] transition-transform group-hover:scale-105 group-hover:rotate-0">
                    <span class="text-2xl font-black italic">S</span>
                    <span class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-[#ff7652]"></span>
                </div>
                <div>
                    <div class="text-lg font-black tracking-tight text-white">SMASH<span class="text-[#d8ff3e]">HUB</span></div>
                    <div class="text-[9px] font-bold uppercase tracking-[.22em] text-[#71808a]">Play Harder</div>
                </div>
            </a>
            <button aria-label="Đóng menu" class="lg:hidden text-[#84939b] hover:text-white p-1" onclick="closeSidebar()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Section: Menu Chính -->
        <div class="mt-8 px-2 text-[10px] font-black uppercase tracking-[.2em] text-[#61717b]">Menu chính</div>
        <nav class="mt-3 space-y-1.5">
            <?php if($user_role === 'user' || $user_role === 'guest'): ?>
            <!-- Tổng quan -->
            <a href="index.php?action=home" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-[13px] font-bold transition <?php echo ($current_action == 'home') ? 'bg-[#d8ff3e] text-[#0d1715] shadow-[0_0_22px_rgba(216,255,62,.15)]' : 'text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white'; ?>">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="flex-1">Tổng quan</span>
            </a>

            <!-- Đặt sân -->
            <a href="index.php?action=home#pitches" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-[13px] font-bold transition <?php echo (strpos($_SERVER['REQUEST_URI'], '#pitches') !== false) ? 'bg-[#d8ff3e] text-[#0d1715]' : 'text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white'; ?>">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="flex-1">Đặt sân</span>
            </a>

            <!-- Smart Match (AI) -->
            <a href="index.php?action=smart_match" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-[13px] font-bold transition <?php echo ($current_action == 'smart_match') ? 'bg-[#d8ff3e] text-[#0d1715] shadow-[0_0_22px_rgba(216,255,62,.15)]' : 'text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white'; ?>">
                <svg class="w-[18px] h-[18px] flex-shrink-0 text-amber-400 group-hover:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span class="flex-1">Smart Match</span>
                <span class="rounded bg-[#ff7652] px-1.5 py-0.5 text-[8px] font-black uppercase text-white shadow-sm">AI</span>
            </a>

            <!-- Bảng Kèo -->
            <a href="index.php?action=matchmaking" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-[13px] font-bold transition <?php echo ($current_action == 'matchmaking' || $current_action == 'my_matches') ? 'bg-[#d8ff3e] text-[#0d1715] shadow-[0_0_22px_rgba(216,255,62,.15)]' : 'text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white'; ?>">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="flex-1">Bảng Kèo</span>
                <span class="rounded bg-[#d8ff3e]/20 text-[#d8ff3e] px-1.5 py-0.5 text-[8px] font-black uppercase">HOT</span>
            </a>

            <!-- Câu Lạc Bộ & Hội Nhóm -->
            <a href="index.php?action=clubs" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-[13px] font-bold transition <?php echo (strpos($current_action, 'club') !== false) ? 'bg-[#d8ff3e] text-[#0d1715] shadow-[0_0_22px_rgba(216,255,62,.15)]' : 'text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white'; ?>">
                <svg class="w-[18px] h-[18px] flex-shrink-0 text-cyan-400 group-hover:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span class="flex-1">Câu Lạc Bộ</span>
                <span class="rounded bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 px-1.5 py-0.5 text-[8px] font-black uppercase">CLB</span>
            </a>

            <?php endif; ?>

            <!-- Tin Nhắn Chat -->
            <a href="index.php?action=chat" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-[13px] font-bold transition <?php echo ($current_action == 'chat') ? 'bg-[#d8ff3e] text-[#0d1715]' : 'text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white'; ?>">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="flex-1">Tin nhắn Chat</span><span id="chat-badge-global" class="hidden bg-red-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full ml-auto shadow-sm">0</span>
            </a>
        </nav>

        <!-- Section: Cá Nhân / Quản Trị -->
        <div class="mt-8 px-2 text-[10px] font-black uppercase tracking-[.2em] text-[#61717b]">
            <?php echo isset($_SESSION['user_id']) ? 'Cá nhân & Quản trị' : 'Tài khoản'; ?>
        </div>
        <nav class="mt-3 space-y-1.5">
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($user_role === 'user'): ?>
                    <a href="index.php?action=my_bookings" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold transition <?php echo ($current_action == 'my_bookings') ? 'bg-[#d8ff3e] text-[#0d1715]' : 'text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white'; ?>">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <span>Lịch sử đặt</span>
                    </a>
                    <a href="index.php?action=user_profile" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold transition <?php echo ($current_action == 'user_profile') ? 'bg-[#d8ff3e] text-[#0d1715]' : 'text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white'; ?>">
                        <svg class="w-[18px] h-[18px] text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span class="flex-1">Điểm uy tín</span>
                        <span class="text-[10px] font-black text-cyan-400"><?php echo $user_rep_val; ?>/100</span>
                    </a>
                <?php endif; ?>

                <!-- Role specific actions -->
                <?php if($user_role === 'admin'): ?>
                    <a href="index.php?action=admin_dashboard" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold bg-purple-500/10 text-purple-300 hover:bg-purple-500/20 border border-purple-500/20 transition">
                        <span>🛡️ Admin Panel</span>
                    </a>
                    <a href="index.php?action=admin_users" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white transition">
                        <span>👥 QL Người Dùng</span>
                    </a>
                    <a href="index.php?action=admin_pitches" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white transition">
                        <span>🏟️ QL Sân Bóng</span>
                    </a>
                <?php elseif($user_role === 'staff'): ?>
                    <a href="index.php?action=staff_dashboard" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold bg-amber-500/10 text-amber-300 hover:bg-amber-500/20 border border-amber-500/20 transition">
                        <span>🎟️ Bàn Trực & Check-in</span>
                    </a>
                <?php elseif($user_role === 'owner'): ?>
                    <a href="index.php?action=owner_dashboard" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20 border border-emerald-500/20 transition">
                        <span>📊 Dashboard Chủ Sân</span>
                    </a>
                    <a href="index.php?action=staff_dashboard" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold bg-amber-500/10 text-amber-300 hover:bg-amber-500/20 border border-amber-500/20 transition">
                        <span>🎟️ Bàn Trực & Check-in</span>
                    </a>
                    <a href="index.php?action=owner_pitches" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white transition">
                        <span>🏸 Quản Lý Cụm Sân</span>
                    </a>
                    <a href="index.php?action=owner_services" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white transition">
                        <span>🥤 Dịch Vụ Sân</span>
                    </a>
                    <a href="index.php?action=owner_schedule" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white transition">
                        <span>📅 Lịch Đặt Sân</span>
                    </a>
                    <a href="index.php?action=owner_staff" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white transition">
                        <span>👥 Quản Lý Nhân Viên</span>
                    </a>
                <?php endif; ?>


            <?php else: ?>
                <a href="index.php?action=login" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold text-[#84939b] hover:bg-[#0b1116]/5 hover:text-white transition">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span>Đăng Nhập</span>
                </a>
                <a href="index.php?action=register" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-bold bg-[#d8ff3e] text-[#0b1116] shadow-[2px_2px_0_#73940f] transition">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    <span>Đăng Ký Thành Viên</span>
                </a>
            <?php endif; ?>
        </nav>


    </aside>

    <!-- Main Section (offset by sidebar width on lg) -->
    <section class="lg:pl-[260px] flex-1 flex flex-col min-h-screen">
        <!-- Top Header -->
        <header class="sticky top-0 z-30 flex h-[78px] items-center justify-between border-b border-white/10 bg-[#0d151b]/85 px-4 sm:px-8 lg:px-10 backdrop-blur-xl">
            <!-- Left: Mobile Menu Button & Breadcrumbs -->
            <div class="flex items-center gap-3">
                <button aria-label="Mở menu" class="lg:hidden p-2 rounded-xl text-[#84939b] hover:text-white hover:bg-[#0b1116]/5 transition" onclick="openSidebar()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="hidden items-center gap-2 text-[11px] font-bold text-[#687981] sm:flex tracking-wider">
                    <a href="index.php" class="hover:text-white transition">SMASHHUB</a>
                    <svg class="w-3.5 h-3.5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <span class="text-[#d8ff3e] uppercase font-black"><?php echo htmlspecialchars($page_title ?? 'TỔNG QUAN'); ?></span>
                </div>
            </div>

            <!-- Right: Search, Notification, Profile Chip, Auth Actions -->
            <div class="flex items-center gap-4 sm:gap-5">
                <?php if($user_role === 'user' || $user_role === 'guest'): ?>
                <a href="index.php?action=map_search" aria-label="Tìm kiếm vị trí" class="hidden sm:flex items-center gap-1.5 text-xs text-[#7a8991] hover:text-white bg-[#0b1116]/5 hover:bg-[#0b1116]/10 px-3 py-2 rounded-xl transition border border-white/5">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span>Tìm sân quanh bạn</span>
                </a>

                <!-- Notifications Dropdown -->
                <div class="relative" id="notif-dropdown-container">
                    <button id="notif-btn" class="relative text-[#7a8991] hover:text-white transition p-2 rounded-xl hover:bg-[#0b1116]/5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span id="notif-badge" class="hidden absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-[#ff7652]"></span>
                        <span id="notif-ping" class="hidden absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-[#ff7652] animate-ping"></span>
                    </button>
                    <!-- Dropdown content -->
                    <div id="notif-menu" class="hidden absolute right-0 mt-2 w-80 bg-[#121b21] rounded-2xl shadow-lg border border-white/10 z-50 overflow-hidden">
                        <div class="p-4 border-b border-white/5 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-white">Thông Báo</h3>
                        </div>
                        <div id="notif-list" class="max-h-80 overflow-y-auto">
                            <!-- Items will be injected here via JS -->
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="h-7 w-px bg-[#0b1116]/10"></div>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="index.php?action=user_profile" class="flex items-center gap-3 group">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-[#d8ff3e] bg-[#1a2b24] text-[11px] font-black text-[#d8ff3e] shadow-[0_0_12px_rgba(216,255,62,0.3)]">
                            <?php echo $user_initials; ?>
                        </div>
                        <div class="hidden text-left sm:block">
                            <div class="text-[12px] font-black group-hover:text-[#d8ff3e] transition"><?php echo htmlspecialchars($clean_user_name); ?></div>
                            <div class="text-[10px] font-bold text-[#71818a] tracking-wider"><?php echo $user_badge_text; ?></div>
                        </div>
                    </a>
                    <a href="index.php?action=logout" title="Đăng xuất" class="p-2 rounded-xl text-red-400/80 hover:text-red-400 hover:bg-red-500/10 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </a>
                <?php else: ?>
                    <a href="index.php?action=login" class="text-[12px] font-bold text-[#84939b] hover:text-white px-3 py-2 transition">Đăng Nhập</a>
                    <a href="index.php?action=register" class="bg-[#d8ff3e] text-[#0b1116] px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider shadow-[3px_3px_0_#73940f] hover:translate-x-0.5 hover:translate-y-0.5 transition">Đăng Ký</a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Main Dynamic View Content -->
        <main class="flex-1 w-full max-w-[1400px] mx-auto px-4 sm:px-8 lg:px-10 py-7">
            <?php require_once 'views/' . $content; ?>
        </main>

        <!-- Modern Neo-Sport Footer -->
        <footer class="mt-auto border-t border-white/8 bg-[#0d151b] py-6 px-4 sm:px-8 lg:px-10 text-xs text-[#687981]">
            <div class="max-w-[1400px] mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="font-black text-white">SMASH<span class="text-[#d8ff3e]">HUB</span></span>
                    <span class="text-white/20">|</span>
                    <span>Nền tảng đặt sân & Ghép trận thông minh theo Elo (KLTN 2026)</span>
                </div>
                <div class="flex items-center gap-4 text-[11px]">
                    <a href="index.php?action=map_search" class="hover:text-white transition">Bản Đồ Bán Kính</a>
                    <span>•</span>
                    <a href="index.php?action=smart_match" class="hover:text-white transition">Smart Match AI</a>
                    <span>•</span>
                    <a href="index.php?action=matchmaking" class="hover:text-white transition">Bảng Kèo</a>
                    <span>•</span>
                    <span class="text-[#71808a]">&copy; 2026 SmashHub</span>
                </div>
            </div>
        </footer>
    </section>

    <!-- Sidebar Mobile Toggle Script -->
    <script>
        function openSidebar() {
            document.getElementById('sidebar').classList.remove('-translate-x-full');
            document.getElementById('mobileBackdrop').classList.remove('hidden');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('mobileBackdrop').classList.add('hidden');
        }
    </script>
<script>
function escapeHtmlNotif(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
window.escapeHtmlNotif = escapeHtmlNotif;

document.addEventListener("DOMContentLoaded", function() {
    const notifBtn = document.getElementById("notif-btn");
    const notifMenu = document.getElementById("notif-menu");
    const notifList = document.getElementById("notif-list");
    const notifBadge = document.getElementById("notif-badge");
    const notifPing = document.getElementById("notif-ping");

    if(!notifBtn) return;

    notifBtn.addEventListener("click", function(e) {
        e.stopPropagation();
        notifMenu.classList.toggle("hidden");
    });

    document.addEventListener("click", function(e) {
        if(!notifMenu.contains(e.target) && !notifBtn.contains(e.target)) {
            notifMenu.classList.add("hidden");
        }
    });

    function fetchNotifications() {
        fetch("index.php?action=api_get_unread_chat_count")
        .then(res => res.json())
        .then(data => {
            const chatBadge = document.getElementById("chat-badge-global");
            if (chatBadge) {
                if (data.count > 0) {
                    chatBadge.textContent = data.count;
                    chatBadge.classList.remove("hidden");
                } else {
                    chatBadge.classList.add("hidden");
                }
            }
        });
        
        fetch("index.php?action=api_get_notifications")
        .then(res => res.json())
        .then(data => {
            if(data && data.length > 0) {
                notifBadge.classList.remove("hidden");
                notifPing.classList.remove("hidden");
                notifList.innerHTML = "";

                data.forEach(item => {
                    let div = document.createElement("div");
                    div.className = "p-4 border-b border-white/5 hover:bg-[#0b1116]/5 cursor-pointer transition";
                    div.innerHTML = '<h4 class="text-xs font-bold text-white mb-1">' + escapeHtmlNotif(item.title) + '</h4><p class="text-[10px] text-white/60 line-clamp-2">' + escapeHtmlNotif(item.content) + '</p>';
                    div.onclick = function() {
                        fetch("index.php?action=api_read_notification", {
                            method: "POST",
                            headers: {"Content-Type": "application/x-www-form-urlencoded"},
                            body: "id=" + item.id
                        }).then(() => {
                            if(item.link) window.location.href = item.link;
                            else { fetchNotifications(); notifMenu.classList.add("hidden"); }
                        });
                    };
                    notifList.appendChild(div);
                });
            } else {
                notifBadge.classList.add("hidden");
                notifPing.classList.add("hidden");
                notifList.innerHTML = "<div class='p-4 text-center text-xs text-white/40'>Không có thông báo mới</div>";
            }
        });
    }

    fetchNotifications();
    setInterval(fetchNotifications, 30000);
});

// Global Zero-Reload Toast Notification
window.showToast = function(message, type = 'success') {
    let container = document.getElementById('global-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'global-toast-container';
        container.className = 'fixed bottom-6 right-6 z-[99999] flex flex-col gap-2 pointer-events-none max-w-sm w-full px-4';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    const isSuccess = type === 'success';
    const bgClass = isSuccess 
        ? 'bg-[#0b1c14] border-emerald-500/50 text-emerald-200' 
        : (type === 'warning' ? 'bg-[#261c0c] border-amber-500/50 text-amber-200' : 'bg-[#291316] border-rose-500/50 text-rose-200');
    const icon = isSuccess ? '✅' : (type === 'warning' ? '⚡' : '⚠️');
    
    toast.className = `${bgClass} pointer-events-auto border rounded-2xl p-4 shadow-2xl flex items-center gap-3 transform translate-y-4 opacity-0 transition-all duration-300 backdrop-blur-md`;
    toast.innerHTML = `
        <span class="text-xl flex-shrink-0">${icon}</span>
        <div class="text-xs font-bold leading-snug flex-1">${message}</div>
        <button onclick="this.parentElement.remove()" class="text-white/40 hover:text-white text-base leading-none p-1 transition">&times;</button>
    `;
    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-4', 'opacity-0');
    });
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 3800);
};
</script>
<!-- Floating AI Copilot Chat Button -->
<div class="fixed bottom-6 right-6 z-50">
    <button type="button" onclick="window.toggleAiChatModal()" class="group flex items-center gap-2 rounded-full bg-gradient-to-r from-[#d8ff3e] to-[#a3e635] p-3 sm:px-4 sm:py-3 text-[#10181e] font-black shadow-[0_10px_25px_rgba(216,255,62,0.4)] hover:scale-105 transition duration-200 cursor-pointer border border-[#d8ff3e]" title="Trò Chuyện Cùng Trợ Lý AI (Machine Learning)">
        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2a10 10 0 1010 10A10 10 0 0012 2zm1 14.93V17a1 1 0 01-2 0v-.07A7.003 7.003 0 015.07 11H5a1 1 0 010-2h.07A7.003 7.003 0 0111 3.07V3a1 1 0 012 0v.07A7.003 7.003 0 0118.93 9H19a1 1 0 010 2h-.07A7.003 7.003 0 0113 16.93zM12 7a5 5 0 105 5 5 5 0 00-5-5z"/>
        </svg>
        <span class="hidden sm:inline text-xs font-black uppercase tracking-wider">Trợ Lý AI ML</span>
    </button>
</div>

<!-- Interactive Conversational AI Chat Dialog (Powered by Local ML Engine) -->
<div id="aiChatModal" class="fixed bottom-20 right-4 sm:right-6 z-50 w-[calc(100vw-2rem)] sm:w-[420px] h-[580px] max-h-[82vh] flex flex-col bg-[#121b21]/95 border border-[#d8ff3e]/40 rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.8)] backdrop-blur-2xl transition-all duration-300 transform scale-95 opacity-0 pointer-events-none overflow-hidden">
    <!-- Chat Header -->
    <div class="px-4 py-3 bg-[#0b1116] border-b border-white/10 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <div class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-[#d8ff3e] text-[#10181e] font-black text-sm shadow">
                AI
                <span class="absolute -bottom-0.5 -right-0.5 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
            </div>
            <div>
                <div class="flex items-center gap-1.5">
                    <h3 class="text-xs font-bold text-white tracking-wide">SmashHub AI Copilot</h3>
                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-[#d8ff3e]/20 text-[#d8ff3e] font-bold">ML IN-HOUSE</span>
                </div>
                <p class="text-[10px] text-white/50">Mô hình Machine Learning tiếng Việt</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5">
            <button type="button" onclick="window.clearAiChatHistory()" class="text-white/40 hover:text-white p-1 text-xs transition" title="Xóa hội thoại">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
            <button type="button" onclick="window.toggleAiChatModal()" class="text-white/40 hover:text-white p-1 text-base leading-none transition" title="Đóng chat">
                &times;
            </button>
        </div>
    </div>

    <!-- Chat Message Body -->
    <div id="aiChatMessageList" class="flex-1 overflow-y-auto p-4 space-y-3 scroll-smooth">
        <!-- Initial Assistant Message -->
        <div class="flex items-start gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-[#d8ff3e] text-[#10181e] font-black text-xs flex items-center justify-center shrink-0 shadow">
                AI
            </div>
            <div class="flex-1 bg-[#0b1116] border border-white/10 rounded-2xl p-3 text-xs text-white/90 leading-relaxed shadow-sm">
                <p class="font-bold text-[#d8ff3e] mb-1">Xin chào!</p>
                <p>Tôi là <strong>Trợ Lý AI SmashHub</strong> được vận hành bằng mô hình Machine Learning riêng. Hãy nói cho tôi biết nhu cầu của bạn (tìm sân trống, tư vấn vợt, luật BWF, chia tiền 50/50...):</p>
                
                <!-- Quick Chips -->
                <div class="mt-2.5 pt-2 border-t border-white/10 flex flex-wrap gap-1.5">
                    <button type="button" onclick="window.sendAiQuickPrompt('Chiều nay 18h ở Bình Thạnh giá dưới 100k')" class="rounded-lg bg-white/5 hover:bg-[#d8ff3e]/15 hover:text-[#d8ff3e] border border-white/10 px-2 py-1 text-[11px] text-white/80 transition cursor-pointer">
                        Tìm sân Bình Thạnh 18h
                    </button>
                    <button type="button" onclick="window.sendAiQuickPrompt('Người mới chơi cổ tay yếu nên mua vợt gì?')" class="rounded-lg bg-white/5 hover:bg-[#d8ff3e]/15 hover:text-[#d8ff3e] border border-white/10 px-2 py-1 text-[11px] text-white/80 transition cursor-pointer">
                        Tư vấn chọn vợt
                    </button>
                    <button type="button" onclick="window.sendAiQuickPrompt('Luật phát cầu 1m15 mới nhất tính sao?')" class="rounded-lg bg-white/5 hover:bg-[#d8ff3e]/15 hover:text-[#d8ff3e] border border-white/10 px-2 py-1 text-[11px] text-white/80 transition cursor-pointer">
                        Luật phát cầu BWF
                    </button>
                    <button type="button" onclick="window.sendAiQuickPrompt('Cơ chế chia đôi tiền sân 50/50 hoạt động thế nào?')" class="rounded-lg bg-white/5 hover:bg-[#d8ff3e]/15 hover:text-[#d8ff3e] border border-white/10 px-2 py-1 text-[11px] text-white/80 transition cursor-pointer">
                        Luật chia tiền 50/50
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Typing Indicator -->
    <div id="aiChatTypingIndicator" class="hidden px-4 py-2 bg-[#0b1116]/60 border-t border-white/5 flex items-center gap-2 text-[11px] text-[#d8ff3e]">
        <span class="inline-flex h-2 w-2 rounded-full bg-[#d8ff3e] animate-ping"></span>
        <span>AI đang suy luận ngôn ngữ tự nhiên...</span>
    </div>

    <!-- Chat Footer Input -->
    <div class="p-3 bg-[#0b1116] border-t border-white/10">
        <form id="aiChatForm" onsubmit="window.handleAiChatSubmit(event)" class="flex items-center gap-2">
            <button type="button" id="btnAiChatGps" onclick="window.toggleAiChatGps()" class="p-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white/70 hover:text-emerald-400 transition cursor-pointer" title="Đính kèm vị trí GPS">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
            <input type="text" id="aiChatInput" placeholder="Nhập câu hỏi tự nhiên..." class="flex-1 bg-black/40 border border-white/15 focus:border-[#d8ff3e] rounded-xl px-3 py-2 text-xs text-white placeholder-white/40 focus:outline-none transition font-medium">
            <button type="submit" id="btnAiChatSend" class="p-2.5 rounded-xl bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] font-bold transition shadow cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<script>
(function() {
    let aiChatGps = { lat: null, lng: null };
    let isModalOpen = false;

    window.toggleAiChatModal = function() {
        const modal = document.getElementById('aiChatModal');
        if (!modal) return;
        isModalOpen = !isModalOpen;
        if (isModalOpen) {
            modal.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
            modal.classList.add('scale-100', 'opacity-100', 'pointer-events-auto');
            setTimeout(() => document.getElementById('aiChatInput')?.focus(), 200);
        } else {
            modal.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
            modal.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto');
        }
    };

    window.toggleGlobalAiModal = function() {
        window.toggleAiChatModal();
    };

    window.toggleAiChatGps = function() {
        const btn = document.getElementById('btnAiChatGps');
        if (!navigator.geolocation) {
            if (window.showToast) window.showToast('Trình duyệt không hỗ trợ GPS.', 'warning');
            return;
        }
        if (btn) btn.classList.add('animate-pulse');
        navigator.geolocation.getCurrentPosition(
            pos => {
                aiChatGps.lat = pos.coords.latitude;
                aiChatGps.lng = pos.coords.longitude;
                if (btn) {
                    btn.classList.remove('animate-pulse');
                    btn.classList.add('text-emerald-400', 'border-emerald-500/50', 'bg-emerald-500/10');
                }
                if (window.showToast) window.showToast('Đã đính kèm GPS: ' + aiChatGps.lat.toFixed(3) + ', ' + aiChatGps.lng.toFixed(3), 'success');
            },
            err => {
                if (btn) btn.classList.remove('animate-pulse');
                if (window.showToast) window.showToast('Không lấy được GPS: ' + err.message, 'warning');
            },
            { timeout: 8000 }
        );
    };

    window.sendAiQuickPrompt = function(promptText) {
        const input = document.getElementById('aiChatInput');
        if (input) {
            input.value = promptText;
            window.handleAiChatSubmit(new Event('submit'));
        }
    };

    window.clearAiChatHistory = function() {
        const list = document.getElementById('aiChatMessageList');
        if (!list) return;
        list.innerHTML = `
            <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-[#d8ff3e] text-[#10181e] font-black text-xs flex items-center justify-center shrink-0 shadow">AI</div>
                <div class="flex-1 bg-[#0b1116] border border-white/10 rounded-2xl p-3 text-xs text-white/90 leading-relaxed shadow-sm">
                    <p class="font-bold text-[#d8ff3e] mb-1">Cuộc trò chuyện mới</p>
                    <p>Hãy hỏi tôi bất kỳ điều gì về sân bãi, vợt cước, luật thi đấu BWF hoặc cơ chế chia đôi tiền sân!</p>
                </div>
            </div>
        `;
    };

    window.handleAiChatSubmit = async function(e) {
        if (e && e.preventDefault) e.preventDefault();
        const input = document.getElementById('aiChatInput');
        const text = input ? input.value.trim() : '';
        if (!text) return;

        input.value = '';
        appendUserMessage(text);

        const typing = document.getElementById('aiChatTypingIndicator');
        const sendBtn = document.getElementById('btnAiChatSend');
        if (typing) typing.classList.remove('hidden');
        if (sendBtn) sendBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('message', text);
            if (aiChatGps.lat && aiChatGps.lng) {
                formData.append('user_lat', aiChatGps.lat);
                formData.append('user_lng', aiChatGps.lng);
            }

            const res = await fetch('index.php?action=api_ai_chat', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                appendAssistantMessage(data);
            } else {
                appendAssistantMessage({
                    reply: data.message || 'Lỗi khi xử lý phản hồi.',
                    intent: 'error',
                    confidence: 0,
                    pitches: []
                });
            }
        } catch (err) {
            appendAssistantMessage({
                reply: 'Lỗi kết nối máy chủ Machine Learning.',
                intent: 'error',
                confidence: 0,
                pitches: []
            });
        } finally {
            if (typing) typing.classList.add('hidden');
            if (sendBtn) sendBtn.disabled = false;
        }
    };

    function appendUserMessage(text) {
        const list = document.getElementById('aiChatMessageList');
        if (!list) return;

        const userDiv = document.createElement('div');
        userDiv.className = 'flex items-start justify-end gap-2.5';
        userDiv.innerHTML = `
            <div class="max-w-[85%] bg-gradient-to-r from-[#1f3026] to-[#12221b] border border-[#d8ff3e]/40 text-white rounded-2xl p-3 text-xs leading-relaxed shadow">
                ${escapeHtml(text)}
            </div>
        `;
        list.appendChild(userDiv);
        list.scrollTop = list.scrollHeight;
    }

    function appendAssistantMessage(data) {
        const list = document.getElementById('aiChatMessageList');
        if (!list) return;

        const aiDiv = document.createElement('div');
        aiDiv.className = 'flex items-start gap-2.5';

        const confPercent = Math.round((data.confidence || 0) * 100);
        const intentLabels = {
            'find_pitch': 'Đặt Sân Thông Minh',
            'ask_price': 'Bảng Giá & Chi Phí',
            'racket_consult': 'Tư Vấn Thiết Bị Vợt/Cước',
            'split_bill_info': 'Cơ Chế Chia Đôi 50/50',
            'rule_inquiry': 'Luật Thi Đấu BWF',
            'radar_matchmaking': 'Radar Cáp Kèo Lân Cận',
            'calendar_schedule': 'Lịch Ngày & Giờ Thi Đấu',
            'weather_mood': 'Thời Tiết & Lên Sân',
            'health_fitness': 'Sức Khỏe & Thể Lực',
            'greeting': 'Hội Thoại Chào Hỏi',
            'out_of_scope': 'Trợ Lý Chuyên Môn Cầu Lông'
        };
        const intentText = intentLabels[data.intent] || data.intent;

        let pitchesHtml = '';
        if (data.pitches && data.pitches.length > 0) {
            pitchesHtml = '<div class="mt-3 space-y-2">';
            data.pitches.forEach(p => {
                const bookUrl = p.booking_url || ('index.php?action=book&id=' + p.id);
                pitchesHtml += `
                    <div class="flex items-center gap-2.5 bg-black/40 border border-white/10 rounded-xl p-2.5 hover:border-[#d8ff3e]/40 transition">
                        <img src="${p.image_url}" alt="${p.name}" class="w-14 h-14 rounded-lg object-cover shrink-0">
                        <div class="flex-1 min-w-0">
                            <h5 class="text-xs font-bold text-white truncate">${p.name}</h5>
                            <p class="text-[10px] text-white/60 truncate">${p.location} (${p.district})</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[11px] font-black text-[#d8ff3e]">${p.price_formatted || (p.price_per_hour + 'đ')}</span>
                                <span class="text-[9px] px-1.5 py-0.2 rounded bg-white/10 text-white/70">${p.distance_display || (p.distance_km + ' km')}</span>
                            </div>
                        </div>
                        <a href="${bookUrl}" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] font-bold text-[10px] transition shadow">
                            Đặt Ngay
                        </a>
                    </div>
                `;
            });
            pitchesHtml += '</div>';
        }

        let opponentsHtml = '';
        if (data.opponents && data.opponents.length > 0) {
            opponentsHtml = '<div class="mt-3 space-y-2">';
            data.opponents.forEach(opp => {
                opponentsHtml += `
                    <div class="flex items-center gap-2.5 bg-black/40 border border-white/10 rounded-xl p-2.5 hover:border-[#d8ff3e]/40 transition">
                        <div class="relative shrink-0">
                            <img src="${escapeHtml(opp.avatar)}" alt="${escapeHtml(opp.full_name)}" class="w-11 h-11 rounded-full object-cover border-2" style="border-color: ${opp.rank_color || '#d8ff3e'};">
                            <span class="absolute -bottom-1 -right-1 text-[8px] px-1 rounded font-black text-black bg-[#d8ff3e]">
                                ${escapeHtml(opp.rank_name ? opp.rank_name.replace('Rank ', '') : 'Elo')}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5">
                                <h5 class="text-xs font-bold text-white truncate">${escapeHtml(opp.full_name)}</h5>
                                <span class="text-[9px] px-1.5 py-0.2 rounded bg-white/10 text-[#d8ff3e] font-black">${opp.elo} Elo</span>
                            </div>
                            <p class="text-[10px] text-white/60 truncate mt-0.5">
                                📅 ${escapeHtml(opp.play_date_formatted || opp.play_date)} • ⏰ ${escapeHtml(opp.time_slot)} • 🏸 ${escapeHtml(opp.format)}
                            </p>
                            <p class="text-[9px] text-[#71818a] truncate mt-0.5">
                                📍 ${escapeHtml(opp.pitch_name)} (${escapeHtml(opp.distance_display || opp.district)})
                            </p>
                        </div>
                        <button onclick="window.sendAiChatRadarInvite(${opp.ticket_id}, this)" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] font-bold text-[10px] transition shadow">
                            Rủ Bắt Cặp
                        </button>
                    </div>
                `;
            });
            opponentsHtml += '</div>';
        }

        let quickTicketHtml = '';
        if (data.quick_ticket) {
            const qt = data.quick_ticket;
            quickTicketHtml = `
                <div class="mt-3 p-3 rounded-xl bg-gradient-to-r from-[#121b21] to-[#0f171d] border border-dashed border-[#d8ff3e]/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-[#d8ff3e] flex items-center gap-1">
                            🎯 Lệnh Tìm Đối AI Đề Xuất
                        </span>
                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-[#d8ff3e]/15 text-[#d8ff3e] font-bold">1-Click</span>
                    </div>
                    <div class="text-[10px] text-white/80 space-y-1">
                        <div>📍 <span class="text-white/60">Khu vực:</span> <strong class="text-white">${escapeHtml(qt.district)}</strong></div>
                        <div>📅 <span class="text-white/60">Lịch đấu:</span> <strong class="text-white">${escapeHtml(qt.play_date_formatted || qt.play_date)}</strong> • ⏰ <strong class="text-white">${escapeHtml(qt.time_slot)}</strong></div>
                        <div>🏸 <span class="text-white/60">Thể thức:</span> <strong class="text-white">${escapeHtml(qt.format)}</strong></div>
                    </div>
                    <button onclick="window.createTicketFromAiChat('${escapeHtml(qt.district)}', '${escapeHtml(qt.play_date)}', '${escapeHtml(qt.start_time)}', '${escapeHtml(qt.end_time)}', '${escapeHtml(qt.format)}', this)" 
                            class="w-full py-2 rounded-lg bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] font-bold text-xs transition shadow flex items-center justify-center gap-1.5">
                        <span>⚡ Kích Hoạt Vé Chờ Ngay</span>
                    </button>
                </div>
            `;
        }

        const formattedReply = formatMarkdown(data.reply || '');

        aiDiv.innerHTML = `
            <div class="w-7 h-7 rounded-lg bg-[#d8ff3e] text-[#10181e] font-black text-xs flex items-center justify-center shrink-0 shadow">
                AI
            </div>
            <div class="flex-1 bg-[#0b1116] border border-white/10 rounded-2xl p-3 text-xs text-white/90 leading-relaxed shadow-sm">
                <div class="flex items-center justify-between gap-1 mb-2 pb-1.5 border-b border-white/10">
                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-[#d8ff3e]/15 text-[#d8ff3e] font-bold">
                        ${intentText}
                    </span>
                    <span class="text-[9px] text-white/40">
                        Độ tin cậy: ${confPercent}%
                    </span>
                </div>
                <div class="space-y-1.5">${formattedReply}</div>
                ${pitchesHtml}
                ${opponentsHtml}
                ${quickTicketHtml}
            </div>
        `;

        list.appendChild(aiDiv);
        list.scrollTop = list.scrollHeight;
    }

    function escapeHtml(str) {
        return (str || '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatMarkdown(text) {
        let out = escapeHtml(text);
        out = out.replace(/\*\*(.*?)\*\*/g, '<strong class="text-white font-bold">$1</strong>');
        out = out.replace(/\n\n/g, '<br><br>');
        out = out.replace(/\n/g, '<br>');
        return out;
    }

    window.sendAiChatRadarInvite = function(ticketId, btn) {
        if (!window.isLoggedIn) {
            if (window.showToast) window.showToast('Vui lòng đăng nhập để rủ bắt cặp.', 'warning');
            setTimeout(() => { window.location.href = 'index.php?action=login'; }, 1000);
            return;
        }
        if (typeof window.sendNearbyRadarInvite === 'function') {
            window.sendNearbyRadarInvite(ticketId, btn);
        } else {
            const formData = new FormData();
            formData.append('target_ticket_id', ticketId);
            formData.append('csrf_token', window.csrfToken || '');
            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Đang gửi...';
            }
            fetch('index.php?action=api_send_nearby_invite', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    if (window.showToast) window.showToast(data.message, 'success');
                    if (btn) {
                        btn.className = 'shrink-0 px-2.5 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold text-[10px] border border-emerald-500/30';
                        btn.innerText = 'Đã Gửi Lời Mời';
                    }
                } else {
                    if (window.showToast) window.showToast(data.message || 'Lỗi gửi lời mời.', 'warning');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerText = 'Rủ Bắt Cặp';
                    }
                }
            }).catch(e => {
                if (window.showToast) window.showToast('Lỗi kết nối máy chủ.', 'warning');
                if (btn) {
                    btn.disabled = false;
                    btn.innerText = 'Rủ Bắt Cặp';
                }
            });
        }
    };

    window.createTicketFromAiChat = async function(district, playDate, startTime, endTime, format, btn) {
        if (!window.isLoggedIn) {
            if (window.showToast) window.showToast('Vui lòng đăng nhập để tạo vé tìm trận.', 'warning');
            setTimeout(() => { window.location.href = 'index.php?action=login'; }, 1000);
            return;
        }
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Đang kích hoạt...';
        }
        const formData = new FormData();
        formData.append('is_ajax', '1');
        formData.append('district', district || 'Bình Thạnh');
        formData.append('play_date', playDate);
        formData.append('start_time', startTime);
        formData.append('end_time', endTime);
        formData.append('format', format || 'Đánh Đôi');
        formData.append('pitch_mode', 'auto');
        formData.append('radius_km', '10.0');
        formData.append('note', 'Tạo tự động qua Trợ Lý AI Copilot');
        formData.append('csrf_token', window.csrfToken || '');

        try {
            const res = await fetch('index.php?action=store_ticket', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (window.showToast) window.showToast(data.message || 'Đã tạo lệnh tìm đối thành công!', 'success');
                if (btn) {
                    btn.className = 'w-full py-2 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold text-xs border border-emerald-500/30 text-center cursor-default';
                    btn.innerText = 'Đã Kích Hoạt Vé Chờ';
                }
                if (data.matched) {
                    setTimeout(() => { window.location.href = 'index.php?action=smart_match&msg=matched_instant'; }, 1200);
                }
            } else {
                if (window.showToast) window.showToast(data.message || 'Lỗi khi tạo vé tìm trận.', 'warning');
                if (btn) {
                    btn.disabled = false;
                    btn.innerText = 'Kích Hoạt Lại';
                }
            }
        } catch (e) {
            if (window.showToast) window.showToast('Lỗi kết nối máy chủ.', 'warning');
            if (btn) {
                btn.disabled = false;
                btn.innerText = 'Kích Hoạt Lại';
            }
        }
    };
})();
</script>


<?php if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? 'user') === 'user'): ?>
<!-- Nearby Player Radar Floating Widget -->
<div id="nearbyRadarContainer" class="fixed bottom-6 left-6 z-40 max-w-sm w-full px-4 sm:px-0 pointer-events-none transition-all duration-500 transform -translate-x-full opacity-0">
    <div id="nearbyRadarCard" class="pointer-events-auto bg-[#121b21]/95 border border-[#d8ff3e]/40 rounded-2xl p-4 shadow-[0_15px_35px_rgba(0,0,0,0.6)] backdrop-blur-xl relative overflow-hidden">
        <div class="absolute -top-12 -left-12 w-28 h-28 bg-[#d8ff3e]/10 rounded-full blur-2xl pointer-events-none"></div>
        <div id="radarContent">
            <!-- Dynamically populated by JS -->
        </div>
    </div>
</div>

<script>
(function() {
    let radarDismissed = false;

    function initNearbyRadar() {
        if (radarDismissed) return;
        
        let userLat = null, userLng = null;
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    userLat = pos.coords.latitude;
                    userLng = pos.coords.longitude;
                    checkRadar(userLat, userLng);
                },
                () => {
                    checkRadar(null, null);
                },
                { timeout: 4000, maximumAge: 60000 }
            );
        } else {
            checkRadar(null, null);
        }
    }

    async function checkRadar(lat, lng) {
        if (radarDismissed) return;
        try {
            let url = 'index.php?action=api_nearby_radar';
            if (lat && lng) {
                url += '&lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng);
            }
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success && data.has_nearby) {
                renderRadar(data);
            }
        } catch (e) {
            // Quiet fail
        }
    }

    function renderRadar(data) {
        const container = document.getElementById('nearbyRadarContainer');
        const content = document.getElementById('radarContent');
        if (!container || !content) return;

        if (data.type === 'incoming_invite') {
            const inv = data.invite;
            content.innerHTML = `
                <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-400">Lời Mời Bắt Cặp Thi Đấu</span>
                    </div>
                    <button type="button" onclick="window.dismissRadarCard()" class="text-white/40 hover:text-white text-xs leading-none transition">&times;</button>
                </div>
                <div class="flex items-center gap-3 mb-3">
                    <img src="${inv.sender_avatar}" alt="${inv.sender_name}" class="w-11 h-11 rounded-full object-cover border border-emerald-500/50 shadow-md">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h4 class="text-xs font-bold text-white truncate">${inv.sender_name}</h4>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-semibold">${inv.sender_rank}</span>
                        </div>
                        <p class="text-[11px] text-[#d8ff3e] font-medium">Cách bạn ~${inv.distance_km} km</p>
                        <p class="text-[10px] text-white/50 truncate">${inv.formatted_time} · ${inv.formatted_date}</p>
                    </div>
                </div>
                <p class="text-[11px] text-white/80 mb-3 bg-black/30 p-2 rounded-lg border border-white/5 leading-snug">
                    Ở gần bạn có đối thủ muốn rủ bạn bắt cặp đánh cầu lông! Bạn có muốn thử không?
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.respondRadarInvite(${inv.invite_id}, 'accept')" class="w-full py-2 px-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-[#0b1116] font-bold text-xs text-center transition shadow-lg shadow-emerald-500/20 cursor-pointer">
                        Đồng Ý Bắt Cặp
                    </button>
                    <button type="button" onclick="window.respondRadarInvite(${inv.invite_id}, 'reject')" class="w-full py-2 px-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs text-center transition cursor-pointer">
                        Từ Chối
                    </button>
                </div>
            `;
        } else if (data.type === 'nearby_player') {
            const cand = data.candidate;
            const sessionDismissed = sessionStorage.getItem('dismissed_radar_ticket_' + cand.ticket_id);
            if (sessionDismissed) return;

            content.innerHTML = `
                <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#d8ff3e] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-[#d8ff3e]"></span>
                        </span>
                        <span class="text-[10px] font-black uppercase tracking-wider text-[#d8ff3e]">Radar Bạn Đấu Lân Cận</span>
                    </div>
                    <button type="button" onclick="window.dismissRadarCard(${cand.ticket_id})" class="text-white/40 hover:text-white text-xs leading-none transition">&times;</button>
                </div>
                <div class="flex items-center gap-3 mb-3">
                    <img src="${cand.avatar_url}" alt="${cand.user_name}" class="w-11 h-11 rounded-full object-cover border border-[#d8ff3e]/40 shadow-md">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h4 class="text-xs font-bold text-white truncate">${cand.user_name}</h4>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-[#d8ff3e]/20 text-[#d8ff3e] font-semibold">${cand.rank_name}</span>
                        </div>
                        <p class="text-[11px] text-volt font-medium">Cách bạn ~${cand.distance_km} km (${cand.district})</p>
                        <p class="text-[10px] text-white/50 truncate">${cand.formatted_time} · ${cand.formatted_date}</p>
                    </div>
                </div>
                <p class="text-[11px] text-white/80 mb-3 bg-black/30 p-2 rounded-lg border border-white/5 leading-snug">
                    Đang tìm đối thủ ${cand.format} tương đương trình độ. Bạn có muốn gửi lời mời bắt cặp không?
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" id="btnRadarSendInvite" onclick="window.sendRadarInvite(${cand.ticket_id})" class="w-full py-2 px-3 rounded-xl bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] font-bold text-xs text-center transition shadow-lg shadow-[#d8ff3e]/20 cursor-pointer">
                        Rủ Bắt Cặp Ngay
                    </button>
                    <button type="button" onclick="window.dismissRadarCard(${cand.ticket_id})" class="w-full py-2 px-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs text-center transition cursor-pointer">
                        Bỏ Qua
                    </button>
                </div>
            `;
        }

        container.classList.remove('-translate-x-full', 'opacity-0');
        container.classList.add('translate-x-0', 'opacity-100');
    }

    window.dismissRadarCard = function(ticketId = null) {
        if (ticketId) {
            sessionStorage.setItem('dismissed_radar_ticket_' + ticketId, '1');
        }
        const container = document.getElementById('nearbyRadarContainer');
        if (container) {
            container.classList.add('-translate-x-full', 'opacity-0');
            container.classList.remove('translate-x-0', 'opacity-100');
        }
    };

    window.sendRadarInvite = async function(ticketId) {
        const btn = document.getElementById('btnRadarSendInvite');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Đang gửi...';
        }

        const formData = new FormData();
        formData.append('target_ticket_id', ticketId);
        formData.append('csrf_token', window.csrfToken || '');

        try {
            const res = await fetch('index.php?action=api_send_nearby_invite', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (window.showToast) window.showToast(data.message, 'success');
                window.dismissRadarCard(ticketId);
            } else {
                if (window.showToast) window.showToast(data.message || 'Lỗi khi gửi lời mời.', 'warning');
                if (btn) {
                    btn.disabled = false;
                    btn.innerText = 'Rủ Bắt Cặp Ngay';
                }
            }
        } catch (e) {
            if (window.showToast) window.showToast('Lỗi kết nối máy chủ.', 'warning');
            if (btn) {
                btn.disabled = false;
                btn.innerText = 'Rủ Bắt Cặp Ngay';
            }
        }
    };

    window.respondRadarInvite = async function(inviteId, action) {
        const formData = new FormData();
        formData.append('invite_id', inviteId);
        formData.append('response_action', action);
        formData.append('csrf_token', window.csrfToken || '');

        try {
            const res = await fetch('index.php?action=api_respond_nearby_invite', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (window.showToast) window.showToast(data.message, 'success');
                window.dismissRadarCard();
                if (action === 'accept') {
                    setTimeout(() => {
                        window.location.href = 'index.php?action=smart_match';
                    }, 1000);
                }
            } else {
                if (window.showToast) window.showToast(data.message || 'Có lỗi xảy ra.', 'warning');
            }
        } catch (e) {
            if (window.showToast) window.showToast('Lỗi kết nối máy chủ.', 'warning');
        }
    };

    setTimeout(initNearbyRadar, 3500);
    setInterval(initNearbyRadar, 45000);
})();
</script>
<?php endif; ?>

</body>
</html>
