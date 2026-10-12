<!-- SMASHHUB: Kèo Thi Đấu Của Tôi -->
<div class="max-w-6xl mx-auto space-y-6 text-white animate-fade-in">
    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 pb-4 border-b border-white/10">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="rounded bg-[#d8ff3e] text-[#0b1116] px-2 py-0.5 text-[9px] font-black uppercase shadow-sm">MY MATCHES</span>
                <span class="text-xs text-[#71818a] font-bold">Kèo đấu thực chiến</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black uppercase italic tracking-tight text-white">Kèo Thi Đấu Của Tôi</h1>
            <p class="mt-1 text-xs sm:text-sm text-[#7c8b93]">Danh sách các trận đấu bạn đang tham gia. Đừng quên nộp đủ phần chia tiền sân nhé!</p>
        </div>
        <a href="index.php?action=matchmaking" class="rounded-xl border border-white/10 bg-[#0b1116]/5 hover:bg-[#0b1116]/10 text-white px-4 py-2.5 text-xs font-black uppercase tracking-wider transition w-fit">
            ← Quay lại Bảng Kèo
        </a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'accepted'): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/30 p-4 rounded-xl text-emerald-400 text-xs font-bold flex items-center gap-2">
            <span>✓</span> Chốt kèo thành công! Hãy chuẩn bị thể lực cho trận đấu nảy lửa.
        </div>
    <?php endif; ?>

    <div class="space-y-6">
        <?php if(count($matches) > 0): ?>
            <?php foreach($matches as $m): 
                // Calculate prices
                $start = strtotime($m['start_time']);
                $end = strtotime($m['end_time']);
                $hours = ($end - $start) / 3600;
                $half_base = ($hours * $m['price_per_hour']) / 2;
                $my_final_price = $half_base * ((100 - ($myDiscount ?? 0)) / 100);
                
                $is_host = ($m['user_id'] == ($_SESSION['user_id'] ?? 0));
            ?>
            <div class="bg-[#121b21] rounded-2xl shadow-xl border border-white/10 hover:border-[#d8ff3e]/40 transition overflow-hidden flex flex-col md:flex-row relative">
                <!-- Status Badge -->
                <div class="absolute top-4 right-4 z-10">
                    <?php if($m['status'] == 'cancelled'): ?>
                        <span class="bg-red-500/20 text-red-400 text-[10px] font-black uppercase px-3 py-1 rounded-full border border-red-500/30 shadow-sm">Bị Hủy - Hoàn Tiền</span>
                    <?php elseif($m['match_result'] == 'pending'): ?>
                        <span class="bg-cyan-500/20 text-cyan-300 text-[10px] font-black uppercase px-3 py-1 rounded-full border border-cyan-500/30">Chờ Chủ Sân Cập Nhật Điểm</span>
                    <?php else: ?>
                        <span class="bg-[#d8ff3e]/20 text-[#d8ff3e] text-[10px] font-black uppercase px-3 py-1 rounded-full border border-[#d8ff3e]/30">Đã Tính Điểm Rank</span>
                    <?php endif; ?>
                </div>

                <!-- Pitch Info -->
                <div class="md:w-1/3 relative h-52 md:h-auto overflow-hidden">
                    <img src="<?php echo htmlspecialchars($m['pitch_image'] ?? 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=600'); ?>" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#121b21] via-black/40 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <h3 class="font-black text-lg leading-tight mb-2"><?php echo htmlspecialchars($m['pitch_name']); ?></h3>
                        <div class="flex gap-2 text-xs">
                            <span class="font-bold bg-black/60 px-2 py-1 rounded-lg backdrop-blur flex items-center gap-1 border border-white/10">
                                <svg class="w-3.5 h-3.5 text-[#d8ff3e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <?php echo date('d/m/Y', strtotime($m['play_date'])); ?>
                            </span>
                            <span class="font-bold bg-black/60 px-2 py-1 rounded-lg backdrop-blur flex items-center gap-1 border border-white/10">
                                <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <?php echo substr($m['start_time'],0,5); ?> – <?php echo substr($m['end_time'],0,5); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Match Details -->
                <div class="p-6 md:w-2/3 flex flex-col justify-between">
                    <div>
                        <div class="flex flex-col sm:flex-row items-center justify-between mb-5 bg-[#0b1116] rounded-xl p-4 border border-white/5 relative">
                            <!-- Host -->
                            <div class="flex flex-col items-center flex-1 w-full text-center">
                                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-cyan-400 bg-[#1a2b24] mb-2 relative flex items-center justify-center font-black text-sm text-cyan-300">
                                    <?php if($is_host): ?><div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-[#d8ff3e] rounded-full border-2 border-[#0b1116] z-10"></div><?php endif; ?>
                                    <?php if(!empty($m['host_avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($m['host_avatar']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <?php echo mb_substr($m['host_name'] ?? 'H', 0, 1, 'UTF-8'); ?>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm font-black text-white truncate max-w-[140px]"><?php echo htmlspecialchars($m['host_name']); ?></p>
                                <span class="text-[9px] font-black uppercase text-cyan-400 mt-1 bg-cyan-500/10 px-2 py-0.5 rounded-full border border-cyan-500/20">Chủ Nhà (Host)</span>
                            </div>

                            <!-- VS -->
                            <div class="flex flex-col items-center px-4 py-3 sm:py-0 relative z-10 w-full sm:w-auto">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-red-600 to-amber-600 text-white flex items-center justify-center font-black text-sm italic shadow-lg">
                                    VS
                                </div>
                                <?php if($m['match_result'] !== 'pending'): ?>
                                    <span class="mt-2 text-[9px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md bg-[#d8ff3e] text-[#0b1116] shadow-sm whitespace-nowrap">
                                        <?php 
                                            if($m['match_result'] == 'host_won') echo "Chủ Nhà Thắng";
                                             elseif($m['match_result'] == 'guest_won') echo "Khách Thắng";
                                            else echo "Hòa Nhau";
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Guest -->
                            <div class="flex flex-col items-center flex-1 w-full text-center">
                                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-purple-400 bg-[#1a2b24] mb-2 relative flex items-center justify-center font-black text-sm text-purple-300">
                                    <?php if(!$is_host): ?><div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-[#d8ff3e] rounded-full border-2 border-[#0b1116] z-10"></div><?php endif; ?>
                                    <?php if(!empty($m['guest_avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($m['guest_avatar']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <?php echo mb_substr($m['guest_name'] ?? 'G', 0, 1, 'UTF-8'); ?>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm font-black text-white truncate max-w-[140px]"><?php echo htmlspecialchars($m['guest_name']); ?></p>
                                <span class="text-[9px] font-black uppercase text-purple-400 mt-1 bg-purple-500/10 px-2 py-0.5 rounded-full border border-purple-500/20">Đội Khách</span>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        <div class="bg-[#0d151b] rounded-xl p-4 border border-white/5">
                            <h4 class="text-[10px] font-black text-[#71818a] uppercase tracking-widest mb-2 border-b border-white/5 pb-1.5">Hóa Đơn Sân (Split 50/50)</h4>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs text-[#84939b]">Nửa tiền sân gốc: <span class="line-through decoration-red-400 ml-1"><?php echo number_format($half_base, 0, ',', '.'); ?>đ</span></p>
                                    <?php if(($myDiscount ?? 0) > 0): ?>
                                    <p class="text-[10px] font-black text-[#d8ff3e] bg-[#d8ff3e]/10 inline-block px-2 py-0.5 rounded mt-1 uppercase border border-[#d8ff3e]/20">Voucher Rank: -<?php echo $myDiscount; ?>%</p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right flex flex-col items-end">
                                    <p class="text-2xl font-black text-[#d8ff3e]"><?php echo number_format($my_final_price, 0, ',', '.'); ?>đ</p>
                                    <?php 
                                        $is_paid = ($is_host && !empty($m['host_paid'])) || (!$is_host && !empty($m['guest_paid']));
                                        if($is_paid):
                                    ?>
                                        <span class="text-[10px] font-black text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded mt-1 border border-emerald-500/20 flex items-center gap-1">✓ Đã Xác Nhận Thanh Toán</span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-black text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded mt-1 border border-amber-500/20 animate-pulse">Chờ xác nhận thanh toán</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 pt-3 border-t border-white/5">
                        <?php if($m['status'] == 'cancelled'): ?>
                            <div class="bg-red-500/10 text-red-400 py-2.5 rounded-xl text-center text-xs font-bold border border-red-500/20">
                                Trận đấu đã bị hủy. Lịch đã dọn và tiền đã hoàn vào thẻ.
                            </div>
                        <?php elseif($m['status'] == 'open' && $is_host): ?>
                            <div class="flex gap-3">
                                <button onclick="if(confirm('Bạn có chắc chắn muốn Hủy Kèo này? Sân sẽ được giải phóng và hoàn tiền.')) window.location.href='index.php?action=cancel_match&id=<?php echo $m['id']; ?>'" class="flex-1 border border-red-500/30 bg-red-500/10 text-red-400 hover:bg-red-500/20 text-center py-2.5 rounded-xl text-xs font-bold transition">
                                    Hủy Kèo (Chưa Có Khách)
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="flex gap-3">
                                <a href="index.php?action=chat&user=<?php echo $is_host ? ($m['opponent_id'] ?? $m['user_id']) : $m['user_id']; ?>" class="flex-1 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 hover:bg-cyan-500/20 text-center py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition flex justify-center items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    <span>Nhắn Tin Trực Tiếp</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-20 bg-[#121b21] rounded-2xl border border-white/8 flex flex-col items-center">
                <span class="text-4xl block mb-3">🏸</span>
                <h3 class="text-base font-black text-white">Bạn chưa tham gia Kèo nào</h3>
                <p class="mt-1 text-xs text-[#71818a] mb-5 max-w-sm">Hãy ra Bảng Tìm Đối hoặc vào Smart Match AI để tìm đối thủ ngay!</p>
                <a href="index.php?action=matchmaking" class="px-5 py-2.5 bg-[#d8ff3e] text-[#0b1116] text-xs font-black uppercase rounded-xl shadow-[3px_3px_0_#73940f] hover:translate-x-0.5 transition">Đến Bảng Cáp Kèo Ngay</a>
            </div>
        <?php endif; ?>
    </div>
</div>
