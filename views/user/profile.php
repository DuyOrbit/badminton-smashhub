<?php
// Defensive initialization for userData
if (!isset($userData) || !is_array($userData)) {
    $userData = [
        'name' => $_SESSION['user_name'] ?? 'Vận động viên',
        'username' => 'user',
        'phone' => '',
        'avatar_url' => '',
        'skill_level' => 3,
        'preferred_format' => 'Đánh Đôi',
        'district' => 'Bình Thạnh'
    ];
}
?>
<!-- SMASHHUB User Profile View -->
<div class="max-w-4xl mx-auto py-4 text-white space-y-6 animate-fade-in">
    <div class="bg-[#121b21] rounded-3xl shadow-2xl overflow-hidden border border-white/10">
        <!-- Profile Banner -->
        <div class="bg-[#0d151b] px-8 py-8 text-white flex flex-col sm:flex-row items-center gap-6 border-b border-white/10">
            <div class="h-24 w-24 rounded-full overflow-hidden border-2 border-[#d8ff3e] shadow-[0_0_20px_rgba(216,255,62,0.3)] bg-[#1a2b24] flex-shrink-0 flex items-center justify-center">
                <?php if(!empty($userData['avatar_url'])): ?>
                    <img src="<?php echo htmlspecialchars($userData['avatar_url']); ?>" alt="Avatar" class="h-full w-full object-cover">
                <?php else: ?>
                    <span class="text-3xl font-black text-[#d8ff3e]">
                        <?php echo mb_strtoupper(mb_substr($userData['name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8'); ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="text-center sm:text-left">
                <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
                    <span class="rounded bg-[#d8ff3e] text-[#0b1116] px-2 py-0.5 text-[9px] font-black uppercase shadow-sm">
                        <?php echo strtoupper($currentRankName ?? 'GOLD PLAYER'); ?>
                    </span>
                    <span class="rounded bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 px-2 py-0.5 text-[9px] font-black uppercase">
                        <?php echo $reputation['score'] ?? 100; ?> PTS UY TÍN
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black uppercase italic tracking-tight text-white"><?php echo htmlspecialchars($userData['name'] ?? ($_SESSION['user_name'] ?? 'Vận động viên')); ?></h1>
                <p class="text-[#84939b] font-medium text-xs mt-1">@<?php echo htmlspecialchars($userData['username'] ?? 'user'); ?></p>
            </div>
        </div>
        
        <div class="p-6 sm:p-10">
            <!-- Flash Messages -->
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/30 p-4 mb-6 rounded-xl text-emerald-400 text-xs font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Cập nhật hồ sơ thành công!</span>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-500/10 border border-red-500/30 p-4 mb-6 rounded-xl text-red-400 text-xs font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Đã có lỗi xảy ra, vui lòng kiểm tra lại.</span>
                </div>
            <?php endif; ?>

            <form action="index.php?action=update_profile" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Họ và Tên</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name'] ?? ''); ?>" required class="w-full border border-white/10 p-3 bg-[#0b1116] text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-sm font-medium transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Số Điện Thoại</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>" placeholder="Ví dụ: 0912345678" class="w-full border border-white/10 p-3 bg-[#0b1116] text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-sm font-medium transition">
                        <p class="text-[10px] text-[#71818a] mt-1.5 flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1 text-[#d8ff3e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Số điện thoại bảo mật theo tiêu chuẩn giải đấu.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Đổi Ảnh Đại Diện (Avatar)</label>
                    <input type="file" name="avatar_file" accept="image/png, image/jpeg, image/webp" class="w-full border border-white/10 p-2.5 rounded-xl text-xs bg-[#0b1116] text-[#84939b] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:bg-[#d8ff3e] file:text-[#0b1116] cursor-pointer">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-4 border-t border-white/5">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Trình Độ Cầu Lông</label>
                        <select name="skill_level" class="w-full border border-white/10 p-3 bg-[#0b1116] text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-xs font-medium cursor-pointer">
                            <option value="1" <?php echo ($userData['skill_level'] ?? 3) == 1 ? 'selected' : ''; ?>>Level 1 - Mới chơi / Tập luyện</option>
                            <option value="2" <?php echo ($userData['skill_level'] ?? 3) == 2 ? 'selected' : ''; ?>>Level 2 - Trung bình yếu</option>
                            <option value="3" <?php echo ($userData['skill_level'] ?? 3) == 3 ? 'selected' : ''; ?>>Level 3 - Trung bình khá</option>
                            <option value="4" <?php echo ($userData['skill_level'] ?? 3) == 4 ? 'selected' : ''; ?>>Level 4 - Khá tốt (Chơi lâu năm)</option>
                            <option value="5" <?php echo ($userData['skill_level'] ?? 3) == 5 ? 'selected' : ''; ?>>Level 5 - Bán chuyên / Thi đấu</option>
                        </select>
                        <p class="text-[10px] text-[#71818a] mt-1">Dùng để tính độ tương đồng ghép sân.</p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Hình Thức Ưa Thích</label>
                        <select name="preferred_format" class="w-full border border-white/10 p-3 bg-[#0b1116] text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-xs font-medium cursor-pointer">
                            <option value="Đánh Đôi" <?php echo ($userData['preferred_format'] ?? '') == 'Đánh Đôi' ? 'selected' : ''; ?>>Đánh Đôi (2 vs 2)</option>
                            <option value="Đánh Đơn" <?php echo ($userData['preferred_format'] ?? '') == 'Đánh Đơn' ? 'selected' : ''; ?>>Đánh Đơn (1 vs 1)</option>
                            <option value="Giao lưu nhóm" <?php echo ($userData['preferred_format'] ?? '') == 'Giao lưu nhóm' ? 'selected' : ''; ?>>Giao lưu nhóm / Tự do</option>
                        </select>
                        <p class="text-[10px] text-[#71818a] mt-1">Ưu tiên đề xuất kèo phù hợp.</p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Khu Vực Thường Chơi</label>
                        <input type="text" name="district" value="<?php echo htmlspecialchars($userData['district'] ?? 'Bình Thạnh'); ?>" placeholder="Ví dụ: Bình Thạnh, Gò Vấp" class="w-full border border-white/10 p-3 bg-[#0b1116] text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-xs font-medium">
                        <p class="text-[10px] text-[#71818a] mt-1">Gợi ý sân và đối thủ cùng quận.</p>
                    </div>
                </div>

                <div class="border-t border-white/5 pt-6 mt-6">
                    <h3 class="text-sm font-black uppercase tracking-wider text-white mb-4">Đổi Mật Khẩu <span class="text-xs font-normal text-[#71818a]">(Tùy chọn)</span></h3>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Mật Khẩu Mới</label>
                        <input type="password" name="new_password" placeholder="Bỏ trống nếu không muốn đổi" class="w-full md:w-1/2 border border-white/10 p-3 bg-[#0b1116] text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-xs font-medium">
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] font-black uppercase text-xs tracking-wider px-6 py-3 rounded-xl shadow-[3px_3px_0_#73940f] transition hover:translate-x-0.5 hover:translate-y-0.5">
                        Lưu Thay Đổi
                    </button>
                </div>
            </form>
            
            <!-- Ranking Display -->
            <div class="mt-10 pt-8 border-t border-white/10">
                <h3 class="text-lg font-black uppercase italic tracking-tight text-white mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#d8ff3e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    Tiến Trình Xếp Hạng & Ưu Đãi (<span class="text-[#d8ff3e]"><?php echo $currentSeason; ?></span>)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="bg-[#0d151b] rounded-2xl p-6 border border-white/10 relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-[#b98aee]/10 blur-2xl pointer-events-none"></div>
                        <p class="text-[#71818a] text-[10px] font-black uppercase tracking-wider mb-1">Điểm Tích Lũy Mùa Này</p>
                        <p class="text-3xl font-black text-white"><?php echo number_format($currentPoints); ?> <span class="text-sm font-bold text-[#71818a]">Pts</span></p>
                        <div class="flex justify-between items-end mt-4 text-xs font-black">
                            <span class="text-[#ffd45b]">Bậc Rank: <?php echo $currentRankName; ?></span>
                            <span class="text-[#d8ff3e]">Giảm cố định: <?php echo $ranking->calculateDiscount($currentPoints); ?>%</span>
                        </div>
                    </div>

                    <div class="bg-[#17231d] rounded-2xl p-6 border border-[#d8ff3e]/20 relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-[#d8ff3e]/10 blur-2xl pointer-events-none"></div>
                        <p class="text-[#82918b] text-[10px] font-black uppercase tracking-wider mb-1">Mã Giảm Áp Dụng Tức Thì</p>
                        <?php if($activeDiscount > 0): ?>
                            <p class="text-3xl font-black text-[#d8ff3e] mb-1"><?php echo $activeDiscount; ?>%</p>
                            <p class="text-[11px] text-[#82918b]">Dựa trên Rank cao nhất giữa Mùa Trước và Mùa Này.</p>
                        <?php else: ?>
                            <p class="text-2xl font-black text-white mb-1">Chưa Có Ưu Đãi</p>
                            <p class="text-[11px] text-[#82918b]">Giao lưu thêm trên Bảng Cáp Kèo để nhận ưu đãi giảm giá.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bảng Hướng Dẫn Rank -->
                <div class="mt-6 bg-[#0d151b] rounded-2xl p-5 border border-white/5">
                    <h4 class="text-xs font-black uppercase tracking-wider text-[#84939b] mb-4 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#d8ff3e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Thể Thức Xếp Hạng & Đặc Quyền Giảm Giá
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <!-- Đồng -->
                        <div class="bg-[#121b21] p-3.5 rounded-xl border border-white/5 text-center">
                            <h5 class="font-black text-amber-600 text-xs mb-0.5">Đồng</h5>
                            <p class="text-[10px] text-[#71818a] font-bold mb-2">0 - 299 Pts</p>
                            <span class="inline-block bg-[#0b1116]/5 text-[#71818a] text-[9px] font-black uppercase px-2 py-0.5 rounded">Không G.Giá</span>
                        </div>
                        <!-- Bạc -->
                        <div class="bg-[#121b21] p-3.5 rounded-xl border border-white/10 text-center">
                            <h5 class="font-black text-slate-300 text-xs mb-0.5">Bạc</h5>
                            <p class="text-[10px] text-[#71818a] font-bold mb-2">300 - 599 Pts</p>
                            <span class="inline-block bg-[#0b1116]/10 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded">Giảm 5%</span>
                        </div>
                        <!-- Vàng -->
                        <div class="bg-[#121b21] p-3.5 rounded-xl border border-[#ffd45b]/30 text-center">
                            <h5 class="font-black text-[#ffd45b] text-xs mb-0.5">Vàng</h5>
                            <p class="text-[10px] text-[#71818a] font-bold mb-2">600 - 999 Pts</p>
                            <span class="inline-block bg-[#ffd45b]/20 text-[#ffd45b] text-[9px] font-black uppercase px-2 py-0.5 rounded">Giảm 10%</span>
                        </div>
                        <!-- Kim Cương -->
                        <div class="bg-[#121b21] p-3.5 rounded-xl border border-cyan-400/30 text-center">
                            <h5 class="font-black text-cyan-400 text-xs mb-0.5">Kim Cương</h5>
                            <p class="text-[10px] text-[#71818a] font-bold mb-2">1000+ Pts</p>
                            <span class="inline-block bg-cyan-400/20 text-cyan-300 text-[9px] font-black uppercase px-2 py-0.5 rounded">Giảm 20%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
