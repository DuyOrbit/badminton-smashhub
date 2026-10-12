<?php
// Defensive initialization for smart_match.php
?>
<!-- LEAFLET.JS FOR MAP INTERACTION -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php
if (!isset($currentUser) || !is_array($currentUser)) {
    $currentUser = [
        'name' => $_SESSION['user_name'] ?? 'Vận động viên',
        'avatar_url' => '',
        'skill_level' => 3,
        'district' => 'Bình Thạnh'
    ];
}
if (!isset($userReputation) || !is_array($userReputation)) {
    $userReputation = [
        'score' => 100,
        'tier' => 'Gương Mẫu',
        'can_matchmake' => true,
        'is_penalized' => false
    ];
}
if (!isset($myTickets) || !is_array($myTickets)) {
    $myTickets = [];
}
if (!isset($season)) {
    $season = '2026-H1';
}
if (!isset($currentElo)) {
    $currentElo = 600;
}
if (!isset($rankName)) {
    $rankName = 'Vàng';
}
if (!isset($discount)) {
    $discount = 10;
}
if (!isset($allPitches) || !is_array($allPitches)) {
    $allPitches = [];
}
if (!isset($districtCounts) || !is_array($districtCounts)) {
    $districtCounts = [];
}
$allDistrictsList = [
    'Bình Thạnh', 'Tân Bình', 'Gò Vấp', 'Phú Nhuận', 'Tân Phú', 'Bình Tân', 'TP. Thủ Đức',
    'Quận 1', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7', 'Quận 8', 'Quận 10', 'Quận 11', 'Quận 12',
    'Huyện Củ Chi', 'Huyện Hóc Môn', 'Huyện Bình Chánh', 'Huyện Nhà Bè', 'Huyện Cần Giờ'
];
?>
<div class="max-w-6xl mx-auto space-y-8 animate-fade-in">

    <!-- Flash Messages -->
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'ticket_queued'): ?>
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🎯</span>
                    <div>
                        <h4 class="font-bold text-lg">Đã đặt lệnh tìm trận thành công!</h4>
                        <p class="text-sm text-emerald-100">Hệ thống đã lưu vào hàng đợi và đang tự động quét đối thủ cùng Bậc Rank Elo trong khu vực của bạn.</p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold">&times;</button>
            </div>
        <?php elseif($_GET['msg'] == 'pitch_switched'): ?>
            <div class="bg-gradient-to-r from-teal-600 to-emerald-600 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🔄</span>
                    <div>
                        <h4 class="font-bold text-lg">Đã cập nhật sân thi đấu mới!</h4>
                        <p class="text-sm text-teal-100">Hệ thống đã chuyển trận đấu sang sân mới và thông báo cho đối thủ. Vui lòng bấm Xác nhận Kèo!</p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold">&times;</button>
            </div>
        <?php elseif($_GET['msg'] == 'matched_instant'): ?>
            <div class="bg-gradient-to-r from-amber-500 to-yellow-600 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between animate-bounce">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">⚔️</span>
                    <div>
                        <h4 class="font-black text-xl">ĐÃ TÌM THẤY ĐỐI THỦ NGAY LẬP TỨC!</h4>
                        <p class="text-sm text-yellow-100">Hệ thống đã tìm thấy đối thủ có cùng điểm Elo và thời gian rảnh. Vui lòng kiểm tra và bấm Xác Nhận Kèo bên dưới!</p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold">&times;</button>
            </div>
        <?php elseif($_GET['msg'] == 'waiting_opponent'): ?>
            <div class="bg-blue-600 text-white p-4 rounded-2xl shadow-lg flex items-center gap-3">
                <span class="text-2xl">⏳</span>
                <div>
                    <h4 class="font-bold">Bạn đã xác nhận kèo thành công!</h4>
                    <p class="text-sm text-blue-100">Hệ thống đang chờ đối thủ bấm xác nhận. Khi cả hai cùng đồng ý, sân sẽ được tự động khóa.</p>
                </div>
            </div>
        <?php elseif($_GET['msg'] == 'fully_confirmed'): ?>
            <div class="bg-gradient-to-r from-green-600 to-emerald-700 text-white p-5 rounded-2xl shadow-none flex items-center gap-4">
                <span class="text-3xl">🎉</span>
                <div>
                    <h4 class="font-black text-xl">CHỐT KÈO THÀNH CÔNG! SÂN ĐÃ ĐƯỢC ĐẶT!</h4>
                    <p class="text-sm text-green-100">Cả hai bạn đã xác nhận. Lịch thi đấu đã được khóa và tạo mã Check-in. Hãy chuẩn bị thể lực tốt nhất nhé!</p>
                </div>
            </div>
        <?php elseif($_GET['msg'] == 'noshow_reported'): ?>
            <div class="bg-gradient-to-r from-red-600 to-rose-700 text-white p-5 rounded-2xl shadow-none flex items-center gap-4 animate-fade-in">
                <span class="text-3xl">⚠️</span>
                <div>
                    <h4 class="font-black text-lg">ĐÃ GHI NHẬN BÁO CÁO BÙNG KÈO (NO-SHOW)!</h4>
                    <p class="text-sm text-red-100">Đối thủ đã bị trừ 30 điểm uy tín Fair-Play, trừ 50 điểm Elo và bị tạm khóa tính năng tìm trận trong 3 ngày.</p>
                </div>
            </div>
        <?php elseif($_GET['msg'] == 'cancelled'): ?>
            <div class="bg-gray-700 text-white p-4 rounded-2xl shadow">
                <span>Đã hủy lệnh tìm trận thành công.</span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="bg-red-50 border-2 border-red-300 p-4 rounded-2xl text-red-900 flex items-center justify-between shadow-none">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🚫</span>
                <div>
                    <strong class="font-bold text-sm block">Không thể thực hiện yêu cầu!</strong>
                    <span class="text-xs text-red-700"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></span>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-700 font-bold">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Header: Gamification Profile Banner (Huy hiệu Rank & Điểm Elo) -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-none relative overflow-hidden border border-indigo-800/50">
        <!-- Background Glow FX -->
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="relative">
                    <img src="<?php echo htmlspecialchars($currentUser['avatar_url'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150'); ?>" 
                         alt="Avatar" class="w-20 h-20 rounded-2xl object-cover border-2 border-amber-400 shadow-none">
                    <span class="absolute -bottom-2 -right-2 bg-amber-400 text-slate-950 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider shadow">
                        LV <?php echo $currentUser['skill_level'] ?? 3; ?>
                    </span>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl md:text-3xl font-black text-white"><?php echo htmlspecialchars($currentUser['name'] ?? ($_SESSION['user_name'] ?? 'Vận động viên')); ?></h1>
                        <span class="text-xs bg-[#0b1116]/10 text-white px-2.5 py-1 rounded-full border border-white/20">
                            <?php echo htmlspecialchars($season); ?>
                        </span>
                    </div>
                    <p class="text-indigo-200 text-sm mt-1">Cơ chế ghép cặp thông minh theo Elo • Không cần tự khai báo level ảo</p>
                </div>
            </div>

            <!-- Rank & Reputation Cards Display -->
            <div class="flex flex-wrap items-center gap-4 bg-[#0b1116]/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/15">
                <!-- Rank -->
                <div class="text-right">
                    <div class="text-xs uppercase font-extrabold text-amber-300 tracking-wider">Bậc Xếp Hạng</div>
                    <div class="text-xl font-black text-white flex items-center justify-end gap-1.5">
                        <?php 
                            if ($rankName == 'Kim Cương') echo '💎 Kim Cương';
                            elseif ($rankName == 'Vàng') echo '🥇 Rank Vàng';
                            elseif ($rankName == 'Bạc') echo '🥈 Rank Bạc';
                            else echo '🥉 Rank Đồng';
                        ?>
                    </div>
                    <div class="text-xs text-indigo-200">Elo: <strong class="text-amber-300 font-bold"><?php echo $currentElo; ?> PTS</strong> (Giảm <?php echo $discount; ?>%)</div>
                </div>

                <div class="h-10 w-[1px] bg-[#0b1116]/20 hidden sm:block"></div>

                <!-- Điểm Uy Tín Fair-Play -->
                <div class="text-left">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs uppercase font-extrabold text-emerald-300 tracking-wider">Điểm Uy Tín Fair-Play</span>
                        <button type="button" onclick="document.getElementById('repModal').classList.remove('hidden')" 
                                class="text-[10px] text-amber-300 underline hover:text-white font-medium">Lịch sử</button>
                    </div>
                    <div class="text-xl font-black flex items-center gap-2">
                        <?php 
                            $repScore = $userReputation['score'] ?? 100;
                            $repColor = $repScore >= 90 ? 'text-emerald-400' : ($repScore >= 70 ? 'text-blue-400' : ($repScore >= 50 ? 'text-amber-400' : 'text-red-400'));
                        ?>
                        <span class="<?php echo $repColor; ?>"><?php echo $repScore; ?>/100</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-[#0b1116]/15 text-white">
                            <?php echo $userReputation['tier']; ?>
                        </span>
                    </div>
                    <!-- Mini Progress bar -->
                    <div class="w-32 bg-[#0b1116]/20 h-1.5 rounded-full overflow-hidden mt-1">
                        <div class="h-full rounded-full <?php echo $repScore >= 70 ? 'bg-emerald-400' : ($repScore >= 50 ? 'bg-amber-400' : 'bg-red-500'); ?>" 
                             style="width: <?php echo $repScore; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid: Form Đặt Lệnh (Bên Trái) & Danh Sách Ticket (Bên Phải) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- CỘT TRÁI: FORM ĐẶT LỆNH TÌM TRẬN THÔNG MINH (5 Cột) -->
        <div class="lg:col-span-5 bg-[#0b1116] rounded-3xl p-6 md:p-8 shadow-none border border-white/5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-white/5">
                    <div>
                        <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                            <span>🎯</span> Lập Phiếu Tìm Đối Thủ
                        </h2>
                        <p class="text-xs text-white/60 mt-1">Hệ thống sẽ gom cặp có Elo tương đương và tự tìm sân trống</p>
                    </div>
                    <span class="bg-amber-100 text-amber-800 text-[11px] font-bold px-2.5 py-1 rounded-full border border-amber-500/20">
                        Tự Động 100%
                    </span>
                </div>

                <?php if(!$userReputation['can_matchmake']): ?>
                    <!-- CẢNH BÁO TÀI KHOẢN BỊ KHÓA DO ĐIỂM UY TÍN THẤP HOẶC BÙNG KÈO -->
                    <div class="bg-red-50 border-2 border-red-300 p-6 rounded-2xl text-red-900 space-y-3">
                        <div class="flex items-center gap-2.5 font-black text-red-700 text-lg">
                            <span class="text-2xl">🚫</span>
                            <span>TÍNH NĂNG TÌM TRẬN BỊ KHÓA</span>
                        </div>
                        <p class="text-xs text-red-800 leading-relaxed font-medium">
                            <?php echo htmlspecialchars($userReputation['lock_reason']); ?>
                        </p>
                        <div class="pt-3 border-t border-red-200 text-xs text-red-700">
                            <strong>Chính sách Fair-Play:</strong> Để bảo vệ trải nghiệm của cộng đồng, tài khoản có điểm Uy Tín dưới 50 hoặc có hành vi bùng kèo (No-Show) sẽ không thể tham gia hệ thống Ghép Trận Tự Động.
                        </div>
                    </div>
                <?php else: ?>
                    <form action="index.php?action=store_ticket" method="POST" class="space-y-5">
                        <!-- Ngày muốn thi đấu -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                📅 Ngày Thi Đấu Dự Kiến
                            </label>
                            <input type="date" name="play_date" required min="<?php echo date('Y-m-d'); ?>" 
                                   value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                   class="w-full px-4 py-3 bg-[#0b1116]/5 border border-white/10 rounded-xl font-medium focus:ring-2 focus:ring-green-500 focus:bg-[#0b1116] transition-all text-sm">
                        </div>

                        <!-- Khung giờ rảnh (Start - End) -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    ⏰ Từ Giờ
                                </label>
                                <input type="time" name="start_time" value="18:00" required
                                       class="w-full px-3 py-3 bg-[#0b1116]/5 border border-white/10 rounded-xl font-medium focus:ring-2 focus:ring-green-500 focus:bg-[#0b1116] transition-all text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    ⏰ Đến Giờ
                                </label>
                                <input type="time" name="end_time" value="20:00" required
                                       class="w-full px-3 py-3 bg-[#0b1116]/5 border border-white/10 rounded-xl font-medium focus:ring-2 focus:ring-green-500 focus:bg-[#0b1116] transition-all text-sm">
                            </div>
                        </div>

                        <!-- Chế độ Đặt Sân: Tự động vs Đã có sân -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                🏟️ Chế Độ Chọn Sân Thi Đấu
                            </label>
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <label class="border border-white/10 rounded-xl p-3 text-center cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-500/10 font-bold text-xs text-white">
                                    <input type="radio" name="pitch_mode" value="auto" checked class="hidden" onchange="togglePitchSelector(false)">
                                    <span>🤖 Nhờ Hệ Thống Đặt Hộ</span>
                                </label>
                                <label class="border border-white/10 rounded-xl p-3 text-center cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-500/10 font-bold text-xs text-white">
                                    <input type="radio" name="pitch_mode" value="specified" class="hidden" onchange="togglePitchSelector(true)">
                                    <span>🏟️ Tôi Đã Có Sân Sẵn</span>
                                </label>
                            </div>

                            <!-- Selector hiển thị khi chọn 'Tôi Đã Có Sân Sẵn' -->
                            <div id="specified_pitch_box" class="hidden animate-fade-in space-y-3 mt-3 p-4 bg-[#0b1116] border border-amber-500/40 rounded-2xl shadow-xl">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-black text-amber-400 uppercase tracking-wider">
                                        🏟️ CHỌN SÂN CẦU LÔNG BẠN ĐÃ ĐẶT TRỐNG:
                                    </label>
                                    <span id="selectedPitchTag" class="text-[10px] font-bold text-amber-300/90 bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-500/30">
                                        Chưa chọn sân
                                    </span>
                                </div>

                                <!-- Quick Action Buttons: GPS Auto Select & Toggle Map -->
                                <div class="flex gap-2">
                                    <button type="button" onclick="autoSelectNearestPitchByGPS()" 
                                            class="flex-1 bg-gradient-to-r from-amber-500 to-yellow-600 hover:from-amber-400 hover:to-yellow-500 text-white font-black py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 shadow">
                                        <span>🧭</span> Chọn Sân Gần GPS Của Tôi Nhất
                                    </button>
                                    <button type="button" onclick="togglePitchMapPicker()" 
                                            class="bg-[#121b21] hover:bg-amber-500/20 text-amber-300 border border-amber-500/40 font-bold py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1">
                                        <span>🗺️</span> Chọn Trực Quan Trên Bản Đồ
                                    </button>
                                </div>

                                <!-- Interactive Leaflet Map Container for Pitch Selection -->
                                <div id="pitchMapPickerContainer" class="hidden rounded-xl overflow-hidden border border-amber-500/30 relative">
                                    <div id="pitchMapPicker" style="height: 250px;" class="w-full z-10"></div>
                                    <div class="absolute bottom-2 left-2 z-20 bg-[#0b1116]/90 border border-white/10 px-3 py-1.5 rounded-lg text-[10px] text-amber-300 font-bold shadow-lg">
                                        💡 Nhấp vào ghim sân trên bản đồ để chọn sân ngay
                                    </div>
                                </div>

                                <!-- Dropdown Selector -->
                                <select name="specified_pitch_id" id="specified_pitch_id" onchange="onPitchDropdownChange()" class="w-full px-4 py-3 bg-[#121b21] border border-amber-500/50 rounded-xl font-bold text-white focus:ring-2 focus:ring-amber-500 transition-all text-sm">
                                    <option value="" data-lat="" data-lng="" data-name="" data-district="" data-price="">-- Chọn Cụm Sân Cầu Lông --</option>
                                    <?php foreach($allPitches as $p): ?>
                                        <option value="<?php echo $p['id']; ?>"
                                                data-lat="<?php echo $p['latitude'] ?? ''; ?>" 
                                                data-lng="<?php echo $p['longitude'] ?? ''; ?>"
                                                data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                                data-district="<?php echo htmlspecialchars($p['district']); ?>"
                                                data-price="<?php echo number_format($p['price_per_hour'], 0, ',', '.'); ?>">
                                            <?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['district']); ?>) - <?php echo number_format($p['price_per_hour'], 0, ',', '.'); ?>đ/h
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <div id="selectedPitchInfoCard" class="hidden bg-amber-500/10 border border-amber-500/30 p-3 rounded-xl text-xs text-amber-200 flex items-center justify-between animate-fade-in">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl">📍</span>
                                        <div>
                                            <strong id="selectedPitchNameLabel" class="font-bold text-white text-sm block">Sân đã chọn</strong>
                                            <span id="selectedPitchDistLabel" class="text-[10px] text-amber-300/80">Khoảng cách: N/A</span>
                                        </div>
                                    </div>
                                    <span id="selectedPitchPriceLabel" class="font-black text-amber-400 text-sm">0đ/h</span>
                                </div>
                            </div>
                        </div>

                        <!-- BỘ ĐỊNH VỊ GPS CÁ NHÂN & BÁN KÍNH TOÀN QUỐC -->
                        <div class="bg-[#0b1116] p-4 rounded-2xl border border-cyan-500/30 space-y-3 relative overflow-hidden">
                            <!-- Priority Tag -->
                            <div class="flex items-center justify-between">
                                <span class="bg-cyan-500/20 text-cyan-300 text-[10px] font-black px-2.5 py-0.5 rounded-full border border-cyan-500/40 uppercase tracking-wider flex items-center gap-1">
                                    🥇 Ưu Tiên 1: Vị Trí GPS & Bán Kính
                                </span>
                                <span class="text-[10px] text-cyan-300/70" id="gpsStatusText">Chưa định vị</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>🧭</span> Định Vị Vị Trí Cá Nhân (GPS):
                                </label>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" id="btnGpsLocate" onclick="getLocationGPS()" 
                                        class="flex-1 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 shadow">
                                    <span>🧭</span> Dùng Vị Trí Hiện Tại (GPS)
                                </button>
                            </div>

                            <!-- Ô nhập địa chỉ tự do toàn quốc -->
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1">Hoặc Nhập Tỉnh/Thành/Địa Chỉ Bất Kỳ Toàn Quốc:</label>
                                <div class="flex gap-1.5">
                                    <input type="text" id="customLocationInput" name="location_name" placeholder="Ví dụ: Cầu Giấy, Hà Nội hoặc Hải Châu, Đà Nẵng..."
                                           class="flex-1 px-3 py-2 bg-[#121b21] border border-white/10 rounded-xl text-xs font-medium text-white focus:ring-2 focus:ring-cyan-500">
                                    <button type="button" onclick="geocodeCustomAddress()" class="px-3 py-2 bg-cyan-700 hover:bg-cyan-600 text-white font-bold text-xs rounded-xl whitespace-nowrap">
                                        Tìm GPS
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="user_lat" id="ticketUserLat" value="">
                            <input type="hidden" name="user_lng" id="ticketUserLng" value="">

                            <!-- Thanh Trượt Bán Kính Quét Tìm Kèo -->
                            <div class="pt-2 border-t border-white/10">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[11px] font-bold text-gray-300">📏 Bán Kính Tìm Đối Thuận Tiện:</span>
                                    <span class="text-xs font-black text-cyan-400" id="radiusLabelVal">10 km</span>
                                </div>
                                <input type="range" name="radius_km" id="ticketRadiusSlider" min="2" max="30" step="1" value="10"
                                       oninput="document.getElementById('radiusLabelVal').innerText = this.value + ' km'"
                                       class="w-full h-1.5 bg-[#121b21] rounded-lg appearance-none cursor-pointer accent-cyan-400">
                                <div class="flex justify-between text-[9px] text-gray-500 font-bold mt-1">
                                    <span>2 km</span>
                                    <span>10 km</span>
                                    <span>20 km</span>
                                    <span>30 km</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tip Box giải thích cơ chế ưu tiên -->
                        <div class="bg-indigo-950/40 border border-indigo-500/30 p-3 rounded-2xl text-[11px] text-indigo-200 flex items-start gap-2">
                            <span class="text-base">💡</span>
                            <div class="leading-relaxed">
                                <strong class="text-indigo-300 block font-bold mb-0.5">Cơ chế ưu tiên vị trí ghép trận:</strong>
                                • <strong class="text-cyan-300">Khi BẬT GPS:</strong> Hệ thống ưu tiên cao nhất cho vị trí tọa độ & bán kính (quét sân thực tế gần bạn trong X km, kể cả giáp ranh khác quận).<br>
                                • <strong class="text-emerald-300">Khi KHÔNG CÓ GPS:</strong> Hệ thống sẽ tự động dùng <strong class="text-white">Quận Huyện</strong> chọn ở bên dưới làm tiêu chí lọc.
                            </div>
                        </div>

                        <!-- Khu vực / Quận mong muốn -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider">
                                    📍 Chọn Khu Vực Hoặc Quận Huyện Cụ Thể
                                </label>
                                <span class="bg-white/10 text-gray-300 text-[10px] font-bold px-2 py-0.5 rounded-full border border-white/10">
                                    🥈 Ưu Tiên 2 (Dự phòng)
                                </span>
                            </div>
                            <select name="district" class="w-full px-4 py-3 bg-[#0b1116] border border-white/10 rounded-xl font-medium text-white focus:ring-2 focus:ring-green-500 transition-all text-sm">
                                <option value="Tất cả" <?php echo ($currentUser['district'] ?? '') == 'Tất cả' ? 'selected' : ''; ?>>
                                    🌐 Tất cả khu vực / Bất kỳ quận nào (Dễ ghép trận nhất)
                                </option>
                                
                                <optgroup label="🟢 Các Quận ĐANG CÓ SÂN Cầu Lông Hoạt Động">
                                    <?php foreach($allDistrictsList as $d): ?>
                                        <?php if(isset($districtCounts[$d]) && $districtCounts[$d] > 0): ?>
                                            <option value="<?php echo $d; ?>" <?php echo ($currentUser['district'] ?? '') == $d ? 'selected' : ''; ?>>
                                                📍 <?php echo $d; ?> [🟢 <?php echo $districtCounts[$d]; ?> sân]
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php foreach($districtCounts as $dName => $cnt): ?>
                                        <?php if(!in_array($dName, $allDistrictsList)): ?>
                                            <option value="<?php echo htmlspecialchars($dName); ?>" <?php echo ($currentUser['district'] ?? '') == $dName ? 'selected' : ''; ?>>
                                                📍 <?php echo htmlspecialchars($dName); ?> [🟢 <?php echo $cnt; ?> sân]
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>

                                <optgroup label="🏢 Tất Cả Các Quận / Huyện Khác (TP.HCM)">
                                    <?php foreach($allDistrictsList as $d): ?>
                                        <?php if(!isset($districtCounts[$d]) || $districtCounts[$d] == 0): ?>
                                            <option value="<?php echo $d; ?>" <?php echo ($currentUser['district'] ?? '') == $d ? 'selected' : ''; ?>>
                                                ⚪ <?php echo $d; ?> (Chưa có sân liên kết)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Thể thức thi đấu -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                🏸 Thể Thức Muốn Chơi
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="border border-white/10 rounded-xl p-3 text-center cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-500/100/10">
                                    <input type="radio" name="format" value="Đánh Đôi" checked class="hidden">
                                    <span class="block text-xs font-bold text-white">Đôi (2v2)</span>
                                </label>
                                <label class="border border-white/10 rounded-xl p-3 text-center cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-500/100/10">
                                    <input type="radio" name="format" value="Đánh Đơn" class="hidden">
                                    <span class="block text-xs font-bold text-white">Đơn (1v1)</span>
                                </label>
                                <label class="border border-white/10 rounded-xl p-3 text-center cursor-pointer hover:border-green-500 transition-all has-[:checked]:border-green-600 has-[:checked]:bg-green-500/100/10">
                                    <input type="radio" name="format" value="Giao lưu nhóm" class="hidden">
                                    <span class="block text-xs font-bold text-white">Nhóm tự do</span>
                                </label>
                            </div>
                        </div>

                        <!-- Lời nhắn phong cách đánh -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                💬 Ghi Chú Kèo Đấu (Tùy chọn)
                            </label>
                            <input type="text" name="note" placeholder="Ví dụ: Đánh bền cầu, giao lưu học hỏi, chia đều tiền sân..."
                                   class="w-full px-4 py-3 bg-[#0b1116]/5 border border-white/10 rounded-xl font-medium focus:ring-2 focus:ring-green-500 focus:bg-[#0b1116] transition-all text-sm">
                        </div>

                        <!-- Nút Gửi Lệnh -->
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-green-600/30 transform hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 text-base">
                            <span>🎯</span> ĐẶT LỆNH TÌM ĐỐI THỦ
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Tip thông minh bên dưới -->
            <div class="mt-6 bg-slate-50 p-4 rounded-2xl border border-slate-200/60 text-xs text-slate-600 flex items-start gap-2.5">
                <span class="text-base text-amber-500">💡</span>
                <p>
                    <strong>Cơ chế hàng đợi bất đồng bộ:</strong> Bạn không cần treo máy chờ đợi! Hệ thống sẽ chạy ngầm và gửi thông báo ngay khi có người cùng khung giờ và cùng Bậc Rank với bạn.
                </p>
            </div>
        </div>

        <!-- CỘT PHẢI: TRẠNG THÁI CÁC PHIẾU TÌM TRẬN CỦA TÔI (7 Cột) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="flex items-center justify-between pb-2 border-b border-white/10">
                <h3 class="text-xl font-extrabold text-white flex items-center gap-2">
                    <span>📋</span> Lệnh Tìm Trận Của Bạn
                </h3>
                <span class="text-xs font-semibold text-white/60">
                    Cập nhật tự động thời gian thực
                </span>
            </div>

            <?php if(empty($myTickets)): ?>
                <div class="bg-[#0b1116] rounded-3xl p-12 text-center border-2 border-dashed border-white/10 shadow-none">
                    <div class="w-16 h-16 bg-green-500/100/10 text-green-400 rounded-2xl flex items-center justify-center mx-auto text-3xl mb-4">
                        🏸
                    </div>
                    <h4 class="text-lg font-bold text-white">Bạn chưa có lệnh tìm trận nào</h4>
                    <p class="text-white/60 text-sm mt-1 max-w-md mx-auto">
                        Hãy điền khung giờ rảnh và khu vực ở cột bên trái để hệ thống bắt đầu quét đối thủ xứng tầm cho bạn nhé!
                    </p>
                </div>
            <?php else: ?>
                <div class="space-y-5">
                    <?php foreach($myTickets as $t): ?>
                        
                        <!-- CARD TRƯỜNG HỢP 1: ĐANG CHỜ (WAITING) -->
                        <?php if($t['status'] == 'waiting'): ?>
                            <div class="bg-[#0b1116] rounded-3xl p-6 shadow-none border-2 border-emerald-400 relative overflow-hidden transition-all hover:shadow-lg" id="ticket-card-<?php echo $t['id']; ?>">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/5">
                                    <div class="flex items-center gap-3">
                                        <!-- Radar Pulse Icon -->
                                        <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-bold">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-xl bg-emerald-400 opacity-20"></span>
                                            <span>📡</span>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-extrabold text-white text-base">Đang Quét Tìm Đối Thủ...</span>
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Hàng Đợi</span>
                                            </div>
                                            <p class="text-xs text-white/60">Mã lệnh: #TICK-<?php echo $t['id']; ?> • Khởi tạo: <?php echo date('H:i d/m', strtotime($t['created_at'])); ?></p>
                                        </div>
                                    </div>

                                    <a href="index.php?action=cancel_ticket&id=<?php echo $t['id']; ?>" 
                                       onclick="return confirm('Bạn có chắc chắn muốn hủy lệnh tìm trận này?');"
                                       class="text-xs text-red-500 hover:text-red-700 font-bold px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 transition-colors self-start sm:self-center">
                                        ✕ Hủy Lệnh
                                    </a>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 py-4 text-xs">
                                    <div class="bg-[#0b1116]/5 p-3 rounded-xl">
                                        <span class="text-white/40 block mb-0.5">Ngày chơi:</span>
                                        <strong class="text-white text-sm"><?php echo date('d/m/Y', strtotime($t['play_date'])); ?></strong>
                                    </div>
                                    <div class="bg-[#0b1116]/5 p-3 rounded-xl">
                                        <span class="text-white/40 block mb-0.5">Khung giờ:</span>
                                        <strong class="text-white text-sm"><?php echo substr($t['start_time'], 0, 5); ?> - <?php echo substr($t['end_time'], 0, 5); ?></strong>
                                    </div>
                                    <div class="bg-[#0b1116]/5 p-3 rounded-xl">
                                        <span class="text-white/40 block mb-0.5">Khu vực:</span>
                                        <strong class="text-white text-sm"><?php echo htmlspecialchars($t['district']); ?></strong>
                                    </div>
                                    <div class="bg-[#0b1116]/5 p-3 rounded-xl">
                                        <span class="text-white/40 block mb-0.5">Thể thức:</span>
                                        <strong class="text-white text-sm"><?php echo htmlspecialchars($t['format']); ?></strong>
                                    </div>
                                </div>

                                <div class="bg-emerald-50/70 p-3 rounded-xl flex items-center justify-between text-xs text-emerald-800">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Đang so khớp các vận động viên Rank <strong class="text-emerald-950 font-bold"><?php echo $t['rank_name']; ?> (~<?php echo $t['elo_points']; ?> Elo)</strong>...</span>
                                    </div>
                                    <span class="text-[11px] text-emerald-600 font-semibold hidden md:inline">Tự động báo khi có kết quả</span>
                                </div>
                            </div>

                        <!-- CARD TRƯỜNG HỢP 2: ĐÃ GHÉP CẶP - CHỜ XÁC NHẬN (MATCHED) -->
                        <?php elseif($t['status'] == 'matched'): ?>
                            <div class="bg-gradient-to-br from-amber-500/10 via-white to-amber-500/5 rounded-3xl p-6 shadow-none border-2 border-amber-400 relative overflow-hidden">
                                
                                <!-- Top Status Bar -->
                                <div class="flex items-center justify-between pb-4 border-b border-amber-500/20 mb-5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl">🔥</span>
                                        <h4 class="font-black text-amber-900 text-lg uppercase tracking-wide">
                                            ĐÃ TÌM THẤY ĐỐI THỦ PHÙ HỢP!
                                        </h4>
                                    </div>
                                    <span class="bg-amber-500 text-white text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider animate-pulse shadow">
                                        Chờ Xác Nhận
                                    </span>
                                </div>

                                <!-- VS BATTLE CARD -->
                                <div class="bg-[#0b1116] rounded-2xl p-4 shadow-none border border-amber-500/20 mb-5">
                                    <div class="flex items-center justify-between">
                                        
                                        <!-- Bạn (Left) -->
                                        <div class="flex items-center gap-3 w-5/12">
                                            <img src="<?php echo htmlspecialchars($currentUser['avatar_url'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150'); ?>" 
                                                 class="w-12 h-12 rounded-xl object-cover border border-amber-300">
                                            <div>
                                                <div class="font-bold text-white text-sm truncate"><?php echo htmlspecialchars($currentUser['name'] ?? ($_SESSION['user_name'] ?? 'Bạn')); ?> (Bạn)</div>
                                                <div class="text-xs text-amber-600 font-extrabold"><?php echo $t['rank_name']; ?> • <?php echo $t['elo_points']; ?> Elo</div>
                                                <div class="text-[11px] mt-0.5">
                                                    <?php if($t['user_confirmed']): ?>
                                                        <span class="text-green-400 font-bold flex items-center gap-0.5">✓ Bạn đã đồng ý</span>
                                                    <?php else: ?>
                                                        <span class="text-amber-500 font-medium">Chờ bạn xác nhận...</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- VS Icon (Center) -->
                                        <div class="w-2/12 text-center">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-red-600 to-amber-600 text-white font-black flex items-center justify-center mx-auto text-sm shadow-none">
                                                VS
                                            </div>
                                        </div>

                                        <!-- Đối Thủ (Right) -->
                                        <div class="flex items-center justify-end gap-3 w-5/12 text-right">
                                            <div>
                                                <div class="font-bold text-white text-sm truncate"><?php echo htmlspecialchars($t['opp_name']); ?></div>
                                                <div class="text-xs text-amber-600 font-extrabold"><?php echo $t['opp_rank']; ?> • <?php echo $t['opp_elo']; ?> Elo</div>
                                                <div class="text-[11px] mt-0.5">
                                                    <?php if($t['opp_confirmed']): ?>
                                                        <span class="text-green-400 font-bold flex items-center justify-end gap-0.5">✓ Đối thủ đã đồng ý</span>
                                                    <?php else: ?>
                                                        <span class="text-white/40 font-medium">Đối thủ chưa bấm...</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <img src="<?php echo htmlspecialchars($t['opp_avatar'] ?? 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=150'); ?>" 
                                                 class="w-12 h-12 rounded-xl object-cover border border-amber-300">
                                        </div>

                                    </div>
                                </div>

                                <!-- THÔNG TIN SÂN & GIÁ TIỀN CHIA ĐÔI -->
                                <div class="bg-amber-50/60 rounded-2xl p-4 border border-amber-500/20 text-xs text-gray-700 space-y-2.5 mb-5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-white/60 font-medium">📍 Sân thi đấu:</span>
                                            <?php if(!empty($t['has_pitch']) || !empty($t['specified_pitch_id'])): ?>
                                                <span class="text-[10px] bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded-full border border-amber-300">Chủ Kèo Chọn Sân</span>
                                            <?php else: ?>
                                                <span class="text-[10px] bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded-full border border-blue-300">Hệ Thống Đề Xuất</span>
                                            <?php endif; ?>
                                        </div>
                                        <strong class="text-white font-bold text-sm"><?php echo htmlspecialchars($t['pitch_name']); ?></strong>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-white/60 font-medium">⏰ Khung giờ:</span>
                                        <strong class="text-white"><?php echo date('d/m/Y', strtotime($t['play_date'])); ?> lúc <?php echo substr($t['start_time'], 0, 5); ?> - <?php echo substr($t['end_time'], 0, 5); ?></strong>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-amber-500/20/60">
                                        <div>
                                            <span class="text-white/60 font-medium">Chi phí chia đôi (Split Bill 50/50):</span>
                                            <?php if($discount > 0): ?>
                                                <span class="text-emerald-600 font-bold ml-1">(Đã giảm <?php echo $discount; ?>% theo Rank của bạn)</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php 
                                            $hrs = max(1, (strtotime($t['end_time']) - strtotime($t['start_time'])) / 3600);
                                            $baseHalf = ($hrs * $t['price_per_hour']) / 2;
                                            $myPay = $baseHalf * ((100 - $discount) / 100);
                                        ?>
                                        <strong class="text-emerald-700 font-black text-base"><?php echo number_format($myPay, 0, ',', '.'); ?>đ</strong>
                                    </div>

                                    <!-- Nút Mở Form Đổi Sân -->
                                    <div class="pt-2 flex items-center justify-between border-t border-amber-200/50">
                                        <span class="text-[11px] text-amber-800 italic">Muốn chọn sân khác? Bạn có thể thương lượng chọn lại trước khi bấm xác nhận.</span>
                                        <button type="button" onclick="document.getElementById('switchPitchBox_<?php echo $t['id']; ?>').classList.toggle('hidden')"
                                                class="text-xs text-amber-700 hover:text-amber-900 font-extrabold bg-amber-100 hover:bg-amber-200 px-3 py-1.5 rounded-lg border border-amber-300 transition-colors flex items-center gap-1 shadow-sm">
                                            <span>🔄</span> Đổi Sân Khác
                                        </button>
                                    </div>

                                    <!-- Box Đổi Sân Thi Đấu (Ẩn/Hiện) -->
                                    <div id="switchPitchBox_<?php echo $t['id']; ?>" class="hidden bg-[#0b1116] p-3.5 rounded-xl border border-amber-400 space-y-2 animate-fade-in mt-2">
                                        <h5 class="font-bold text-amber-400 text-xs flex items-center gap-1">
                                            <span>🏟️</span> Chọn Sân Thi Đấu Thay Thế Trống Slot:
                                        </h5>
                                        <form action="index.php?action=switch_pitch" method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="ticket_id" value="<?php echo $t['id']; ?>">
                                            <select name="pitch_id" required class="flex-1 px-3 py-2 bg-[#0b1116] border border-gray-600 rounded-lg text-xs font-medium text-white focus:ring-2 focus:ring-amber-500">
                                                <?php 
                                                    $availPitches = $ticketModel->getAvailablePitchesForMatch($t['id']);
                                                    foreach($availPitches as $ap): 
                                                ?>
                                                    <option value="<?php echo $ap['id']; ?>" <?php echo $ap['is_current'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($ap['name']); ?> (<?php echo htmlspecialchars($ap['district']); ?>) - <?php echo number_format($ap['price_per_hour'], 0, ',', '.'); ?>đ/h
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs px-3.5 py-2 rounded-lg transition-colors whitespace-nowrap">
                                                Xác Nhận Đổi
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-3">
                                    <?php if(!$t['user_confirmed']): ?>
                                        <a href="index.php?action=confirm_ticket&id=<?php echo $t['id']; ?>" 
                                           class="flex-1 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500 text-white font-extrabold py-3.5 px-4 rounded-xl shadow-none text-center transition-all flex items-center justify-center gap-2 text-sm">
                                            <span>✅</span> XÁC NHẬN KÈO ĐẤU
                                        </a>
                                    <?php else: ?>
                                        <div class="flex-1 bg-green-100 text-green-800 font-bold py-3.5 px-4 rounded-xl text-center text-sm border border-green-200 flex items-center justify-center gap-2">
                                            <span>✓</span> Đang chờ đối thủ bấm xác nhận...
                                        </div>
                                    <?php endif; ?>

                                    <a href="index.php?action=cancel_ticket&id=<?php echo $t['id']; ?>" 
                                       onclick="return confirm('Bạn có chắc chắn muốn từ chối kèo này? Hệ thống sẽ hủy và tiếp tục tìm đối thủ khác.');"
                                       class="px-4 py-3.5 bg-[#0b1116]/10 hover:bg-[#0b1116]/10 text-gray-700 font-bold rounded-xl text-xs transition-colors">
                                        Từ Chối
                                    </a>
                                </div>
                            </div>

                        <!-- CARD TRƯỜNG HỢP 3: ĐÃ CHỐT XONG (CONFIRMED) -->
                        <?php elseif($t['status'] == 'confirmed'): ?>
                            <div class="bg-[#0b1116] rounded-3xl p-6 shadow-none border-2 border-green-500 relative overflow-hidden">
                                <div class="flex items-center justify-between pb-3 border-b border-white/5 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-400 font-black text-lg">🎉 TRẬN ĐẤU ĐÃ ĐƯỢC CHỐT!</span>
                                    </div>
                                    <span class="bg-green-600 text-white text-xs font-black px-3 py-1 rounded-full uppercase">
                                        Đã Đặt Sân
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-4">
                                    <div class="bg-[#0b1116]/5 p-3 rounded-xl space-y-1">
                                        <span class="text-white/40 block">Sân thi đấu:</span>
                                        <strong class="text-white font-bold text-sm block"><?php echo htmlspecialchars($t['pitch_name']); ?></strong>
                                        <span class="text-white/60 block"><?php echo htmlspecialchars($t['pitch_location'] ?? ''); ?></span>
                                    </div>
                                    <div class="bg-[#0b1116]/5 p-3 rounded-xl space-y-1">
                                        <span class="text-white/40 block">Thời gian:</span>
                                        <strong class="text-white font-bold text-sm block"><?php echo date('d/m/Y', strtotime($t['play_date'])); ?></strong>
                                        <span class="text-emerald-600 font-bold"><?php echo substr($t['start_time'], 0, 5); ?> - <?php echo substr($t['end_time'], 0, 5); ?></span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between bg-green-500/100/10 p-3 rounded-xl mb-4 text-xs">
                                    <div>
                                        <span class="text-green-800">Đối thủ của bạn: <strong class="font-bold"><?php echo htmlspecialchars($t['opp_name']); ?></strong> (<?php echo $t['opp_rank']; ?>)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="index.php?action=chat&user=<?php echo $t['opp_user_id']; ?>" 
                                           class="bg-green-700 hover:bg-green-800 text-white font-bold px-3 py-1.5 rounded-lg flex items-center gap-1 shadow-none">
                                            <span>💬</span> Nhắn Tin
                                        </a>
                                    </div>
                                </div>

                                <!-- Nút Báo Cáo Đối Thủ Bùng Kèo (No-Show) -->
                                <div class="pt-3 border-t border-white/5 flex items-center justify-between text-xs">
                                    <a href="index.php?action=my_bookings" class="text-white/60 hover:text-white underline font-medium">
                                        Chi tiết đơn #<?php echo $t['booking_id']; ?> →
                                    </a>
                                    <a href="index.php?action=report_noshow&booking_id=<?php echo $t['booking_id']; ?>&violator_id=<?php echo $t['opp_user_id']; ?>" 
                                       onclick="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn báo cáo đối thủ [<?php echo addslashes($t['opp_name']); ?>] bùng kèo không đến sân? Hệ thống sẽ phạt trừ 30 điểm uy tín, trừ 50 Elo và tạm khóa tìm trận của đối thủ.');"
                                       class="text-red-500 hover:text-red-700 font-bold hover:bg-red-50 px-2.5 py-1 rounded-lg transition-colors flex items-center gap-1">
                                        <span>⚠️ Báo cáo bùng kèo</span>
                                    </a>
                                </div>
                            </div>

                        <?php endif; ?>

                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- MODAL POPUP: LỊCH SỬ ĐIỂM UY TÍN FAIR-PLAY -->
<div id="repModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-[#0b1116] rounded-3xl max-w-lg w-full p-6 md:p-8 shadow-none space-y-6 animate-scale-up border border-white/5">
        <div class="flex items-center justify-between pb-4 border-b border-white/5">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">🛡️</span>
                <div>
                    <h3 class="font-extrabold text-white text-lg">Hệ Thống Điểm Uy Tín Fair-Play</h3>
                    <p class="text-xs text-white/60">Chính sách kiểm soát chất lượng & chống bùng kèo</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('repModal').classList.add('hidden')" 
                    class="text-white/40 hover:text-gray-700 text-2xl font-bold">&times;</button>
        </div>

        <!-- Current Score Highlight -->
        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between">
            <div>
                <span class="text-xs text-white/60 block uppercase font-bold tracking-wider">Điểm Hiện Tại Của Bạn</span>
                <span class="text-2xl font-black <?php echo $repColor; ?>"><?php echo $repScore; ?> / 100 Điểm</span>
            </div>
            <span class="text-xs font-black px-3 py-1.5 rounded-full bg-[#0b1116] border border-white/10 shadow-none text-white">
                <?php echo $userReputation['tier_badge']; ?>
            </span>
        </div>

        <!-- Quy tắc tính điểm -->
        <div class="space-y-2 text-xs text-white/80 bg-amber-50/60 p-4 rounded-2xl border border-amber-500/20/60">
            <h4 class="font-bold text-amber-900 text-sm flex items-center gap-1">
                <span>📜</span> Quy Tắc Cộng / Trừ Điểm:
            </h4>
            <ul class="space-y-1.5 list-disc list-inside">
                <li><strong class="text-emerald-700">+2 điểm</strong> khi hoàn thành trận đấu đúng hẹn tại sân.</li>
                <li><strong class="text-amber-700">-5 điểm</strong> khi hủy kèo sau khi đã khớp đối thủ.</li>
                <li><strong class="text-rose-700">-15 điểm</strong> khi hủy kèo cận giờ thi đấu (&lt; 2 tiếng).</li>
                <li><strong class="text-red-700 font-bold">-30 điểm, -50 Elo & khóa tìm trận 3 ngày</strong> nếu bỏ trận không đến (No-Show).</li>
                <li>Dưới <strong class="text-red-600">50 điểm</strong>: Bị tước quyền dùng tính năng Ghép Trận Tự Động.</li>
            </ul>
        </div>

        <!-- Lịch sử biến động gần đây -->
        <div>
            <h4 class="font-bold text-white text-sm mb-3">Lịch Sử Biến Động Gần Đây:</h4>
            <?php if(empty($reputationLogs)): ?>
                <p class="text-xs text-white/40 italic text-center py-4">Chưa có ghi nhận vi phạm nào. Bạn là người chơi tuyệt vời!</p>
            <?php else: ?>
                <div class="max-h-48 overflow-y-auto space-y-2 pr-1">
                    <?php foreach($reputationLogs as $log): ?>
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#0b1116]/5 text-xs border border-white/5">
                            <div>
                                <span class="font-semibold text-white block"><?php echo htmlspecialchars($log['reason']); ?></span>
                                <span class="text-[10px] text-white/40"><?php echo date('H:i d/m/Y', strtotime($log['created_at'])); ?> • Điểm sau đó: <?php echo $log['current_score']; ?></span>
                            </div>
                            <span class="font-black text-sm <?php echo $log['change_amount'] > 0 ? 'text-emerald-600' : 'text-red-600'; ?>">
                                <?php echo $log['change_amount'] > 0 ? '+' . $log['change_amount'] : $log['change_amount']; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="button" onclick="document.getElementById('repModal').classList.add('hidden')"
                class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-xl text-sm transition-colors shadow">
            Đã Hiểu & Đóng Lại
        </button>
    </div>
</div>

<!-- SCRIPT AUTO-POLL KIỂM TRA TÌNH TRẠNG KÈO (REALTIME CHECK) -->
<script>
function getLocationGPS() {
    const statusText = document.getElementById('gpsStatusText');
    const btn = document.getElementById('btnGpsLocate');
    if ("geolocation" in navigator) {
        statusText.innerText = 'Đang định vị GPS...';
        btn.disabled = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                document.getElementById('ticketUserLat').value = lat;
                document.getElementById('ticketUserLng').value = lng;
                statusText.innerText = `✅ GPS: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                document.getElementById('customLocationInput').value = "Tọa độ GPS cá nhân";
                btn.disabled = false;
            },
            (err) => {
                statusText.innerText = '⚠️ Lỗi GPS: ' + err.message;
                btn.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    } else {
        alert("Trình duyệt không hỗ trợ Geolocation.");
    }
}

function geocodeCustomAddress() {
    const input = document.getElementById('customLocationInput').value.trim();
    const statusText = document.getElementById('gpsStatusText');
    if (!input) {
        alert("Vui lòng nhập địa chỉ hoặc Thành phố/Quận Huyện.");
        return;
    }
    statusText.innerText = 'Đang tìm GPS...';
    fetch('api_geocode.php?q=' + encodeURIComponent(input))
        .then(r => r.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                document.getElementById('ticketUserLat').value = lat;
                document.getElementById('ticketUserLng').value = lng;
                statusText.innerText = `✅ GPS: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
            } else {
                statusText.innerText = '⚠️ Không tìm thấy vị trí.';
                alert('Không tìm thấy tọa độ cho địa chỉ "' + input + '". Vui lòng thử lại.');
            }
        })
        .catch(err => {
            statusText.innerText = '⚠️ Lỗi kết nối API.';
        });
}

