<!-- SMASHHUB User Bookings: Lịch Sử Đặt Sân -->
<div class="max-w-7xl mx-auto space-y-6 text-white animate-fade-in">
    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 pb-4 border-b border-white/10">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="rounded bg-[#d8ff3e] text-[#0b1116] px-2 py-0.5 text-[9px] font-black uppercase shadow-sm">BOOKINGS</span>
                <span class="text-xs text-[#71818a] font-bold">Lịch đấu của bạn</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black uppercase italic tracking-tight text-white">Lịch Sử Đặt Sân</h1>
            <p class="mt-1 text-xs sm:text-sm text-[#7c8b93]">Theo dõi các đơn đặt sân và mã check-in thi đấu của bạn.</p>
        </div>
        <a href="index.php#pitches" class="rounded-xl bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] px-4 py-2.5 text-xs font-black uppercase tracking-wider shadow-[3px_3px_0_#73940f] transition flex items-center gap-1.5 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Đặt Thêm Sân</span>
        </a>
    </div>

    <div class="bg-[#121b21] rounded-2xl shadow-xl border border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/5">
                <thead class="bg-[#0d151b] border-b border-white/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-[#71818a] uppercase tracking-wider">Thông Tin Sân</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-[#71818a] uppercase tracking-wider">Lịch Thi Đấu</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-[#71818a] uppercase tracking-wider">Tổng Chi Phí</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-[#71818a] uppercase tracking-wider">Người Đại Diện</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-[#71818a] uppercase tracking-wider">Mã Check-in / Trạng Thái</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-[#71818a] uppercase tracking-wider">Đánh Giá</th>
                    </tr>
                </thead>
                <tbody class="bg-[#121b21] divide-y divide-white/5">
                    <?php if(count($bookings) > 0): ?>
                        <?php foreach($bookings as $b): ?>
                        <tr class="hover:bg-[#0b1116]/5 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-black text-white"><?php echo htmlspecialchars($b['pitch_name']); ?></div>
                                <div class="text-[10px] text-[#71818a] mt-0.5">Mã đơn: #<?php echo str_pad($b['id'], 6, '0', STR_PAD_LEFT); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-xs text-white"><?php echo date('d/m/Y', strtotime($b['booking_date'])); ?></div>
                                <div class="text-xs text-[#d8ff3e] font-black mt-0.5"><?php echo substr($b['start_time'],0,5); ?> – <?php echo substr($b['end_time'],0,5); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-base font-black text-[#d8ff3e]">
                                <?php echo number_format($b['total_price'], 0, ',', '.'); ?>đ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-bold text-white"><?php echo htmlspecialchars($b['customer_name']); ?></div>
                                <div class="text-[10px] text-[#71818a]"><?php echo htmlspecialchars($b['customer_phone']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex flex-col gap-1 items-center">
                                    <?php if(!empty($b['checkin_code'])): ?>
                                        <span class="font-mono text-xs font-black text-amber-400 bg-amber-400/10 border border-amber-400/20 px-2 py-0.5 rounded">
                                            <?php echo htmlspecialchars($b['checkin_code']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if($b['status'] == 'confirmed'): ?>
                                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase rounded-full bg-[#d8ff3e]/10 text-[#d8ff3e] border border-[#d8ff3e]/20">Đã Xác Nhận</span>
                                    <?php elseif($b['status'] == 'pending'): ?>
                                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">Chờ Duyệt</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase rounded-full bg-red-500/10 text-red-400 border border-red-500/20">Đã Hủy</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if($b['status'] == 'confirmed'): ?>
                                    <?php if(isset($userReviews[$b['id']])): ?>
                                        <?php $ur = $userReviews[$b['id']]; ?>
                                        <div class="flex flex-col gap-1.5 items-center">
                                            <span class="text-[#ffd45b] font-black text-xs"><?php echo $ur['rating']; ?> ★</span>
                                            <div class="flex gap-2 text-[10px]">
                                                <button onclick="openEditModal(<?php echo $ur['id']; ?>, <?php echo $ur['rating']; ?>, '<?php echo htmlspecialchars(addslashes($ur['comment'])); ?>')" class="text-cyan-400 hover:underline font-bold">Sửa</button>
                                                <a href="index.php?action=delete_review&id=<?php echo $ur['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?');" class="text-red-400 hover:underline font-bold">Xóa</a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <button onclick="openReviewModal(<?php echo $b['id']; ?>)" class="text-[11px] font-black uppercase text-[#0b1116] bg-[#d8ff3e] hover:bg-[#c6f030] px-3 py-1.5 rounded-lg shadow-sm transition">
                                            Đánh Giá
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-[10px] text-[#71818a]">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-[#71818a] text-xs">
                                <span class="text-3xl block mb-2">🏸</span>
                                <p class="font-bold text-white">Bạn chưa có đơn đặt sân nào</p>
                                <p class="mt-1">Hãy bắt đầu đặt sân ngay hôm nay!</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-[#121b21] border border-white/10 rounded-2xl p-6 w-full max-w-md shadow-2xl text-white">
        <h3 class="text-lg font-black uppercase italic tracking-tight mb-4">Đánh giá sân cầu lông</h3>
        <form action="index.php?action=store_review" method="POST" class="space-y-4">
            <input type="hidden" name="booking_id" id="review_booking_id">
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5">Mức độ hài lòng</label>
                <select name="rating" class="w-full bg-[#0b1116] border border-white/10 text-white text-xs p-3 rounded-xl focus:border-[#d8ff3e] focus:outline-none" required>
                    <option value="5">5 Sao (Rất tốt - Sân đẹp, đèn sáng)</option>
                    <option value="4">4 Sao (Khá tốt)</option>
                    <option value="3">3 Sao (Bình thường)</option>
                    <option value="2">2 Sao (Dưới kỳ vọng)</option>
                    <option value="1">1 Sao (Tệ)</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5">Nhận xét chi tiết</label>
                <textarea name="comment" rows="3" class="w-full bg-[#0b1116] border border-white/10 text-white text-xs p-3 rounded-xl focus:border-[#d8ff3e] focus:outline-none" placeholder="Cảm nhận về độ bám thảm, ánh sáng, máy lạnh, nhân viên..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('reviewModal')" class="px-4 py-2 bg-[#0b1116]/5 hover:bg-[#0b1116]/10 text-white rounded-xl text-xs font-bold transition">Hủy bỏ</button>
                <button type="submit" class="px-4 py-2 bg-[#d8ff3e] text-[#0b1116] rounded-xl text-xs font-black uppercase tracking-wider shadow-[2px_2px_0_#73940f] transition">Gửi Đánh Giá</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-[#121b21] border border-white/10 rounded-2xl p-6 w-full max-w-md shadow-2xl text-white">
        <h3 class="text-lg font-black uppercase italic tracking-tight mb-4">Chỉnh sửa đánh giá</h3>
        <form action="index.php?action=update_review" method="POST" class="space-y-4">
            <input type="hidden" name="review_id" id="edit_review_id">
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5">Mức độ hài lòng</label>
                <select name="rating" id="edit_rating" class="w-full bg-[#0b1116] border border-white/10 text-white text-xs p-3 rounded-xl focus:border-[#d8ff3e] focus:outline-none" required>
                    <option value="5">5 Sao (Rất tốt)</option>
                    <option value="4">4 Sao (Khá tốt)</option>
                    <option value="3">3 Sao (Bình thường)</option>
                    <option value="2">2 Sao (Dưới kỳ vọng)</option>
                    <option value="1">1 Sao (Tệ)</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5">Nhận xét chi tiết</label>
                <textarea name="comment" id="edit_comment" rows="3" class="w-full bg-[#0b1116] border border-white/10 text-white text-xs p-3 rounded-xl focus:border-[#d8ff3e] focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 bg-[#0b1116]/5 hover:bg-[#0b1116]/10 text-white rounded-xl text-xs font-bold transition">Hủy bỏ</button>
                <button type="submit" class="px-4 py-2 bg-[#d8ff3e] text-[#0b1116] rounded-xl text-xs font-black uppercase tracking-wider shadow-[2px_2px_0_#73940f] transition">Cập Nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReviewModal(bookingId) {
    document.getElementById('review_booking_id').value = bookingId;
    document.getElementById('reviewModal').classList.remove('hidden');
}
function openEditModal(reviewId, rating, comment) {
    document.getElementById('edit_review_id').value = reviewId;
    document.getElementById('edit_rating').value = rating;
    document.getElementById('edit_comment').value = comment;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>
