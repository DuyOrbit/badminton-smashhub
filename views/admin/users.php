<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white flex items-center gap-2">
                <span>👥</span> Quản Lý & Phân Quyền Người Dùng
            </h1>
            <p class="mt-1 text-white/60 text-sm">Danh sách tài khoản, điều chỉnh phân quyền (Admin / Chủ Sân / Nhân Viên / Khách) và xóa tài khoản.</p>
        </div>
        <div class="flex space-x-3">
            <a href="index.php?action=admin_create_user" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-none transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tạo Tài Khoản Mới
            </a>
            <a href="index.php?action=admin_dashboard" class="bg-[#0b1116]/10 border border-white/20 text-white/80 hover:bg-[#0b1116]/20 px-4 py-2.5 rounded-xl text-sm font-bold shadow-none transition-colors">Quay Lại Dashboard</a>
        </div>
    </div>

    <!-- Notification Alerts -->
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'created'): ?>
    <div class="bg-green-500/100/20 border-l-4 border-green-500 p-4 rounded-r-xl flex items-center">
        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="text-green-800 font-bold text-sm">Tài khoản mới đã được tạo thành công!</span>
    </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'role_updated'): ?>
    <div class="bg-blue-500/100/20 border-l-4 border-blue-500 p-4 rounded-r-xl flex items-center">
        <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="text-blue-800 font-bold text-sm">Đã cập nhật cấp quyền (Role) cho người dùng thành công!</span>
    </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl flex items-center">
        <svg class="w-5 h-5 text-amber-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="text-amber-800 font-bold text-sm">Đã xóa tài khoản người dùng khỏi hệ thống!</span>
    </div>
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'cannot_delete_self'): ?>
    <div class="bg-red-500/20 border-l-4 border-red-500 p-4 rounded-r-xl flex items-center">
        <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-red-800 font-bold text-sm">Không thể xóa tài khoản Admin bạn đang đăng nhập!</span>
    </div>
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'cannot_demote_self'): ?>
    <div class="bg-red-500/20 border-l-4 border-red-500 p-4 rounded-r-xl flex items-center">
        <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-red-800 font-bold text-sm">Bạn không thể tự hạ quyền Admin của chính mình!</span>
    </div>
    <?php endif; ?>

    <!-- Role Summary Statistics -->
    <?php
        $count_user = 0;
        $count_staff = 0;
        $count_owner = 0;
        $count_admin = 0;
        foreach($users as $item) {
            if ($item['role'] == 'user') $count_user++;
            elseif ($item['role'] == 'staff') $count_staff++;
            elseif ($item['role'] == 'owner') $count_owner++;
            elseif ($item['role'] == 'admin') $count_admin++;
        }
    ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-[#121b21] p-4 rounded-2xl border border-white/10 shadow-none flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-white/60 uppercase">Khách Hàng</p>
                <p class="text-2xl font-black text-white/90 mt-1"><?php echo $count_user; ?></p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-[#0b1116]/10 flex items-center justify-center text-lg">🏸</span>
        </div>
        <div class="bg-[#121b21] p-4 rounded-2xl border border-white/10 shadow-none flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-purple-600 uppercase">Nhân Viên Sân</p>
                <p class="text-2xl font-black text-purple-600 mt-1"><?php echo $count_staff; ?></p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-lg">🎟️</span>
        </div>
        <div class="bg-[#121b21] p-4 rounded-2xl border border-white/10 shadow-none flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-blue-600 uppercase">Chủ Sân</p>
                <p class="text-2xl font-black text-blue-600 mt-1"><?php echo $count_owner; ?></p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-blue-500/100/20 flex items-center justify-center text-lg">🏟️</span>
        </div>
        <div class="bg-[#121b21] p-4 rounded-2xl border border-white/10 shadow-none flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-red-600 uppercase">Quản Trị Viên</p>
                <p class="text-2xl font-black text-red-600 mt-1"><?php echo $count_admin; ?></p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center text-lg">👑</span>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-[#121b21] rounded-3xl shadow-none border border-white/10 overflow-hidden">
        <div class="p-5 border-b border-white/5 flex justify-between items-center bg-[#0b1116]/50">
            <h3 class="font-extrabold text-white text-base">Toàn Bộ Danh Sách Người Dùng (<?php echo count($users); ?>)</h3>
            <span class="text-xs text-white/40 font-medium">Thay đổi phân quyền trực tiếp tại từng dòng</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-[#0b1116] text-white/70 uppercase font-black text-[11px] tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5 text-left">Họ Tên & Username</th>
                        <th class="px-6 py-3.5 text-left">Số Điện Thoại</th>
                        <th class="px-6 py-3.5 text-center">Vai Trò Hiện Tại</th>
                        <th class="px-6 py-3.5 text-center">Cấp Quyền Nhanh</th>
                        <th class="px-6 py-3.5 text-center">Ngày Tham Gia</th>
                        <th class="px-6 py-3.5 text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="bg-[#121b21] divide-y divide-white/5">
                    <?php if(count($users) > 0): ?>
                        <?php foreach($users as $u): ?>
                        <tr class="hover:bg-[#0b1116]/80 transition-colors <?php echo $u['id'] == $_SESSION['user_id'] ? 'bg-amber-50/30' : ''; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-white flex items-center gap-1.5">
                                    <?php echo htmlspecialchars($u['name']); ?>
                                    <?php if($u['id'] == $_SESSION['user_id']): ?>
                                        <span class="text-[10px] font-bold bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">Bạn</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs text-white/40 font-mono">@<?php echo htmlspecialchars($u['username']); ?></div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-white/70 font-mono text-xs">
                                <?php echo htmlspecialchars($u['phone'] ?? 'Chưa cập nhật'); ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if($u['role'] == 'admin'): ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">👑 Admin</span>
                                <?php elseif($u['role'] == 'owner'): ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-800">🏟️ Chủ Sân</span>
                                <?php elseif($u['role'] == 'staff'): ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-purple-100 text-purple-800">🎟️ Nhân Viên</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-[#0b1116]/10 text-white/90">🏸 Khách Hàng</span>
                                <?php endif; ?>
                            </td>

                            <!-- Cấp Quyền Trực Tiếp -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if($u['id'] == $_SESSION['user_id']): ?>
                                    <span class="text-xs text-gray-300 italic">Không khả dụng</span>
                                <?php else: ?>
                                <form action="index.php?action=admin_update_role" method="POST" class="inline-flex items-center gap-1">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <select name="role" onchange="if(confirm('Xác nhận đổi quyền cho tài khoản @<?php echo htmlspecialchars($u['username']); ?>?')) this.form.submit();" class="border border-white/20 rounded-lg px-2.5 py-1 text-xs font-semibold text-white/80 bg-[#121b21] hover:border-gray-400 focus:ring-2 focus:ring-green-500 focus:outline-none cursor-pointer">
                                        <option value="user" <?php echo ($u['role'] == 'user') ? 'selected' : ''; ?>>Khách Hàng</option>
                                        <option value="staff" <?php echo ($u['role'] == 'staff') ? 'selected' : ''; ?>>Nhân Viên</option>
                                        <option value="owner" <?php echo ($u['role'] == 'owner') ? 'selected' : ''; ?>>Chủ Sân</option>
                                        <option value="admin" <?php echo ($u['role'] == 'admin') ? 'selected' : ''; ?>>Quản Trị Viên</option>
                                    </select>
                                </form>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-xs text-center text-white/60">
                                <?php echo date('d/m/Y', strtotime($u['created_at'])); ?>
                            </td>

                            <!-- Thao tác Xóa -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if($u['id'] == $_SESSION['user_id']): ?>
                                    <span class="text-xs text-gray-300 italic">Đang đăng nhập</span>
                                <?php else: ?>
                                    <a href="index.php?action=admin_delete_user&id=<?php echo $u['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>" 
                                       onclick="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn XÓA VĨNH VIỄN người dùng @<?php echo htmlspecialchars($u['username']); ?> không?')" 
                                       class="inline-flex items-center text-red-600 hover:text-red-800 font-bold text-xs bg-red-500/20 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Xóa
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-6 py-8 text-center text-white/60">Không có người dùng nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


