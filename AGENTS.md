# 🏸 SMASHHUB — Project Context & Antigravity Agent Guidelines

> **Mục đích của tệp này:** Cung cấp đầy đủ bối cảnh dự án, kiến trúc hệ thống, quy tắc nghiệp vụ và tiêu chuẩn code để Antigravity (hoặc bất kỳ AI Assistant nào) khi mở dự án trên máy mới có thể nắm bắt 100% bối cảnh ngay lập tức mà không cần phân tích lại từ đầu.

---

## 1. 📌 Tổng Quan Dự Án (Project Identity)
- **Tên dự án:** SMASHHUB — Nền Tảng Đặt Sân Cầu Lông & Cáp Kèo Thông Minh (Smart Matchmaking AI).
- **Mục tiêu:** Giải quyết bài toán đặt sân chống trùng lịch (concurrency-safe), chia đôi hóa đơn (Split Bill 50/50), kết nối cơ thủ cân trình theo Elo và vị trí GPS Haversine, cùng cơ chế chống bùng kèo (No-Show Fair-Play).
- **Công nghệ cốt lõi:**
  - **Backend:** Pure PHP 7.4+ MVC (Không dùng Laravel/Symfony để tối ưu hiệu năng và dễ chấm đồ án tốt nghiệp).
  - **Database:** MySQL / MariaDB (kết nối qua PDO, hỗ trợ ACID Transaction chống Race Condition).
  - **Frontend:** HTML5, Vanilla JavaScript (Zero external JS frameworks, dùng Fetch API thuần), Tailwind CSS (Dark Slate Theme).
  - **Bản đồ:** Leaflet.js & OpenStreetMap (tính khoảng cách địa lý GPS).
  - **Thanh toán:** Mô phỏng VietQR Sandbox chuyển trạng thái tức thì.
  - **Kiểm thử:** Jest (Unit/Integration Test), k6 (Stress/Concurrency Test).

---

## 2. 👥 Hệ Thống 4 Phân Quyền (RBAC)

1. **Khách hàng (`user`):**
   - Đặt sân theo khung giờ (06:00 - 22:00), chọn vị trí sân con BWF.
   - Lập lệnh tìm đối (Smart Match AI): Chế độ *"Nhờ hệ thống tìm sân hộ"* hoặc *"Tôi đã có sân sẵn"*.
   - Đề xuất đổi sân, đàm phán, xác nhận thanh toán chia đôi (50/50).
   - Tham gia / lập Câu lạc bộ (CLB), đăng tin tuyển slot vãng lai.
   - Báo vắng đối thủ (No-Show) nếu đối phương không đến sân.
2. **Chủ sân (`owner`):**
   - Quản lý cụm sân, cấu hình số lượng sân con và giá theo giờ.
   - Lịch đặt sân dạng lưới (Court Schedule Grid) và tạo đơn đặt tại quầy (Walk-in).
   - Biểu đồ Chart.js thống kê doanh thu 7 ngày và phân tích khung giờ cao điểm.
   - Trọng tài xử lý khiếu nại bùng kèo (No-Show): Bấm *"Xác Nhận Bùng Kèo"* (trừ 30 Uy Tín, -50 Elo đối thủ vi phạm) hoặc *"Bác Bỏ"*.
   - Duyệt thu 50% tiền mặt cho Chủ nhà / Đội khách.
   - Cấp tài khoản Lễ tân / Thu ngân và gán sân quản lý.
3. **Nhân viên / Lễ tân (`staff`):**
   - Giao diện lễ tân màn hình cảm ứng: Check-in 1-Click bằng Mã đơn / Barcode / SĐT.
   - Chỉ được check-in đơn thuộc cụm sân mình phụ trách (`assigned_pitch_id`).
   - Check-in riêng biệt từng người (Chủ nhà hoặc Đội khách) trong các đơn cáp kèo AI.
4. **Quản trị viên tối cao (`admin`):**
   - Quản lý toàn bộ người dùng, cấp quyền, khóa/mở sân, thống kê toàn sàn.

---

## 3. 🧠 Thuật Toán & Nghiệp Vụ Cốt Lõi (Core Algorithms)

### 3.1. Thuật Toán Ghép Trận AI (Smart Matchmaking) — `models/MatchTicket.php`
- **Tầng 1 (Lọc thô - Hard Constraints):**
  - Trạng thái `waiting`, khác `user_id`, cùng `play_date`, giao thoa thời gian `(start_time < cand.end_time AND end_time > cand.start_time)`.
