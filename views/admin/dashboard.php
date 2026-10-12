<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-extrabold text-white">Dashboard Quản Trị Hệ Thống</h1>
        <div class="flex space-x-3">
            <a href="index.php?action=admin_users" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Quản Lý Người Dùng</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-[#121b21] rounded-2xl p-6 shadow-none border border-white/5 flex items-center">
            <div class="p-4 rounded-xl bg-blue-500/20 text-blue-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-white/60 font-medium">Tổng Người Dùng</p>
                <p class="text-2xl font-black text-white"><?php echo number_format($total_users); ?></p>
            </div>
        </div>
        
        <div class="bg-[#121b21] rounded-2xl p-6 shadow-none border border-white/5 flex items-center">
            <div class="p-4 rounded-xl bg-green-500/20 text-green-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <div>
                <p class="text-sm text-white/60 font-medium">Tổng Số Sân</p>
                <p class="text-2xl font-black text-white"><?php echo number_format($total_pitches); ?></p>
            </div>
        </div>

        <div class="bg-[#121b21] rounded-2xl p-6 shadow-none border border-white/5 flex items-center">
            <div class="p-4 rounded-xl bg-purple-50 text-purple-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <div>
                <p class="text-sm text-white/60 font-medium">Tổng Lượt Đặt</p>
                <p class="text-2xl font-black text-white"><?php echo number_format($total_bookings); ?></p>
            </div>
        </div>
    </div>

    <div class="bg-[#121b21] rounded-2xl shadow-none border border-white/5 overflow-hidden">
        <div class="px-6 py-5 border-b border-white/5 bg-[#0b1116]">
            <h3 class="text-lg leading-6 font-bold text-white">Lượt đặt sân gần đây (Toàn hệ thống)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
                <thead class="bg-[#121b21]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Khách Hàng</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Sân</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Thời Gian Đá</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white/60 uppercase tracking-wider">Tổng Tiền</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white/60 uppercase tracking-wider">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="bg-[#121b21] divide-y divide-white/5">
                    <?php if(count($bookings) > 0): ?>
                        <?php foreach($bookings as $b): ?>
                        <tr class="hover:bg-[#0b1116] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-white"><?php echo htmlspecialchars($b['customer_name']); ?></div>
                                <div class="text-sm text-white/60"><?php echo htmlspecialchars($b['customer_phone']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($b['pitch_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-white"><?php echo date('d/m/Y', strtotime($b['booking_date'])); ?></div>
                                <div class="text-xs text-white/60"><?php echo substr($b['start_time'],0,5); ?> - <?php echo substr($b['end_time'],0,5); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-green-600">
                                <?php echo number_format($b['total_price'], 0, ',', '.'); ?>đ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if($b['status'] == 'confirmed'): ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">Đã Xác Nhận</span>
                                <?php elseif($b['status'] == 'pending'): ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800">Đang Chờ</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">Đã Hủy</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-white/60">Chưa có lượt đặt sân nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



