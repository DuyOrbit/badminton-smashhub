<?php
// Layout wrapper for BadmintonConnect / SmashHub
$clean_user_name = '';
$user_role = $_SESSION['user_role'] ?? 'guest';
$user_initials = 'KH';
$user_badge_text = 'GUEST PLAYER';
$user_elo_pts = 600;
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
                    $user_badge_text = strtoupper($rk_name) . ' · ' . $user_elo_pts . ' ELO';
                } else {
                    $role_names = ['staff' => 'NHÂN VIÊN SÂN', 'owner' => 'CHỦ CỤM SÂN', 'admin' => 'ADMIN TỔNG'];
                    $user_badge_text = $role_names[$user_role] ?? strtoupper($user_role);
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
</script>
</body>
</html>
