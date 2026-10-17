const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const { execSync } = require('child_process');

const BASE_URL = 'http://localhost/badminton/index.php';
const OUT_DIR = path.join(__dirname, '..', 'docs', 'screenshots');

if (!fs.existsSync(OUT_DIR)) {
    fs.mkdirSync(OUT_DIR, { recursive: true });
}

// Seed realistic 7-day revenue data for Owner Dashboard chart
function seedReportData() {
    const phpPath = 'F:\\wamp\\bin\\php\\php7.4.9\\php.exe';
    const phpCode = `
        try {
            require_once __DIR__ . '/../config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            $owner = $db->query("SELECT id FROM users WHERE username='chusan_demo'")->fetch(PDO::FETCH_ASSOC);
            if ($owner) {
                $pitch = $db->query("SELECT id FROM pitches WHERE owner_id=" . (int)$owner['id'] . " LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                if ($pitch) {
                    $pid = (int)$pitch['id'];
                    $revenues = [
                        6 => [180000, '07:00:00', '09:00:00'],
                        5 => [360000, '18:00:00', '20:00:00'],
                        4 => [270000, '16:00:00', '18:00:00'],
                        3 => [540000, '19:00:00', '21:00:00'],
                        2 => [450000, '18:00:00', '20:00:00'],
                        1 => [720000, '18:00:00', '21:00:00'],
                        0 => [630000, '19:00:00', '21:00:00'],
                    ];
                    foreach ($revenues as $daysAgo => $info) {
                        $date = date('Y-m-d', strtotime("-" . $daysAgo . " days"));
                        $code = 'RPT' . $daysAgo . 'X99';
                        $db->exec("DELETE FROM bookings WHERE checkin_code = '{$code}'");
                        $db->exec("INSERT INTO bookings (user_id, pitch_id, booking_date, start_time, end_time, total_price, status, payment_method, payment_status, checkin_code, customer_name, customer_phone) VALUES (2, {$pid}, '{$date}', '{$info[1]}', '{$info[2]}', {$info[0]}, 'confirmed', 'transfer', 'paid', '{$code}', 'Nguyen Anh Tuan', '0901234567')");
                    }
                }
            }
            echo "SEED_OK";
        } catch (Exception $e) {
            echo "SEED_ERR: " . $e->getMessage();
        }
    `;
    const tmpFile = path.join(__dirname, 'seed_tmp.php');
    fs.writeFileSync(tmpFile, '<?php ' + phpCode);
    try {
        const out = execSync(`"${phpPath}" "${tmpFile}"`).toString();
        console.log('   ->', out);
    } catch (e) {
        console.log('   -> Seed skipped:', e.message);
    } finally {
        if (fs.existsSync(tmpFile)) fs.unlinkSync(tmpFile);
    }
}

