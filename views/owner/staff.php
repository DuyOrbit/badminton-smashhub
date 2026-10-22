<?php
// Owner Staff Management View - SMASHHUB Badminton
?>
<div class="max-w-7xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-[#121b21] p-6 rounded-3xl border border-white/10 shadow-xl">
        <div>
            <div class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#d8ff3e] mb-1">
                <span class="h-2 w-2 rounded-full bg-[#d8ff3e] animate-ping"></span>
                QUẢN LÝ TỔ CHỨC & PHÂN QUYỀN LỄ TÂN
            </div>
            <h1 class="text-3xl font-black uppercase tracking-tight text-white flex items-center gap-3">
                <span>👥 Quản Lý Nhân Viên Quầy</span>
                <span class="text-xs bg-[#d8ff3e]/20 text-[#d8ff3e] border border-[#d8ff3e]/30 px-2.5 py-1 rounded-full font-bold">STAFF ASSIGNMENT</span>
            </h1>
            <p class="mt-1 text-xs text-[#71808a]">Tạo tài khoản lễ tân & Gán nhân viên phụ trách theo từng cụm sân cụ thể.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="index.php?action=owner_schedule" class="bg-[#0b1116] border border-white/10 text-white/80 hover:text-white px-4 py-3 rounded-2xl text-xs font-bold transition">📅 Xem Lịch Ma Trận</a>
            <a href="index.php?action=owner_dashboard" class="bg-[#0b1116] border border-white/10 text-white/80 hover:text-white px-4 py-3 rounded-2xl text-xs font-bold transition">Dashboard</a>
        </div>
    </div>

    <!-- Alerts -->
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'created'): ?>
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">👤</span>
                    <div>
                        <h4 class="font-bold text-sm">ĐÃ TẠO TÀI KHOẢN NHÂN VIÊN MỚI THÀNH CÔNG!</h4>
                        <p class="text-xs text-emerald-100">Nhân viên đã có thể đăng nhập vào Bàn Trực Check-in của Cụm sân được phân công.</p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold">&times;</button>
            </div>
        <?php elseif($_GET['msg'] == 'assigned'): ?>
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🔄</span>
                    <div>
                        <h4 class="font-bold text-sm">ĐÃ CẬP NHẬT PHÂN CÔNG CỤM SÂN!</h4>
                        <p class="text-xs text-blue-100">Quyền truy cập Bàn trực đã được giới hạn đúng cụm sân phụ trách.</p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold">&times;</button>
            </div>
        <?php elseif($_GET['msg'] == 'removed'): ?>
            <div class="bg-gray-700 text-white p-4 rounded-2xl shadow flex items-center justify-between">
                <span>Đã thu hồi quyền nhân viên thành công.</span>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold">&times;</button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-4 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <strong class="font-bold text-sm">Thao tác thất bại!</strong>
                    <span class="text-xs block opacity-90">
                        <?php 
                        if($_GET['error'] == 'username_exists') echo 'Tên đăng nhập này đã được sử dụng. Vui lòng chọn tên khác.';
                        else echo 'Đã xảy ra lỗi hệ thống. Vui lòng kiểm tra lại.';
                        ?>
                    </span>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Active Staff List -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-[#121b21] rounded-3xl border border-white/10 p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-5">
                    <div>
                        <h3 class="text-lg font-black text-white uppercase tracking-tight">Danh Sách Nhân Viên Hiện Tại</h3>
                        <p class="text-xs text-[#71808a]">Tất cả lễ tân đang trực thuộc các cụm sân của bạn.</p>
                    </div>
                    <span class="bg-[#d8ff3e]/20 text-[#d8ff3e] border border-[#d8ff3e]/30 px-3 py-1 rounded-full text-xs font-black">
                        <?php echo count($staff_list ?? []); ?> Nhân viên
                    </span>
                </div>

                <?php if(!empty($staff_list)): ?>
                    <div class="space-y-3">
                        <?php foreach($staff_list as $st): ?>
                            <div class="bg-[#0b1116] border border-white/10 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-white/20 transition">
                                <div class="flex items-center gap-3.5">
                                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-[#d8ff3e] text-[#0b1116] font-black text-sm shadow-[0_0_12px_rgba(216,255,62,0.3)]">
                                        <?php 
                                        $words = explode(' ', $st['name']);
                                        echo mb_substr(end($words), 0, 1, 'UTF-8');
                                        ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-black text-white text-sm"><?php echo htmlspecialchars($st['name']); ?></h4>
                                            <span class="text-[10px] font-bold text-cyan-400 bg-cyan-500/10 border border-cyan-500/20 px-2 py-0.5 rounded">@<?php echo htmlspecialchars($st['username']); ?></span>
                                        </div>
                                        <div class="text-xs text-[#71808a] mt-0.5 flex items-center gap-2">
                                            <span>📞 <?php echo htmlspecialchars($st['phone'] ?? 'Chưa cập nhật SĐT'); ?></span>
                                            <span>•</span>
                                            <span class="text-[#d8ff3e] font-bold">
                                                🏟️ <?php echo htmlspecialchars($st['pitch_name'] ?? 'Quản lý chung tất cả sân'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Form: Update pitch assignment -->
                                <div class="flex items-center gap-2">
                                    <form action="index.php?action=owner_assign_staff" method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="staff_id" value="<?php echo $st['id']; ?>">
                                        <select name="assigned_pitch_id" onchange="this.form.submit()" class="bg-[#121b21] border border-white/15 text-white text-xs font-bold rounded-xl px-2.5 py-2 focus:border-[#d8ff3e] outline-none">
                                            <option value="">-- Tất cả cụm sân --</option>
                                            <?php foreach($pitches as $p): ?>
                                                <option value="<?php echo $p['id']; ?>" <?php echo ($st['assigned_pitch_id'] == $p['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($p['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>

                                    <a href="index.php?action=owner_remove_staff&id=<?php echo $st['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>" 
                                       onclick="return confirm('Bạn có chắc muốn thu hồi quyền nhân viên của tài khoản này?')"
                                       class="p-2 rounded-xl text-red-400/80 hover:text-red-400 hover:bg-red-500/10 border border-transparent hover:border-red-500/20 transition"
                                       title="Thu hồi quyền">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-10 text-[#71808a] bg-[#0b1116] rounded-2xl border-dashed border-2 border-white/10">
                        Chưa có nhân viên nào được phân công. Hãy tạo tài khoản nhân viên mới bên cạnh.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Col: Add New Staff Form -->
        <div class="space-y-4">
            <div class="bg-[#121b21] rounded-3xl border border-white/10 p-6 shadow-xl">
                <div class="border-b border-white/10 pb-3 mb-4">
                    <h3 class="text-base font-black text-white uppercase tracking-tight flex items-center gap-2">
                        <span>➕</span> Tạo Tài Khoản Staff Mới
                    </h3>
                    <p class="text-xs text-[#71808a]">Tự động cấp quyền nhân viên & gán sân.</p>
                </div>

                <form action="index.php?action=owner_create_staff" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Tên Đăng Nhập (Username)</label>
                        <input type="text" name="username" required placeholder="nhanvien_tanphu" class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Mật Khẩu</label>
                        <input type="password" name="password" required value="123456" class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Họ Và Tên</label>
                        <input type="text" name="name" required placeholder="Nguyễn Văn A" class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Số Điện Thoại</label>
                        <input type="text" name="phone" placeholder="0933112233" class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-[#71808a] mb-1">Cụm Sân Phụ Trách</label>
                        <select name="assigned_pitch_id" class="w-full bg-[#0b1116] border border-white/15 text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:border-[#d8ff3e] outline-none">
                            <option value="">-- Tất cả cụm sân sở hữu --</option>
                            <?php foreach($pitches as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-[#d8ff3e] text-[#0b1116] py-3 rounded-2xl font-black text-xs uppercase tracking-wider shadow-[3px_3px_0_#73940f] hover:translate-x-0.5 hover:translate-y-0.5 transition cursor-pointer">
                        ⚡ Tạo Tài Khoản Staff
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
