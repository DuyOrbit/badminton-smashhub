<div class="space-y-8 animate-fade-in">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#17231d] via-[#121b21] to-[#0b1116] p-6 sm:p-8 border border-white/10 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[#d8ff3e]/10 blur-3xl pointer-events-none"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#d8ff3e]/10 border border-[#d8ff3e]/30 text-[#d8ff3e] text-[11px] font-black uppercase tracking-wider mb-2">
                    <span class="h-2 w-2 rounded-full bg-[#d8ff3e] animate-ping"></span>
                    CỘNG ĐỒNG LÔNG THỦ SMASHHUB
                </div>
                <h1 class="text-3xl sm:text-4xl font-black uppercase tracking-tight text-white flex items-center gap-3">
                    <span>👥</span> Câu Lạc Bộ & Nhóm Cố Định
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-white/60 max-w-2xl leading-relaxed">
                    Gia nhập các hội nhóm cầu lông sinh hoạt định kỳ, tìm suất vãng lai giao lưu cuối tuần hoặc lập CLB riêng cho nhóm của bạn.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="index.php?action=club_create" class="inline-flex items-center gap-2 rounded-xl bg-[#d8ff3e] px-5 py-3 text-xs font-black uppercase text-[#10181e] shadow-[4px_4px_0_#6d8a16] transition hover:translate-x-0.5 hover:translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tạo CLB Của Bạn
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'created'): ?>
        <div class="bg-emerald-500/10 border-l-4 border-emerald-500 p-4 rounded-r-2xl text-emerald-300 font-bold text-sm">
            🎉 Chúc mừng! Bạn đã thành lập Câu Lạc Bộ mới thành công. Hãy đăng tin tuyển quân đầu tiên nhé!
        </div>
    <?php endif; ?>

    <!-- CLB Của Tôi (Hiển thị nếu đã đăng nhập và có tham gia CLB) -->
    <?php if(!empty($my_clubs)): ?>
        <div class="bg-[#121b21] rounded-3xl p-6 border border-white/10 space-y-4">
            <div class="flex items-center justify-between border-b border-white/5 pb-3">
                <h2 class="text-base font-black text-white uppercase tracking-tight flex items-center gap-2">
                    <span>⭐</span> CLB Bạn Đang Tham Gia (<?php echo count($my_clubs); ?>)
                </h2>
                <span class="text-xs text-[#d8ff3e] font-bold">Thành viên chính thức</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach($my_clubs as $mc): ?>
                    <a href="index.php?action=club_detail&id=<?php echo $mc['id']; ?>" class="bg-[#0b1116] border border-white/10 hover:border-[#d8ff3e]/50 p-4 rounded-2xl transition group flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black text-white group-hover:text-[#d8ff3e] transition">
                                    <?php echo htmlspecialchars($mc['name']); ?>
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold <?php echo $mc['member_role'] === 'leader' ? 'bg-[#d8ff3e]/20 text-[#d8ff3e]' : 'bg-blue-500/20 text-blue-300'; ?>">
                                    <?php echo $mc['member_role'] === 'leader' ? '👑 Trưởng Nhóm' : 'Thành Viên'; ?>
                                </span>
                            </div>
                            <p class="text-xs text-white/50 line-clamp-1 mb-2">
                                <?php echo htmlspecialchars($mc['schedule_info'] ?? 'Chưa cập nhật lịch'); ?>
                            </p>
                        </div>
                        <div class="text-[11px] text-cyan-400 font-bold pt-2 border-t border-white/5 flex items-center justify-between">
                            <span>📍 <?php echo htmlspecialchars($mc['district'] ?? 'TP.HCM'); ?></span>
                            <span>Vào nhóm →</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filter & Search Controls -->
    <div class="bg-[#121b21] rounded-2xl p-4 sm:p-5 border border-white/10 shadow-lg">
        <form action="index.php" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            <input type="hidden" name="action" value="clubs">

            <!-- Keyword -->
            <div class="flex-grow w-full md:w-auto">
                <label for="q" class="block text-[11px] font-black text-white/60 uppercase tracking-widest mb-1.5 ml-1">Tìm Kiếm CLB</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-white/40">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Tên CLB, khu vực, lịch đánh..." class="block w-full pl-10 pr-4 py-2.5 bg-[#0b1116] border border-white/15 text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-xs font-medium">
                </div>
            </div>

            <!-- District Filter -->
            <div class="w-full md:w-52">
                <label for="district" class="block text-[11px] font-black text-white/60 uppercase tracking-widest mb-1.5 ml-1">Khu Vực (Quận)</label>
                <select name="district" id="district" class="block w-full px-3 py-2.5 bg-[#0b1116] border border-white/15 text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-xs font-medium cursor-pointer">
                    <option value="">Tất cả khu vực</option>
                    <?php foreach($districts as $d): ?>
                        <option value="<?php echo htmlspecialchars($d); ?>" <?php echo ($district === $d) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($d); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Recruiting Toggle Filter -->
            <div class="w-full md:w-48">
                <label for="recruiting" class="block text-[11px] font-black text-white/60 uppercase tracking-widest mb-1.5 ml-1">Trạng Thái Tuyển</label>
                <select name="recruiting" id="recruiting" class="block w-full px-3 py-2.5 bg-[#0b1116] border border-white/15 text-white rounded-xl focus:border-[#d8ff3e] focus:outline-none text-xs font-medium cursor-pointer">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" <?php echo ($recruiting === '1') ? 'selected' : ''; ?>>🟢 Đang Tuyển Vãng Lai</option>
                    <option value="0" <?php echo ($recruiting === '0') ? 'selected' : ''; ?>>⚪ Đủ Thành Viên</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="w-full md:w-auto">
                <button type="submit" class="w-full md:w-auto bg-[#d8ff3e] text-[#10181e] font-black px-6 py-2.5 rounded-xl text-xs uppercase tracking-wider hover:bg-[#c6ec2e] transition">
                    Lọc CLB
                </button>
            </div>
        </form>
    </div>

    <!-- CLB Grid List -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-black text-white uppercase tracking-tight flex items-center gap-2">
                <span>🏸</span> Danh Sách Câu Lạc Bộ (<?php echo count($clubs); ?>)
            </h2>
            <span class="text-xs text-white/50">Cập nhật liên tục</span>
        </div>

        <?php if(!empty($clubs)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($clubs as $c): ?>
                    <div class="bg-[#121b21] rounded-3xl border border-white/10 hover:border-[#d8ff3e]/40 p-5 shadow-xl transition-all flex flex-col justify-between group">
                        <div class="space-y-3">
                            <!-- Status Tag & Member Count -->
                            <div class="flex items-center justify-between gap-2">
                                <?php if($c['is_recruiting']): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Tuyển Vãng Lai
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-white/60">
                                        Đã đủ người
                                    </span>
                                <?php endif; ?>

                                <span class="text-xs font-bold text-white/60 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-[#d8ff3e]" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
                                    <?php echo (int)$c['member_count']; ?> thành viên
                                </span>
                            </div>

                            <!-- Name & District -->
                            <div>
                                <h3 class="text-lg font-black text-white group-hover:text-[#d8ff3e] transition line-clamp-1">
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </h3>
                                <div class="flex items-center gap-1.5 text-xs text-cyan-400 font-bold mt-1">
                                    <span>📍</span>
                                    <span><?php echo htmlspecialchars($c['district'] ?? 'Khu vực chưa gán'); ?></span>
                                    <?php if(!empty($c['pitch_name'])): ?>
                                        <span class="text-white/40">• Sân: <?php echo htmlspecialchars($c['pitch_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Schedule Info -->
                            <div class="bg-[#0b1116] p-3 rounded-2xl border border-white/5 space-y-1.5 text-xs">
                                <div class="flex items-center gap-2 text-white/90">
                                    <span class="text-amber-400">⏰</span>
                                    <span class="font-bold"><?php echo htmlspecialchars($c['schedule_info'] ?? 'Chưa cập nhật lịch'); ?></span>
                                </div>
                                <div class="flex items-center gap-2 text-white/60 text-[11px]">
                                    <span class="text-blue-400">🏸</span>
                                    <span>Trình độ: <strong class="text-white/80"><?php echo htmlspecialchars($c['level_requirement']); ?></strong></span>
                                </div>
                            </div>

                            <!-- Description -->
                            <?php if(!empty($c['description'])): ?>
                                <p class="text-xs text-white/60 line-clamp-2 leading-relaxed">
                                    <?php echo htmlspecialchars($c['description']); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Footer Actions -->
                        <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between">
                            <span class="text-[11px] text-white/40">
                                Trưởng nhóm: <strong class="text-white/70">@<?php echo htmlspecialchars($c['leader_username'] ?? 'admin'); ?></strong>
                            </span>
                            <a href="index.php?action=club_detail&id=<?php echo $c['id']; ?>" class="inline-flex items-center gap-1 text-xs font-black text-[#d8ff3e] group-hover:translate-x-1 transition">
                                Chi Tiết & Giao Lưu →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-[#121b21] rounded-3xl border border-white/10 space-y-4">
                <span class="text-4xl">🏸</span>
                <h3 class="text-lg font-black text-white">Chưa có CLB nào phù hợp với tìm kiếm của bạn</h3>
                <p class="text-xs text-white/50 max-w-md mx-auto">Hãy thử chọn lại khu vực hoặc trở thành người đầu tiên thành lập Câu Lạc Bộ trong khu vực này!</p>
                <a href="index.php?action=club_create" class="inline-flex items-center gap-2 bg-[#d8ff3e] text-[#10181e] px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider">
                    Thành Lập CLB Ngay
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
