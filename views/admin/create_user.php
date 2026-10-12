<div class="max-w-2xl mx-auto animate-fade-in">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
                <span>➕</span> Tạo Tài Khoản Mới
            </h1>
            <p class="mt-2 text-white/60 text-sm">Thêm tài khoản Quản trị viên, Chủ Sân, Thu Ngân hoặc Khách hàng vào hệ thống.</p>
        </div>
        <a href="index.php?action=admin_users" class="bg-[#0b1116]/10 border border-white/20 text-white/80 hover:bg-[#0b1116]/20 px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Quay Lại</a>
    </div>

    <?php if(isset($_GET['error'])): ?>
    <div class="mb-6 bg-red-500/20 border border-red-500/40 text-red-200 px-4 py-3 rounded-xl flex items-center gap-2" role="alert">
        <span class="text-lg">⚠️</span>
        <span class="text-sm font-bold">
            <?php 
                if($_GET['error'] == 'exists') echo 'Tên đăng nhập này đã tồn tại trong hệ thống. Vui lòng chọn tên khác.';
                else echo 'Không thể tạo tài khoản. Vui lòng kiểm tra lại thông tin.';
            ?>
        </span>
    </div>
    <?php endif; ?>

    <div class="bg-[#121b21] rounded-3xl shadow-xl border border-white/10 overflow-hidden">
        <form action="index.php?action=admin_store_user" method="POST" class="p-8">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="space-y-6">
                <!-- Họ và tên -->
                <div>
                    <label for="name" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Họ và tên</label>
                    <input type="text" name="name" id="name" required class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm" placeholder="Ví dụ: Nguyễn Văn A">
                </div>

                <!-- Username & Mật khẩu -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Tên đăng nhập (Username)</label>
                        <input type="text" name="username" id="username" required class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm" placeholder="thungan_tanphu">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Mật khẩu</label>
                        <input type="password" name="password" id="password" required value="123456" class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm" placeholder="Nhập mật khẩu">
                    </div>
                </div>

                <!-- Số điện thoại -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Số điện thoại</label>
                    <input type="tel" name="phone" id="phone" class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm" placeholder="Ví dụ: 0912345678">
                </div>

                <!-- Phân quyền (Role) -->
                <div>
                    <label for="role" class="block text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Phân quyền tài khoản</label>
                    <select name="role" id="role" onchange="toggleStaffAssignment(this.value)" class="w-full px-4 py-3 bg-[#0b1116] rounded-xl border border-white/15 text-white font-bold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm">
                        <option value="user">Khách Hàng (User)</option>
                        <option value="staff">🎟️ Nhân Viên Thu Ngân / Lễ Tân (Staff)</option>
                        <option value="owner">🏟️ Chủ Sân (Owner)</option>
                        <option value="admin">👑 Quản Trị Viên (Admin)</option>
                    </select>
                </div>

                <!-- KHỐI PHÂN CÔNG SÂN CHO THU NGÂN (HIỂN THỊ KHI ROLE = STAFF) -->
                <div id="staffAssignmentSection" class="hidden bg-[#0b1116] p-5 rounded-2xl border border-[#d8ff3e]/40 space-y-4 animate-fade-in">
                    <div class="flex items-center gap-2 text-xs font-black uppercase text-[#d8ff3e] tracking-wider border-b border-white/10 pb-2.5">
                        <span>🎟️</span> PHÂN CÔNG THU NGÂN VÀO CỤM SÂN & CHỦ SÂN
                    </div>
                    <p class="text-xs text-white/60">Chỉ định cụm sân cụ thể mà nhân viên này phụ trách check-in và thu tiền tại quầy.</p>

                    <!-- Chọn Chủ Sân -->
                    <div>
                        <label for="owner_id" class="block text-xs font-bold text-gray-300 mb-1.5">1. Chủ Sân Quản Lý (Owner):</label>
                        <select name="owner_id" id="owner_id" onchange="onOwnerChange(this.value)" class="w-full px-3.5 py-2.5 bg-[#121b21] rounded-xl border border-white/20 text-white font-bold text-xs focus:ring-2 focus:ring-[#d8ff3e] transition-colors">
                            <option value="">-- Chọn Chủ Sân (Hoặc để tự động theo cụm sân) --</option>
                            <?php if(!empty($owners)): ?>
                                <?php foreach($owners as $ow): ?>
                                    <option value="<?php echo $ow['id']; ?>">
                                        👤 <?php echo htmlspecialchars($ow['name']); ?> (@<?php echo htmlspecialchars($ow['username']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Chọn Cụm Sân -->
                    <div>
                        <label for="assigned_pitch_id" class="block text-xs font-bold text-gray-300 mb-1.5">2. Cụm Sân Cầu Lông Phụ Trách (Pitch):</label>
                        <select name="assigned_pitch_id" id="assigned_pitch_id" onchange="onPitchChange(this)" class="w-full px-3.5 py-2.5 bg-[#121b21] rounded-xl border border-white/20 text-white font-bold text-xs focus:ring-2 focus:ring-[#d8ff3e] transition-colors">
                            <option value="" data-owner="">-- Tất cả cụm sân của chủ (Quản lý chung) --</option>
                            <?php if(!empty($pitches)): ?>
                                <?php foreach($pitches as $p): ?>
                                    <option value="<?php echo $p['id']; ?>" data-owner="<?php echo $p['owner_id']; ?>" class="pitch-option">
                                        🏟️ <?php echo htmlspecialchars($p['name']); ?> <?php echo !empty($p['owner_name']) ? ' [Chủ: ' . htmlspecialchars($p['owner_name']) . ']' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="text-[11px] text-[#d8ff3e]/80 bg-[#d8ff3e]/10 p-2.5 rounded-xl border border-[#d8ff3e]/20">
                        💡 <strong>Lưu ý:</strong> Khi được gán vào 1 Cụm Sân cụ thể, Thu ngân chỉ có thể check-in các đơn đặt lịch của đúng Cụm Sân đó trên Bàn Trực.
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-white/10 flex justify-end gap-3">
                <a href="index.php?action=admin_users" class="px-6 py-3 rounded-xl border border-white/15 text-white/70 hover:text-white font-bold text-sm transition-colors">Hủy</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-black py-3 px-8 rounded-xl shadow-lg transition-all text-sm">Tạo Tài Khoản</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleStaffAssignment(role) {
    const box = document.getElementById('staffAssignmentSection');
    if (role === 'staff') {
        box.classList.remove('hidden');
    } else {
        box.classList.add('hidden');
    }
}

function onOwnerChange(ownerId) {
    const pitchSelect = document.getElementById('assigned_pitch_id');
    const options = pitchSelect.querySelectorAll('.pitch-option');
    
    options.forEach(opt => {
        const optOwner = opt.getAttribute('data-owner');
        if (!ownerId || optOwner === ownerId) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });

    // Nếu option đang chọn bị ẩn thì reset về trống
    const currentOpt = pitchSelect.selectedOptions[0];
    if (currentOpt && currentOpt.style.display === 'none') {
        pitchSelect.value = '';
    }
}

function onPitchChange(selectEl) {
    const selectedOpt = selectEl.selectedOptions[0];
    if (selectedOpt) {
        const ownerId = selectedOpt.getAttribute('data-owner');
        const ownerSelect = document.getElementById('owner_id');
        if (ownerId && (!ownerSelect.value || ownerSelect.value !== ownerId)) {
            ownerSelect.value = ownerId;
        }
    }
}
</script>
