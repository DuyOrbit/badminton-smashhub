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
        <?php elseif($_GET['msg'] == 'pitch_proposed'): ?>
            <div class="bg-gradient-to-r from-amber-600 to-yellow-600 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">⏳</span>
                    <div>
                        <h4 class="font-bold text-lg">Đã gửi đề xuất đổi sân thi đấu!</h4>
                        <p class="text-sm text-amber-100">Lời đề xuất đã được gửi tới đối thủ. Sân sẽ chính thức thay đổi ngay khi đối thủ bấm Đồng Ý.</p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold">&times;</button>
            </div>
        <?php elseif($_GET['msg'] == 'pitch_switch_accepted'): ?>
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🎉</span>
                    <div>
                        <h4 class="font-bold text-lg">Đồng thuận đổi sân thành công!</h4>
                        <p class="text-sm text-emerald-100">Địa điểm thi đấu mới đã được cập nhật cho cả 2 bên. Vui lòng kiểm tra lại tiền sân và bấm xác nhận!</p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold">&times;</button>
            </div>
        <?php elseif($_GET['msg'] == 'pitch_switch_rejected'): ?>
            <div class="bg-gradient-to-r from-gray-700 to-slate-800 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">ℹ️</span>
                    <div>
                        <h4 class="font-bold text-lg">Đã từ chối đề xuất đổi sân</h4>
                        <p class="text-sm text-slate-300">Trận đấu vẫn giữ nguyên sân thi đấu ban đầu theo thỏa thuận trước đó.</p>
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
            <div class="bg-blue-600 text-white p-4 rounded-2xl shadow-lg flex items-center gap-3 animate-fade-in">
                <span class="text-2xl">⏳</span>
                <div>
                    <h4 class="font-bold">Bạn đã xác nhận kèo thành công!</h4>
                    <p class="text-sm text-blue-100">Hệ thống đang chờ đối thủ bấm xác nhận & thanh toán. Khi cả hai cùng hoàn tất, sân sẽ được tự động khóa.</p>
                </div>
            </div>
        <?php elseif($_GET['msg'] == 'waiting_opponent_payment'): ?>
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-5 rounded-2xl shadow-lg flex items-center gap-4 animate-fade-in">
                <span class="text-3xl">💳</span>
                <div>
                    <h4 class="font-black text-xl">BẠN ĐÃ HOÀN TẤT THANH TOÁN 50%!</h4>
                    <p class="text-sm text-emerald-100">Hệ thống đang chờ đối thủ thanh toán phần còn lại. Khi đối thủ hoàn tất, sân sẽ được khóa tự động và cấp mã Check-in ngay lập tức!</p>
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

                        <!-- BỘ ĐỊNH VỊ VỊ TRÍ & BÁN KÍNH GHÉP TRẬN (GPS & BÁN KÍNH THÔNG MINH) -->
                        <div class="bg-[#0b1116] p-4 rounded-2xl border border-cyan-500/30 space-y-3.5 relative overflow-hidden">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>🧭</span> Vị Trí Của Bạn & Bán Kính Tìm Sân:
                                </label>
                                <span class="text-[10px] text-cyan-300/80 font-semibold" id="gpsStatusText">Chưa định vị</span>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" id="btnGpsLocate" onclick="getLocationGPS()" 
                                        class="flex-1 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 shadow cursor-pointer">
                                    <span>📍</span> Dùng Vị Trí Hiện Tại (GPS)
                                </button>
                            </div>

                            <!-- Ô nhập địa chỉ tự do toàn quốc -->
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1">Hoặc Nhập Khu Vực / Tỉnh Thành Bất Kỳ:</label>
                                <div class="flex gap-1.5">
                                    <input type="text" id="customLocationInput" name="location_name" placeholder="Ví dụ: Bình Thạnh, Cầu Giấy, Hải Châu..."
                                           class="flex-1 px-3 py-2 bg-[#121b21] border border-white/10 rounded-xl text-xs font-medium text-white focus:ring-2 focus:ring-cyan-500">
                                    <button type="button" onclick="geocodeCustomAddress()" class="px-3.5 py-2 bg-cyan-700 hover:bg-cyan-600 text-white font-bold text-xs rounded-xl whitespace-nowrap cursor-pointer">
                                        Tìm GPS
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="user_lat" id="ticketUserLat" value="">
                            <input type="hidden" name="user_lng" id="ticketUserLng" value="">
                            <input type="hidden" name="district" id="ticketDistrictInput" value="Tất cả">

                            <!-- Thanh Trượt Bán Kính Quét Tìm Kèo -->
                            <div class="pt-2 border-t border-white/10">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[11px] font-bold text-gray-300">📏 Bán Kính Quét Sân & Đối Thủ:</span>
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

                                <div class="bg-emerald-500/10 p-3 rounded-xl flex items-center justify-between text-xs text-emerald-400 border border-emerald-500/20">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <span>Đang so khớp các vận động viên Rank <strong class="text-white font-bold"><?php echo $t['rank_name']; ?> (~<?php echo $t['elo_points']; ?> Elo)</strong>...</span>
                                    </div>
                                    <span class="text-[11px] text-emerald-400 font-semibold hidden md:inline">Tự động báo khi có kết quả</span>
                                </div>
                            </div>

                        <!-- CARD TRƯỜNG HỢP 2: ĐÃ GHÉP CẶP - CHỜ XÁC NHẬN & THANH TOÁN (MATCHED) -->
                        <?php elseif($t['status'] == 'matched'): ?>
                            <?php 
                                $hrs = max(1, (strtotime($t['end_time']) - strtotime($t['start_time'])) / 3600);
                                $totalBase = $hrs * $t['price_per_hour'];
                                $baseHalf = $totalBase / 2;
                                $myPay = $baseHalf * ((100 - $discount) / 100);
                                
                                $myConfirmed = !empty($t['user_confirmed']);
                                $oppConfirmed = !empty($t['opp_confirmed']);
                                $myMethod = $t['payment_method'] ?? 'cash';
                                $myPaid = ($t['payment_status'] ?? '') === 'paid';
                                
                                $oppMethod = $t['opp_payment_method'] ?? 'cash';
                                $oppPaid = ($t['opp_payment_status'] ?? '') === 'paid';
                            ?>
                            <div class="bg-gradient-to-br from-[#121b21] via-[#0b1116] to-[#121b21] rounded-3xl p-6 shadow-2xl border-2 border-amber-500/60 relative overflow-hidden transition-all hover:border-amber-400" id="matched-card-<?php echo $t['id']; ?>" data-matched-id="<?php echo $t['id']; ?>">
                                
                                <!-- Glow decorative FX -->
                                <div class="absolute -right-16 -top-16 w-44 h-44 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
                                <div class="absolute -left-16 -bottom-16 w-44 h-44 bg-cyan-500/10 rounded-full blur-2xl pointer-events-none"></div>

                                <!-- Top Status Bar -->
                                <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-5 relative z-10">
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-2xl animate-bounce">🔥</span>
                                        <div>
                                            <h4 class="font-black text-amber-400 text-lg uppercase tracking-wide">
                                                ĐÃ TÌM THẤY ĐỐI THỦ PHÙ HỢP!
                                            </h4>
                                            <p class="text-xs text-white/60">Hai bên có thể Chat thỏa thuận trước khi bấm xác nhận & thanh toán chia đôi (50/50)</p>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if($myConfirmed && !$oppConfirmed): ?>
                                            <span class="bg-amber-500/20 text-amber-300 border border-amber-500/40 text-xs font-black px-3 py-1.5 rounded-full uppercase tracking-wider animate-pulse flex items-center gap-1.5 shadow">
                                                <span>⏳</span> Đang Chờ Đối Thủ Trả
                                            </span>
                                        <?php elseif(!$myConfirmed && $oppConfirmed): ?>
                                            <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-xs font-black px-3 py-1.5 rounded-full uppercase tracking-wider animate-pulse flex items-center gap-1.5 shadow">
                                                <span>⚡</span> Đối Thủ Đã Xác Nhận!
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-amber-500 text-slate-950 text-xs font-black px-3 py-1.5 rounded-full uppercase tracking-wider shadow">
                                                Chờ Xác Nhận
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- VS BATTLE CARD -->
                                <div class="bg-[#121b21] rounded-2xl p-4 border border-white/10 mb-5 relative z-10 shadow-inner">
                                    <div class="flex items-center justify-between">
                                        
                                        <!-- Bạn (Left) -->
                                        <div class="flex items-center gap-3 w-5/12">
                                            <img src="<?php echo htmlspecialchars($currentUser['avatar_url'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150'); ?>" 
                                                 class="w-12 h-12 rounded-xl object-cover border-2 border-emerald-400 shrink-0">
                                            <div class="overflow-hidden">
                                                <div class="font-bold text-white text-sm truncate"><?php echo htmlspecialchars($currentUser['name'] ?? ($_SESSION['user_name'] ?? 'Bạn')); ?> (Bạn)</div>
                                                <div class="text-xs text-amber-400 font-extrabold"><?php echo $t['rank_name']; ?> • <?php echo $t['elo_points']; ?> Elo</div>
                                                <div class="text-[11px] mt-1">
                                                    <?php if($myConfirmed): ?>
                                                        <span class="text-emerald-400 font-bold inline-flex items-center gap-1 bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded-md">
                                                            ✓ Đã trả 50% (<?php echo $myMethod === 'vietqr' ? 'VietQR' : 'Tiền mặt'; ?>)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-amber-400 font-medium">Chưa thanh toán 50%</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- VS Icon (Center) -->
                                        <div class="w-2/12 text-center">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-red-600 to-amber-600 text-white font-black flex items-center justify-center mx-auto text-sm shadow-lg ring-4 ring-amber-500/20">
                                                VS
                                            </div>
                                        </div>

                                        <!-- Đối Thủ (Right) -->
                                        <div class="flex items-center justify-end gap-3 w-5/12 text-right">
                                            <div class="overflow-hidden">
                                                <div class="font-bold text-white text-sm truncate"><?php echo htmlspecialchars($t['opp_name']); ?></div>
                                                <div class="text-xs text-amber-400 font-extrabold"><?php echo $t['opp_rank']; ?> • <?php echo $t['opp_elo']; ?> Elo</div>
                                                <div class="text-[11px] mt-1">
                                                    <?php if($oppConfirmed): ?>
                                                        <span class="text-emerald-400 font-bold inline-flex items-center gap-1 bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded-md">
                                                            ✓ Đối thủ đã thanh toán 50%
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-white/40 font-medium">Đối thủ chưa xác nhận...</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <img src="<?php echo htmlspecialchars($t['opp_avatar'] ?? 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=150'); ?>" 
                                                 class="w-12 h-12 rounded-xl object-cover border-2 border-amber-400 shrink-0">
                                        </div>

                                    </div>
                                </div>

                                <!-- THÔNG TIN SÂN & GIÁ TIỀN CHIA ĐÔI -->
                                <div class="bg-[#121b21] rounded-2xl p-4 border border-white/10 text-xs text-white/80 space-y-3 mb-5 relative z-10">
                                    <div id="pitch_proposal_banner_container_<?php echo $t['id']; ?>">
                                    <?php if(!empty($t['proposed_pitch_id'])): ?>
                                        <?php if($t['proposed_by_user_id'] == $_SESSION['user_id']): ?>
                                            <div class="p-3.5 bg-amber-950/70 border border-amber-500/50 rounded-2xl text-xs text-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md animate-pulse">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xl shrink-0">⏳</span>
                                                    <div>
                                                        <strong>Bạn đã gửi đề xuất đổi sang sân: <span class="text-white underline"><?php echo htmlspecialchars($t['proposed_pitch_name']); ?></span> (<?php echo number_format($t['proposed_pitch_price'],0,',','.'); ?>đ/h).</strong>
                                                        <span class="block text-[11px] text-amber-300/70">Đang chờ đối thủ <?php echo htmlspecialchars($t['opp_name']); ?> đồng ý...</span>
                                                    </div>
                                                </div>
                                                <button type="button" 
                                                   onclick="return ajaxRejectSwitchPitch(event, this, <?php echo $t['id']; ?>);"
                                                   class="bg-white/10 hover:bg-white/20 text-white font-bold px-3 py-1.5 rounded-lg text-xs whitespace-nowrap border border-white/20 transition-all self-end sm:self-center cursor-pointer">
                                                    ✕ Hủy Đề Xuất
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="p-4 bg-emerald-950/80 border-2 border-emerald-500 rounded-2xl text-xs text-white shadow-xl animate-bounce-subtle">
                                                <div class="flex items-start gap-3">
                                                    <span class="text-2xl shrink-0">🏟️</span>
                                                    <div class="flex-1 space-y-2">
                                                        <div>
                                                            <span class="text-emerald-400 font-black uppercase tracking-wider text-[11px] block">ĐỐI THỦ ĐỀ XUẤT ĐỔI SÂN THI ĐẤU</span>
                                                            <p class="text-sm font-bold text-white mt-0.5">
                                                                <?php echo htmlspecialchars($t['opp_name']); ?> muốn đổi sang: <span class="text-[#d8ff3e] underline"><?php echo htmlspecialchars($t['proposed_pitch_name']); ?></span>
                                                            </p>
                                                            <p class="text-xs text-white/70 mt-0.5">
                                                                📍 <?php echo htmlspecialchars($t['proposed_pitch_location']); ?> • Giá: <?php echo number_format($t['proposed_pitch_price'], 0, ',', '.'); ?>đ/h (Mỗi bên 50%: <?php echo number_format($t['proposed_pitch_price'] / 2, 0, ',', '.'); ?>đ)
                                                            </p>
                                                        </div>
                                                        <div class="flex items-center gap-2 pt-1">
                                                            <button type="button" 
                                                               onclick="return ajaxAcceptSwitchPitch(event, this, <?php echo $t['id']; ?>);"
                                                               class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black px-4 py-2 rounded-xl text-xs uppercase tracking-wider shadow transition-all cursor-pointer">
                                                                ✅ Đồng Ý Đổi Sân
                                                            </button>
                                                            <button type="button" 
                                                               onclick="return ajaxRejectSwitchPitch(event, this, <?php echo $t['id']; ?>);"
                                                               class="bg-white/10 hover:bg-white/20 text-white font-bold px-3 py-2 rounded-xl text-xs border border-white/15 transition-all cursor-pointer">
                                                                ✕ Từ Chối (Giữ Sân Cũ)
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-white/60 font-medium">📍 Sân thi đấu:</span>
                                            <?php if(!empty($t['has_pitch']) || !empty($t['specified_pitch_id'])): ?>
                                                <span class="text-[10px] bg-amber-500/20 text-amber-300 font-bold px-2 py-0.5 rounded-full border border-amber-500/40">Chủ Kèo Chọn Sân</span>
                                            <?php else: ?>
                                                <span class="text-[10px] bg-blue-500/20 text-blue-300 font-bold px-2 py-0.5 rounded-full border border-blue-500/40">Hệ Thống Đề Xuất</span>
                                            <?php endif; ?>
                                        </div>
                                        <strong class="text-white font-bold text-sm" id="current_pitch_name_<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['pitch_name']); ?></strong>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-white/60 font-medium">⏰ Khung giờ:</span>
                                        <strong class="text-emerald-400"><?php echo date('d/m/Y', strtotime($t['play_date'])); ?> lúc <?php echo substr($t['start_time'], 0, 5); ?> - <?php echo substr($t['end_time'], 0, 5); ?></strong>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-white/10">
                                        <div>
                                            <span class="text-white/60 font-medium">Chi phí chia đôi (Split 50/50):</span>
                                            <?php if($discount > 0): ?>
                                                <span class="text-emerald-400 font-bold ml-1">(Giảm <?php echo $discount; ?>% theo Rank)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] text-white/40 block">Tổng sân: <?php echo number_format($totalBase, 0, ',', '.'); ?>đ</span>
                                            <strong class="text-[#d8ff3e] font-black text-base">Phần bạn (50%): <?php echo number_format($myPay, 0, ',', '.'); ?>đ</strong>
                                        </div>
                                    </div>

                                    <!-- Nút Mở Form Đổi Sân -->
                                    <div class="pt-2 flex items-center justify-between border-t border-white/10">
                                        <span class="text-[11px] text-amber-300/80 italic">Chat thương lượng trước, bạn có thể đề xuất đổi sân khác:</span>
                                        <button type="button" onclick="document.getElementById('switchPitchBox_<?php echo $t['id']; ?>').classList.toggle('hidden')"
                                                class="text-xs text-amber-300 hover:text-white font-extrabold bg-amber-500/20 hover:bg-amber-500/30 px-3 py-1.5 rounded-lg border border-amber-500/40 transition-colors flex items-center gap-1 shadow-sm cursor-pointer">
                                            <span>🔄</span> Đề Xuất Đổi Sân
                                        </button>
                                    </div>

                                    <!-- Box Đổi Sân Thi Đấu (Ẩn/Hiện) -->
                                    <div id="switchPitchBox_<?php echo $t['id']; ?>" class="hidden bg-[#0b1116] p-3.5 rounded-xl border border-amber-500/40 space-y-2 animate-fade-in mt-2">
                                        <h5 class="font-bold text-amber-400 text-xs flex items-center gap-1">
                                            <span>🏟️</span> Chọn Sân Thi Đấu Thay Thế (Gợi ý theo khoảng cách GPS của cả 2 bạn):
                                        </h5>
                                        <form action="index.php?action=switch_pitch" method="POST" onsubmit="return ajaxProposeSwitchPitch(event, this, <?php echo $t['id']; ?>);" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                            <input type="hidden" name="ticket_id" value="<?php echo $t['id']; ?>">
                                            <select name="pitch_id" id="pitch_select_<?php echo $t['id']; ?>" required class="flex-1 px-3 py-2 bg-[#121b21] border border-white/20 rounded-lg text-xs font-medium text-white focus:ring-2 focus:ring-amber-500">
                                                <?php 
                                                    $availPitches = $ticketModel->getAvailablePitchesForMatch($t['id']);
                                                    foreach($availPitches as $ap): 
                                                        $distText = '';
                                                        if ($ap['dist_user'] !== null) $distText .= ' • Bạn: ' . $ap['dist_user'] . 'km';
                                                        if ($ap['dist_opp'] !== null) $distText .= ' • Đối thủ: ' . $ap['dist_opp'] . 'km';
                                                ?>
                                                    <option value="<?php echo $ap['id']; ?>" data-name="<?php echo htmlspecialchars($ap['name']); ?>" data-price="<?php echo number_format($ap['price_per_hour'], 0, ',', '.'); ?>" <?php echo $ap['is_current'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($ap['name']); ?> (<?php echo htmlspecialchars($ap['district']); ?>)<?php echo $distText; ?> - <?php echo number_format($ap['price_per_hour'], 0, ',', '.'); ?>đ/h
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs px-4 py-2 rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                                                Gửi Đề Xuất Đổi Sân
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- ACTION BUTTONS: CHAT THƯƠNG LƯỢNG & XÁC NHẬN THANH TOÁN 50% -->
                                <div class="space-y-3 relative z-10">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <!-- NÚT 1: CHAT THƯƠNG LƯỢNG VỚI ĐỐI THỦ -->
                                        <button type="button" 
                                                onclick="openMatchQuickChat(<?php echo (int)$t['opp_user_id']; ?>, '<?php echo addslashes($t['opp_name']); ?>', '<?php echo addslashes($t['opp_avatar'] ?? ''); ?>', '<?php echo addslashes($t['opp_rank']); ?>')"
                                                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-black py-3.5 px-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 text-sm cursor-pointer">
                                            <span class="text-base">💬</span>
                                            <span>Chat Thỏa Thuận Với <?php echo htmlspecialchars($t['opp_name']); ?></span>
                                        </button>

                                        <!-- NÚT 2: XÁC NHẬN KÈO & THANH TOÁN 50% -->
                                        <?php if(!$myConfirmed): ?>
                                            <button type="button" 
                                                    onclick="openSplitPaymentModal(<?php echo (int)$t['id']; ?>, <?php echo (int)$myPay; ?>, '<?php echo addslashes($t['pitch_name']); ?>', '<?php echo addslashes($t['opp_name']); ?>')"
                                                    class="w-full bg-gradient-to-r from-[#d8ff3e] to-lime-400 hover:from-[#c8ef2e] hover:to-lime-300 text-slate-950 font-black py-3.5 px-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 text-sm cursor-pointer">
                                                <span>💳</span>
                                                <span>Xác Nhận & Trả 50% (<?php echo number_format($myPay, 0, ',', '.'); ?>đ)</span>
                                            </button>
                                        <?php else: ?>
                                            <div class="bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-bold py-3.5 px-4 rounded-xl text-center text-xs flex items-center justify-center gap-2">
                                                <span>✅</span>
                                                <span>Bạn đã trả 50% (<?php echo $myMethod === 'vietqr' ? 'VietQR' : 'Tiền mặt'; ?>) - Chờ đối thủ</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Nút từ chối kèo -->
                                    <div class="flex justify-between items-center pt-2">
                                        <span class="text-[11px] text-white/50">Mã lệnh: #TICK-<?php echo $t['id']; ?></span>
                                        <a href="index.php?action=cancel_ticket&id=<?php echo $t['id']; ?>" 
                                           onclick="return confirm('Bạn có chắc chắn muốn từ chối kèo này? Hệ thống sẽ hủy và tiếp tục tìm đối thủ khác.');"
                                           class="text-xs text-red-400 hover:text-red-300 font-bold transition-colors">
                                            ✕ Hủy & Tìm Đối Thủ Khác
                                        </a>
                                    </div>
                                </div>

                            </div>

                        <!-- CARD TRƯỜNG HỢP 3: ĐÃ CHỐT XONG (CONFIRMED) -->
                        <?php elseif($t['status'] == 'confirmed'): ?>
                            <div class="bg-gradient-to-br from-[#0b1116] via-[#121b21] to-[#0b1116] rounded-3xl p-6 shadow-2xl border-2 border-emerald-500 relative overflow-hidden">
                                
                                <div class="flex items-center justify-between pb-3 border-b border-white/10 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl animate-bounce">🎉</span>
                                        <div>
                                            <span class="text-emerald-400 font-black text-lg block">KÈO ĐÃ ĐƯỢC CHỐT THÀNH CÔNG!</span>
                                            <span class="text-xs text-white/60">Cả 2 cơ thủ đã hoàn tất thanh toán chia đôi (50/50)</span>
                                        </div>
                                    </div>
                                    <span class="bg-emerald-500 text-slate-950 text-xs font-black px-3 py-1.5 rounded-full uppercase tracking-wider shadow">
                                        ✓ Đã Đặt Sân
                                    </span>
                                </div>

                                <!-- THẺ MÃ CHECK-IN RIÊNG CỦA BẠN (CÁ NHÂN) -->
                                <?php 
                                    $bookingCheckin = 'CKIN-CONFIRMED';
                                    if (!empty($t['booking_id'])) {
                                        $bStmt = (new Database())->getConnection()->prepare("SELECT checkin_code FROM bookings WHERE id = ?");
                                        $bStmt->execute([$t['booking_id']]);
                                        $bRow = $bStmt->fetch(PDO::FETCH_ASSOC);
                                        if ($bRow && !empty($bRow['checkin_code'])) {
                                            $bookingCheckin = $bRow['checkin_code'];
                                        }
                                    }
                                    $myCheckinCode = !empty($t['checkin_code']) ? $t['checkin_code'] : $bookingCheckin;
                                    $myCheckedIn = !empty($t['is_checked_in']);
                                    $oppCheckedIn = !empty($t['opp_is_checked_in']);
                                    $noshowStatus = $t['noshow_status'] ?? 'none';
                                ?>

                                <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-green-600 text-white p-5 rounded-2xl mb-5 shadow-xl">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-[11px] uppercase tracking-widest font-black text-emerald-100">MÃ CHECK-IN RIÊNG CỦA BẠN (TẠI QUẦY)</span>
                                                <?php if($myCheckedIn): ?>
                                                    <span class="bg-[#d8ff3e] text-slate-950 font-black text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider shadow">
                                                        ✓ ĐÃ CHECK-IN
                                                    </span>
                                                <?php else: ?>
                                                    <span class="bg-amber-400 text-slate-950 font-black text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider shadow animate-pulse">
                                                        ⏳ CHƯA CHECK-IN
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-3xl font-black tracking-widest my-1 font-mono text-white">
                                                <?php echo htmlspecialchars($myCheckinCode); ?>
                                            </div>
                                            <p class="text-[11px] text-emerald-100">
                                                Mỗi người có 1 mã riêng. Xuất trình mã này cho lễ tân để điểm danh vào sân và kiểm tra phần tiền 50% của bạn.
                                            </p>
                                        </div>
                                        <button type="button" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($myCheckinCode); ?>'); alert('Đã sao chép mã check-in: <?php echo htmlspecialchars($myCheckinCode); ?>');" 
                                                class="bg-white/20 hover:bg-white/30 text-white font-black text-xs px-4 py-3 rounded-xl transition border border-white/30 shrink-0 cursor-pointer flex items-center justify-center gap-1.5">
                                            <span>📋</span> Sao Chép Mã
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-4">
                                    <div class="bg-[#121b21] p-3.5 rounded-xl border border-white/5 space-y-1">
                                        <span class="text-white/40 block">Sân thi đấu:</span>
                                        <strong class="text-white font-bold text-sm block"><?php echo htmlspecialchars($t['pitch_name']); ?></strong>
                                        <span class="text-white/60 block"><?php echo htmlspecialchars($t['pitch_location'] ?? ''); ?></span>
                                    </div>
                                    <div class="bg-[#121b21] p-3.5 rounded-xl border border-white/5 space-y-1">
                                        <span class="text-white/40 block">Thời gian:</span>
                                        <strong class="text-white font-bold text-sm block"><?php echo date('d/m/Y', strtotime($t['play_date'])); ?></strong>
                                        <span class="text-emerald-400 font-black text-sm"><?php echo substr($t['start_time'], 0, 5); ?> - <?php echo substr($t['end_time'], 0, 5); ?></span>
                                    </div>
                                </div>

                                <!-- THÔNG TIN THANH TOÁN & CHECK-IN TỪNG NGƯỜI (TÁCH BIỆT 100%) -->
                                <div class="bg-[#121b21] p-4 rounded-xl border border-white/10 mb-4 text-xs space-y-3">
                                    <div class="flex items-center justify-between border-b border-white/10 pb-2">
                                        <span class="text-xs font-bold text-white/60 uppercase tracking-wider">Trạng Thái Check-in & Thanh Toán Riêng Biệt (50/50):</span>
                                        <span class="text-[10px] text-cyan-400 font-semibold">Độc Lập Từng Bên</span>
                                    </div>

                                    <!-- Bạn -->
                                    <div class="flex justify-between items-center py-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm">🏸</span>
                                            <div>
                                                <span class="text-white font-bold block">Bạn (50% - <?php echo number_format($t['split_price'] ?? 0, 0, ',', '.'); ?>đ):</span>
                                                <span class="text-[10px] <?php echo $myCheckedIn ? 'text-emerald-400' : 'text-amber-400'; ?>">
                                                    <?php echo $myCheckedIn ? '✓ Đã điểm danh tại quầy' : '⏳ Chưa điểm danh tại quầy'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <span class="font-bold text-xs px-2.5 py-1 rounded-md <?php echo ($t['payment_method'] ?? '') === 'vietqr' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'; ?>">
                                            <?php echo ($t['payment_method'] ?? '') === 'vietqr' ? '📱 VietQR (Đã Thanh Toán)' : '💵 Tiền Mặt (Trả Tại Quầy)'; ?>
                                        </span>
                                    </div>

                                    <!-- Đối thủ -->
                                    <div class="flex justify-between items-center py-1 border-t border-white/5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm">👤</span>
                                            <div>
                                                <span class="text-white/80 font-bold block">Đối thủ [<?php echo htmlspecialchars($t['opp_name']); ?>] (50% - <?php echo number_format($t['opp_split_price'] ?? 0, 0, ',', '.'); ?>đ):</span>
                                                <span class="text-[10px] <?php echo $oppCheckedIn ? 'text-emerald-400' : 'text-amber-400'; ?>">
                                                    <?php echo $oppCheckedIn ? '✓ Đã điểm danh tại quầy' : '⏳ Chưa điểm danh tại quầy'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <span class="font-bold text-xs px-2.5 py-1 rounded-md <?php echo ($t['opp_payment_method'] ?? '') === 'vietqr' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'; ?>">
                                            <?php echo ($t['opp_payment_method'] ?? '') === 'vietqr' ? '📱 VietQR (Đã Thanh Toán)' : '💵 Tiền Mặt (Trả Tại Quầy)'; ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- THÔNG BÁO VỀ TRẠNG THÁI BÙNG KÈO (NẾU CÓ) -->
                                <?php if($noshowStatus === 'pending'): ?>
                                    <div class="bg-amber-500/20 border border-amber-500/40 text-amber-300 p-4 rounded-xl mb-4 text-xs flex items-start gap-3">
                                        <span class="text-xl">⏳</span>
                                        <div>
                                            <strong class="font-black text-amber-200 text-sm block mb-1">ĐÃ GỬI YÊU CẦU BÁO VẮNG ĐỐI THỦ</strong>
                                            <p class="leading-relaxed text-amber-300/90">
                                                Chủ Sân / Lễ tân đã nhận được báo cáo vắng mặt. Chủ sân sẽ kiểm tra thực tế tại sân thi đấu và bấm <strong>[Xác Nhận Bùng Kèo]</strong>. Bạn được bảo vệ quyền lợi và không bị ảnh hưởng.
                                            </p>
                                        </div>
                                    </div>
                                <?php elseif($noshowStatus === 'confirmed_noshow'): ?>
                                    <div class="bg-red-500/20 border border-red-500/40 text-red-300 p-4 rounded-xl mb-4 text-xs flex items-start gap-3">
                                        <span class="text-xl">🚫</span>
                                        <div>
                                            <strong class="font-black text-red-200 text-sm block mb-1">CHỦ SÂN ĐÃ XÁC NHẬN ĐỐI THỦ BÙNG KÈO</strong>
                                            <p class="leading-relaxed text-red-300/90">
                                                Đối thủ đã bị xử phạt nghiêm minh (-30 Điểm Uy Tín, -50 Elo và tạm khóa ghép trận). Quyền lợi của bạn được bảo vệ 100%. Vui lòng liên hệ quầy để nhận sân hoặc hoàn cọc theo quy định.
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="flex items-center justify-between bg-emerald-500/10 border border-emerald-500/20 p-3 rounded-xl mb-4 text-xs">
                                    <div>
                                        <span class="text-emerald-300 font-medium">Đối thủ: <strong class="font-bold text-white"><?php echo htmlspecialchars($t['opp_name']); ?></strong> (<?php echo $t['opp_rank']; ?> • <?php echo $t['opp_elo']; ?> Elo)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                onclick="openMatchQuickChat(<?php echo (int)$t['opp_user_id']; ?>, '<?php echo addslashes($t['opp_name']); ?>', '<?php echo addslashes($t['opp_avatar'] ?? ''); ?>', '<?php echo addslashes($t['opp_rank']); ?>')"
                                                class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-3 py-1.5 rounded-lg flex items-center gap-1 shadow transition cursor-pointer">
                                            <span>💬</span> Chat Với Đối Thủ
                                        </button>
                                    </div>
                                </div>

                                <!-- THANH HÀNH ĐỘNG DƯỚI CÙNG: BÁO VẮNG CHO CHỦ SÂN XÁC NHẬN -->
                                <div class="pt-3 border-t border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                    <a href="index.php?action=my_bookings" class="text-white/60 hover:text-white underline font-medium">
                                        Xem chi tiết trong lịch sử đặt sân →
                                    </a>
                                    
                                    <?php if($noshowStatus === 'none' || $noshowStatus === 'rejected'): ?>
                                        <button type="button" 
                                                onclick="openReportAbsentModal(<?php echo $t['id']; ?>, '<?php echo addslashes($t['opp_name']); ?>')" 
                                                class="text-amber-400 hover:text-amber-300 font-bold hover:bg-amber-500/10 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 self-start sm:self-center border border-amber-500/30 cursor-pointer">
                                            <span>⚠️</span> Báo Vắng Đối Thủ (Chờ Chủ Sân Xác Nhận)
                                        </button>
                                    <?php endif; ?>
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

<!-- MODAL: QUICK CHAT THƯƠNG LƯỢNG KÈO ĐẤU -->
<div id="quickChatModal" class="hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-md flex items-center justify-center p-4">
    <div class="bg-[#0b1116] border border-white/10 rounded-3xl max-w-lg w-full shadow-2xl flex flex-col overflow-hidden animate-scale-up h-[600px] max-h-[90vh]">
        
        <!-- Header -->
        <div class="p-4 bg-[#121b21] border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <img id="chatOppAvatar" src="" class="w-11 h-11 rounded-xl object-cover border-2 border-amber-400 shrink-0">
                    <span class="w-3 h-3 bg-emerald-500 rounded-full border-2 border-[#121b21] absolute -bottom-0.5 -right-0.5 animate-pulse"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 id="chatOppName" class="font-extrabold text-white text-base">Đối thủ</h4>
                        <span id="chatOppRank" class="text-[10px] bg-amber-500/20 text-amber-300 border border-amber-500/40 px-2 py-0.5 rounded-full font-bold">Rank</span>
                    </div>
                    <span class="text-[11px] text-emerald-400 font-medium flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Đang trực tuyến • Chat thỏa thuận kèo
                    </span>
                </div>
            </div>
            <button type="button" onclick="closeMatchQuickChat()" class="text-white/40 hover:text-white text-2xl font-bold w-9 h-9 rounded-xl hover:bg-white/10 flex items-center justify-center transition cursor-pointer">&times;</button>
        </div>

        <!-- Quick Reply Chips -->
        <div class="px-4 py-2.5 bg-[#0b1116] border-b border-white/5 flex items-center gap-2 overflow-x-auto text-xs whitespace-nowrap">
            <span class="text-white/40 text-[11px] font-bold">Gợi ý:</span>
            <button type="button" onclick="insertQuickMessage('Sân này và giờ này ok không bạn ơi?')" class="bg-[#121b21] hover:bg-white/10 text-white/80 hover:text-white px-2.5 py-1 rounded-lg border border-white/10 text-[11px] font-medium transition cursor-pointer">
                🏸 Sân này ok không bạn?
            </button>
            <button type="button" onclick="insertQuickMessage('Mình đồng ý giờ này nhé, bạn bấm xác nhận kèo nhé!')" class="bg-[#121b21] hover:bg-white/10 text-white/80 hover:text-white px-2.5 py-1 rounded-lg border border-white/10 text-[11px] font-medium transition cursor-pointer">
                ✅ Mình chốt thanh toán 50% rồi!
            </button>
            <button type="button" onclick="insertQuickMessage('Bạn có muốn đổi sang sân khác gần hơn không?')" class="bg-[#121b21] hover:bg-white/10 text-white/80 hover:text-white px-2.5 py-1 rounded-lg border border-white/10 text-[11px] font-medium transition cursor-pointer">
                🔄 Đổi sân khác không?
            </button>
        </div>

        <!-- Message List Container -->
        <div id="quickChatMessagesList" class="flex-1 p-4 overflow-y-auto space-y-3 bg-[#080d11]">
            <div class="text-center py-6 text-white/40 text-xs italic" id="quickChatLoading">
                Đang tải tin nhắn...
            </div>
        </div>

        <!-- Input Box -->
        <div class="p-3 bg-[#121b21] border-t border-white/10">
            <form onsubmit="sendQuickChatMessage(event)" class="flex items-center gap-2">
                <input type="text" id="quickChatInput" placeholder="Nhập tin nhắn thỏa thuận..." required autocomplete="off"
                       class="flex-1 bg-[#0b1116] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white placeholder-white/40 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <button type="submit" id="btnSendQuickChat" class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black px-4 py-2.5 rounded-xl text-xs flex items-center gap-1.5 transition cursor-pointer shadow">
                    <span>Gửi</span>
                    <span>➤</span>
                </button>
            </form>
            <div class="flex items-center justify-between mt-2 pt-1.5 border-t border-white/5 text-[11px]">
                <span class="text-white/40">Tin nhắn được gửi trực tiếp</span>
                <a id="linkFullChat" href="index.php?action=chat" class="text-cyan-400 hover:text-cyan-300 font-bold hover:underline">
                    Mở toàn màn hình tin nhắn →
                </a>
            </div>
        </div>

    </div>
</div>

<!-- MODAL: THANH TOÁN CHIA ĐÔI 50/50 (SPLIT PAYMENT) -->
<div id="splitPaymentModal" class="hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-md flex items-center justify-center p-4">
    <div class="bg-[#0b1116] border border-white/15 rounded-3xl max-w-md w-full shadow-2xl overflow-hidden animate-scale-up">
        
        <!-- Header -->
        <div class="p-5 bg-gradient-to-r from-[#121b21] to-[#0b1116] border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#d8ff3e] to-emerald-400 text-slate-950 font-black flex items-center justify-center text-lg shadow">
                    💳
                </div>
                <div>
                    <h4 class="font-extrabold text-white text-base">Thanh Toán Chia Đôi (Split 50/50)</h4>
                    <p class="text-xs text-white/60">Mỗi bên thanh toán 50% để chính thức khóa sân</p>
                </div>
            </div>
            <button type="button" onclick="closeSplitPaymentModal()" class="text-white/40 hover:text-white text-2xl font-bold w-9 h-9 rounded-xl hover:bg-white/10 flex items-center justify-center transition cursor-pointer">&times;</button>
        </div>

        <div class="p-6 space-y-5">
            <!-- Hóa đơn tóm tắt -->
            <div class="bg-[#121b21] p-4 rounded-2xl border border-white/10 space-y-2 text-xs">
                <div class="flex justify-between items-center text-white/70">
                    <span>Sân thi đấu:</span>
                    <strong id="modalPitchName" class="text-white font-bold text-right truncate max-w-[200px]">Sân Cầu Lông</strong>
                </div>
                <div class="flex justify-between items-center text-white/70">
                    <span>Đối thủ cùng chia tiền:</span>
                    <strong id="modalOppName" class="text-white font-bold">Đối thủ</strong>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-white/10">
                    <span class="font-bold text-white text-sm">Phần bạn thanh toán (50%):</span>
                    <strong id="modalMyPayText" class="text-2xl font-black text-[#d8ff3e]">0đ</strong>
                </div>
            </div>

            <!-- Tab Buttons -->
            <div class="grid grid-cols-2 gap-2 bg-[#121b21] p-1.5 rounded-2xl border border-white/10">
                <button type="button" id="tabBtnVietQr" onclick="switchPaymentTab('vietqr')" 
                        class="py-2.5 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-1.5 bg-[#d8ff3e] text-slate-950 shadow cursor-pointer">
                    <span>📱</span> VietQR Online
                </button>
                <button type="button" id="tabBtnCash" onclick="switchPaymentTab('cash')" 
                        class="py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 text-white/60 hover:text-white cursor-pointer">
                    <span>💵</span> Tiền Mặt Tại Quầy
                </button>
            </div>

            <!-- TAB 1: VIETQR -->
            <div id="tabContentVietQr" class="space-y-4">
                <div class="p-4 border-2 border-dashed border-cyan-500/40 rounded-2xl bg-cyan-950/20 text-center">
                    <span class="text-[10px] font-black uppercase text-cyan-400 tracking-wider block mb-2">QUÉT MÃ VIETQR CHUYỂN KHOẢN (50% TIỀN SÂN)</span>
                    <div class="inline-block p-3 bg-white rounded-2xl shadow-xl my-1 border border-white/10">
                        <img id="modalQrImg" src="" alt="VietQR" class="w-36 h-36 object-contain mx-auto">
                    </div>
                    <div class="text-xs text-white/80 mt-2 space-y-1">
                        <div>Số tiền: <strong id="modalQrAmount" class="text-[#d8ff3e] font-black text-sm">0đ</strong></div>
                        <div class="text-[11px] text-white/60">Nội dung: <span id="modalQrContent" class="font-mono text-cyan-300 font-bold">SMART_TICK</span></div>
                    </div>
                </div>

                <button type="button" id="btnSimulateSplit" onclick="simulateSplitPayment()" 
                        class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black py-3 px-4 rounded-xl text-xs uppercase tracking-wider shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                    <span>⚡</span> Mô Phỏng Quét VietQR Thành Công (Demo Sandbox)
                </button>
                <p class="text-[10px] text-white/40 text-center italic">* Dành cho buổi bảo vệ đồ án: Bấm để giả lập giao dịch chuyển khoản 50% thành công ngay</p>
            </div>

            <!-- TAB 2: CASH -->
            <div id="tabContentCash" class="hidden space-y-4">
                <div class="p-4 border border-emerald-500/30 rounded-2xl bg-emerald-500/10 text-center space-y-2">
                    <span class="text-2xl block">💵</span>
                    <h5 class="font-black text-emerald-400 text-sm">Cam Kết Thanh Toán Tiền Mặt Khi Đến Sân</h5>
                    <p class="text-xs text-white/70 leading-relaxed">
                        Bạn cam kết sẽ thanh toán đúng <strong>50% số tiền</strong> cho nhân viên lễ tân khi đến check-in tại sân.
                    </p>
                    <div class="text-[11px] text-amber-300 bg-amber-500/10 border border-amber-500/20 p-2 rounded-xl mt-2">
                        ⚠️ <strong>Lưu ý:</strong> Hành vi bùng kèo (No-Show) không đến sân sẽ bị phạt trừ 30 điểm Uy Tín Fair-Play, trừ 50 Elo và khóa tài khoản 3 ngày.
                    </div>
                </div>

                <button type="button" id="btnConfirmCash" onclick="confirmCashSplitPayment()" 
                        class="w-full bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500 text-white font-black py-3.5 px-4 rounded-xl text-xs uppercase tracking-wider shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                    <span>✓</span> Xác Nhận Trả 50% Tiền Mặt Khi Đến Sân
                </button>
            </div>

        </div>

    </div>
</div>

<!-- MODAL: BÁO CÁO ĐỐI THỦ VẮNG MẶT (CHỦ SÂN XÁC NHẬN) -->
<div id="reportAbsentModal" class="hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-md flex items-center justify-center p-4">
    <div class="bg-[#0b1116] border border-amber-500/40 rounded-3xl max-w-md w-full shadow-2xl overflow-hidden animate-scale-up">
        <div class="p-5 bg-gradient-to-r from-amber-950/60 via-[#121b21] to-[#0b1116] border-b border-amber-500/30 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl animate-pulse">⚠️</span>
                <div>
                    <h3 class="font-black text-amber-300 text-base uppercase">Báo Đối Thủ Vắng Mặt</h3>
                    <p class="text-[11px] text-white/60">Gửi yêu cầu tới Chủ Sân / Lễ tân để kiểm tra & xác nhận bùng kèo</p>
                </div>
            </div>
            <button type="button" onclick="closeReportAbsentModal()" class="text-white/40 hover:text-white text-2xl font-bold w-8 h-8 rounded-xl hover:bg-white/10 flex items-center justify-center transition cursor-pointer">&times;</button>
        </div>

        <form action="index.php?action=report_absent" method="POST" class="p-5 space-y-4">
            <input type="hidden" name="ticket_id" id="reportAbsentTicketId" value="">
            
            <div class="bg-amber-500/10 border border-amber-500/30 p-3 rounded-2xl text-xs text-amber-200">
                <span>Đối thủ bị báo cáo:</span> <strong id="reportAbsentOppName" class="text-white font-bold block text-sm">Tên đối thủ</strong>
            </div>

            <div>
                <label class="block text-xs font-bold text-white/80 mb-1.5 uppercase tracking-wider">Lý Do / Chi Tiết Thực Tế Tại Sân:</label>
                <textarea name="reason" rows="3" required placeholder="Ví dụ: Đã quá 15 phút so với giờ thi đấu nhưng đối thủ không đến, gọi điện không bắt máy..."
                          class="w-full bg-[#121b21] border border-white/10 rounded-xl p-3 text-xs text-white placeholder-white/30 focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
            </div>

            <div class="text-[11px] text-white/60 bg-[#121b21] p-3 rounded-xl border border-white/5 space-y-1">
                <span class="text-amber-400 font-bold block">🛡️ Cơ chế trọng tài công bằng:</span>
                <p>Chủ Sân / Lễ tân đang có mặt trực tiếp tại sân sẽ xác minh thực tế. Khi Chủ Sân bấm <strong>[Xác Nhận Bùng Kèo]</strong>, đối thủ sẽ bị xử phạt (-30 điểm uy tín, -50 Elo) và bạn được bảo đảm quyền lợi.</p>
            </div>

            <div class="flex gap-2.5 pt-2">
                <button type="button" onclick="closeReportAbsentModal()" class="flex-1 py-3 bg-[#121b21] hover:bg-white/10 text-white/70 font-bold text-xs rounded-xl transition cursor-pointer">
                    Hủy Bỏ
                </button>
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-black text-xs uppercase rounded-xl transition shadow cursor-pointer">
                    Gửi Tới Chủ Sân
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPT AUTO-POLL KIỂM TRA TÌNH TRẠNG KÈO (REALTIME CHECK) -->
<script>
function openReportAbsentModal(ticketId, oppName) {
    document.getElementById('reportAbsentTicketId').value = ticketId;
    document.getElementById('reportAbsentOppName').textContent = oppName;
    document.getElementById('reportAbsentModal').classList.remove('hidden');
}

function closeReportAbsentModal() {
    document.getElementById('reportAbsentModal').classList.add('hidden');
}
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

// --- QUICK CHAT SYSTEM FOR SMART MATCH ---
let activeChatOpponentId = null;
let quickChatTimer = null;

function openMatchQuickChat(oppUserId, oppName, oppAvatar, oppRank) {
    activeChatOpponentId = oppUserId;
    document.getElementById('chatOppName').textContent = oppName;
    document.getElementById('chatOppRank').textContent = oppRank || 'VĐV';
    document.getElementById('chatOppAvatar').src = oppAvatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150';
    document.getElementById('linkFullChat').href = `index.php?action=chat&user=${oppUserId}`;
    
    document.getElementById('quickChatModal').classList.remove('hidden');
    loadQuickChatMessages(oppUserId, true);
    
    if (quickChatTimer) clearInterval(quickChatTimer);
    quickChatTimer = setInterval(() => {
        if (activeChatOpponentId && !document.getElementById('quickChatModal').classList.contains('hidden')) {
            loadQuickChatMessages(activeChatOpponentId, false);
        }
    }, 2500);

    setTimeout(() => {
        const inp = document.getElementById('quickChatInput');
        if (inp) inp.focus();
    }, 150);
}

function closeMatchQuickChat() {
    document.getElementById('quickChatModal').classList.add('hidden');
    activeChatOpponentId = null;
    if (quickChatTimer) {
        clearInterval(quickChatTimer);
        quickChatTimer = null;
    }
}

function insertQuickMessage(text) {
    const input = document.getElementById('quickChatInput');
    input.value = text;
    input.focus();
}

function loadQuickChatMessages(userId, scroll = true) {
    const container = document.getElementById('quickChatMessagesList');
    if (!container) return;

    fetch(`index.php?action=api_get_messages&user=${userId}`)
        .then(r => r.json())
        .then(messages => {
            if (!Array.isArray(messages) || messages.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-10 space-y-2">
                        <span class="text-3xl">🏸</span>
                        <p class="text-white/60 text-xs font-medium">Chưa có tin nhắn nào giữa 2 bạn.</p>
                        <p class="text-[11px] text-white/40">Hãy gửi tin nhắn đầu tiên để chào và thỏa thuận kèo nhé!</p>
                    </div>`;
                return;
            }

            let html = '';
            const myUserId = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
            messages.forEach(m => {
                const isMe = (parseInt(m.sender_id) === myUserId);
                const timeStr = m.created_at ? m.created_at.substring(11, 16) : '';
                if (isMe) {
                    html += `
                        <div class="flex flex-col items-end">
                            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-xs px-3.5 py-2.5 rounded-2xl rounded-tr-none max-w-[80%] shadow-md">
                                ${escapeHtml(m.content)}
                            </div>
                            <span class="text-[10px] text-white/30 mt-1 mr-1">${timeStr}</span>
                        </div>`;
                } else {
                    html += `
                        <div class="flex flex-col items-start">
                            <div class="bg-[#121b21] border border-white/10 text-white text-xs px-3.5 py-2.5 rounded-2xl rounded-tl-none max-w-[80%] shadow-md">
                                ${escapeHtml(m.content)}
                            </div>
                            <span class="text-[10px] text-white/30 mt-1 ml-1">${timeStr}</span>
                        </div>`;
                }
            });
            container.innerHTML = html;
            if (scroll) {
                container.scrollTop = container.scrollHeight;
            }
        })
        .catch(err => console.log('Chat load error:', err));
}

function sendQuickChatMessage(e) {
    if (e) e.preventDefault();
    const input = document.getElementById('quickChatInput');
    const content = input.value.trim();
    if (!content || !activeChatOpponentId) return;

    const btn = document.getElementById('btnSendQuickChat');
    btn.disabled = true;

    const formData = new URLSearchParams();
    formData.append('receiver_id', activeChatOpponentId);
    formData.append('content', content);

    fetch('index.php?action=api_send_message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            input.value = '';
            loadQuickChatMessages(activeChatOpponentId, true);
        } else {
            alert('Lỗi gửi tin nhắn: ' + (data.error || 'Vui lòng thử lại'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        alert('Lỗi kết nối máy chủ.');
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// --- SPLIT PAYMENT 50/50 MODAL SYSTEM ---
let currentPayTicketId = null;
let currentPayAmount = 0;
let currentPayMethod = 'vietqr';

function openSplitPaymentModal(ticketId, myPay, pitchName, oppName) {
    currentPayTicketId = ticketId;
    currentPayAmount = myPay;
    
    document.getElementById('modalPitchName').textContent = pitchName;
    document.getElementById('modalOppName').textContent = oppName;
    document.getElementById('modalMyPayText').textContent = Number(myPay).toLocaleString('vi-VN') + 'đ';
    document.getElementById('modalQrAmount').textContent = Number(myPay).toLocaleString('vi-VN') + 'đ';
    
    const qrContentStr = `SMART_TICK_${ticketId}`;
    document.getElementById('modalQrContent').textContent = qrContentStr;
    document.getElementById('modalQrImg').src = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=BADMINTON_SPLIT_${ticketId}_${myPay}`;
    
    switchPaymentTab('vietqr');
    document.getElementById('splitPaymentModal').classList.remove('hidden');
}

function closeSplitPaymentModal() {
    document.getElementById('splitPaymentModal').classList.add('hidden');
    currentPayTicketId = null;
}

function switchPaymentTab(tab) {
    currentPayMethod = tab;
    const btnV = document.getElementById('tabBtnVietQr');
    const btnC = document.getElementById('tabBtnCash');
    const contentV = document.getElementById('tabContentVietQr');
    const contentC = document.getElementById('tabContentCash');
    
    if (tab === 'vietqr') {
        btnV.className = "py-2.5 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-1.5 bg-[#d8ff3e] text-slate-950 shadow cursor-pointer";
        btnC.className = "py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 text-white/60 hover:text-white cursor-pointer";
        contentV.classList.remove('hidden');
        contentC.classList.add('hidden');
    } else {
        btnC.className = "py-2.5 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-1.5 bg-[#d8ff3e] text-slate-950 shadow cursor-pointer";
        btnV.className = "py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 text-white/60 hover:text-white cursor-pointer";
        contentC.classList.remove('hidden');
        contentV.classList.add('hidden');
    }
}

function simulateSplitPayment() {
    if (!currentPayTicketId) return;
    const btn = document.getElementById('btnSimulateSplit');
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> Đang xác thực giao dịch chuyển khoản 50%...';

    const params = new URLSearchParams();
    params.append('ticket_id', currentPayTicketId);
    params.append('payment_method', 'vietqr');
    params.append('is_ajax', '1');

    fetch('index.php?action=api_confirm_split_payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            btn.className = 'w-full bg-emerald-500/20 text-emerald-300 font-bold py-3 px-4 rounded-xl text-xs uppercase cursor-default';
            btn.innerHTML = '✅ ĐÃ THANH TOÁN 50% THÀNH CÔNG!';
            setTimeout(() => {
                closeSplitPaymentModal();
                window.location.href = 'index.php?action=smart_match&msg=' + (res.status === 'fully_confirmed' ? 'fully_confirmed' : 'waiting_opponent_payment');
            }, 800);
        } else {
            btn.disabled = false;
            btn.innerHTML = '<span>⚡</span> Mô Phỏng Quét VietQR Thành Công (Demo Sandbox)';
            alert('Lỗi: ' + (res.message || 'Không thể ghi nhận thanh toán.'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<span>⚡</span> Mô Phỏng Quét VietQR Thành Công (Demo Sandbox)';
        alert('Lỗi kết nối máy chủ.');
    });
}

function confirmCashSplitPayment() {
    if (!currentPayTicketId) return;
    const btn = document.getElementById('btnConfirmCash');
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> Đang ghi nhận cam kết trả tiền mặt...';

    const params = new URLSearchParams();
    params.append('ticket_id', currentPayTicketId);
    params.append('payment_method', 'cash');
    params.append('is_ajax', '1');

    fetch('index.php?action=api_confirm_split_payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            closeSplitPaymentModal();
            window.location.href = 'index.php?action=smart_match&msg=' + (res.status === 'fully_confirmed' ? 'fully_confirmed' : 'waiting_opponent_payment');
        } else {
            btn.disabled = false;
            btn.innerHTML = '<span>✓</span> Xác Nhận Trả 50% Tiền Mặt Khi Đến Sân';
            alert('Lỗi: ' + (res.message || 'Không thể xác nhận.'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<span>✓</span> Xác Nhận Trả 50% Tiền Mặt Khi Đến Sân';
        alert('Lỗi kết nối máy chủ.');
    });
}

// === AJAX Zero-Reload Switch Pitch Handlers ===
async function ajaxProposeSwitchPitch(event, form, ticketId) {
    if (event) event.preventDefault();
    const select = document.getElementById('pitch_select_' + ticketId);
    if (!select || !select.value) {
        if (typeof window.showToast === 'function') window.showToast('Vui lòng chọn sân thay thế!', 'error');
        return false;
    }
    const selectedOption = select.options[select.selectedIndex];
    const pitchName = selectedOption.getAttribute('data-name') || selectedOption.text;
    const pitchPrice = selectedOption.getAttribute('data-price') || '';

    const btn = form.querySelector('button[type="submit"]');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> Đang gửi đề xuất...';

    const formData = new URLSearchParams();
    formData.append('ticket_id', ticketId);
    formData.append('pitch_id', select.value);
    formData.append('is_ajax', '1');

    try {
        const response = await fetch('index.php?action=switch_pitch&is_ajax=1', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        });
        const data = await response.json();
        if (data.success) {
            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Đã gửi đề xuất đổi sân!', 'success');
            }
            const bannerContainer = document.getElementById('pitch_proposal_banner_container_' + ticketId);
            if (bannerContainer) {
                bannerContainer.innerHTML = `
                    <div class="p-3.5 bg-amber-950/70 border border-amber-500/50 rounded-2xl text-xs text-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md animate-pulse">
                        <div class="flex items-center gap-2">
                            <span class="text-xl shrink-0">⏳</span>
                            <div>
                                <strong>Bạn đã gửi đề xuất đổi sang sân: <span class="text-white underline">${pitchName}</span> ${pitchPrice ? '(' + pitchPrice + 'đ/h)' : ''}.</strong>
                                <span class="block text-[11px] text-amber-300/70">Đang chờ đối thủ đồng ý...</span>
                            </div>
                        </div>
                        <button type="button" 
                           onclick="return ajaxRejectSwitchPitch(event, this, ${ticketId});"
                           class="bg-white/10 hover:bg-white/20 text-white font-bold px-3 py-1.5 rounded-lg text-xs whitespace-nowrap border border-white/20 transition-all self-end sm:self-center cursor-pointer">
                            ✕ Hủy Đề Xuất
                        </button>
                    </div>
                `;
            }
            const switchBox = document.getElementById('switchPitchBox_' + ticketId);
            if (switchBox) switchBox.classList.add('hidden');
        } else {
            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Không thể đề xuất đổi sân', 'error');
            } else {
                alert(data.message || 'Không thể đề xuất đổi sân');
            }
        }
    } catch (err) {
        console.error(err);
        if (typeof window.showToast === 'function') window.showToast('Lỗi kết nối máy chủ', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
    return false;
}

async function ajaxAcceptSwitchPitch(event, btn, ticketId) {
    if (event) event.preventDefault();
    if (!confirm('Xác nhận đồng ý đổi sang sân theo đề xuất của đối thủ?')) return false;

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> Đang xử lý...';

    try {
        const response = await fetch(`index.php?action=accept_switch_pitch&ticket_id=${ticketId}&is_ajax=1`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (data.success) {
            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Đã đồng ý chuyển sang sân mới!', 'success');
            }
            const bannerContainer = document.getElementById('pitch_proposal_banner_container_' + ticketId);
            if (bannerContainer) {
                bannerContainer.innerHTML = `
                    <div class="p-3 bg-emerald-950/90 border border-emerald-500 rounded-xl text-xs text-emerald-300 font-bold flex items-center gap-2">
                        <span>🎉</span>
                        <span>Đã chốt chuyển sang sân: <strong class="text-white underline">${data.pitch_name || ''}</strong>. Hai bên tiến hành thanh toán 50%!</span>
                    </div>
                `;
            }
            const currentPitchEl = document.getElementById('current_pitch_name_' + ticketId);
            if (currentPitchEl && data.pitch_name) {
                currentPitchEl.textContent = data.pitch_name;
            }
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } else {
            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Không thể chấp nhận đề xuất', 'error');
            } else {
                alert(data.message || 'Không thể chấp nhận đề xuất');
            }
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    } catch (err) {
        console.error(err);
        if (typeof window.showToast === 'function') window.showToast('Lỗi kết nối máy chủ', 'error');
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
    return false;
}

async function ajaxRejectSwitchPitch(event, btn, ticketId) {
    if (event) event.preventDefault();
    if (!confirm('Xác nhận từ chối / hủy đề xuất đổi sân và giữ nguyên sân ban đầu?')) return false;

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span>';

    try {
        const response = await fetch(`index.php?action=reject_switch_pitch&ticket_id=${ticketId}&is_ajax=1`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (data.success) {
            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Đã hủy đề xuất đổi sân', 'info');
            }
            const bannerContainer = document.getElementById('pitch_proposal_banner_container_' + ticketId);
            if (bannerContainer) {
                bannerContainer.innerHTML = '';
            }
        } else {
            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Không thể từ chối đề xuất', 'error');
            } else {
                alert(data.message || 'Không thể từ chối đề xuất');
            }
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    } catch (err) {
        console.error(err);
        if (typeof window.showToast === 'function') window.showToast('Lỗi kết nối máy chủ', 'error');
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Polling tự động kiểm tra cả ticket đang 'waiting' và 'matched' (chờ đối thủ trả tiền / đổi sân)
    const activeTicketCards = document.querySelectorAll('[id^="ticket-card-"], [id^="matched-card-"]');
    if (activeTicketCards.length > 0) {
        setInterval(() => {
            activeTicketCards.forEach(card => {
                let ticketId = card.getAttribute('data-matched-id');
                if (!ticketId) {
                    ticketId = card.id.replace('ticket-card-', '').replace('matched-card-', '');
                }
                if (!ticketId) return;

                fetch(`index.php?action=api_check_ticket&id=${ticketId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.status) {
                            if (card.id.startsWith('ticket-card-') && data.status !== 'waiting') {
                                window.location.reload();
                            } else if (card.id.startsWith('matched-card-')) {
                                if (data.status === 'confirmed') {
                                    window.location.reload();
                                }
                            }
                        }
                    })
                    .catch(err => console.log('Polling check:', err));
            });
        }, 3500);
    }
});
</script>