function togglePitchSelector(show) {
    const box = document.getElementById('specified_pitch_box');
    if (box) {
        if (show) {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    }
}

// --- PITCH SELECTION MAP & GPS INTERACTION ---
let pitchPickerMap = null;
let pitchMapMarkers = {};

const ALL_PITCHES_LIST = <?php echo json_encode(array_values($allPitches ?? []), JSON_UNESCAPED_UNICODE); ?>;

function calcDistanceHaversine(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function togglePitchMapPicker() {
    const container = document.getElementById('pitchMapPickerContainer');
    if (!container) return;

    const isHidden = container.classList.contains('hidden');
    if (isHidden) {
        container.classList.remove('hidden');
        if (!pitchPickerMap) {
            initPitchMapPicker();
        } else {
            setTimeout(() => { pitchPickerMap.invalidateSize(); }, 200);
        }
    } else {
        container.classList.add('hidden');
    }
}

function initPitchMapPicker() {
    if (typeof L === 'undefined') return;
    
    // Default center: HCMC (10.8123, 106.7118)
    pitchPickerMap = L.map('pitchMapPicker').setView([10.8123, 106.7118], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(pitchPickerMap);

    const bounds = [];

    ALL_PITCHES_LIST.forEach(p => {
        const lat = parseFloat(p.latitude);
        const lng = parseFloat(p.longitude);
        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
            bounds.push([lat, lng]);

            const customIcon = L.divIcon({
                className: 'custom-pitch-map-marker',
                html: `<div style="background:#10b981; color:#fff; font-weight:800; font-size:11px; padding:4px 8px; border-radius:10px; border:2px solid #fff; box-shadow:0 3px 10px rgba(0,0,0,0.4); text-align:center; white-space:nowrap; cursor:pointer;">🏸 ${p.name}</div>`,
                iconSize: [120, 30],
                iconAnchor: [60, 15]
            });

            const marker = L.marker([lat, lng], { icon: customIcon }).addTo(pitchPickerMap);
            marker.bindPopup(`
                <div style="font-family:sans-serif; text-align:center; color:#0b1116;">
                    <strong style="font-size:13px; color:#10b981;">${p.name}</strong><br>
                    <span style="font-size:11px; color:#64748b;">${p.district || ''}</span><br>
                    <span style="font-size:12px; font-weight:bold; color:#059669;">${Number(p.price_per_hour).toLocaleString('vi-VN')}đ/h</span><br>
                    <button onclick="selectPitchFromMap(${p.id})" style="margin-top:6px; background:#10b981; color:#fff; font-weight:bold; border:none; padding:5px 12px; border-radius:8px; cursor:pointer; font-size:11px;">
                        ✅ Chọn Sân Này
                    </button>
                </div>
            `);

            pitchMapMarkers[p.id] = { marker: marker, lat: lat, lng: lng, data: p };
        }
    });

    if (bounds.length > 0) {
        pitchPickerMap.fitBounds(bounds, { padding: [30, 30] });
    }
}

function selectPitchFromMap(pitchId) {
    const select = document.getElementById('specified_pitch_id');
    if (!select) return;

    select.value = pitchId;
    onPitchDropdownChange();

    if (pitchMapMarkers[pitchId]) {
        const item = pitchMapMarkers[pitchId];
        if (pitchPickerMap) {
            pitchPickerMap.flyTo([item.lat, item.lng], 15, { duration: 1 });
            item.marker.openPopup();
        }
    }
}

function autoSelectNearestPitchByGPS() {
    let uLat = parseFloat(document.getElementById('ticketUserLat').value);
    let uLng = parseFloat(document.getElementById('ticketUserLng').value);

    const findAndSelectNearest = (lat, lng) => {
        if (!ALL_PITCHES_LIST || ALL_PITCHES_LIST.length === 0) return;

        let nearestPitch = null;
        let minDistance = Infinity;

        ALL_PITCHES_LIST.forEach(p => {
            const pLat = parseFloat(p.latitude);
            const pLng = parseFloat(p.longitude);
            if (!isNaN(pLat) && !isNaN(pLng) && pLat !== 0 && pLng !== 0) {
                const dist = calcDistanceHaversine(lat, lng, pLat, pLng);
                if (dist < minDistance) {
                    minDistance = dist;
                    nearestPitch = p;
                }
            }
        });

        if (nearestPitch) {
            selectPitchFromMap(nearestPitch.id);
            
            // Show GPS Distance Tag
            const distLabel = document.getElementById('selectedPitchDistLabel');
            if (distLabel) {
                distLabel.textContent = `🎯 Gần vị trí GPS của bạn nhất: cách ${minDistance.toFixed(1)} km`;
                distLabel.className = "text-[10px] text-[#d8ff3e] font-bold";
            }
            
            // Highlight pitch tag
            const tag = document.getElementById('selectedPitchTag');
            if (tag) {
                tag.textContent = `🧭 GPS đã chọn sân gần nhất (${minDistance.toFixed(1)} km)`;
                tag.className = "text-[10px] font-bold text-[#d8ff3e] bg-[#d8ff3e]/10 px-2.5 py-0.5 rounded-full border border-[#d8ff3e]/30";
            }

            // Show Map Container if hidden
            const container = document.getElementById('pitchMapPickerContainer');
            if (container && container.classList.contains('hidden')) {
                togglePitchMapPicker();
            }
        }
    };

    if (!isNaN(uLat) && !isNaN(uLng) && uLat !== 0 && uLng !== 0) {
        findAndSelectNearest(uLat, uLng);
    } else {
        // Trigger Geolocation
        const statusText = document.getElementById('gpsStatusText');
        if ("geolocation" in navigator) {
            if (statusText) statusText.innerText = 'Đang định vị GPS để tìm sân gần nhất...';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    uLat = pos.coords.latitude;
                    uLng = pos.coords.longitude;
                    document.getElementById('ticketUserLat').value = uLat;
                    document.getElementById('ticketUserLng').value = uLng;
                    if (statusText) statusText.innerText = `✅ GPS: ${uLat.toFixed(4)}, ${uLng.toFixed(4)}`;
                    findAndSelectNearest(uLat, uLng);
                },
                (err) => {
                    alert("Lỗi định vị GPS: " + err.message + ". Vui lòng bấm Dùng Vị Trí Hiện Tại (GPS) trước.");
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        } else {
            alert("Trình duyệt không hỗ trợ Geolocation.");
        }
    }
}

function onPitchDropdownChange() {
    const select = document.getElementById('specified_pitch_id');
    const infoCard = document.getElementById('selectedPitchInfoCard');
    const tag = document.getElementById('selectedPitchTag');
    if (!select) return;

    const pitchId = select.value;
    if (!pitchId) {
        if (infoCard) infoCard.classList.add('hidden');
        if (tag) {
            tag.textContent = 'Chưa chọn sân';
            tag.className = 'text-[10px] font-bold text-amber-300/90 bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-500/30';
        }
        return;
    }

    const selectedOpt = select.options[select.selectedIndex];
    const pitchName = selectedOpt.getAttribute('data-name') || selectedOpt.text;
    const pitchDist = selectedOpt.getAttribute('data-district') || '';
    const pitchPrice = selectedOpt.getAttribute('data-price') || '';
    const pLat = parseFloat(selectedOpt.getAttribute('data-lat'));
    const pLng = parseFloat(selectedOpt.getAttribute('data-lng'));

    if (infoCard) {
        infoCard.classList.remove('hidden');
        document.getElementById('selectedPitchNameLabel').textContent = pitchName + (pitchDist ? ' (' + pitchDist + ')' : '');
        document.getElementById('selectedPitchPriceLabel').textContent = pitchPrice ? pitchPrice + 'đ/h' : '';
        
        let uLat = parseFloat(document.getElementById('ticketUserLat').value);
        let uLng = parseFloat(document.getElementById('ticketUserLng').value);
        if (!isNaN(uLat) && !isNaN(uLng) && !isNaN(pLat) && !isNaN(pLng)) {
            const dist = calcDistanceHaversine(uLat, uLng, pLat, pLng);
            document.getElementById('selectedPitchDistLabel').textContent = `Cách vị trí GPS của bạn: ${dist.toFixed(1)} km`;
        } else {
            document.getElementById('selectedPitchDistLabel').textContent = `Khu vực: ${pitchDist}`;
        }
    }

    if (tag) {
        tag.textContent = '✅ Đã chọn sân';
        tag.className = 'text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-500/30';
    }

    if (pitchMapMarkers[pitchId] && pitchPickerMap) {
        const item = pitchMapMarkers[pitchId];
        pitchPickerMap.flyTo([item.lat, item.lng], 15, { duration: 1 });
        item.marker.openPopup();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Nếu có ticket đang ở trạng thái 'waiting', tự động polling API mỗi 4 giây
    const waitingCards = document.querySelectorAll('[id^="ticket-card-"]');
    if (waitingCards.length > 0) {
        setInterval(() => {
            waitingCards.forEach(card => {
                const ticketId = card.id.replace('ticket-card-', '');
                fetch(`index.php?action=api_check_ticket&id=${ticketId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.status && data.status !== 'waiting') {
                            // Khi trạng thái đổi từ waiting sang matched hoặc confirmed -> Tự động reload để người dùng xem trận
                            window.location.reload();
                        }
                    })
                    .catch(err => console.log('Polling check:', err));
            });
        }, 4000);
    }
});
</script>
