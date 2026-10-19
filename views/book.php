<div class="max-w-4xl mx-auto bg-[#121b21] rounded-3xl shadow-2xl border border-white/10 overflow-hidden transform transition-all text-white">
    <div class="md:flex">
        <!-- Pitch Summary -->
        <div class="md:w-5/12 bg-[#0d151b] p-8 md:p-10 border-r border-white/10 flex flex-col">
            <h2 class="text-2xl font-black uppercase italic text-white mb-6">Thông tin sân</h2>
            
            <div class="rounded-2xl overflow-hidden mb-6 shadow-md relative group border border-white/10">
                 <img src="<?php echo htmlspecialchars($pitchData['image_url']); ?>" alt="<?php echo htmlspecialchars($pitchData['name']); ?>" class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-105">
                 <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                     <span class="bg-[#d8ff3e] text-[#0b1116] text-xs font-black px-2.5 py-1 rounded-md uppercase tracking-wider"><?php echo htmlspecialchars($pitchData['type']); ?></span>
                 </div>
            </div>
            
            <h3 class="font-black text-xl text-white mb-2"><?php echo htmlspecialchars($pitchData['name']); ?></h3>
            <p class="text-xs text-[#84939b] mb-6 flex-grow leading-relaxed"><?php echo htmlspecialchars($pitchData['description']); ?></p>
            
            <div class="space-y-4 w-full">
                <div class="flex items-center justify-between py-3 border-t border-white/10">
                    <span class="text-[#84939b] text-xs font-medium">Đơn giá / giờ</span>
                    <span class="font-black text-lg text-[#d8ff3e]" id="pricePerHour" data-price="<?php echo $pitchData['price_per_hour']; ?>"><?php echo number_format($pitchData['price_per_hour'], 0, ',', '.'); ?>đ</span>
                </div>
                <div class="flex items-center justify-between py-3 border-t border-white/10">
                    <div class="flex flex-col">
                        <span class="text-[#84939b] text-xs font-medium">Tình trạng trống</span>
                        <?php if(isset($discount) && $discount > 0): ?>
                            <span class="text-[11px] text-cyan-400 font-bold mt-0.5">Voucher Rank (Giảm <?php echo $discount; ?>%)</span>
                        <?php endif; ?>
                    </div>
                    <span class="font-black text-[#84939b] text-xs" id="availabilityDisplay">Chờ chọn giờ</span>
                </div>
                <div class="flex items-center justify-between py-3 border-t border-white/10">
                    <span class="text-[#84939b] text-xs font-medium">Vị trí sân chọn</span>
                    <span class="font-black text-xs text-[#d8ff3e] bg-[#d8ff3e]/10 border border-[#d8ff3e]/30 px-2.5 py-1 rounded-lg" id="selectedCourtBadge">🏸 Sân số 01</span>
                </div>
                <div class="flex items-center justify-between py-4 border-t border-white/10 bg-[#d8ff3e]/10 rounded-xl px-4 mt-2 border border-[#d8ff3e]/20 relative overflow-hidden">
                    <span class="text-white text-xs font-black uppercase tracking-wider relative z-10">Tổng Dự Kiến</span>
                    <span class="font-black text-[#d8ff3e] text-2xl relative z-10" id="totalPriceDisplay">0đ</span>
                    <?php if(isset($discount) && $discount > 0): ?>
                        <div class="absolute -right-6 top-1 transform rotate-45 bg-[#ff7652] text-white text-[9px] font-black py-0.5 px-7 shadow">RANK <?php echo $discount; ?>%</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="md:w-7/12 p-8 md:p-10 bg-[#121b21]">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-black uppercase italic text-white">Chi tiết đặt lịch</h2>
                <a href="index.php" class="text-xs font-bold text-[#84939b] hover:text-white transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Quay lại</span>
                </a>
            </div>

            <form action="index.php?action=store_booking" method="POST" id="bookingForm" class="space-y-6">
                <input type="hidden" name="pitch_id" value="<?php echo $pitchData['id']; ?>">
                <input type="hidden" name="court_number" id="court_number_input" value="1">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label for="customer_name" class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Họ và Tên</label>
                        <input type="text" name="customer_name" id="customer_name" required value="<?php echo isset($currentUser) ? htmlspecialchars($currentUser['name']) : ''; ?>" placeholder="Nhập họ tên của bạn" class="block w-full border border-white/10 rounded-xl focus:border-[#d8ff3e] focus:outline-none text-sm py-3 px-4 bg-[#0b1116] text-white transition">
                    </div>

                    <div class="md:col-span-2">
                        <label for="customer_phone" class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Số Điện Thoại</label>
                        <input type="tel" name="customer_phone" id="customer_phone" required value="<?php echo isset($currentUser) ? htmlspecialchars($currentUser['phone']) : ''; ?>" placeholder="Ví dụ: 0912345678" class="block w-full border border-white/10 rounded-xl focus:border-[#d8ff3e] focus:outline-none text-sm py-3 px-4 bg-[#0b1116] text-white transition">
                    </div>

                    <div class="md:col-span-2">
                        <label for="booking_date" class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Ngày Đặt Sân</label>
                        <input type="date" name="booking_date" id="booking_date" required value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" class="block w-full border border-white/10 rounded-xl focus:border-[#d8ff3e] focus:outline-none text-sm py-3 px-4 bg-[#0b1116] text-white transition">
                    </div>

                    <!-- FEATURE 1: INTERACTIVE TIME-SLOT GRID (06:00 - 22:00) -->
                    <div class="md:col-span-2 bg-[#0b1116] border border-white/10 rounded-2xl p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <div>
                                <span class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-1.5">
                                    <span class="text-[#d8ff3e]">⏰</span> Sơ Đồ Lưới Khung Giờ Trống (06:00 – 22:00)
                                </span>
                                <p class="text-[10px] text-[#71818a] mt-0.5">Bấm chọn 1 ô (1 tiếng) hoặc bấm ô đầu & ô cuối để chọn nhiều tiếng liên tiếp</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2.5 text-[10px] font-bold">
                                <span class="inline-flex items-center gap-1 text-emerald-400"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500/30 border border-emerald-500"></span> Trống</span>
                                <span class="inline-flex items-center gap-1 text-amber-400"><span class="w-2.5 h-2.5 rounded-sm bg-amber-500/30 border border-amber-500"></span> 🔥 Giờ Vàng</span>
                                <span class="inline-flex items-center gap-1 text-[#d8ff3e]"><span class="w-2.5 h-2.5 rounded-sm bg-[#d8ff3e]"></span> Đang chọn</span>
                                <span class="inline-flex items-center gap-1 text-red-400"><span class="w-2.5 h-2.5 rounded-sm bg-red-500/30 border border-red-500/50"></span> Kín sân</span>
                            </div>
                        </div>

                        <div id="timeSlotGrid" class="grid grid-cols-4 sm:grid-cols-4 gap-2">
                            <!-- Rendered dynamically by JS -->
                        </div>
                    </div>

                    <div>
                        <label for="start_time" class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Giờ Bắt Đầu</label>
                        <input type="time" name="start_time" id="start_time" required class="block w-full border border-white/10 rounded-xl focus:border-[#d8ff3e] focus:outline-none text-sm py-3 px-4 bg-[#0b1116] text-white transition">
                    </div>
                    
                    <div>
                        <label for="end_time" class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Giờ Kết Thúc</label>
                        <input type="time" name="end_time" id="end_time" required class="block w-full border border-white/10 rounded-xl focus:border-[#d8ff3e] focus:outline-none text-sm py-3 px-4 bg-[#0b1116] text-white transition">
                    </div>

                    <!-- FEATURE 2: VISUAL BWF SUB-COURT MAP PICKER (SÂN 1 -> SÂN N) -->
                    <div class="md:col-span-2 bg-[#0b1116] border border-white/10 rounded-2xl p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <div>
                                <span class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-1.5">
                                    <span class="text-[#d8ff3e]">🏟️</span> Sơ Đồ Chọn Vị Trí Sân Con (<?php echo (int)$pitchData['quantity']; ?> Sân Tiêu Chuẩn BWF)
                                </span>
                                <p class="text-[10px] text-[#71818a] mt-0.5">Chọn mặt sân thảm bạn muốn thi đấu (tự động khóa các sân đã có người đặt)</p>
                            </div>
                            <span id="courtMapStatusHint" class="text-[10px] font-bold text-[#d8ff3e] bg-[#d8ff3e]/10 px-2.5 py-1 rounded-full border border-[#d8ff3e]/20">
                                Đang chọn: Sân số 01
                            </span>
                        </div>

                        <div id="visualCourtMap" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5" data-total-courts="<?php echo (int)$pitchData['quantity']; ?>">
                            <?php 
                            $totalSubCourts = max(1, (int)$pitchData['quantity']);
                            $courtZones = [
                                1 => 'Gần quầy lễ tân',
                                2 => 'Trung tâm nhà thi đấu',
                                3 => 'Trung tâm nhà thi đấu',
                                4 => 'Trung tâm • Trần cao',
                                5 => 'Khu vực yên tĩnh',
                                6 => 'Gần khu ghế chờ VIP'
                            ];
                            for ($c = 1; $c <= $totalSubCourts; $c++): 
                                $zoneLabel = $courtZones[$c] ?? 'Tiêu chuẩn BWF';
                            ?>
                            <button type="button" 
                                    data-court="<?php echo $c; ?>" 
                                    onclick="selectSubCourt(<?php echo $c; ?>)"
                                    class="court-map-item group relative rounded-xl p-2.5 border transition-all duration-200 text-left overflow-hidden <?php echo $c === 1 ? 'border-[#d8ff3e] bg-emerald-950/60 ring-2 ring-[#d8ff3e]/40' : 'border-emerald-500/30 bg-emerald-950/25 hover:border-emerald-400'; ?>">
                                <!-- Realistic BWF Court markings background -->
                                <div class="relative rounded-lg border border-white/25 bg-[#0e4f36]/80 p-2">
                                    <!-- Net line -->
                                    <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-white/40 pointer-events-none"></div>
                                    <!-- Center service line -->
                                    <div class="absolute inset-y-0 left-1/2 border-l border-white/20 pointer-events-none"></div>
                                    <div class="relative z-10 flex items-center justify-between">
                                        <span class="text-xs font-black tracking-wider text-white">SÂN <?php echo str_pad($c, 2, '0', STR_PAD_LEFT); ?></span>
                                        <span class="court-status-pill text-[9px] font-black uppercase px-1.5 py-0.5 rounded <?php echo $c === 1 ? 'bg-[#d8ff3e] text-[#0b1116]' : 'bg-emerald-500/20 text-emerald-300'; ?>">
                                            <?php echo $c === 1 ? '✓ ĐANG CHỌN' : 'TRỐNG'; ?>
                                        </span>
                                    </div>
                                    <div class="relative z-10 mt-2 text-[9px] text-emerald-100/80 font-medium truncate">
                                        <?php echo htmlspecialchars($zoneLabel); ?>
                                    </div>
                                </div>
                            </button>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Services Selection -->
                    <div class="md:col-span-2 border-t border-white/10 pt-5">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                                <span class="flex items-center justify-center w-6 h-6 rounded-lg bg-[#d8ff3e]/20 text-[#d8ff3e] text-xs">🏸</span>
                                <span>Dịch Vụ Tiện Ích Kèm Theo</span>
                                <span class="text-[10px] text-[#71818a] font-normal">(Tùy chọn)</span>
                            </label>
                            <span class="text-[11px] text-[#71818a]">Cộng vào hóa đơn</span>
                        </div>

                        <?php if(!empty($availableServices)): ?>
                            <div class="space-y-2.5">
                                <?php foreach($availableServices as $svc): 
                                    $sName = mb_strtolower($svc['name'], 'UTF-8');
                                    $cat = $svc['category'] ?? '';
                                    $icon = '🏸';
                                    $iconBg = 'bg-[#d8ff3e]/15 text-[#d8ff3e]';
                                    if ($cat == 'beverage' || strpos($sName, 'nước') !== false || strpos($sName, 'redbull') !== false || strpos($sName, 'revive') !== false || strpos($sName, 'pocari') !== false || strpos($sName, 'khoáng') !== false) {
                                        $icon = '🥤';
                                        $iconBg = 'bg-amber-500/15 text-amber-400';
                                    } elseif (strpos($sName, 'đan') !== false || strpos($sName, 'cước') !== false || strpos($sName, 'vợt') !== false || strpos($sName, 'quấn') !== false) {
                                        $icon = '⚡';
                                        $iconBg = 'bg-cyan-500/15 text-cyan-400';
                                    }
                                ?>
                                    <div class="service-item-card flex items-center justify-between p-3.5 rounded-xl border border-white/10 bg-[#0b1116] hover:border-[#d8ff3e]/40 transition-all duration-200">
                                        <div class="flex items-center gap-3 pr-2 min-w-0">
                                            <div class="w-10 h-10 rounded-xl <?php echo $iconBg; ?> flex items-center justify-center text-lg shrink-0">
                                                <?php echo $icon; ?>
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="font-bold text-white text-xs leading-snug break-words">
                                                    <?php echo htmlspecialchars($svc['name']); ?>
                                                </h4>
                                                <div class="flex items-center gap-1.5 mt-1">
                                                    <span class="text-xs font-black text-[#d8ff3e]">
                                                        +<?php echo number_format($svc['price'], 0, ',', '.'); ?>đ
                                                    </span>
                                                    <span class="text-[10px] text-[#71818a]">/ <?php echo htmlspecialchars($svc['unit']); ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0 ml-3">
                                            <div class="flex items-center border border-white/10 rounded-lg overflow-hidden bg-[#121b21]">
                                                <button type="button" onclick="adjustServiceQty(this, -1)" class="w-8 h-8 flex items-center justify-center text-[#84939b] hover:bg-[#0b1116]/10 active:scale-95 font-bold text-base transition select-none" title="Giảm">
                                                    −
                                                </button>
                                                <input type="number" min="0" max="20" name="services[<?php echo $svc['id']; ?>]" value="0" data-price="<?php echo $svc['price']; ?>" class="service-qty w-10 h-8 text-center font-bold text-white border-x border-white/10 bg-[#0b1116] text-xs p-0 focus:outline-none focus:ring-0">
                                                <button type="button" onclick="adjustServiceQty(this, 1)" class="w-8 h-8 flex items-center justify-center text-[#84939b] hover:bg-[#d8ff3e] hover:text-[#0b1116] active:scale-95 font-bold text-base transition select-none" title="Tăng">
                                                    +
                                                </button>
                                            </div>
                                            <span class="text-[11px] text-[#71818a] font-semibold min-w-[24px] text-left"><?php echo htmlspecialchars($svc['unit']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-4 bg-[#0b1116] rounded-xl border border-dashed border-white/10 text-center">
                                <p class="text-xs text-[#71818a] italic">Sân chưa cập nhật dịch vụ tiện ích thêm.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="md:col-span-2 border-t border-white/10 pt-4">
                        <label class="block text-xs font-black uppercase tracking-wider text-white mb-2">Phương Thức Thanh Toán</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center p-3 rounded-xl border border-white/10 cursor-pointer hover:border-[#d8ff3e]/40 transition-all bg-[#0b1116] has-[:checked]:border-[#d8ff3e] has-[:checked]:bg-[#d8ff3e]/5">
                                <input type="radio" name="payment_method" value="cash" checked class="text-[#d8ff3e] focus:ring-[#d8ff3e] h-4 w-4">
                                <div class="ml-3">
                                    <span class="block text-xs font-black text-white">💵 Tiền mặt</span>
                                    <span class="block text-[10px] text-[#71818a]">Tại quầy check-in</span>
                                </div>
                            </label>
                            <label class="flex items-center p-3 rounded-xl border border-white/10 cursor-pointer hover:border-[#d8ff3e]/40 transition-all bg-[#0b1116] has-[:checked]:border-[#d8ff3e] has-[:checked]:bg-[#d8ff3e]/5">
                                <input type="radio" name="payment_method" value="vietqr" class="text-[#d8ff3e] focus:ring-[#d8ff3e] h-4 w-4">
                                <div class="ml-3">
                                    <span class="block text-xs font-black text-white">📱 VietQR / Chuyển Khoản</span>
                                    <span class="block text-[10px] text-[#71818a]">Quét mã QR tức thì</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] font-black uppercase text-xs tracking-wider py-4 px-8 rounded-xl shadow-[3px_3px_0_#73940f] transition hover:translate-x-0.5 hover:translate-y-0.5">
                        Xác Nhận & Nhận Mã Check-in
                    </button>
                    <p class="text-[11px] text-center text-[#71818a] mt-3">Bằng việc đặt sân, bạn đồng ý với quy tắc Fair-Play của SMASHHUB.</p>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-white/10 text-center">
                <h3 class="text-xs font-black uppercase tracking-wider text-[#84939b] mb-3">Bạn đang tìm thêm người chơi hoặc đối thủ?</h3>
                <button type="button" onclick="document.getElementById('matchmakingModal').classList.remove('hidden')" class="w-full bg-[#0b1116]/5 border border-white/10 text-white hover:bg-[#0b1116]/10 font-bold py-3 px-6 rounded-xl text-xs uppercase tracking-wider transition flex justify-center items-center gap-2">
                    <svg class="w-4 h-4 text-[#d8ff3e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Đăng Tin Tìm Đối Thủ / Ghép Đôi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="p-8 md:p-10 border-t border-white/10 bg-[#0d151b]">
        <h2 class="text-xl font-black uppercase italic text-white mb-6 flex items-center">
            Đánh giá từ khách hàng 
            <span class="ml-3 bg-[#d8ff3e]/15 text-[#d8ff3e] text-[10px] font-black px-2.5 py-1 rounded-full uppercase"><?php echo count($pitchReviews); ?> đánh giá</span>
        </h2>
        
        <?php if(count($pitchReviews) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <?php foreach($pitchReviews as $r): ?>
                <div class="bg-[#121b21] p-5 rounded-2xl border border-white/10">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="font-bold text-white block text-sm"><?php echo htmlspecialchars($r['customer_name']); ?></span>
                            <span class="text-[10px] text-[#71818a]"><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></span>
                        </div>
                        <div class="flex items-center bg-yellow-50 px-2 py-1 rounded">
                            <span class="text-yellow-500 font-bold mr-1"><?php echo $r['rating']; ?></span>
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                    </div>
                    <?php if(!empty($r['comment'])): ?>
                        <p class="text-white/80 text-sm leading-relaxed mt-2"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <p class="text-white/40 font-medium">Sân này chưa có đánh giá nào. Hãy là người đầu tiên trải nghiệm và đánh giá nhé!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Matchmaking Modal -->
<div id="matchmakingModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 px-4">
    <div class="bg-[#0b1116] rounded-2xl p-6 w-full max-w-lg shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-white">🏸 Đăng Tin Cáp Kèo Ghép Sân</h3>
            <button onclick="document.getElementById('matchmakingModal').classList.add('hidden')" class="text-white/40 hover:text-white/80">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <p class="text-sm text-white/60 mb-5">Tin của bạn sẽ xuất hiện trên <a href="index.php?action=matchmaking" class="text-green-600 font-bold hover:underline">Bảng Tìm Đối</a> kèm theo điểm gợi ý tương đồng thông minh.</p>
        
        <form action="index.php?action=create_match" method="POST" class="space-y-4">
            <input type="hidden" name="pitch_id" value="<?php echo $pitchData['id']; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Ngày Chơi Cầu</label>
                    <input type="date" name="play_date" required min="<?php echo date('Y-m-d'); ?>" class="block w-full border-white/10 rounded-xl bg-white/5 border p-3">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Giờ Bắt Đầu</label>
                    <input type="time" name="start_time" required class="block w-full border-white/10 rounded-xl bg-white/5 border p-3">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Giờ Kết Thúc</label>
                    <input type="time" name="end_time" required class="block w-full border-white/10 rounded-xl bg-white/5 border p-3">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Hình Thức Thi Đấu</label>
                    <select name="format" class="block w-full border-white/10 rounded-xl bg-white/5 border p-3 text-sm">
                        <option value="Đánh Đôi" selected>Đánh Đôi (2 vs 2)</option>
                        <option value="Đánh Đơn">Đánh Đơn (1 vs 1)</option>
                        <option value="Giao lưu nhóm">Giao lưu nhóm / Tự do</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Trình Độ Yêu Cầu</label>
                    <select name="skill_level" class="block w-full border-white/10 rounded-xl bg-white/5 border p-3 text-sm">
                        <option value="1">Level 1 - Mới chơi</option>
                        <option value="2">Level 2 - Trung bình yếu</option>
                        <option value="3" selected>Level 3 - Trung bình khá</option>
                        <option value="4">Level 4 - Khá tốt</option>
                        <option value="5">Level 5 - Bán chuyên</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Khu Vực Sân</label>
                    <input type="text" name="district" value="<?php echo htmlspecialchars($pitchData['district'] ?? 'Bình Thạnh'); ?>" required class="block w-full border-white/10 rounded-xl bg-white/5 border p-3 text-sm">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Lời Nhắn</label>
                <textarea name="message" rows="2" placeholder="Ví dụ: Team mình thiếu 1 bạn đánh đôi, trình độ TB+, vui vẻ fair-play..." class="block w-full border-white/10 rounded-xl bg-white/5 border p-3 text-sm"></textarea>
            </div>
            
            <div class="pt-3 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('matchmakingModal').classList.add('hidden')" class="px-5 py-2.5 bg-white/10 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Hủy</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-green-500 text-white font-bold rounded-xl hover:from-green-700 hover:to-green-600 shadow-md transition-colors">Đăng Bài Cáp Kèo</button>
            </div>
        </form>
    </div>
</div>

<script>
    function adjustServiceQty(btn, delta) {
        const parent = btn.parentElement;
        const input = parent.querySelector('.service-qty');
        if (input) {
            let val = parseInt(input.value) || 0;
            val = Math.max(0, Math.min(20, val + delta));
            input.value = val;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const totalPriceDisplay = document.getElementById('totalPriceDisplay');
        const priceElement = document.getElementById('pricePerHour');
        const pricePerHr = parseFloat(priceElement.getAttribute('data-price'));
        const discountPercent = <?php echo isset($discount) ? $discount : 0; ?>;
        const serviceInputs = document.querySelectorAll('.service-qty');

        function updateServiceCardVisuals() {
            serviceInputs.forEach(input => {
                const card = input.closest('.service-item-card');
                if (card) {
                    const qty = parseInt(input.value) || 0;
                    if (qty > 0) {
                        card.classList.add('border-emerald-500', 'bg-emerald-50/30', 'ring-1', 'ring-emerald-500/20');
                        card.classList.remove('border-white/10', 'bg-[#0b1116]');
                    } else {
                        card.classList.remove('border-emerald-500', 'bg-emerald-50/30', 'ring-1', 'ring-emerald-500/20');
                        card.classList.add('border-white/10', 'bg-[#0b1116]');
                    }
                }
            });
        }

        function calculatePrice() {
            updateServiceCardVisuals();
            const startVal = startTimeInput.value;
            const endVal = endTimeInput.value;

            let serviceTotal = 0;
            serviceInputs.forEach(input => {
                let qty = parseInt(input.value) || 0;
                let price = parseFloat(input.getAttribute('data-price')) || 0;
                serviceTotal += (qty * price);
            });

            if (startVal && endVal) {
                const start = new Date("1970-01-01T" + startVal + "Z").getTime();
                const end = new Date("1970-01-01T" + endVal + "Z").getTime();
                
                let diff = (end - start) / (1000 * 60 * 60);
                if (diff > 0) {
                    let baseTotal = diff * pricePerHr;
                    let pitchFinal = baseTotal * ((100 - discountPercent) / 100);
                    let finalTotal = pitchFinal + serviceTotal;
                    
                    if (discountPercent > 0) {
                        totalPriceDisplay.innerHTML = `<span class="line-through text-white/40 text-sm font-medium mr-2">${new Intl.NumberFormat('vi-VN').format(baseTotal + serviceTotal)}đ</span>${new Intl.NumberFormat('vi-VN').format(finalTotal)}đ`;
                    } else {
                        totalPriceDisplay.textContent = new Intl.NumberFormat('vi-VN').format(finalTotal) + 'đ';
                    }
                } else {
                    totalPriceDisplay.textContent = new Intl.NumberFormat('vi-VN').format(serviceTotal) + 'đ';
                }
            } else if (serviceTotal > 0) {
                totalPriceDisplay.textContent = new Intl.NumberFormat('vi-VN').format(serviceTotal) + 'đ';
            } else {
                totalPriceDisplay.textContent = '0đ';
            }
        }

        serviceInputs.forEach(input => {
            input.addEventListener('input', calculatePrice);
            input.addEventListener('change', calculatePrice);
        });

        startTimeInput.addEventListener('change', calculatePrice);
        endTimeInput.addEventListener('change', calculatePrice);

        const dateInput = document.getElementById('booking_date');
        const pitchId = document.querySelector('input[name="pitch_id"]').value;
        const availabilityDisplay = document.getElementById('availabilityDisplay');
        const submitBtn = document.querySelector('button[type="submit"]');
        const timeSlotGrid = document.getElementById('timeSlotGrid');
        const courtNumberInput = document.getElementById('court_number_input');
        const selectedCourtBadge = document.getElementById('selectedCourtBadge');
        const courtMapStatusHint = document.getElementById('courtMapStatusHint');

        let dailySlotsData = [];
        let currentOccupiedCourts = [];
        let slotAnchorHour = null;

        window.selectSubCourt = function(courtNum) {
            if (currentOccupiedCourts.includes(courtNum)) {
                return; // Court is already booked in this time range
            }
            courtNumberInput.value = courtNum;
            const padded = String(courtNum).padStart(2, '0');
            if (selectedCourtBadge) selectedCourtBadge.textContent = `🏸 Sân số ${padded}`;
            if (courtMapStatusHint) courtMapStatusHint.textContent = `Đang chọn: Sân số ${padded}`;
            updateCourtMapUI(currentOccupiedCourts);
        };

        function updateCourtMapUI(occupiedCourts) {
            currentOccupiedCourts = occupiedCourts || [];
            const selectedCourt = parseInt(courtNumberInput.value) || 1;
            const totalCourts = parseInt(document.getElementById('visualCourtMap')?.getAttribute('data-total-courts')) || 1;

            // If currently selected court becomes occupied, auto-switch to first available court
            if (currentOccupiedCourts.includes(selectedCourt)) {
                for (let c = 1; c <= totalCourts; c++) {
                    if (!currentOccupiedCourts.includes(c)) {
                        courtNumberInput.value = c;
                        const padded = String(c).padStart(2, '0');
                        if (selectedCourtBadge) selectedCourtBadge.textContent = `🏸 Sân số ${padded}`;
                        if (courtMapStatusHint) courtMapStatusHint.textContent = `Đang chọn: Sân số ${padded}`;
                        break;
                    }
                }
            }

            const activeCourt = parseInt(courtNumberInput.value) || 1;
            document.querySelectorAll('.court-map-item').forEach(btn => {
                const c = parseInt(btn.getAttribute('data-court'));
                const pill = btn.querySelector('.court-status-pill');
                const isOccupied = currentOccupiedCourts.includes(c);
                const isSelected = (c === activeCourt) && !isOccupied;

                btn.disabled = isOccupied;
                btn.className = 'court-map-item group relative rounded-xl p-2.5 border transition-all duration-200 text-left overflow-hidden ';

                if (isOccupied) {
                    btn.className += 'border-red-500/40 bg-red-950/30 opacity-60 cursor-not-allowed';
                    if (pill) {
                        pill.className = 'court-status-pill text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-red-500/30 text-red-300';
                        pill.textContent = '🔒 ĐÃ ĐẶT';
                    }
                } else if (isSelected) {
                    btn.className += 'border-[#d8ff3e] bg-emerald-950/70 ring-2 ring-[#d8ff3e]/40 shadow-lg';
                    if (pill) {
                        pill.className = 'court-status-pill text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-[#d8ff3e] text-[#0b1116]';
                        pill.textContent = '✓ ĐANG CHỌN';
                    }
                } else {
                    btn.className += 'border-emerald-500/30 bg-emerald-950/25 hover:border-emerald-400 cursor-pointer';
                    if (pill) {
                        pill.className = 'court-status-pill text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300';
                        pill.textContent = 'TRỐNG';
                    }
                }
            });
        }

        function renderTimeSlotGrid() {
            if (!timeSlotGrid) return;
            const startVal = startTimeInput.value;
            const endVal = endTimeInput.value;
            const startHr = startVal ? parseInt(startVal.split(':')[0], 10) : -1;
            const endHr = endVal ? parseInt(endVal.split(':')[0], 10) : -1;

            timeSlotGrid.innerHTML = '';
            dailySlotsData.forEach(slot => {
                const isFull = slot.available <= 0;
                const isSelected = (startHr >= 0 && endHr > startHr && slot.hour >= startHr && slot.hour < endHr);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.disabled = isFull;
                btn.setAttribute('data-hour', slot.hour);

                let cls = 'slot-cell relative flex flex-col items-center justify-center p-2 rounded-xl border text-center transition-all duration-150 select-none ';
                if (isFull) {
                    cls += 'border-red-500/30 bg-red-950/20 text-red-400/60 cursor-not-allowed opacity-60';
                } else if (isSelected) {
                    cls += 'border-[#d8ff3e] bg-[#d8ff3e] text-[#0b1116] font-black shadow-[0_0_12px_rgba(216,255,62,0.35)] scale-[1.02]';
                } else if (slot.is_peak) {
                    cls += 'border-amber-500/40 bg-amber-500/10 hover:border-[#d8ff3e] text-white cursor-pointer';
                } else {
                    cls += 'border-emerald-500/30 bg-emerald-500/10 hover:border-[#d8ff3e] text-white cursor-pointer';
                }
                btn.className = cls;

                const subBadge = isFull
                    ? `<span class="text-[9px] font-bold text-red-400">Hết sân (0/${slot.total})</span>`
                    : `<span class="text-[9px] font-bold ${isSelected ? 'text-[#0b1116]/80' : (slot.is_peak ? 'text-amber-300' : 'text-emerald-300')}">${slot.is_peak ? '🔥 ' : ''}Còn ${slot.available}/${slot.total} sân</span>`;

                btn.innerHTML = `
                    <span class="text-[11px] font-black tracking-tight">${slot.start} - ${slot.end}</span>
                    ${subBadge}
                `;

                if (!isFull) {
                    btn.addEventListener('click', () => onSlotClick(slot.hour));
                }
                timeSlotGrid.appendChild(btn);
            });
        }

        function onSlotClick(clickedHour) {
            let newStartHr = clickedHour;
            let newEndHr = clickedHour + 1;

            if (slotAnchorHour !== null && clickedHour > slotAnchorHour) {
                // Check if all slots between slotAnchorHour and clickedHour are available
                let canRange = true;
                for (let h = slotAnchorHour; h <= clickedHour; h++) {
                    const s = dailySlotsData.find(x => x.hour === h);
                    if (!s || s.available <= 0) {
                        canRange = false;
                        break;
                    }
                }
                if (canRange && (clickedHour - slotAnchorHour) <= 4) {
                    newStartHr = slotAnchorHour;
                    newEndHr = clickedHour + 1;
                    slotAnchorHour = null;
                } else {
                    slotAnchorHour = clickedHour;
                }
            } else {
                slotAnchorHour = clickedHour;
            }

            startTimeInput.value = String(newStartHr).padStart(2, '0') + ':00';
            endTimeInput.value = String(newEndHr).padStart(2, '0') + ':00';
            calculatePrice();
            renderTimeSlotGrid();
            checkAvailability();
        }

        async function loadDailySlots() {
            const dateVal = dateInput.value;
            if (!dateVal) return;
            try {
                const res = await fetch(`index.php?action=api_check_availability&pitch_id=${pitchId}&date=${dateVal}`);
                const data = await res.json();
                if (data.slots) {
                    dailySlotsData = data.slots;
                    renderTimeSlotGrid();
                }
            } catch (e) {}
        }

        async function checkAvailability() {
            const dateVal = dateInput.value;
            const startVal = startTimeInput.value;
            const endVal = endTimeInput.value;

            if (dateVal && startVal && endVal) {
                // simple comparison check to ensure start < end
                let startT = new Date("1970-01-01T" + startVal + "Z").getTime();
                let endT = new Date("1970-01-01T" + endVal + "Z").getTime();
                if (endT <= startT) {
                    availabilityDisplay.textContent = 'Giờ không hợp lệ';
                    availabilityDisplay.className = 'font-black text-red-500';
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed', 'from-gray-500', 'to-gray-600');
                    return;
                }

                availabilityDisplay.textContent = 'Đang kiểm tra...';
                availabilityDisplay.className = 'font-black text-blue-500 animate-pulse';
                
                try {
                    const response = await fetch(`index.php?action=api_check_availability&pitch_id=${pitchId}&date=${dateVal}&start=${startVal}&end=${endVal}`);
                    const data = await response.json();
                    
                    availabilityDisplay.classList.remove('animate-pulse');

                    if (data.slots) {
                        dailySlotsData = data.slots;
                        renderTimeSlotGrid();
                    }
                    if (data.occupied_courts) {
                        updateCourtMapUI(data.occupied_courts);
                    }

                    if (data.error) {
                        availabilityDisplay.textContent = 'Lỗi kiểm tra';
                        availabilityDisplay.className = 'font-black text-red-500';
                    } else {
                        availabilityDisplay.textContent = `Đã đặt ${data.booked}/${data.total} sân (Còn ${data.available})`;
                        if (data.available <= 0) {
                            availabilityDisplay.className = 'font-black text-red-600';
                            submitBtn.disabled = true;
                            submitBtn.classList.add('opacity-50', 'cursor-not-allowed', 'from-gray-500', 'to-gray-600');
                            submitBtn.classList.remove('from-green-600', 'to-green-500', 'hover:from-green-700', 'hover:to-green-600');
                            submitBtn.textContent = 'Sân Đã Kín Lịch';
                        } else {
                            availabilityDisplay.className = 'font-black text-green-600';
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'from-gray-500', 'to-gray-600');
                            submitBtn.classList.add('from-green-600', 'to-green-500', 'hover:from-green-700', 'hover:to-green-600');
                            submitBtn.textContent = 'Xác Nhận & Tiến Hành Thanh Toán';
                        }
                    }
                } catch(e) {
                    availabilityDisplay.classList.remove('animate-pulse');
                    availabilityDisplay.textContent = 'Lỗi kết nối';
                    availabilityDisplay.className = 'font-black text-red-500';
                }
            } else {
                availabilityDisplay.textContent = 'Chờ chọn giờ';
                availabilityDisplay.className = 'font-black text-white/60';
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'from-gray-500', 'to-gray-600');
                submitBtn.classList.add('from-green-600', 'to-green-500', 'hover:from-green-700', 'hover:to-green-600');
                submitBtn.textContent = 'Xác Nhận & Tiến Hành Thanh Toán';
                loadDailySlots();
            }
        }
        
        dateInput.addEventListener('change', () => {
            slotAnchorHour = null;
            loadDailySlots();
            checkAvailability();
        });
        startTimeInput.addEventListener('change', () => {
            renderTimeSlotGrid();
            checkAvailability();
        });
        endTimeInput.addEventListener('change', () => {
            renderTimeSlotGrid();
            checkAvailability();
        });

        // Initial load of daily slot grid on page open
        loadDailySlots();
    });
</script>
