<div class="space-y-8 animate-fade-in">
    <!-- Club Profile Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#17231d] via-[#121b21] to-[#0b1116] p-6 sm:p-8 border border-white/10 shadow-2xl">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-[#d8ff3e]/10 blur-3xl pointer-events-none"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex items-start gap-4 sm:gap-6">
                <!-- Club Avatar -->
                <div class="flex h-16 w-16 sm:h-20 sm:w-20 flex-shrink-0 items-center justify-center rounded-3xl bg-[#d8ff3e] text-[#0b1116] font-black text-2xl sm:text-3xl shadow-[0_0_25px_rgba(216,255,62,0.4)]">
                    🏸
                </div>

                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-cyan-400 bg-cyan-500/10 border border-cyan-500/30 px-2.5 py-0.5 rounded-full">
                            📍 <?php echo htmlspecialchars($club['district'] ?? 'TP.HCM'); ?>
                        </span>

                        <?php if($club['is_recruiting']): ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                Đang Tuyển Vãng Lai
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold bg-white/10 text-white/60">
                                Đủ Thành Viên
                            </span>
                        <?php endif; ?>

                        <span class="text-xs font-bold text-white/60 bg-white/5 border border-white/10 px-2.5 py-0.5 rounded-full">
                            👥 <?php echo (int)$club['member_count']; ?> thành viên
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black uppercase text-white tracking-tight">
                        <?php echo htmlspecialchars($club['name']); ?>
                    </h1>

                    <p class="text-xs sm:text-sm text-white/70 max-w-2xl leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($club['description'] ?? 'Chào mừng các bạn đến với CLB của chúng tôi!')); ?>
                    </p>

                    <!-- Meta Tags Row -->
                    <div class="flex flex-wrap items-center gap-4 pt-2 text-xs text-white/80">
                        <div class="flex items-center gap-1.5 bg-[#0b1116] px-3 py-1.5 rounded-xl border border-white/5">
                            <span class="text-amber-400">⏰</span>
                            <span class="font-bold"><?php echo htmlspecialchars($club['schedule_info']); ?></span>
                        </div>
                        <?php if(!empty($club['pitch_name'])): ?>
                            <div class="flex items-center gap-1.5 bg-[#0b1116] px-3 py-1.5 rounded-xl border border-white/5">
                                <span class="text-blue-400">🏟️</span>
                                <span>Sân: <strong class="text-white"><?php echo htmlspecialchars($club['pitch_name']); ?></strong></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center gap-1.5 bg-[#0b1116] px-3 py-1.5 rounded-xl border border-white/5">
                            <span class="text-purple-400">🎯</span>
                            <span>Trình độ: <strong class="text-white"><?php echo htmlspecialchars($club['level_requirement']); ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Join / Leave / Leader Action Controls -->
            <div class="flex flex-wrap md:flex-col items-end gap-3 flex-shrink-0">
                <?php if(!$user_id): ?>
                    <a href="index.php?action=login" class="bg-[#d8ff3e] text-[#10181e] font-black px-6 py-3 rounded-2xl text-xs uppercase tracking-wider shadow-lg hover:bg-[#c6ec2e] transition">
                        Đăng Nhập Để Tham Gia
                    </a>
                <?php elseif($is_leader): ?>
                    <form action="index.php?action=club_toggle_recruitment" method="POST" class="w-full">
                        <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                        <button type="submit" class="w-full bg-[#121b21] border <?php echo $club['is_recruiting'] ? 'border-amber-500/40 text-amber-300' : 'border-emerald-500/40 text-emerald-300'; ?> hover:bg-white/5 font-black px-4 py-2.5 rounded-xl text-xs transition">
                            <?php echo $club['is_recruiting'] ? '⏸️ Tạm Dừng Tuyển Quân' : '🟢 Bật Tuyển Vãng Lai'; ?>
                        </button>
                    </form>
                    <span class="text-[11px] font-bold text-[#d8ff3e] bg-[#d8ff3e]/10 border border-[#d8ff3e]/20 px-3 py-1 rounded-xl block text-center">
                        👑 Bạn là Trưởng Nhóm
                    </span>
                <?php elseif($is_member): ?>
                    <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-4 py-2 rounded-xl flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Đã là thành viên
                    </span>
                    <form action="index.php?action=club_leave" method="POST" onsubmit="return confirm('Bạn có chắc muốn rời CLB này?');">
                        <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                        <button type="submit" class="text-xs text-red-400 hover:text-red-300 underline font-bold">
                            Rời CLB
                        </button>
                    </form>
                <?php else: ?>
                    <form action="index.php?action=club_join" method="POST">
                        <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                        <button type="submit" class="bg-[#d8ff3e] text-[#10181e] font-black px-6 py-3 rounded-2xl text-xs uppercase tracking-wider shadow-[4px_4px_0_#6d8a16] transition hover:translate-x-0.5 hover:translate-y-0.5">
                            ➕ Gia Nhập CLB Ngay
                        </button>
                    </form>
                <?php endif; ?>

                <a href="index.php?action=clubs" class="text-xs text-white/50 hover:text-white transition">
                    ← Quay lại danh sách CLB
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'joined'): ?>
            <div class="bg-emerald-500/10 border-l-4 border-emerald-500 p-4 rounded-r-2xl text-emerald-300 font-bold text-sm">
                🎉 Bạn đã gia nhập Câu Lạc Bộ thành công! Chào mừng thành viên mới.
            </div>
        <?php elseif($_GET['msg'] == 'left'): ?>
            <div class="bg-gray-700/50 border-l-4 border-gray-400 p-4 rounded-r-2xl text-gray-300 font-bold text-sm">
                Đã rời Câu Lạc Bộ thành công.
            </div>
        <?php elseif($_GET['msg'] == 'posted'): ?>
            <div class="bg-blue-500/10 border-l-4 border-blue-500 p-4 rounded-r-2xl text-blue-300 font-bold text-sm">
                📢 Đã đăng bài viết / tin tuyển vãng lai lên bảng tin CLB!
            </div>
        <?php elseif($_GET['msg'] == 'status_updated'): ?>
            <div class="bg-amber-500/10 border-l-4 border-amber-500 p-4 rounded-r-2xl text-amber-300 font-bold text-sm">
                Đã cập nhật trạng thái tuyển quân thành công.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Main Grid: Feed & Members -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: BẢNG TIN & TUYỂN VÃNG LAI -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Form Đăng Bài / Đăng Tin Tuyển Slot -->
            <?php if($is_member || $is_leader): ?>
                <div class="bg-[#121b21] rounded-3xl border border-white/10 p-5 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-white/5 pb-3">
                        <span class="text-xs font-black uppercase text-white tracking-wider flex items-center gap-2">
                            <span>✍️</span> Đăng Bài Viết / Tuyển Vãng Lai
                        </span>
                        <span class="text-[10px] text-[#d8ff3e] font-bold">Thành viên có quyền đăng</span>
                    </div>

                    <form action="index.php?action=club_post" method="POST" class="space-y-3.5">
                        <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">

                        <!-- Chọn Loại Bài Viết -->
                        <div class="grid grid-cols-2 gap-2">
                            <label class="border border-white/10 rounded-xl p-2.5 text-center cursor-pointer hover:border-[#d8ff3e] transition has-[:checked]:border-[#d8ff3e] has-[:checked]:bg-[#d8ff3e]/10 text-xs font-bold text-white">
                                <input type="radio" name="post_type" value="recruitment" checked class="hidden" onchange="togglePostType(true)">
                                <span>🔥 Tuyển Vãng Lai</span>
                            </label>
                            <label class="border border-white/10 rounded-xl p-2.5 text-center cursor-pointer hover:border-[#d8ff3e] transition has-[:checked]:border-[#d8ff3e] has-[:checked]:bg-[#d8ff3e]/10 text-xs font-bold text-white">
                                <input type="radio" name="post_type" value="general" class="hidden" onchange="togglePostType(false)">
                                <span>💬 Thảo Luận / Thông Báo</span>
                            </label>
                        </div>

                        <!-- Khối phụ khi chọn Tuyển Vãng Lai -->
                        <div id="recruitmentFields" class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-[#0b1116] p-3 rounded-2xl border border-amber-500/30">
                            <div>
                                <label class="block text-[11px] font-bold text-amber-300 mb-1">Số Lượng Slot Cần Tuyển:</label>
                                <input type="number" name="slot_needed" min="1" max="20" value="2" class="w-full px-3 py-2 bg-[#121b21] border border-white/15 rounded-xl text-white text-xs font-bold focus:border-[#d8ff3e] outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-amber-300 mb-1">Phí Tiền Sân / Người (VNĐ):</label>
                                <input type="number" name="fee_per_slot" step="5000" min="0" value="50000" placeholder="50.000" class="w-full px-3 py-2 bg-[#121b21] border border-white/15 rounded-xl text-white text-xs font-bold focus:border-[#d8ff3e] outline-none">
                            </div>
                        </div>

                        <div>
                            <input type="text" name="title" placeholder="Tiêu đề bài viết (Ví dụ: Cần 2 bạn tối nay 18h-20h...)" class="w-full px-4 py-2.5 bg-[#0b1116] border border-white/15 text-white rounded-xl focus:border-[#d8ff3e] outline-none text-xs font-bold mb-2">
                            <textarea name="content" required rows="2" placeholder="Nội dung chi tiết, trình độ cần tuyển, số sân..." class="w-full px-4 py-2.5 bg-[#0b1116] border border-white/15 text-white rounded-xl focus:border-[#d8ff3e] outline-none text-xs leading-relaxed"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-[#d8ff3e] text-[#10181e] font-black px-6 py-2.5 rounded-xl text-xs uppercase tracking-wider hover:bg-[#c6ec2e] transition">
                                Đăng Lên Bảng Tin
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-[#121b21] rounded-3xl border border-white/10 p-5 text-center space-y-2">
                    <p class="text-xs text-white/60">Gia nhập CLB để cùng đăng bài thảo luận và tuyển thành viên giao lưu nhé!</p>
                </div>
            <?php endif; ?>

            <!-- Danh Sách Bài Viết / Tin Tuyển Dụng -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-black text-white uppercase tracking-tight flex items-center gap-2">
                        <span>📢</span> Bảng Tin Sinh Hoạt CLB (<?php echo count($posts); ?>)
                    </h3>
                </div>

                <?php if(!empty($posts)): ?>
                    <?php foreach($posts as $p): ?>
                        <div class="bg-[#121b21] rounded-3xl border <?php echo $p['post_type'] === 'recruitment' ? 'border-amber-500/40 bg-gradient-to-br from-[#121b21] to-[#1a1711]' : 'border-white/10'; ?> p-5 shadow-xl space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-9 w-9 rounded-full bg-[#d8ff3e]/20 text-[#d8ff3e] flex items-center justify-center font-black text-xs border border-[#d8ff3e]/30">
                                        <?php echo mb_substr($p['author_name'], 0, 1, 'UTF-8'); ?>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-white"><?php echo htmlspecialchars($p['author_name']); ?></h4>
                                        <span class="text-[10px] text-white/40">@<?php echo htmlspecialchars($p['author_username']); ?> • <?php echo date('H:i - d/m/Y', strtotime($p['created_at'])); ?></span>
                                    </div>
                                </div>

                                <?php if($p['post_type'] === 'recruitment'): ?>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/40 uppercase">
                                        🔥 Cần <?php echo (int)$p['slot_needed']; ?> Slot Vãng Lai
                                    </span>
                                <?php else: ?>
                                    <span class="text-[10px] font-bold text-white/50 bg-white/5 px-2.5 py-0.5 rounded-full">
                                        Thảo luận
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if(!empty($p['title'])): ?>
                                <h3 class="text-sm font-black text-white"><?php echo htmlspecialchars($p['title']); ?></h3>
                            <?php endif; ?>

                            <p class="text-xs text-white/80 leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($p['content'])); ?>
                            </p>

                            <!-- Khối hiển thị tiền sân nếu là bài tuyển vãng lai -->
                            <?php if($p['post_type'] === 'recruitment'): ?>
                                <div class="bg-[#0b1116] p-3 rounded-2xl border border-white/5 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="text-amber-400 font-bold">💰 Phí giao lưu ước tính:</span>
                                        <span class="text-white font-black"><?php echo number_format($p['fee_per_slot'], 0, ',', '.'); ?>đ/người</span>
                                    </div>
                                    <a href="index.php?action=chat" class="bg-amber-500 hover:bg-amber-400 text-black font-black px-4 py-1.5 rounded-xl text-[11px] transition">
                                        💬 Xin Slot Giao Lưu
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12 bg-[#121b21] rounded-3xl border border-white/10 space-y-2">
                        <span class="text-3xl">📝</span>
                        <p class="text-xs text-white/50">Chưa có bài viết nào trên bảng tin. Hãy là người đầu tiên đăng tin!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right 1 Col: DANH SÁCH THÀNH VIÊN -->
        <div class="space-y-4">
            <div class="bg-[#121b21] rounded-3xl border border-white/10 p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-white/5 pb-3">
                    <h3 class="text-base font-black text-white uppercase tracking-tight flex items-center gap-2">
                        <span>👥</span> Thành Viên (<?php echo count($members); ?>)
                    </h3>
                </div>

                <div class="space-y-2.5 max-h-[500px] overflow-y-auto pr-1">
                    <?php foreach($members as $m): ?>
                        <div class="bg-[#0b1116] p-3 rounded-2xl border border-white/5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="h-8 w-8 rounded-full <?php echo $m['role'] === 'leader' ? 'bg-[#d8ff3e] text-[#0b1116]' : 'bg-white/10 text-white'; ?> flex items-center justify-center font-black text-xs">
                                    <?php echo mb_substr($m['name'], 0, 1, 'UTF-8'); ?>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-white line-clamp-1"><?php echo htmlspecialchars($m['name']); ?></div>
                                    <div class="text-[10px] text-white/40">@<?php echo htmlspecialchars($m['username']); ?></div>
                                </div>
                            </div>

                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full <?php echo $m['role'] === 'leader' ? 'bg-[#d8ff3e]/20 text-[#d8ff3e] border border-[#d8ff3e]/30' : 'bg-white/10 text-white/60'; ?>">
                                <?php echo $m['role'] === 'leader' ? '👑 Trưởng Nhóm' : 'Thành Viên'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePostType(isRecruitment) {
    const fields = document.getElementById('recruitmentFields');
    if (isRecruitment) {
        fields.classList.remove('hidden');
    } else {
        fields.classList.add('hidden');
    }
}
</script>