- **Tầng 2 (Chấm điểm đa mục tiêu - Weighted Scoring):**
  $$\mathbf{Total\ Score} = (\mathbf{0.5} \times \text{EloScore}) + (\mathbf{0.3} \times \text{District/GPS Score}) + (\mathbf{0.2} \times \text{FormatScore})$$
  - `EloScore = max(0, 100 - (|ΔElo| * 0.2))` (lệch 0 Elo = 100đ, lệch 100 = 80đ).
  - `District/GPS Score`: Dùng công thức Haversine tính $d_{\text{km}}$ thực tế giữa 2 người; nằm trong bán kính $R$ được từ 60–100đ; vượt bán kính bị trừ điểm nặng. Nếu không có GPS, xét cùng Quận/Quận lân cận.
  - `FormatScore`: Cùng thể thức (Đơn/Đôi) = 100đ, khác = 70đ.
- **Tầng 3 (Chọn sân trung tâm - Fair Center):**
  - Ưu tiên 1: Sân chỉ định sẵn của người tạo kèo.
  - Ưu tiên 2: Tự động sắp xếp sân theo tổng quãng đường nhỏ nhất $\min(d(\text{Player A} \to \text{Pitch}) + d(\text{Player B} \to \text{Pitch}))$.
- **Đàm phán đổi sân (`switch_pitch`):**
  - Người chơi có thể chọn sân khác dựa trên gợi ý km của cả 2 bạn. Đối thủ bấm `[✅ Đồng Ý Đổi Sân]` hoặc `[✕ Từ Chối]`.

### 3.2. Điểm Uy Tín Fair-Play & Cơ Chế Chống Bùng Kèo (No-Show) — `models/Reputation.php`
- Điểm uy tín chuẩn: **100 PTS**.
- Đánh xong trận bình thường: **+2 PTS**.
- Bùng kèo bị chủ sân xác nhận: **-30 PTS Uy Tín, -50 Điểm Elo**.
- Điểm uy tín dưới **50 PTS**: Bị hệ thống **khóa tính năng tìm trận**.

### 3.3. Cơ Chế Chống Trùng Lịch Đặt Sân (Concurrency / Race-Condition Safe) — `models/Booking.php`
- Bọc toàn bộ quá trình kiểm tra và lưu đơn trong **Database Transaction** (`$db->beginTransaction()` ... `$db->commit()`).
- Kiểm tra số lượng sân con đang được sử dụng trong khung giờ (`getConfirmedOverlappingCount`). Nếu $\ge \text{quantity}$ ➔ Rollback và chặn ngay.

---

## 4. ⚡ Cơ Chế Không Tải Lại Trang (Zero-Reload AJAX Paradigm)

Mọi thao tác tần suất cao đều chạy qua AJAX fetch và phản hồi Toast UI (`window.showToast`), không reload cả trang:
- **Bộ điều khiển (Controllers):** Nhận diện `$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_REQUEST['is_ajax'])`.
  - Nếu là AJAX: Trả về `header('Content-Type: application/json'); echo json_encode(...)`.
  - Nếu là link thường: Giữ nguyên redirect fallback để tương thích ngược 100%.
- **Các luồng Zero-Reload đã hoàn thiện:**
  1. *Thu ngân check-in:* Quét mã barcode hoặc bấm nút check-in riêng của từng người ➔ Cập nhật badge và tăng số lượt đã vào sân tức thì.
  2. *Chủ sân duyệt đơn / check-in / duyệt thu 50% tiền mặt:* Modal lịch (`owner_schedule`) và bảng dashboard (`owner_dashboard`) giữ nguyên vị trí, cập nhật DOM tức thì.
  3. *Cơ thủ đổi sân:* Bấm *"Gửi Đề Xuất Đổi Sân"*, *"Đồng Ý Đổi Sân"*, *"Từ Chối"* đều tương tác qua AJAX.
  4. *Đồng bộ thời gian thực (Polling):* Định kỳ 3.5s gọi `index.php?action=api_check_ticket` để tự cập nhật trạng thái nếu đối thủ đổi sân hoặc thanh toán.

---

## 5. 📂 Cấu Trúc Thư Mục (Codebase Layout)

