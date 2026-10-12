<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Quản Lý Sân Cầu Lông</h1>
            <p class="mt-2 text-white/60">Danh sách các sân cầu lông thuộc quyền sở hữu của bạn.</p>
        </div>
        <div class="flex space-x-3">
            <a href="index.php?action=owner_schedule" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Lịch Đặt Sân</a>
            <a href="index.php?action=owner_add_pitch" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Thêm Sân Mới</a>
            <a href="index.php?action=owner_dashboard" class="bg-[#0b1116]/10 border border-white/20 text-white/80 hover:bg-[#0b1116]/20 px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Quay Lại</a>
        </div>
    </div>

    <!-- Pitches List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if(count($pitches) > 0): ?>
            <?php foreach($pitches as $p): ?>
                <div class="bg-[#121b21] rounded-2xl border border-white/10 overflow-hidden shadow-none hover:shadow-none transition-shadow">
                    <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-white"><?php echo htmlspecialchars($p['name']); ?></h3>
                            <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded uppercase"><?php echo htmlspecialchars($p['type']); ?></span>
                        </div>
                        <p class="text-2xl font-black text-green-600 mb-4"><?php echo number_format($p['price_per_hour'], 0, ',', '.'); ?>đ <span class="text-sm font-medium text-white/60">/ giờ</span></p>
                        <div class="flex items-center gap-1.5 text-xs text-white/60 mb-3">
                            <span>📍</span>
                            <span><?php echo htmlspecialchars($p['district'] ?? ''); ?> (<?php echo htmlspecialchars($p['location']); ?>)</span>
                        </div>
                        <p class="text-white/70 text-sm mb-6 line-clamp-2"><?php echo htmlspecialchars($p['description']); ?></p>
                        <div class="flex gap-2">
                            <a href="index.php?action=owner_edit_pitch&id=<?php echo $p['id']; ?>" class="flex-1 bg-[#0b1116]/10 hover:bg-[#0b1116]/20 text-white/90 font-bold py-2 px-3 rounded-xl text-xs transition-colors text-center">Sửa</a>
                            <a href="index.php?action=book&id=<?php echo $p['id']; ?>" target="_blank" class="flex-1 bg-[#121b21] border border-white/20 hover:bg-[#0b1116] text-white/90 font-bold py-2 px-3 rounded-xl text-xs transition-colors text-center">Xem Trang</a>
                            <a href="index.php?action=owner_delete_pitch&id=<?php echo $p['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa sân này? Hành động này không thể hoàn tác!')" class="bg-red-500/20 hover:bg-red-100 text-red-600 font-bold py-2 px-3 rounded-xl text-xs transition-colors text-center border border-red-200">Xóa</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full py-12 text-center bg-[#0b1116] rounded-2xl border-2 border-dashed border-white/20">
                <p class="text-white/60 font-medium text-lg">Bạn chưa đăng bán sân nào.</p>
                <a href="index.php?action=owner_add_pitch" class="mt-4 inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-xl transition-colors">Thêm Sân Mới</a>
            </div>
        <?php endif; ?>
    </div>
</div>


