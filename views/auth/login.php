<!-- SMASHHUB Auth: Login -->
<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 w-full">
    <div class="max-w-md w-full mx-auto bg-[#121b21] border border-white/10 rounded-2xl shadow-2xl overflow-hidden p-8 sm:p-10">
        <div class="text-center mb-8">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-[#d8ff3e] text-[#0b1116] shadow-[3px_3px_0_#73940f] mb-3">
                <span class="text-2xl font-black italic">S</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-white">Đăng Nhập</h2>
            <p class="mt-1 text-xs text-[#84939b]">Chào mừng bạn trở lại với hệ thống SMASHHUB</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-3.5 mb-6 text-red-400 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'reset_success'): ?>
            <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-3.5 mb-6 text-green-400 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Mật khẩu mới của bạn là 123456. Vui lòng đăng nhập lại.</span>
            </div>
        <?php endif; ?>

        <form action="index.php?action=login" method="POST" class="space-y-5">
            <div>
                <label for="username" class="block text-[11px] font-black uppercase tracking-wider text-[#84939b] mb-1.5 ml-1">Tên đăng nhập</label>
                <input id="username" name="username" type="text" required class="appearance-none block w-full px-4 py-3 bg-[#0b1116] border border-white/10 rounded-xl text-white placeholder-[#50606a] focus:outline-none focus:border-[#d8ff3e] text-sm font-medium transition" placeholder="Nhập username của bạn">
            </div>

            <div>
                <div class="flex justify-between items-end mb-1.5 ml-1">
                    <label for="password" class="block text-[11px] font-black uppercase tracking-wider text-[#84939b]">Mật khẩu</label>
                    <a href="index.php?action=forgot_password" class="text-[10px] text-blue-400 hover:underline">Quên mật khẩu?</a>
                </div>
                <input id="password" name="password" type="password" required class="appearance-none block w-full px-4 py-3 bg-[#0b1116] border border-white/10 rounded-xl text-white placeholder-[#50606a] focus:outline-none focus:border-[#d8ff3e] text-sm font-medium transition" placeholder="••••••••">
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-3.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider text-[#0b1116] bg-[#d8ff3e] hover:bg-[#c6f030] shadow-[3px_3px_0_#73940f] hover:translate-x-0.5 hover:translate-y-0.5 transition">
                    Đăng Nhập Ngay
                </button>
            </div>
            
            <div class="text-center text-xs text-[#84939b] pt-2">
                Chưa có tài khoản? <a href="index.php?action=register" class="font-bold text-[#d8ff3e] hover:underline">Đăng ký thành viên mới</a>
            </div>
        </form>
    </div>
</div>