```
badminton/
├── config/
│   └── database.php             # Kết nối PDO & Auto-migration các cột mới
├── controllers/
│   ├── AdminController.php      # Quản trị hệ thống
│   ├── AuthController.php       # Đăng ký, đăng nhập, phân quyền session
│   ├── BookingController.php    # Đặt sân, tính tiền, tạo VietQR, concurrency-safe
│   ├── ClubController.php       # CLB, bảng tin tuyển vãng lai
│   ├── HomeController.php       # Trang chủ, tìm kiếm bán kính bản đồ
│   ├── MatchmakingController.php# Cáp kèo AI, đổi sân, báo vắng
│   ├── MessageController.php    # Chat nội bộ giữa 2 cơ thủ
│   ├── NotificationController.php # Thông báo chuông
│   ├── OwnerController.php      # Dashboard chủ sân, duyệt lịch, duyệt thu 50%, no-show
│   ├── StaffController.php      # Bàn lễ tân, check-in mã/barcode
│   └── UserController.php       # Hồ sơ cá nhân, lịch sử thi đấu
├── models/
│   ├── Booking.php              # Xử lý đơn đặt, trùng lịch
│   ├── Club.php                 # CLB cầu lông
│   ├── MatchTicket.php          # Thuật toán Matching AI, Haversine, đổi sân
│   ├── Matchmaking.php          # Bảng matchmaking, tỉ số trận
│   ├── Pitch.php                # Cụm sân, tọa độ GPS, giá theo giờ
│   ├── Ranking.php              # Bảng xếp hạng Elo, giảm giá theo Rank
│   ├── Reputation.php           # Điểm uy tín, lịch sử trừ điểm bùng kèo
│   └── User.php                 # Người dùng, vai trò (admin/owner/staff/user)
├── views/
│   ├── layout.php               # Khung sườn chung, Header, Navbar, window.showToast()
│   ├── home.php                 # Trang chủ, tìm sân theo bán kính
│   ├── matchmaking/
│   │   └── smart_match.php      # Giao diện cáp kèo AI, đổi sân, đàm phán real-time
│   ├── owner/
│   │   ├── dashboard.php        # Biểu đồ Chart.js, quản lý kèo, duyệt thu tiền
│   │   └── schedule.php         # Lịch dạng lưới, modal check-in không reload
│   ├── staff/
│   │   └── dashboard.php        # Bàn trực lễ tân, check-in riêng 2 người
│   └── ...
├── tests/                       # Bộ kiểm thử tự động (Jest & k6 scripts)
├── index.php                    # Router chính, CSRF check, RBAC Middleware
├── badminton_booking.sql        # Database schema chuẩn & dữ liệu mẫu
└── AGENTS.md                    # File bối cảnh & chỉ dẫn Antigravity (Tệp này)
```

---

## 6. 🛠️ Hướng Dẫn Chạy Môi Trường Mới (Quick Start on New Machine)

### Cách 1: Chạy bằng WAMP / XAMPP
1. Copy thư mục dự án vào `C:\wamp64\www\badminton` hoặc `D:\wamp\www\badminton` (hoặc `htdocs/badminton`).
2. Mở phpMyAdmin / MySQL, tạo CSDL tên: `badminton_booking` (Collation: `utf8mb4_unicode_ci`).
3. Import tệp [badminton_booking.sql](badminton_booking.sql).
4. Kiểm tra cấu hình trong [config/database.php](config/database.php):
   - Mặc định kết nối: `localhost`, user `root`, password `""` (trống).
5. Truy cập: `http://localhost/badminton`

### Cách 2: Chạy bằng Docker
```bash
docker-compose up -d
```

### Chạy Bộ Kiểm Thử (Tests)
- **Unit test logic Matching:**
  ```bash
  npm test
  ```
- **Stress test Concurrency (k6):**
  ```bash
  k6 run tests/concurrent_join.js
  ```

---

## 7. ⚠️ Tiêu Chuẩn Viết Code Dành Cho AI Assistant (Rules for Agents)

1. **Tương thích PHP 7.4:** Tuyệt đối không dùng các cú pháp chỉ có từ PHP 8.0+ (như Named Arguments, Union Types `int|string`, cú pháp `match()`, constructor property promotion).
2. **Bảo toàn cơ chế Zero-Reload:** Khi thêm hoặc sửa các nút tác vụ (Check-in, Duyệt đơn, Đổi sân, Thanh toán), luôn hỗ trợ cả 2 chế độ:
   - Có kiểm tra `$isAjax` để trả về JSON `['success' => true/false, 'message' => '...']`.
   - Gọi `window.showToast(msg, type)` ở frontend thay vì `alert()` hoặc redirect reload trang.
3. **An toàn SQL:** Luôn sử dụng PDO Prepared Statements (`$stmt->prepare()` + `$stmt->execute([...])`). Tuyệt đối không nối chuỗi trực tiếp biến vào câu lệnh SQL để chống SQL Injection.
4. **Giữ nguyên Theme Dark-Slate:** Tông màu nền chính `#0b1116`, card container `#121b21`, viền `border-white/10`, màu điểm nhấn xanh neon `#d8ff3e`, xanh ngọc `emerald-500`, vàng hổ phách `amber-400`.
5. **Giữ nguyên Tiếng Việt:** Toàn bộ thông báo người dùng, nhãn giao diện và tài liệu phải giữ bằng Tiếng Việt thân thiện, rõ nghĩa.