async function captureAll() {
    console.log('🌱 Seeding 7-day chart data for report screenshots...');
    seedReportData();

    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true
    });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        deviceScaleFactor: 1.5
    });
    const page = await context.newPage();

    console.log('📸 1/10: Capturing Homepage & Court Cards...');
    await page.goto(BASE_URL, { waitUntil: 'networkidle' });
    await page.waitForTimeout(600);
    await page.screenshot({ path: path.join(OUT_DIR, '01_trang_chu_he_thong.png') });

    console.log('📸 2/10: Capturing Real Court Gallery Modal...');
    const galleryBtn = page.locator('button:has-text("ẢNH THỰC TẾ")').first();
    if (await galleryBtn.count() > 0) {
        await galleryBtn.click();
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, '02_album_anh_thuc_te_san.png') });
        await page.keyboard.press('Escape');
        await page.waitForTimeout(300);
    }

    console.log('📸 3/10: Capturing GPS Radius Map Modal...');
    const mapBtn = page.locator('button:has-text("BẢN ĐỒ SÂN"), button:has-text("BÁN KÍNH")').first();
    if (await mapBtn.count() > 0) {
        await mapBtn.click();
        await page.waitForTimeout(1500);
        await page.screenshot({ path: path.join(OUT_DIR, '03_ban_do_ban_kinh_gps.png') });
        await page.keyboard.press('Escape');
        await page.waitForTimeout(300);
    }

    console.log('📸 4/10: Logging in as Customer (user) & Capturing Smart Matchmaking AI...');
    await page.goto(`${BASE_URL}?action=login`, { waitUntil: 'networkidle' });
    await page.fill('input[name="username"]', 'user');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await page.goto(`${BASE_URL}?action=smart_match`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(600);
    await page.screenshot({ path: path.join(OUT_DIR, '04_ghep_tran_smart_match_ai.png') });

    console.log('📸 5/10: Capturing VietQR & Sandbox Payment Confirmation...');
    const futureDate = new Date(Date.now() + 9 * 86400000).toISOString().split('T')[0];
    await page.goto(`${BASE_URL}?action=book&id=1`, { waitUntil: 'networkidle' });
    await page.fill('input[name="customer_name"]', 'Nguyễn Anh Tuấn');
    await page.fill('input[name="customer_phone"]', '0912345678');
    await page.fill('input[name="booking_date"]', futureDate);
    await page.fill('input[name="start_time"]', '18:00');
    await page.fill('input[name="end_time"]', '19:00');
    const vietQrRadio = page.locator('input[name="payment_method"][value="vietqr"]');
    if (await vietQrRadio.count() > 0) {
        await vietQrRadio.check({ force: true });
    }
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(800);
    const simBtn = page.locator('#btn-simulate-pay');
    if (await simBtn.count() > 0) {
        page.once('dialog', async dialog => { await dialog.accept(); });
        await simBtn.click();
        await page.waitForTimeout(1000);
    }
    await page.screenshot({ path: path.join(OUT_DIR, '05_thanh_toan_vietqr_sandbox.png') });

    console.log('📸 6/10: Capturing Badminton Clubs & Social Feed...');
    await page.goto(`${BASE_URL}?action=club_detail&id=1`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(500);
    await page.screenshot({ path: path.join(OUT_DIR, '06_cong_dong_cau_lac_bo.png') });

    console.log('📸 7/10: Logging in as Staff & Capturing Reception Check-in Desk...');
    await page.goto(`${BASE_URL}?action=logout`, { waitUntil: 'networkidle' });
    await page.goto(`${BASE_URL}?action=login`, { waitUntil: 'networkidle' });
    await page.fill('input[name="username"]', 'nhanvien_demo');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    await page.goto(`${BASE_URL}?action=staff_dashboard`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(500);
    await page.screenshot({ path: path.join(OUT_DIR, '07_ban_truc_tiep_don_staff.png') });

    console.log('📸 8/10: Logging in as Court Owner & Capturing Chart.js Analytics Dashboard...');
    await page.goto(`${BASE_URL}?action=logout`, { waitUntil: 'networkidle' });
    await page.goto(`${BASE_URL}?action=login`, { waitUntil: 'networkidle' });
    await page.fill('input[name="username"]', 'chusan_demo');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    await page.goto(`${BASE_URL}?action=owner_dashboard`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(1200);
    await page.screenshot({ path: path.join(OUT_DIR, '08_dashboard_chu_san_chartjs.png') });

    console.log('📸 9/10: Logging in as Super Admin & Capturing System Overview...');
    await page.goto(`${BASE_URL}?action=logout`, { waitUntil: 'networkidle' });
    await page.goto(`${BASE_URL}?action=login`, { waitUntil: 'networkidle' });
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    await page.goto(`${BASE_URL}?action=admin_dashboard`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(800);
    await page.screenshot({ path: path.join(OUT_DIR, '09_quan_tri_he_thong_admin.png') });

    console.log('📸 10/10: Capturing Mobile Responsive Interface (iPhone 14)...');
    const mobileContext = await browser.newContext({
        viewport: { width: 390, height: 844 },
        deviceScaleFactor: 2,
        isMobile: true
    });
    const mobilePage = await mobileContext.newPage();
    await mobilePage.goto(BASE_URL, { waitUntil: 'networkidle' });
    await mobilePage.waitForTimeout(600);
    await mobilePage.screenshot({ path: path.join(OUT_DIR, '10_giao_dien_mobile_responsive.png') });

    await browser.close();
    console.log('✅ All 10 thesis report screenshots captured in docs/screenshots/!');
}

captureAll().catch(err => {
    console.error(err);
    process.exit(1);
});
