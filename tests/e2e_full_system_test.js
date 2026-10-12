const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

async function runFullSystemTest() {
    console.log('================================================================');
    console.log('🏸 SMASHHUB BADMINTON - HỆ THỐNG KIỂM THỬ TỰ ĐỘNG TOÀN DIỆN E2E');
    console.log('   Nền tảng: Playwright + Google Chrome (Headless)');
    console.log('================================================================\n');

    const screenshotDir = path.join(__dirname, 'screenshots');
    if (!fs.existsSync(screenshotDir)) {
        fs.mkdirSync(screenshotDir, { recursive: true });
    }

    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true
    });

    const context = await browser.newContext({
        viewport: { width: 1366, height: 768 }
    });

    const page = await context.newPage();
    const results = [];

    function assert(name, condition, details = '') {
        if (condition) {
            console.log(`  ✅ [PASS] ${name} ${details ? '-> ' + details : ''}`);
            results.push({ name, status: 'PASS', details });
        } else {
            console.error(`  ❌ [FAIL] ${name} ${details ? '-> ' + details : ''}`);
            results.push({ name, status: 'FAIL', details });
        }
    }

    try {
        // ==============================================================
        // 1. FLOW 1: TRẢI NGHIỆM KHÁCH VÃNG LAI (HOMEPAGE GUEST)
        // ==============================================================
        console.log('📌 FLOW 1: Trải nghiệm Trang Chủ dành cho Khách Vãng Lai');
        await page.goto('http://localhost/badminton/index.php?action=logout');
        await page.goto('http://localhost/badminton/index.php');
        await page.waitForLoadState('networkidle');

        const title = await page.title();
        assert('Tiêu đề trang chủ chuẩn thương hiệu', title.includes('SMASHHUB') || title.includes('Trang Chủ'), title);

        const guestHeadline = await page.textContent('h1');
        assert('Banner Hero chào đón người chơi', guestHeadline.toLowerCase().includes('rally'), guestHeadline.trim());

        const hasAIFeature = await page.locator('text=Ghép Kèo AI').count();
        const hasBwfFeature = await page.locator('text=BWF').count();
        const hasFairPlay = await page.locator('text=Fair-Play').count();
        assert('Hiển thị bộ 3 Card tính năng cốt lõi cho khách', hasAIFeature > 0 && hasBwfFeature > 0 && hasFairPlay > 0);

        await page.screenshot({ path: path.join(screenshotDir, '01_guest_homepage.png') });

        // ==============================================================
        // 2. FLOW 2: ĐĂNG NHẬP VẬN ĐỘNG VIÊN & PROFILE GAMIFICATION
        // ==============================================================
        console.log('\n📌 FLOW 2: Xác thực Vận Động Viên (User: user / 123456)');
        await page.goto('http://localhost/badminton/index.php?action=login');
        await page.fill('input[name="username"]', 'user');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1000);

        const currentUrl = page.url();
        assert('Đăng nhập thành công', !currentUrl.includes('action=login'), currentUrl);

        const hasEloBadge = await page.locator('text=ELO RATING').count();
        const hasHotStreak = await page.locator('text=Hot streak').count();
        assert('Bảng điều khiển cá nhân hóa: Điểm Elo & Chuỗi thắng (Streak)', hasEloBadge > 0 && hasHotStreak > 0);

        await page.screenshot({ path: path.join(screenshotDir, '02_logged_in_dashboard.png') });

        // ==============================================================
        // 3. FLOW 3: SMART MATCHMAKING (GHÉP CẶP THÔNG MINH BẰNG ELO)
        // ==============================================================
        console.log('\n📌 FLOW 3: Tính năng Smart Matchmaking (Ghép cặp AI theo Elo)');
        await page.goto('http://localhost/badminton/index.php?action=smart_match');
        await page.waitForLoadState('networkidle');

        const matchBody = await page.textContent('body');
        assert('Truy cập trang Smart Matchmaking', matchBody.includes('Bậc Xếp Hạng') || matchBody.includes('Cơ chế ghép cặp'));

        const hasMap = await page.locator('#map').count();
        const hasDistrictSelect = await page.locator('select[name="district"]').count();
        assert('Tích hợp hệ thống phân tích bán kính địa lý & GPS', (hasMap > 0 || hasDistrictSelect > 0));

        await page.screenshot({ path: path.join(screenshotDir, '03_smart_matchmaking.png') });

        // ==============================================================
        // 4. FLOW 4: ĐẶT SÂN & MÔ PHỎNG THANH TOÁN VIETQR SANDBOX
        // ==============================================================
        console.log('\n📌 FLOW 4: Luồng Đặt Sân & Mô Phỏng Thanh Toán VietQR Sandbox');
        await page.goto('http://localhost/badminton/index.php?action=book&id=1');
        await page.waitForLoadState('networkidle');

        // Điền form đặt sân
        const futureDate = new Date();
        futureDate.setDate(futureDate.getDate() + 3);
        const bookingDateStr = futureDate.toISOString().split('T')[0];

        await page.fill('input[name="customer_name"]', 'Nguyễn Anh Tuấn (Test E2E)');
        await page.fill('input[name="customer_phone"]', '0912345678');
        await page.fill('input[name="booking_date"]', bookingDateStr);
        await page.fill('input[name="start_time"]', '18:00');
        await page.fill('input[name="end_time"]', '19:00');

        // Chọn phương thức VietQR
        const vietQrRadio = page.locator('input[name="payment_method"][value="vietqr"]');
        if (await vietQrRadio.count() > 0) {
            await vietQrRadio.check();
        }

        // Bấm Submit đặt sân
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1500);

        const successUrl = page.url();
        assert('Đặt sân thành công -> Chuyển đến trang hóa đơn', successUrl.includes('action=success'), successUrl);

        // Kiểm tra phần tử VietQR và nút Sandbox
        const hasCheckinCode = await page.locator('text=CKIN').count();
        const hasQrContainer = await page.locator('#vietqr-container').count();
        const hasSandboxBtn = await page.locator('#btn-simulate-pay').count();
        assert('Tạo mã Check-in và hiển thị QR ngân hàng hợp lệ', hasCheckinCode > 0 && hasQrContainer > 0);
        assert('Nút mô phỏng thanh toán VietQR Sandbox sẵn sàng', hasSandboxBtn > 0);

        // BẤM NÚT MÔ PHỎNG THANH TOÁN SANDBOX
        if (hasSandboxBtn > 0) {
            // Lắng nghe alert hộp thoại
            page.once('dialog', async dialog => {
                await dialog.accept();
            });

            await page.click('#btn-simulate-pay');
            await page.waitForTimeout(1500);

            const badgeText = await page.locator('#payment-status-badge').textContent();
            assert('Trạng thái thanh toán tự động chuyển sang PAID', badgeText.includes('PAID') || badgeText.includes('ĐÃ THANH TOÁN'), badgeText.trim());
        }

        await page.screenshot({ path: path.join(screenshotDir, '04_vietqr_sandbox_paid.png') });

        // ==============================================================
        // 5. FLOW 5: CỘNG ĐỒNG CÂU LẠC BỘ & HỘI NHÓM (BADMINTON CLUBS)
        // ==============================================================
        console.log('\n📌 FLOW 5: Phân hệ Cộng Đồng Câu Lạc Bộ (Badminton Clubs)');
        await page.goto('http://localhost/badminton/index.php?action=clubs');
        await page.waitForLoadState('networkidle');

        const clubHeader = await page.locator('h1').textContent();
        assert('Trang chủ Cộng đồng Câu Lạc Bộ hiển thị chuẩn xác', clubHeader.includes('Câu Lạc Bộ'), clubHeader.trim());

        const clubDetailLink = await page.locator('a[href*="action=club_detail"]').first();
        if (await clubDetailLink.count() > 0) {
            await clubDetailLink.click();
            await page.waitForTimeout(800);

            const clubPageText = await page.textContent('body');
            assert('Xem chi tiết CLB, Lịch sinh hoạt & Bảng tin thảo luận', clubPageText.includes('Bảng Tin') || clubPageText.includes('Sinh Hoạt'));
            await page.screenshot({ path: path.join(screenshotDir, '05_club_detail_feed.png') });
        }

        // ==============================================================
        // 6. FLOW 6: BÀN TRỰC TIẾP ĐÓN & CHECK-IN CỦA THU NGÂN (STAFF)
        // ==============================================================
        console.log('\n📌 FLOW 6: Phân hệ Bàn Trực Tiếp Đón & Check-in (Thu ngân: nhanvien_tanphu)');
        await page.goto('http://localhost/badminton/index.php?action=logout');
        await page.goto('http://localhost/badminton/index.php?action=login');
        await page.fill('input[name="username"]', 'nhanvien_tanphu');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1000);

        await page.goto('http://localhost/badminton/index.php?action=staff_dashboard');
        await page.waitForLoadState('networkidle');

        const staffBodyText = await page.textContent('body');
        assert('Thu ngân đăng nhập & vào Staff Dashboard thành công', staffBodyText.includes('Check-in') || staffBodyText.includes('STAFF'));
        assert('Phân quyền phạm vi cụm sân (Sân Tân Phú Arena)', staffBodyText.includes('Tân Phú Arena') || staffBodyText.includes('CKIN'));

        await page.screenshot({ path: path.join(screenshotDir, '06_staff_checkin_dashboard.png') });

        // ==============================================================
        // 7. FLOW 7: BẢNG ĐIỀU KHIỂN CHỦ SÂN VỚI BIỂU ĐỒ CHART.JS
        // ==============================================================
        console.log('\n📌 FLOW 7: Bảng Điều Khiển Chủ Sân & Biểu Đồ Thống Kê (chusan_demo)');
        await page.goto('http://localhost/badminton/index.php?action=logout');
        await page.goto('http://localhost/badminton/index.php?action=login');
        await page.fill('input[name="username"]', 'chusan_demo');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1000);

        await page.goto('http://localhost/badminton/index.php?action=owner_dashboard');
        await page.waitForLoadState('networkidle');

        const ownerBodyText = await page.textContent('body');
        assert('Chủ sân truy cập Dashboard quản lý doanh thu', ownerBodyText.includes('Dashboard Chủ Sân'));

        // Kiểm tra sự hiện diện của 2 biểu đồ Chart.js
        const hasWeeklyRevChart = await page.locator('#weeklyRevenueChart').count();
        const hasOccupancyChart = await page.locator('#occupancyHourChart').count();
        assert('Biểu đồ 1: Xu hướng Doanh thu 7 ngày (Line Chart)', hasWeeklyRevChart > 0);
        assert('Biểu đồ 2: Phân bổ công suất khung Giờ Vàng (Doughnut Chart)', hasOccupancyChart > 0);

        await page.screenshot({ path: path.join(screenshotDir, '07_owner_analytics_chartjs.png') });

        // ==============================================================
        // TỔNG KẾT
        // ==============================================================
        console.log('\n================================================================');
        console.log('🎉 TỔNG KẾT KIỂM THỬ TỰ ĐỘNG PLAYWRIGHT E2E:');
        const passCount = results.filter(r => r.status === 'PASS').length;
        const failCount = results.filter(r => r.status === 'FAIL').length;
        console.log(`   - Tổng số kịch bản kiểm tra: ${results.length}`);
        console.log(`   - Đạt tiêu chuẩn (PASS)   : ${passCount}`);
        console.log(`   - Thất bại (FAIL)         : ${failCount}`);
        console.log(`   - Tỷ lệ thành công        : ${((passCount / results.length) * 100).toFixed(1)}%`);
        console.log('================================================================');

    } catch (err) {
        console.error('❌ Lỗi ngoại lệ trong quá trình test:', err);
    } finally {
        await browser.close();
    }
}

runFullSystemTest();
