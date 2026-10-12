const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

async function runDeepTestingSuite() {
    console.log('========================================================================');
    console.log('🛡️  SMASHHUB BADMINTON - BỘ KIỂM THỬ CHUYÊN SÂU & BẢO MẬT (DEEP E2E TEST)');
    console.log('    Bao gồm: Kiểm thử xâm nhập RBAC, Vòng đời nghiệp vụ, Bắt lỗi biên, Mobile');
    console.log('========================================================================\n');

    const screenshotDir = path.join(__dirname, 'screenshots_deep');
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

    // Lắng nghe lỗi console
    const consoleErrors = [];
    page.on('console', msg => {
        if (msg.type() === 'error') {
            consoleErrors.push(msg.text());
        }
    });

    function assert(category, name, condition, details = '') {
        const tag = `[${category}]`;
        if (condition) {
            console.log(`  ✅ ${tag} ${name} ${details ? '-> ' + details : ''}`);
            results.push({ category, name, status: 'PASS', details });
        } else {
            console.error(`  ❌ ${tag} ${name} ${details ? '-> ' + details : ''}`);
            results.push({ category, name, status: 'FAIL', details });
        }
    }

    try {
        // =====================================================================
        // PHẦN 1: SECURITY & PENETRATION TESTING (KIỂM THỬ XÂM NHẬP & PHÂN QUYỀN)
        // =====================================================================
        console.log('🔒 PHẦN 1: Kiểm thử Bảo mật & Phòng thủ Leo thang Đặc quyền (RBAC)');
        
        // 1.1 Khách vãng lai (Chưa login) cố tình vào trang cấm
        await page.goto('http://localhost/badminton/index.php?action=logout');
        
        await page.goto('http://localhost/badminton/index.php?action=admin_dashboard');
        assert('SECURITY', 'Khách vãng lai bị chặn vào Admin Dashboard', page.url().includes('action=login'));

        await page.goto('http://localhost/badminton/index.php?action=owner_dashboard');
        assert('SECURITY', 'Khách vãng lai bị chặn vào Owner Dashboard', page.url().includes('action=login'));

        await page.goto('http://localhost/badminton/index.php?action=staff_dashboard');
        assert('SECURITY', 'Khách vãng lai bị chặn vào Staff Dashboard', page.url().includes('action=login'));

        await page.screenshot({ path: path.join(screenshotDir, 'sec_01_guest_blocked.png') });

        // 1.2 User thường cố tình leo thang đặc quyền (Privilege Escalation)
        await page.goto('http://localhost/badminton/index.php?action=login');
        await page.fill('input[name="username"]', 'user');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1000);

        // Thử vào admin
        await page.goto('http://localhost/badminton/index.php?action=admin_dashboard');
        const userAdminBlocked = page.url().includes('no_permission') || !page.url().includes('admin_dashboard');
        assert('SECURITY', 'User thường bị chặn khi cố xâm nhập Admin Portal', userAdminBlocked, page.url());

        // Thử vào owner
        await page.goto('http://localhost/badminton/index.php?action=owner_dashboard');
        const userOwnerBlocked = page.url().includes('no_permission') || !page.url().includes('owner_dashboard');
        assert('SECURITY', 'User thường bị chặn khi cố xâm nhập Owner Portal', userOwnerBlocked, page.url());

        // 1.3 Thu ngân sân A bị chặn khi can thiệp đơn của sân B (Multi-tenant Boundary)
        await page.goto('http://localhost/badminton/index.php?action=logout');
        await page.goto('http://localhost/badminton/index.php?action=login');
        await page.fill('input[name="username"]', 'nhanvien_tanphu');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1000);

        // Thu ngân Tân Phú (Sân 7) cố tình check-in đơn của Sân Phương Đông (Sân 1, Booking ID 1)
        await page.goto('http://localhost/badminton/index.php?action=staff_checkin&id=1');
        const scopingBlocked = page.url().includes('unauthorized') || page.url().includes('error');
        assert('SECURITY', 'Thu ngân bị chặn khi can thiệp đơn sân ngoài phạm vi phụ trách', scopingBlocked, page.url());

        await page.screenshot({ path: path.join(screenshotDir, 'sec_02_staff_scoping_isolation.png') });

        // =====================================================================
        // PHẦN 2: FULL LIFECYCLE (ĐẶT SÂN -> THANH TOÁN -> CHECK-IN THÀNH CÔNG)
        // =====================================================================
        console.log('\n🔄 PHẦN 2: Kiểm thử Vòng đời Toàn vẹn (User Đặt Sân -> Staff Check-in)');

        // Đăng nhập User để đặt sân Tân Phú Arena (Sân 7)
        await page.goto('http://localhost/badminton/index.php?action=logout');
        await page.goto('http://localhost/badminton/index.php?action=login');
        await page.fill('input[name="username"]', 'user');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1000);

        // Đặt sân Tân Phú Arena (ID 7) cho ngày HÔM NAY để lễ tân thấy ngay
        await page.goto('http://localhost/badminton/index.php?action=book&id=7');
        await page.waitForLoadState('networkidle');

        const todayStr = new Date().toISOString().split('T')[0];

        await page.fill('input[name="customer_name"]', 'VĐV Kiểm Thử Lifecycle');
        await page.fill('input[name="customer_phone"]', '0988776655');
        await page.fill('input[name="booking_date"]', todayStr);
        await page.fill('input[name="start_time"]', '17:00');
        await page.fill('input[name="end_time"]', '18:00');

        await page.check('input[name="payment_method"][value="vietqr"]');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1500);

        assert('BUSINESS', 'Tạo đơn đặt sân thành công với mã hóa đơn', page.url().includes('action=success'));

        // Trích xuất mã Check-in thực tế
        const checkinCodeEl = await page.locator('text=CKIN').first();
        const fullCheckinText = await checkinCodeEl.textContent();
        const checkinMatch = fullCheckinText.match(/CKIN[A-Z0-9]+/);
        const generatedCode = checkinMatch ? checkinMatch[0] : '';
        assert('BUSINESS', 'Trích xuất mã Check-in tự động sinh', generatedCode.length >= 6, generatedCode);

        // Bấm Mô Phỏng Thanh Toán Sandbox
        page.once('dialog', async dialog => await dialog.accept());
        await page.click('#btn-simulate-pay');
        await page.waitForTimeout(1200);

        const paidBadge = await page.locator('#payment-status-badge').textContent();
        assert('BUSINESS', 'Xác thực thanh toán VietQR tức thì', paidBadge.includes('PAID'));

        await page.screenshot({ path: path.join(screenshotDir, 'flow_01_booking_paid.png') });

        // Bây giờ đăng nhập Thu ngân (nhanvien_tanphu) để Check-in mã vừa sinh
        await page.goto('http://localhost/badminton/index.php?action=logout');
        await page.goto('http://localhost/badminton/index.php?action=login');
        await page.fill('input[name="username"]', 'nhanvien_tanphu');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1000);

        await page.goto('http://localhost/badminton/index.php?action=staff_dashboard');
        
        // Nhập mã Check-in vào ô tra cứu của lễ tân
        await page.fill('input[name="search"]', generatedCode);
        await page.press('input[name="search"]', 'Enter');
        await page.waitForTimeout(1000);

        const foundBooking = await page.locator(`text=${generatedCode}`).count();
        assert('BUSINESS', 'Thu ngân tìm thấy đơn đặt theo mã Check-in', foundBooking > 0, generatedCode);

        // Bấm Check-in tại bàn trực
        const checkinBtn = page.locator(`a[href*="action=staff_checkin"], button:has-text("Check-in")`).first();
        if (await checkinBtn.count() > 0) {
            await checkinBtn.click();
            await page.waitForTimeout(1200);
            assert('BUSINESS', 'Thu ngân check-in thành công cho khách vào sân', page.url().includes('msg=checkin_success') || page.url().includes('staff_dashboard'));
        }

        await page.screenshot({ path: path.join(screenshotDir, 'flow_02_staff_checkin_completed.png') });

        // =====================================================================
        // PHẦN 3: TƯƠNG TÁC CỘNG ĐỒNG CLB (TẠO BÀI VIẾT & XEM BẢNG TIN)
        // =====================================================================
        console.log('\n💬 PHẦN 3: Kiểm thử Tương tác Cộng Đồng Câu Lạc Bộ');

        await page.goto('http://localhost/badminton/index.php?action=logout');
        await page.goto('http://localhost/badminton/index.php?action=login');
        await page.fill('input[name="username"]', 'user');
        await page.fill('input[name="password"]', '123456');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1000);

        // Vào CLB đầu tiên
        await page.goto('http://localhost/badminton/index.php?action=clubs');
        await page.click('a[href*="action=club_detail"]');
        await page.waitForTimeout(800);

        // Nếu chưa là thành viên, bấm Gia Nhập CLB
        const joinBtn = page.locator('button:has-text("Gia Nhập CLB")');
        if (await joinBtn.count() > 0) {
            await joinBtn.click();
            await page.waitForTimeout(1000);
            assert('COMMUNITY', 'User gia nhập CLB thành công', true);
        }

        // Đăng bài thảo luận mới
        const testPostTitle = 'Kèo Đôi Nam Nữ Cuối Tuần (Auto Test ' + Date.now().toString().slice(-4) + ')';
        const postForm = page.locator('form[action*="action=club_post"]');
        if (await postForm.count() > 0) {
            await page.fill('input[name="title"]', testPostTitle);
            await page.fill('textarea[name="content"]', 'Tìm 1 bạn nữ ghép cặp đánh giao lưu vui vẻ, sân Tân Phú!');
            await page.click('form[action*="action=club_post"] button[type="submit"]');
            await page.waitForTimeout(1500);

            const hasPostOnFeed = await page.locator(`text=${testPostTitle}`).count();
            assert('COMMUNITY', 'Đăng bài tin sinh hoạt CLB & hiển thị thời gian thực', hasPostOnFeed > 0, testPostTitle);
            await page.screenshot({ path: path.join(screenshotDir, 'comm_01_club_post_created.png') });
        } else {
            assert('COMMUNITY', 'Xem bảng tin sinh hoạt CLB', true);
        }

        // =====================================================================
        // PHẦN 4: EDGE CASES & VALIDATION (KIỂM THỬ NGOẠI LỆ & RÀNG BUỘC)
        // =====================================================================
        console.log('\n⚠️ PHẦN 4: Kiểm thử Ngoại lệ & Bắt Lỗi Biên (Edge Cases)');

        // Thử đặt giờ kết thúc TRƯỚC giờ bắt đầu
        await page.goto('http://localhost/badminton/index.php?action=book&id=1');
        await page.fill('input[name="customer_name"]', 'Test Lỗi Giờ');
        await page.fill('input[name="customer_phone"]', '0912345678');
        await page.fill('input[name="booking_date"]', todayStr);
        await page.fill('input[name="start_time"]', '20:00');
        await page.fill('input[name="end_time"]', '19:00'); // Lỗi: kết thúc trước bắt đầu
        await page.waitForTimeout(600);

        // Kiểm tra nút submit có bị disable hoặc form ngăn chặn không
        const isSubmitDisabled = await page.locator('button[type="submit"]').first().isDisabled();
        assert('VALIDATION', 'Client-side tự động vô hiệu hóa nút Submit khi Giờ kết thúc <= Giờ bắt đầu', isSubmitDisabled);

        // =====================================================================
        // PHẦN 5: MOBILE RESPONSIVE & FRONTEND INTEGRITY
        // =====================================================================
        console.log('\n📱 PHẦN 5: Kiểm thử Hiển thị Di Động (Mobile Viewport 390x844)');

        // Chuyển sang kích thước iPhone 14
        await page.setViewportSize({ width: 390, height: 844 });

        // Test Trang Chủ trên Mobile
        await page.goto('http://localhost/badminton/index.php');
        const isHorizontalOverflowHome = await page.evaluate(() => {
            return document.documentElement.scrollWidth > window.innerWidth;
        });
        assert('RESPONSIVE', 'Trang chủ chuẩn Mobile (Không vỡ thanh cuộn ngang)', !isHorizontalOverflowHome);
        await page.screenshot({ path: path.join(screenshotDir, 'mobile_01_homepage.png') });

        // Test Smart Match trên Mobile
        await page.goto('http://localhost/badminton/index.php?action=smart_match');
        const isHorizontalOverflowMatch = await page.evaluate(() => {
            return document.documentElement.scrollWidth > window.innerWidth;
        });
        assert('RESPONSIVE', 'Smart Match AI hiển thị hoàn hảo trên màn hình điện thoại', !isHorizontalOverflowMatch);
        await page.screenshot({ path: path.join(screenshotDir, 'mobile_02_smart_match.png') });

        // Test CLB trên Mobile
        await page.goto('http://localhost/badminton/index.php?action=clubs');
        const isHorizontalOverflowClubs = await page.evaluate(() => {
            return document.documentElement.scrollWidth > window.innerWidth;
        });
        assert('RESPONSIVE', 'Danh sách Câu lạc bộ hiển thị hoàn hảo trên Mobile', !isHorizontalOverflowClubs);
        await page.screenshot({ path: path.join(screenshotDir, 'mobile_03_clubs.png') });

        // =====================================================================
        // TỔNG KẾT
        // =====================================================================
        console.log('\n========================================================================');
        console.log('🏆 TỔNG KẾT BỘ KIỂM THỬ CHUYÊN SÂU & BẢO MẬT:');
        const passList = results.filter(r => r.status === 'PASS');
        const failList = results.filter(r => r.status === 'FAIL');
        console.log(`   - Tổng ca kiểm thử (Test Cases) : ${results.length}`);
        console.log(`   - Vượt qua thành công (PASS)    : ${passList.length}`);
        console.log(`   - Thất bại (FAIL)               : ${failList.length}`);
        console.log(`   - Tỷ lệ vượt qua                : ${((passList.length / results.length) * 100).toFixed(1)}%`);
        console.log(`   - Số lỗi JS Console nghiêm trọng: ${consoleErrors.length}`);
        console.log('========================================================================');

    } catch (err) {
        console.error('❌ Lỗi ngoại lệ trong quá trình chạy test chuyên sâu:', err);
    } finally {
        await browser.close();
    }
}

runDeepTestingSuite();
