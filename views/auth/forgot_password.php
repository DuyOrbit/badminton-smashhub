<div class=max-w-md mx-auto my-10>
    <div class=bg-[#121b21] rounded-3xl border border-white/10 p-8 shadow-2xl>
        <div class=text-center mb-8>
            <div class=w-12 h-12 rounded-2xl bg-[#d8ff3e]/10 border border-[#d8ff3e]/30 flex items-center justify-center text-xl mx-auto mb-3 text-[#d8ff3e]>
                🔐
            </div>
            <h2 class=text-2xl font-black text-white>Khôi Phục Mật Khẩu</h2>
            <p class=text-white/60 text-xs mt-1.5>Xác minh danh tính bằng Tên đăng nhập & Số điện thoại chính chủ để đặt lại mật khẩu an toàn.</p>
        </div>
        
        <?php if(!empty($tempPasswordGenerated)): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 p-5 rounded-2xl mb-6 text-sm text-center leading-relaxed">
                <p class="font-bold text-base mb-1">Xác Minh Danh Tính Thành Công!</p>
                <p class="text-xs text-white/70 mb-3">Mật khẩu tạm thời mới của bạn là:</p>
                <div class="bg-[#0b1116] border border-emerald-500/40 text-volt font-mono font-black text-lg py-2.5 px-4 rounded-xl tracking-wider select-all inline-block">
                    <?php echo htmlspecialchars($tempPasswordGenerated); ?>
                </div>
                <p class="text-[11px] text-white/60 mt-3">Vui lòng sao chép mật khẩu trên để đăng nhập ngay, sau đó đổi mật khẩu trong Hồ Sơ Cá Nhân.</p>
                <a href="index.php?action=login" class="inline-block mt-4 bg-[#d8ff3e] text-[#0d1715] font-black px-6 py-2.5 rounded-xl text-xs uppercase tracking-wider hover:bg-[#bce628] transition">
                    Đăng Nhập Ngay
                </a>
            </div>
        <?php else: ?>
            <?php if(!empty($error)): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-3.5 rounded-xl mb-6 text-xs font-bold text-center leading-relaxed">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="index.php?action=forgot_password" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div>
                    <label class="block text-white/80 font-bold mb-1.5 text-xs">Tên Đăng Nhập</label>
                    <input type="text" name="username" required placeholder="Nhập username của bạn" class="w-full bg-[#0b1116] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#d8ff3e] focus:ring-1 focus:ring-[#d8ff3e] transition">
                </div>

                <div>
                    <label class="block text-white/80 font-bold mb-1.5 text-xs">Số Điện Thoại Đã Đăng Ký</label>
                    <input type="tel" name="phone" required placeholder="Ví dụ: 0912345678" class="w-full bg-[#0b1116] border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#d8ff3e] focus:ring-1 focus:ring-[#d8ff3e] transition">
                </div>

                <button type="submit" class="w-full bg-[#d8ff3e] text-[#0d1715] font-black rounded-xl py-3.5 text-xs uppercase tracking-wider hover:bg-[#bce628] transition-all shadow-[0_0_20px_rgba(216,255,62,0.2)] mt-2">
                    Xác Thực & Cấp Mật Khẩu Tạm Thời
                </button>
            </form>
        <?php endif; ?>
        <p class=mt-6 text-center text-xs text-white/60 font-medium>
            Đã nhớ lại mật khẩu? <a href=index.php?action=login class=text-[#d8ff3e] font-bold hover:underline>Đăng nhập</a>
        </p>
    </div>
</div>
