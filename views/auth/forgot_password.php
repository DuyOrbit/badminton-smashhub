<div class="max-w-md mx-auto mt-10">
    <div class="bg-[#121b21] rounded-3xl border border-white/10 p-8 shadow-none">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-black text-white">Quên M?t Kh?u</h2>
            <p class="text-white/60 text-sm mt-2">Nh?p tên dang nh?p d? khôi ph?c (M?t kh?u s? du?c reset v? 123456)</p>
        </div>
        
        <?php if(isset($_GET["error"])): ?>
            <div class="bg-red-500/20 text-red-500 p-3 rounded-xl mb-6 text-sm font-bold text-center">Tên dang nh?p không t?n t?i!</div>
        <?php endif; ?>

        <form action="index.php?action=forgot_password" method="POST" class="space-y-5">
            <div>
                <label class="block text-white/80 font-bold mb-2 text-sm">Tên dang nh?p</label>
                <input type="text" name="username" required class="w-full bg-[#0b1116] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-[#d8ff3e] focus:ring-1 focus:ring-[#d8ff3e] transition">
            </div>
            <button type="submit" class="w-full bg-[#d8ff3e] text-[#0d1715] font-black rounded-xl py-3.5 hover:bg-[#bce628] transition-colors shadow-[0_0_20px_rgba(216,255,62,0.2)]">Khôi Ph?c Ngay</button>
        </form>
        <p class="mt-6 text-center text-sm text-white/60 font-medium">Nh? l?i r?i? <a href="index.php?action=login" class="text-[#d8ff3e] font-bold hover:underline">Ðang nh?p</a></p>
    </div>
</div>
