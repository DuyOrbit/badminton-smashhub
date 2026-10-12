<div class="max-w-2xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tight text-white flex items-center gap-2">
                <span>➕</span> Thành Lập Câu Lạc Bộ Mới
            </h1>
            <p class="mt-1 text-xs text-white/60">Xây dựng hội nhóm cầu lông cố định, gắn kết thành viên và tuyển vãng lai tiện lợi.</p>
        </div>
        <a href="index.php?action=clubs" class="bg-[#121b21] border border-white/10 text-white/80 hover:text-white px-4 py-2.5 rounded-xl text-xs font-bold transition">
            Quay Lại
        </a>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="bg-red-500/20 border border-red-500/40 text-red-200 p-4 rounded-2xl text-xs font-bold">
            ⚠️ Không thể tạo CLB. Vui lòng kiểm tra lại thông tin và thử lại!
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="bg-[#121b21] rounded-3xl border border-white/10 p-6 sm:p-8 shadow-xl">
        <form action="index.php?action=club_store" method="POST" class="space-y-5">
            <!-- Tên CLB -->
            <div>
                <label for="name" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Tên Câu Lạc Bộ / Tên Nhóm *</label>
                <input type="text" name="name" id="name" required placeholder="Ví dụ: CLB Cầu Lông Tân Phú Arena (Tối 2-4-6)" 
                       class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-bold focus:border-[#d8ff3e] focus:outline-none text-sm">
            </div>

            <!-- Mô tả -->
            <div>
                <label for="description" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Mục Tiêu & Tôn Chỉ Hoạt Động</label>
                <textarea name="description" id="description" rows="3" placeholder="Giới thiệu về nhóm của bạn, tinh thần thi đấu, nội quy sinh hoạt..."
                          class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-medium focus:border-[#d8ff3e] focus:outline-none text-xs leading-relaxed"></textarea>
            </div>

            <!-- Lịch sinh hoạt định kỳ -->
            <div>
                <label for="schedule_info" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Lịch Sinh Hoạt Định Kỳ Hàng Tuần *</label>
                <input type="text" name="schedule_info" id="schedule_info" required placeholder="Ví dụ: Tối Thứ 3 - Thứ 5 - Thứ 7 (18:00 - 20:00)" 
                       class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-bold focus:border-[#d8ff3e] focus:outline-none text-xs">
            </div>

            <!-- Grid 2 Cột: Khu Vực & Cụm Sân Nhà -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Khu vực Quận -->
                <div>
                    <label for="district" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Khu Vực Quận / Huyện *</label>
                    <input type="text" name="district" id="district" required placeholder="Ví dụ: Quận Tân Phú, TP.HCM" 
                           class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-bold focus:border-[#d8ff3e] focus:outline-none text-xs">
                </div>

                <!-- Cụm Sân Nhà (Home pitch) -->
                <div>
                    <label for="pitch_id" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Cụm Sân Sinh Hoạt Cố Định</label>
                    <select name="pitch_id" id="pitch_id" class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-bold focus:border-[#d8ff3e] focus:outline-none text-xs cursor-pointer">
                        <option value="">-- Chưa cố định / Linh hoạt sân --</option>
                        <?php if(!empty($pitches)): ?>
                            <?php foreach($pitches as $p): ?>
                                <option value="<?php echo $p['id']; ?>">
                                    🏟️ <?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['district']); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Yêu Cầu Trình Độ -->
            <div>
                <label for="level_requirement" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Yêu Cầu Trình Độ Lông Thủ</label>
                <select name="level_requirement" id="level_requirement" class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-bold focus:border-[#d8ff3e] focus:outline-none text-xs cursor-pointer">
                    <option value="Mọi trình độ (Giao lưu học hỏi)">🟢 Mọi trình độ (Giao lưu thể thao vui vẻ)</option>
                    <option value="Cơ bản - Trung bình (500 - 700 Elo)">🟡 Cơ bản - Trung bình (500 - 700 Elo)</option>
                    <option value="Trung bình - Khá (700 - 900 Elo)">🟠 Trung bình - Khá (700 - 900 Elo)</option>
                    <option value="Bán Chuyên / Nâng Cao (900+ Elo)">🔴 Bán Chuyên / Nâng Cao (900+ Elo)</option>
                </select>
            </div>

            <!-- Checkbox Đang Tuyển Quân -->
            <div class="bg-[#0b1116] p-4 rounded-2xl border border-white/10 flex items-center justify-between">
                <div>
                    <strong class="text-xs text-white block">Mở Tuyển Thành Viên & Vãng Lai Ngay</strong>
                    <span class="text-[11px] text-white/50">Cho phép các lông thủ tự do trên hệ thống nhìn thấy và xin slot giao lưu.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_recruiting" value="1" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#d8ff3e]"></div>
                </label>
            </div>

            <div class="pt-4 border-t border-white/10 flex justify-end gap-3">
                <a href="index.php?action=clubs" class="px-5 py-3 rounded-xl border border-white/15 text-white/70 hover:text-white font-bold text-xs transition">Hủy</a>
                <button type="submit" class="bg-[#d8ff3e] text-[#10181e] font-black px-8 py-3 rounded-xl text-xs uppercase tracking-wider shadow-[4px_4px_0_#6d8a16] transition hover:translate-x-0.5 hover:translate-y-0.5">
                    Thành Lập CLB
                </button>
            </div>
        </form>
    </div>
</div>
