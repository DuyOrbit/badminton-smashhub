<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Quản Lý Đánh Giá</h1>
            <p class="mt-2 text-white/60">Xem và theo dõi phản hồi từ khách hàng về các sân của bạn.</p>
        </div>
        <div class="flex space-x-3">
            <a href="index.php?action=owner_dashboard" class="bg-[#121b21] border border-white/20 text-white/80 hover:bg-[#0b1116] px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Quay lại Dashboard</a>
        </div>
    </div>

    <div class="bg-[#121b21] rounded-2xl shadow-none border border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
                <thead class="bg-[#0b1116]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Khách Hàng</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Sân Bóng</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Đánh Giá</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Nhận Xét</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Thời Gian</th>
                    </tr>
                </thead>
                <tbody class="bg-[#121b21] divide-y divide-white/5">
                    <?php if(count($reviews) > 0): ?>
                        <?php foreach($reviews as $r): ?>
                        <tr class="hover:bg-[#0b1116] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-white"><?php echo htmlspecialchars($r['customer_name']); ?></div>
                                <div class="text-xs text-white/60 text-green-600 font-bold mt-1">Lịch đá: <?php echo date('d/m/Y', strtotime($r['booking_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-white"><?php echo htmlspecialchars($r['pitch_name']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-yellow-50 px-2 py-1 rounded text-yellow-500 font-bold">
                                    <?php echo $r['rating']; ?>
                                    <svg class="w-3 h-3 inline-block ml-0.5 mt--1 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-white/70 max-w-sm" title="<?php echo htmlspecialchars($r['comment']); ?>">
                                    <?php echo htmlspecialchars($r['comment']) ?: '<span class="italic text-white/40">Không có nhận xét</span>'; ?>
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs text-white/60"><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-white/60">Chưa có đánh giá nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


