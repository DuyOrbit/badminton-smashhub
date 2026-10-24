<?php
require_once 'models/Ranking.php';
$ranking = new Ranking((new Database())->getConnection());
?>
<div class="max-w-6xl mx-auto py-8">
    <div class="mb-8 border-b border-white/10 pb-5">
        <h2 class="text-3xl font-extrabold text-white tracking-tight">Duyệt Kết Quả Kèo Thi Đấu</h2>
        <p class="mt-2 text-white/60 text-lg">Xác nhận tỉ số sau trận đấu căng thẳng. Thu xếp hóa đơn Split Bill (chia đôi) của 2 đội!</p>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'payment_approved'): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-xl shadow-none flex items-center">
            <svg class="w-5 h-5 text-emerald-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <p class="text-emerald-800 font-bold">Xác nhận thu tiền mặt và điểm danh Check-in thành công!</p>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'noshow_confirmed'): ?>
        <div class="bg-red-950/70 border-l-4 border-red-500 p-4 mb-6 rounded-xl flex items-center text-white">
            <span class="text-2xl mr-3">🚫</span>
            <p class="font-bold text-sm">Đã xác nhận bùng kèo (No-Show)! Hệ thống đã phạt trừ 30 điểm uy tín, trừ 50 Elo và tạm khóa tìm trận của người vi phạm. Quyền lợi người không vi phạm đã được bảo vệ.</p>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'noshow_rejected'): ?>
        <div class="bg-gray-800 border-l-4 border-gray-500 p-4 mb-6 rounded-xl flex items-center text-white">
            <span class="text-2xl mr-3">ℹ️</span>
            <p class="font-bold text-sm">Đã bác bỏ báo cáo vắng mặt. Trận đấu vẫn duy trì bình thường.</p>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'reported'): ?>
        <div class="bg-green-500/20 border-l-4 border-green-500 p-4 mb-6 rounded-xl shadow-none">
            <p class="text-green-800 font-bold">Cập nhật điểm số Elo thành công cho 2 đội!</p>
        </div>
    <?php endif; ?>

    <div class="space-y-6 flex flex-col items-center">
        <?php if(count($matches) > 0): ?>
            <?php foreach($matches as $m): 
                $start = strtotime($m['start_time']);
                $end = strtotime($m['end_time']);
                $hours = ($end - $start) / 3600;
                $half_base = ($hours * $m['price_per_hour']) / 2;
                
                $hostDiscount = $ranking->getActiveDiscount($m['user_id']);
                $guestDiscount = $ranking->getActiveDiscount($m['opponent_id']);
                
                $hostFinalPrice = $half_base * ((100 - $hostDiscount) / 100);
                $guestFinalPrice = $half_base * ((100 - $guestDiscount) / 100);
            ?>
            <div class="bg-[#121b21] rounded-2xl shadow-none border border-white/5 overflow-hidden w-full relative">
                <!-- Status Badge -->
                <div class="absolute top-4 right-4 z-10 flex items-center gap-1.5">
                    <?php if(!empty($m['noshow_status']) && $m['noshow_status'] == 'pending'): ?>
                        <span class="bg-red-600 text-white text-xs font-black uppercase px-3 py-1.5 rounded-md border border-red-400 shadow-md animate-pulse">⚠️ Khiếu Nại Bùng Kèo</span>
                    <?php elseif(!empty($m['noshow_status']) && $m['noshow_status'] == 'confirmed_noshow'): ?>
                        <span class="bg-red-950 text-red-400 text-xs font-black uppercase px-3 py-1.5 rounded-md border border-red-800">🚫 Đã Xử Phạt Bùng Kèo</span>
                    <?php endif; ?>

                    <?php if($m['status'] == 'cancelled'): ?>
                        <span class="bg-red-100 text-red-800 text-xs font-black uppercase px-3 py-1.5 rounded-md border border-red-200 shadow-none">Đã Hủy Bỏ</span>
                    <?php elseif($m['match_result'] == 'pending'): ?>
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-black uppercase px-3 py-1.5 rounded-md border border-yellow-200 shadow-none">Chờ Chấm Điểm</span>
                    <?php else: ?>
                        <span class="bg-[#0b1116]/10 text-white/70 text-xs font-bold px-3 py-1.5 rounded-md border border-white/10">Đã Hoàn Tất</span>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col lg:flex-row">
                    <!-- Billing & Match Info -->
                    <div class="lg:w-1/3 bg-[#0b1116] p-6 border-r border-white/5 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-black text-white mb-1 text-blue-600"><?php echo htmlspecialchars($m['pitch_name']); ?></h3>
                            <p class="text-sm font-semibold text-white/70 mb-4"><?php echo date('d/m/Y', strtotime($m['play_date'])); ?> | <?php echo substr($m['start_time'],0,5); ?> - <?php echo substr($m['end_time'],0,5); ?></p>
                            
                            <div class="bg-[#121b21] rounded-xl p-4 border border-white/10 shadow-none space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-white/5">
                                    <h4 class="text-xs font-black text-white/60 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        Hóa Đơn Thu Tiền Mặt
                                    </h4>
                                    <span class="text-[11px] font-semibold text-white/40">Chia 50/50</span>
                                </div>

                                <!-- Chủ Nhà -->
                                <div class="p-3 rounded-xl border <?php echo $m['host_paid'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-amber-50/50 border-amber-200'; ?> space-y-2">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-black text-white/90 uppercase">Chủ Nhà (Đội 1)</span>
                                            <?php if($hostDiscount>0): ?>
                                                <span class="text-[10px] font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded uppercase">Giảm <?php echo $hostDiscount; ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-sm font-black text-white"><?php echo number_format($hostFinalPrice, 0, ',', '.'); ?>đ</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] pt-1 border-t <?php echo $m['host_paid'] ? 'border-emerald-100' : 'border-amber-100'; ?>">
                                        <span class="text-white/60 font-medium">Điểm danh Check-in:</span>
                                        <?php if(!empty($m['host_checkin'])): ?>
                                            <span class="text-emerald-400 font-bold inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                Đã Check-in <?php echo !empty($m['host_checkin_time']) ? '(' . date('H:i', strtotime($m['host_checkin_time'])) . ')' : ''; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-amber-400 font-semibold inline-flex items-center gap-1">
                                                ⏳ Chưa Check-in
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center justify-between pt-1 border-t <?php echo $m['host_paid'] ? 'border-emerald-100' : 'border-amber-100'; ?>">
                                        <?php if($m['host_paid']): ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-[#121b21] px-2 py-0.5 rounded-md border border-emerald-200 shadow-xs">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                Đã Thu Tiền (50%)
                                            </span>
                                            <span class="text-[10px] text-emerald-600 font-semibold">Hoàn tất</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-[#121b21] px-2 py-0.5 rounded-md border border-amber-300 shadow-xs">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                Chưa Thu Tiền
                                            </span>
                                            <a href="index.php?action=owner_approve_host_payment&id=<?php echo $m['id']; ?>" 
                                               onclick="return confirm('Xác nhận đã nhận đủ <?php echo number_format($hostFinalPrice, 0, ',', '.'); ?>đ tiền mặt từ Chủ Nhà?');"
                                               class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white px-2.5 py-1 rounded-lg shadow-none hover:shadow transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                Duyệt Thu Tiền
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Đội Khách -->
                                <div class="p-3 rounded-xl border <?php echo $m['guest_paid'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-amber-50/50 border-amber-200'; ?> space-y-2">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-black text-white/90 uppercase">Đội Khách (Đội 2)</span>
                                            <?php if($guestDiscount>0): ?>
                                                <span class="text-[10px] font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded uppercase">Giảm <?php echo $guestDiscount; ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-sm font-black text-white"><?php echo number_format($guestFinalPrice, 0, ',', '.'); ?>đ</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] pt-1 border-t <?php echo $m['guest_paid'] ? 'border-emerald-100' : 'border-amber-100'; ?>">
                                        <span class="text-white/60 font-medium">Điểm danh Check-in:</span>
                                        <?php if(!empty($m['guest_checkin'])): ?>
                                            <span class="text-emerald-400 font-bold inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                Đã Check-in <?php echo !empty($m['guest_checkin_time']) ? '(' . date('H:i', strtotime($m['guest_checkin_time'])) . ')' : ''; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-amber-400 font-semibold inline-flex items-center gap-1">
                                                ⏳ Chưa Check-in
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center justify-between pt-1 border-t <?php echo $m['guest_paid'] ? 'border-emerald-100' : 'border-amber-100'; ?>">
                                        <?php if($m['guest_paid']): ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-[#121b21] px-2 py-0.5 rounded-md border border-emerald-200 shadow-xs">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                Đã Thu Tiền (50%)
                                            </span>
                                            <span class="text-[10px] text-emerald-600 font-semibold">Hoàn tất</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-[#121b21] px-2 py-0.5 rounded-md border border-amber-300 shadow-xs">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                Chưa Thu Tiền
                                            </span>
                                            <a href="index.php?action=owner_approve_guest_payment&id=<?php echo $m['id']; ?>" 
                                               onclick="return confirm('Xác nhận đã nhận đủ <?php echo number_format($guestFinalPrice, 0, ',', '.'); ?>đ tiền mặt từ Đội Khách?');"
                                               class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white px-2.5 py-1 rounded-lg shadow-none hover:shadow transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                Duyệt Thu Tiền
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center pt-2 px-1 border-t border-white/5">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-white uppercase">TỔNG THU (Thực nhận)</span>
                                        <span class="text-[10px] text-white/40">Sau khi trừ giảm giá</span>
                                    </div>
                                    <span class="font-black text-lg text-emerald-600"><?php echo number_format($hostFinalPrice + $guestFinalPrice, 0, ',', '.'); ?>đ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Versus & Reporting -->
                    <div class="lg:w-2/3 p-6 flex flex-col justify-center">
                        <?php if(!empty($m['noshow_status']) && $m['noshow_status'] == 'pending'): ?>
                            <div class="mb-6 p-4 bg-red-950/70 border-2 border-red-500 rounded-2xl text-white shadow-lg">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xl">⚖️</span>
                                    <h4 class="font-black text-sm text-red-300 uppercase tracking-wide">Trọng Tài Chủ Sân: Có Báo Cáo Đối Thủ Bùng Kèo (No-Show)</h4>
                                </div>
                                <p class="text-xs text-white/90 mb-2">
                                    Người báo: <strong class="text-white"><?php echo ($m['noshow_reported_by'] == $m['user_id']) ? htmlspecialchars($m['host_name']) : htmlspecialchars($m['guest_name']); ?></strong> 
                                    tố giác đối thủ 
                                    <strong class="text-red-300 underline font-black"><?php echo ($m['noshow_target_user_id'] == $m['user_id']) ? htmlspecialchars($m['host_name']) : htmlspecialchars($m['guest_name']); ?></strong> 
                                    vắng mặt không đến sân thi đấu.
                                </p>
                                <?php if(!empty($m['noshow_reason'])): ?>
                                    <div class="text-xs bg-black/50 p-2.5 rounded-lg border border-red-500/30 mb-3 text-red-200 italic">
                                        "<?php echo htmlspecialchars($m['noshow_reason']); ?>"
                                    </div>
                                <?php endif; ?>
                                <p class="text-[11px] text-white/60 mb-3">Vui lòng kiểm tra thực tế tại sân. Nếu đối phương thực sự không đến, bấm "Xác Nhận Bùng Kèo" để phạt -30 Điểm Uy Tín, -50 Elo và giải phóng sân.</p>
                                <div class="flex flex-wrap gap-2">
                                    <a href="index.php?action=owner_confirm_noshow&match_id=<?php echo $m['id']; ?>&decision=confirm" 
                                       onclick="return confirm('XÁC NHẬN BÙNG KÈO:\nBạn đã kiểm tra thực tế và xác nhận người này không đến sân?\nHệ thống sẽ:\n- Trừ 30 điểm Uy Tín người vi phạm\n- Trừ 50 điểm Elo xếp hạng\n- Tạm khóa tìm trận\n- Bảo lưu quyền lợi cho người có mặt\n- Hủy trận và giải phóng sân.');"
                                       class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white font-black px-4 py-2 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                                        ⚖️ Xác Nhận Bùng Kèo (Xử Phạt)
                                    </a>
                                    <a href="index.php?action=owner_confirm_noshow&match_id=<?php echo $m['id']; ?>&decision=reject"
                                       onclick="return confirm('BÁC BỎ BÁO CÁO:\nBạn xác nhận người này có đến sân hoặc hai bên vẫn thi đấu bình thường?');"
                                       class="inline-flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white font-bold px-3 py-2 rounded-xl text-xs transition-all border border-white/10">
                                        ✕ Bác Bỏ Báo Cáo
                                    </a>
                                </div>
                            </div>
                        <?php elseif(!empty($m['noshow_status']) && $m['noshow_status'] == 'confirmed_noshow'): ?>
                            <div class="mb-6 p-3.5 bg-red-950/40 border border-red-500/40 rounded-xl text-xs text-red-300 flex items-center gap-2">
                                <span class="text-lg">🚫</span>
                                <span>Kèo đấu này đã được xác nhận bùng kèo (No-Show). Người vi phạm đã bị trừ 30 điểm uy tín, -50 Elo và bị xử phạt theo đúng quy định.</span>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center justify-between mb-8">
                            <!-- Host -->
                            <div class="flex flex-col items-center flex-1">
                                <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-blue-100 mb-2 shadow-none">
                                    <?php if(!empty($m['host_avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($m['host_avatar']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <svg class="w-full h-full text-white/40 p-2" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <?php endif; ?>
                                </div>
                                <p class="text-lg font-bold text-white flex flex-col items-center">
                                    <?php echo htmlspecialchars($m['host_name']); ?>
                                    <span class="text-[10px] text-white bg-blue-600 uppercase px-2 py-0.5 rounded-full mt-1 shadow-none">Chủ Nhà</span>
                                </p>
                            </div>

                            <!-- VS Graphic -->
                            <div class="flex flex-col items-center px-4">
                                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-gray-800 to-gray-900 text-white flex items-center justify-center font-black text-2xl italic shadow-lg ring-4 ring-gray-100 z-10">
                                    VS
                                </div>
                            </div>

                            <!-- Guest -->
                            <div class="flex flex-col items-center flex-1">
                                <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-red-100 mb-2 shadow-none">
                                    <?php if(!empty($m['guest_avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($m['guest_avatar']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <svg class="w-full h-full text-white/40 p-2" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <?php endif; ?>
                                </div>
                                <p class="text-lg font-bold text-white flex flex-col items-center">
                                    <?php echo htmlspecialchars($m['guest_name']); ?>
                                    <span class="text-[10px] text-white bg-red-600 uppercase px-2 py-0.5 rounded-full mt-1 shadow-none">Đội Khách</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <?php if($m['status'] == 'cancelled'): ?>
                                <div class="bg-red-500/20 text-red-700 rounded-xl p-4 text-center border border-red-200">
                                    <p class="font-bold text-sm">Trận đấu đã bị HỦY</p>
                                    <p class="text-xs mt-1">Sân đã được tự động trả lại và giải phóng.</p>
                                </div>
                            <?php elseif($m['match_result'] == 'pending'): ?>
                                <form action="index.php?action=owner_report_result" method="POST" class="bg-[#0b1116] p-4 rounded-xl border border-white/10 mb-3">
                                    <input type="hidden" name="match_id" value="<?php echo $m['id']; ?>">
                                    <div class="flex items-center gap-2 mb-3 justify-center">
                                        <svg class="w-5 h-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p class="font-bold text-sm text-white/80 uppercase tracking-wide">Xác Nhận Kết Quả Trận Đấu</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" name="match_result" value="host_won" onclick="return confirm('Chắc chắn CHỦ NHÀ Thắng?');" class="flex-1 bg-[#121b21] text-blue-700 hover:bg-blue-600 hover:text-white font-black py-3 rounded-lg border-2 border-blue-200 transition-all focus:ring-4 focus:ring-blue-100 uppercase tracking-wider text-[11px]">Chủ Thắng</button>
                                        <button type="submit" name="match_result" value="draw" onclick="return confirm('Chắc chắn kết quả HÒA NHAU?');" class="flex-1 bg-[#121b21] text-white/80 hover:bg-gray-600 hover:text-white font-black py-3 rounded-lg border-2 border-white/20 transition-all focus:ring-4 focus:ring-gray-100 uppercase tracking-wider text-[11px]">Hòa Nhau</button>
                                        <button type="submit" name="match_result" value="guest_won" onclick="return confirm('Chắc chắn ĐỘI KHÁCH Thắng?');" class="flex-1 bg-[#121b21] text-red-700 hover:bg-red-600 hover:text-white font-black py-3 rounded-lg border-2 border-red-200 transition-all focus:ring-4 focus:ring-red-100 uppercase tracking-wider text-[11px]">Khách Thắng</button>
                                    </div>
                                </form>
                                <button onclick="if(confirm('Chú ý: Hành động này HỦY sạch tiền sử trận đấu, giải phóng Sân và báo hoàn tiền cho cả 2 Đội. Bạn có chắc không?')) window.location.href='index.php?action=owner_cancel_match&id=<?php echo $m['id']; ?>'" class="w-full bg-[#121b21] border border-red-300 text-red-600 hover:bg-red-500/20 text-center py-2.5 rounded-xl text-sm font-bold transition-all">
                                    Hủy Trận Đấu Này (Sự Cố/Bùng Kèo)
                                </button>
                            <?php else: ?>
                                <div class="bg-gray-800 text-white rounded-xl p-4 text-center border-b-4 border-yellow-500">
                                    <p class="font-bold uppercase tracking-widest text-xs mb-1 text-white/40">Kết Quả Đã Được Bạn Duyệt</p>
                                    <p class="text-xl font-black text-yellow-400">
                                        <?php 
                                            if($m['match_result'] == 'host_won') echo "🏆 ĐỘI CHỦ NHÀ GIÀNH CHIẾN THẮNG (+30 Pts)";
                                            elseif($m['match_result'] == 'guest_won') echo "🏆 ĐỘI KHÁCH CƯỚP ĐIỂM THÀNH CÔNG (+30 Pts)";
                                            else echo "🤝 HAI ĐỘI BẤT PHÂN THẮNG BẠI (+10 Pts)";
                                        ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-20 bg-[#121b21] rounded-3xl border border-white/5 w-full shadow-none flex flex-col items-center">
                <svg class="h-24 w-24 text-gray-200 mb-6 drop-shadow-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3m0 0l3-3m-3 3V8" /></svg>
                <h3 class="text-2xl font-black text-white/90 tracking-tight">Sân bạn chưa có trận Thi đấu Cáp Kèo nào</h3>
                <p class="mt-3 max-w-md text-white/60 font-medium">Các trận đấu Cáp Kèo mang tính leo xếp hạng sẽ tự động xuất hiện ở báo cáo này khi hai đội đã thỏa thuận thi đấu tại chảo lửa của bạn.</p>
            </div>
        <?php endif; ?>
    </div>
</div>



