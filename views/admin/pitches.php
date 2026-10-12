<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Quản Lý Toàn Bộ Sân</h1>
            <p class="mt-2 text-white/60">Quản lý và kiểm duyệt các sân đang hoạt động trên hệ thống.</p>
        </div>
        <a href="index.php?action=admin_dashboard" class="bg-[#0b1116]/10 border border-white/20 text-white/80 hover:bg-[#0b1116]/20 px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Quay Lại</a>
    </div>

    <?php if(isset($_GET["msg"]) && $_GET["msg"] == "updated"): ?>
        <div class="bg-green-500/20 border-l-4 border-green-500 p-4 rounded-xl mb-6 flex items-center">
            <span class="text-green-400 font-bold text-sm">Đã cập nhật trạng thái sân thành công!</span>
        </div>
    <?php endif; ?>

    <div class="bg-[#121b21] rounded-2xl shadow-none border border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
                <thead class="bg-[#0b1116]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Tên Sân</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Chủ Sân</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Giá/Giờ</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white/60 uppercase tracking-wider">Trạng Thái</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white/60 uppercase tracking-wider">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="bg-[#121b21] divide-y divide-white/5">
                    <?php if(count($pitches) > 0): ?>
                        <?php foreach($pitches as $p): ?>
                        <tr class="hover:bg-[#0b1116] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-white"><?php echo htmlspecialchars($p["name"]); ?></div>
                                <div class="text-xs text-white/60 mt-1"><?php echo htmlspecialchars($p["type"]); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white/80">
                                User ID: <?php echo $p["owner_id"]; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-green-500">
                                <?php echo number_format($p["price_per_hour"], 0, ",", "."); ?>đ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if(isset($p["status"]) && $p["status"] == "locked"): ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-500/20 text-red-400">Đã Khóa</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-500/20 text-green-400">Hoạt Động</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <?php if(isset($p["status"]) && $p["status"] == "locked"): ?>
                                    <a href="index.php?action=admin_toggle_pitch&id=<?php echo $p["id"]; ?>&status=active" onclick="return confirm('Mở khóa sân này để khách có thể đặt lại?');" class="text-green-400 hover:text-green-300 font-bold bg-green-500/10 px-3 py-1.5 rounded-lg mr-2">Mở Khóa</a>
                                <?php else: ?>
                                    <a href="index.php?action=admin_toggle_pitch&id=<?php echo $p["id"]; ?>&status=locked" onclick="return confirm('Khóa sân này? Sân sẽ bị ẩn khỏi trang đặt sân.');" class="text-red-400 hover:text-red-300 font-bold bg-red-500/10 px-3 py-1.5 rounded-lg mr-2">Khóa Sân</a>
                                <?php endif; ?>
                                <a href="index.php?action=book&id=<?php echo $p["id"]; ?>" target="_blank" class="text-blue-400 hover:text-blue-300 font-bold">Xem</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-white/60">Chưa có sân nào trên hệ thống.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
