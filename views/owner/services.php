<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white flex items-center gap-2">
                <span>🏸</span> Quản Lý Dịch Vụ & Tiện Ích Sân
            </h1>
            <p class="mt-1 text-white/60 text-sm">Quản lý các mặt hàng phụ trợ: nước uống, ống cầu, cho thuê vợt và đan cước.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button type="button" onclick="document.getElementById('addServiceModal').classList.remove('hidden')" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm shadow-none transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Thêm Dịch Vụ Mới
            </button>
            <a href="index.php?action=owner_dashboard" class="bg-[#0b1116]/10 border border-white/20 text-white/80 hover:bg-[#0b1116]/20 px-4 py-2.5 rounded-xl text-sm font-bold shadow-none transition-colors">Dashboard</a>
        </div>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'created'): ?>
        <div class="bg-green-500/100/20 border-l-4 border-green-500 p-4 rounded-r-xl">
            <span class="text-green-800 font-bold text-sm">Đã thêm dịch vụ mới thành công!</span>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="bg-blue-500/100/20 border-l-4 border-blue-500 p-4 rounded-r-xl">
            <span class="text-blue-800 font-bold text-sm">Đã xóa dịch vụ thành công!</span>
        </div>
    <?php endif; ?>

    <!-- Services Table -->
    <div class="bg-[#121b21] rounded-3xl border border-white/10 shadow-none overflow-hidden">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="font-extrabold text-white text-lg">Bảng Dịch Vụ Áp Dụng Cho Khách Đặt Sân</h3>
            <span class="text-xs text-white/40 font-bold"><?php echo count($services); ?> mục</span>
        </div>

        <?php if(count($services) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-white/70">
                    <thead class="bg-[#0b1116] text-white/80 uppercase font-black text-[11px] tracking-wider border-b border-white/10">
                        <tr>
                            <th class="py-3.5 px-6">Tên Dịch Vụ / Hàng Hóa</th>
                            <th class="py-3.5 px-6">Danh Mục</th>
                            <th class="py-3.5 px-6">Đơn Giá</th>
                            <th class="py-3.5 px-6">Đơn Vị Tính</th>
                            <th class="py-3.5 px-6">Tồn Kho</th>
                            <th class="py-3.5 px-6 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach($services as $s): ?>
                        <tr class="hover:bg-[#0b1116]/80 transition-colors">
                            <td class="py-4 px-6 font-bold text-white">
                                <?php echo htmlspecialchars($s['name']); ?>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?php 
                                    $catStyles = [
                                        'beverage' => 'bg-blue-100 text-blue-800',
                                        'shuttlecock' => 'bg-green-100 text-green-800',
                                        'rental' => 'bg-purple-100 text-purple-800',
                                        'stringing' => 'bg-amber-100 text-amber-800'
                                    ];
                                    echo $catStyles[$s['category']] ?? 'bg-[#0b1116]/10 text-white/90';
                                ?>">
                                    <?php 
                                        $catNames = [
                                            'beverage' => '🥤 Nước Uống',
                                            'shuttlecock' => '🏸 Ống Cầu',
                                            'rental' => '🏸 Thuê Vợt',
                                            'stringing' => '🧵 Căng Cước'
                                        ];
                                        echo $catNames[$s['category']] ?? $s['category'];
                                    ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 font-black text-green-400">
                                <?php echo number_format($s['price'], 0, ',', '.'); ?>đ
                            </td>
                            <td class="py-4 px-6 font-medium text-white/80">
                                <?php echo htmlspecialchars($s['unit']); ?>
                            </td>
                            <td class="py-4 px-6 font-mono text-white/90 font-bold">
                                <?php echo (int)$s['stock']; ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <a href="index.php?action=owner_delete_service&id=<?php echo $s['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>" onclick="return confirm('Bạn có chắc muốn xóa dịch vụ này không?')" class="text-red-500 hover:text-red-700 font-bold text-xs">
                                    Xóa
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <p class="text-white/40 font-medium">Chưa có dịch vụ nào được cấu hình.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Add Service -->
<div id="addServiceModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 px-4">
    <div class="bg-[#121b21] rounded-3xl p-6 md:p-8 w-full max-w-lg shadow-none">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-white">Thêm Dịch Vụ Mới</h3>
            <button onclick="document.getElementById('addServiceModal').classList.add('hidden')" class="text-white/40 hover:text-white/70">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="index.php?action=owner_store_service" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-white/80 mb-1">Tên Dịch Vụ / Hàng Hóa</label>
                <input type="text" name="name" required placeholder="Ví dụ: Vợt Yonex Astrox 88D (Thuê theo buổi)" class="w-full border-white/10 border p-3 rounded-xl bg-[#0b1116] focus:bg-[#121b21] text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-white/80 mb-1">Danh Mục</label>
                    <select name="category" class="w-full border-white/10 border p-3 rounded-xl bg-[#0b1116] focus:bg-[#121b21] text-sm">
                        <option value="beverage">🥤 Nước Uống</option>
                        <option value="shuttlecock">🏸 Ống Cầu Lông</option>
                        <option value="rental">🏸 Cho Thuê Vợt</option>
                        <option value="stringing">🧵 Căng Cước Vợt</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-white/80 mb-1">Đơn Vị Tính</label>
                    <input type="text" name="unit" required placeholder="Chai / Ống / Cây / Buổi" class="w-full border-white/10 border p-3 rounded-xl bg-[#0b1116] focus:bg-[#121b21] text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-white/80 mb-1">Đơn Giá (VNĐ)</label>
                    <input type="number" name="price" required min="1000" step="1000" placeholder="30000" class="w-full border-white/10 border p-3 rounded-xl bg-[#0b1116] focus:bg-[#121b21] text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-white/80 mb-1">Số Lượng Tồn</label>
                    <input type="number" name="stock" value="50" min="0" class="w-full border-white/10 border p-3 rounded-xl bg-[#0b1116] focus:bg-[#121b21] text-sm">
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('addServiceModal').classList.add('hidden')" class="px-5 py-2.5 bg-[#0b1116]/10 text-white/80 font-bold rounded-xl text-sm hover:bg-[#0b1116]/20 transition-colors">Hủy</button>
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white font-bold rounded-xl text-sm hover:bg-green-700 shadow-none transition-colors">Lưu Dịch Vụ</button>
            </div>
        </form>
    </div>
</div>


