<!-- SMASHHUB Matchmaking: Bảng Kèo -->
<div class="max-w-6xl mx-auto space-y-8 animate-fade-in">
    <!-- Header Row -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 pb-4 border-b border-white/10">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="rounded bg-[#d8ff3e] text-[#0b1116] px-2 py-0.5 text-[9px] font-black uppercase shadow-none">COMMUNITY</span>
                <span class="text-xs text-[#71818a] font-bold">Cập nhật trực tiếp</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black uppercase italic tracking-tight text-white">
                Bảng Kèo & Ghép Sân Giao Lưu
            </h1>
            <p class="mt-1 text-xs sm:text-sm text-[#7c8b93]">
                Tìm kiếm đối thủ giao lưu thi đấu theo khung giờ, quận và trình độ thực tế.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="index.php?action=smart_match" class="rounded-xl bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] px-4 py-2.5 text-xs font-black uppercase tracking-wider shadow-[3px_3px_0_#73940f] transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span>Hàng Chờ AI (Elo)</span>
            </a>
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'user'): ?>
                <a href="index.php?action=my_matches" class="rounded-xl border border-white/10 bg-[#0b1116]/5 hover:bg-[#0b1116]/10 text-white px-4 py-2.5 text-xs font-black uppercase tracking-wider transition">
                    Kèo Của Tôi
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'created'): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4 text-emerald-400 text-xs font-bold flex items-center gap-2">
            <span>✓</span> Đăng bài tìm đối thủ giao lưu thành công! Kèo đã sẵn sàng trên bảng tin.
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'closed'): ?>
        <div class="bg-[#0b1116]/5 border border-white/10 rounded-xl p-4 text-[#84939b] text-xs font-bold">
            Đã gỡ bài đăng tìm đối!
        </div>
    <?php endif; ?>

    <!-- Match Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if(count($matches) > 0): ?>
            <?php foreach($matches as $m): ?>
            <div class="bg-[#121b21] rounded-2xl border border-white/10 hover:border-[#d8ff3e]/40 shadow-none overflow-hidden flex flex-col transition hover:-translate-y-1">
                <!-- Pitch Preview Banner -->
                <div class="relative h-40 overflow-hidden">
                    <img src="<?php echo htmlspecialchars($m['pitch_image'] ?? 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=600'); ?>" alt="Pitch" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#121b21] via-black/40 to-transparent"></div>
                    <div class="absolute bottom-3 left-4 right-4 text-white">
                        <h3 class="font-black text-base truncate"><?php echo htmlspecialchars($m['pitch_name']); ?></h3>
                        <p class="text-xs text-[#d8ff3e] font-bold"><?php echo number_format($m['price_per_hour'], 0, ',', '.'); ?>đ / giờ</p>
                    </div>
                </div>

                <!-- Match Details -->
                <div class="p-5 flex-grow flex flex-col">
                    <!-- Host Info -->
                    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-white/5">
                        <div class="h-10 w-10 rounded-full border-2 border-[#d8ff3e] bg-[#1a2b24] overflow-hidden flex-shrink-0 flex items-center justify-center font-black text-xs text-[#d8ff3e]">
                            <?php if(!empty($m['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($m['avatar_url']); ?>" class="h-full w-full object-cover">
                            <?php else: ?>
                                <?php echo mb_substr($m['user_name'], 0, 1, 'UTF-8'); ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-sm font-black text-white"><?php echo htmlspecialchars($m['user_name']); ?></p>
                            <p class="text-[10px] text-[#71818a]">Đăng lúc <?php echo date('H:i d/m', strtotime($m['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <!-- Date & Time -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-xs text-[#84939b]">
                            <svg class="w-4 h-4 mr-2 text-[#d8ff3e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="font-bold text-white bg-[#0b1116]/5 px-2 py-0.5 rounded border border-white/5"><?php echo date('d/m/Y', strtotime($m['play_date'])); ?></span>
                        </div>
                        <div class="flex items-center text-xs text-[#84939b]">
                            <svg class="w-4 h-4 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-black text-cyan-400"><?php echo substr($m['start_time'],0,5); ?> – <?php echo substr($m['end_time'],0,5); ?></span>
                        </div>
                    </div>

                    <!-- Format & Level Badges -->
                    <div class="flex flex-wrap gap-1.5 mb-4 text-[11px]">
                        <span class="px-2 py-0.5 rounded-md font-bold bg-[#d8ff3e]/10 text-[#d8ff3e] border border-[#d8ff3e]/20">
                            🏸 <?php echo htmlspecialchars($m['format'] ?? 'Đánh Đôi'); ?>
                        </span>
                        <span class="px-2 py-0.5 rounded-md font-bold bg-purple-500/10 text-purple-300 border border-purple-500/20">
                            ⭐ Cấp <?php echo $m['skill_level'] ?? 3; ?>/5
                        </span>
                        <?php if(!empty($m['district'])): ?>
                            <span class="px-2 py-0.5 rounded-md font-bold bg-[#0b1116]/5 text-[#94a3b8] border border-white/5">
                                📍 <?php echo htmlspecialchars($m['district']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if(!empty($m['message'])): ?>
                        <div class="bg-[#0b1116] rounded-xl p-3 border border-white/5 text-xs text-[#94a3b8] italic mb-4">
                            "<?php echo htmlspecialchars($m['message']); ?>"
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="mt-auto pt-2 flex gap-2">
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'user' && $_SESSION['user_id'] != $m['user_id']): ?>
                            <a href="index.php?action=accept_match&id=<?php echo $m['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn CHỐT KÈO trận cầu này?');" class="flex-1 bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] text-center py-2.5 rounded-xl text-xs font-black uppercase tracking-wider shadow-[2px_2px_0_#73940f] transition flex justify-center items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Nhận Kèo</span>
                            </a>
                            <a href="index.php?action=chat&user=<?php echo $m['user_id']; ?>" class="bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 hover:bg-cyan-500/20 py-2.5 px-3 rounded-xl transition flex justify-center items-center" title="Nhắn Tin Trực Tiếp">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            </a>
                        <?php elseif(!isset($_SESSION['user_id'])): ?>
                            <a href="index.php?action=login" class="flex-1 bg-[#0b1116]/5 hover:bg-[#0b1116]/10 text-white text-center py-2.5 rounded-xl text-xs font-bold transition">Đăng nhập để nhận kèo</a>
                        <?php elseif(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $m['user_id']): ?>
                            <a href="index.php?action=close_match&id=<?php echo $m['id']; ?>" onclick="return confirm('Bạn muốn gỡ bài đăng tìm đối này?');" class="flex-1 border border-red-500/30 bg-red-500/10 text-red-400 hover:bg-red-500/20 text-center py-2.5 rounded-xl text-xs font-bold transition">Gỡ Bài Đăng</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full py-16 text-center bg-[#121b21] rounded-2xl border border-white/8">
                <span class="text-4xl block mb-3">🏸</span>
                <h3 class="text-base font-black text-white">Chưa có bài đăng tìm đối nào lúc này</h3>
                <p class="mt-1 text-xs text-[#71818a]">Hãy tham gia hàng chờ AI hoặc đặt một sân bất kỳ để bắt đầu ghép kèo!</p>
                <a href="index.php?action=smart_match" class="mt-4 inline-block px-5 py-2.5 rounded-xl bg-[#d8ff3e] text-[#0b1116] text-xs font-black uppercase shadow-[2px_2px_0_#73940f]">Vào Smart Match AI</a>
            </div>
        <?php endif; ?>
    </div>
</div>
