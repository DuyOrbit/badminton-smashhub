# 🏸 SMASHHUB - Nền Tảng Đặt Sân Cầu Lông & Ghép Trận AI (Smart Matchmaking)

[![PHP Version](https://img.shields.io/badge/PHP-7.3%2B%20%2F%208.x-blue.svg)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-orange.svg)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Style-Tailwind%20CSS-38bdf8.svg)](https://tailwindcss.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**SMASHHUB** là hệ thống quản lý đặt sân cầu lông và kết nối cộng đồng lông thủ toàn diện. Nền tảng tích hợp thuật toán **Ghép trận thông minh (Smart Match)** độc quyền theo trình độ Elo và bán kính định vị GPS, giải quyết triệt để bài toán thiếu người đánh, chênh lệch trình độ và bùng kèo (No-Show).

---

## 🌟 Tính Năng Nổi Bật

### 1. 🎯 Ghép Trận Thông Minh (Smart Matchmaking AI)
* **2 Chế độ linh hoạt**: *"Nhờ Hệ Thống Đặt Hộ"* (tìm sân trống tự động) hoặc *"Tôi Đã Có Sân Sẵn"*.
* **Bản đồ Leaflet & GPS tương tác**: Định vị vị trí hiện tại hoặc tìm địa chỉ toàn quốc, tự động chọn sân gần nhất.
* **Cơ chế phân cấp ưu tiên vị trí rõ ràng**:
  * 🥇 **Ưu tiên 1**: Định vị GPS & Bán kính quét (quét sân thực tế trong $X$ km).
  * 🥈 **Ưu tiên 2**: Lọc theo Quận/Huyện dự phòng khi không bật GPS.
* **Thuật toán cân trình theo Elo**: Tự động tính toán điểm Elo qua từng trận đấu, đảm bảo các trận đấu cân tài cân sức.

### 2. 🛡️ Hệ Thống Điểm Uy Tín Fair-Play
* Điểm uy tín cơ bản: **100/100 PTS**.
* Cộng điểm thưởng Fair-Play khi hoàn tất trận đấu đúng hẹn (+2 PTS).
* Tự động cảnh báo và **khóa tính năng tìm trận** nếu điểm uy tín dưới 50 PTS do bùng kèo hoặc hủy trận cận giờ.

### 3. 👥 Cộng Đồng Câu Lạc Bộ & Nhóm Cố Định (Badminton Clubs)
* **Thành lập & Quản lý CLB**: Tự tạo CLB cho nhóm bạn, thiết lập lịch đánh định kỳ (ví dụ: Tối 2-4-6 hoặc cuối tuần), chọn sân nhà và yêu cầu trình độ Elo.
* **Bảng tin nội bộ & Tuyển Vãng Lai**: Khi nhóm thiếu chân, Trưởng nhóm / thành viên đăng tin *"Tuyển X Slot Vãng Lai"* kèm phí chia tiền sân, người chơi tự do có thể vào xin slot giao lưu tức thì.
* **Danh sách thành viên**: Theo dõi danh sách lông thủ, cấp quyền Trưởng nhóm / Thành viên.

### 4. 🏟️ Phân Hệ Dành Cho Chủ Sân (Court Owner)
* **Quản lý cụm sân**: Thêm/Sửa/Xóa sân, định vị GPS, tải ảnh sân, cấu hình số lượng sân con và giá theo giờ.
* **Biểu Đồ Trực Quan Doanh Thu & Công Suất (Chart.js Analytics)**: 
  * 📈 Biểu đồ Doanh thu 7 ngày gần nhất (Line Chart Gradient) cập nhật tự động theo thời gian thực.
  * ⏰ Biểu đồ Doughnut phân tích tỷ lệ đặt sân theo các khung giờ (Sáng, Trưa, Chiều, Giờ Vàng cao điểm 18h-22h).
* **Lịch đặt sân & Đặt tại quầy (Walk-in Booking)**: Tạo đơn trực tiếp tại quầy lễ tân cho khách vãng lai hoặc khách cố định tháng.
* **Phân quyền nhân viên**: Tạo tài khoản thu ngân/lễ tân và gán vào cụm sân phụ trách cụ thể.
* **Quản lý dịch vụ đi kèm**: Nước giải khát, phụ kiện cầu lông, thuê vợt.
* **Xuất báo cáo doanh thu**: Xuất file CSV thống kê chi tiết theo đơn đặt.

### 5. 💳 Thanh Toán VietQR & Mô Phỏng Sandbox (Payment Simulation)
* **Tự động sinh mã VietQR**: Mã QR ngân hàng động kèm mã đơn `CKIN...` và số tiền chính xác sau khi áp dụng giảm giá Rank Elo.
* **Nút Mô Phỏng Thanh Toán (Sandbox Demo Mode)**: Cho phép chuyển trạng thái đơn đặt từ `unpaid` sang `paid` và xác nhận lịch tức thì trên giao diện mà không cần chờ tài khoản ngân hàng thật, tối ưu cho buổi demo bảo vệ đồ án.
* **Bảo đảm toàn vẹn giao dịch (ACID & Concurrency Safe)**: Bọc toàn bộ quá trình kiểm tra trùng lịch và tạo đơn vào **Database Transaction**, giải quyết triệt để lỗi tranh chấp đặt sân cùng thời điểm (Race Condition).

### 6. 🎟️ Phân Hệ Bàn Trực Tiếp Đón (Staff / Receptionist)
* Giao diện tối ưu hóa cho màn hình cảm ứng / máy tính bảng lễ tân.
* **Check-in 1-Click**: Tìm kiếm theo Mã Check-in (ví dụ: `CKIN...`), SĐT hoặc tên khách.
* Ràng buộc bảo mật: Nhân viên chỉ được phép check-in các đơn thuộc Cụm sân mình phụ trách.

### 7. 👑 Phân Hệ Quản Trị Hệ Thống (Super Admin)
* Thống kê toàn cảnh doanh thu, số lượng sân, người dùng trên toàn hệ thống.
* Quản lý & khóa/mở cụm sân, phân quyền vai trò người dùng (Admin, Owner, Staff, User).
* Cấp tài khoản Thu ngân trực tiếp cho các cụm sân trong hệ thống.

---

## 🛠️ Yêu Cầu Hệ Thống & Cài Đặt

### 1. Yêu Cầu Môi Trường
* **Web Server**: Apache hoặc Nginx (Hỗ trợ tốt trên WAMP, XAMPP, Laragon, Docker).
* **PHP**: Phiên bản 7.3 trở lên (Hỗ trợ PHP 8.x).
* **Database**: MySQL 5.7+ hoặc MariaDB 10.3+.
* **Extensions PHP**: `pdo`, `pdo_mysql`, `mbstring`.

### 2. Hướng Dẫn Cài Đặt Nhanh

#### Bước 1: Tải mã nguồn về thư mục Web Server
Đặt thư mục dự án vào thư mục gốc của Web Server:
* WAMP: `C:\wamp64\www\badminton` (hoặc `F:\wamp\www\badminton`)
* XAMPP: `C:\xampp\htdocs\badminton`

#### Bước 2: Khởi tạo Cơ sở dữ liệu
1. Mở **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Tạo cơ sở dữ liệu mới có tên: `badminton_booking` (Collation: `utf8mb4_unicode_ci`).
3. Chọn tab **Import** và tải lên file: `badminton_booking.sql`.

#### Bước 3: Cấu hình kết nối Database
Mở file `config/database.php` và điều chỉnh thông số nếu cần:
```php
private $host = "localhost";
private $db_name = "badminton_booking";
private $username = "root";
private $password = ""; // Mặc định trên WAMP/XAMPP là rỗng
```

#### Bước 4: Khởi chạy ứng dụng
Mở trình duyệt web và truy cập:
```
http://localhost/badminton/index.php
```

---

## 👥 Danh Sách Tài Khoản Demo

| Vai trò (Role) | Tên đăng nhập (Username) | Mật khẩu (Password) | Chức năng kiểm thử |
| :--- | :--- | :--- | :--- |
| **Quản Trị Viên (Admin)** | `admin` | `123456` | Dashboard hệ thống, quản lý người dùng, duyệt sân |
| **Chủ Sân (Owner)** | `chusan_demo` | `123456` | Quản lý sân, đặt tại quầy, quản lý thu ngân |
| **Nhân Viên (Staff)** | `nhanvien_demo` | `123456` | Bàn trực lễ tân, kiểm tra mã check-in |
| **Khách Hàng (User)** | `khach_alpha` | `123456` | Đặt sân trực tuyến, lập lệnh Smart Match AI |

---

## 📁 Cấu Trúc Thư Mục Dự Án

```
badminton/
├── assets/                  # CSS, JS, hình ảnh tải lên
├── config/                  # Cấu hình kết nối cơ sở dữ liệu
│   └── database.php
├── controllers/             # Bộ điều khiển MVC (Admin, Owner, Staff, Booking, Matchmaking...)
├── models/                  # Các lớp thực thể (User, Pitch, Booking, MatchTicket, Reputation...)
├── views/                   # Giao diện người dùng
│   ├── admin/               # Màn hình quản trị
│   ├── owner/               # Màn hình chủ sân
│   ├── staff/               # Màn hình bàn trực thu ngân
│   ├── matchmaking/         # Màn hình tìm kèo Smart Match AI
│   ├── layout.php           # Khung layout chính
│   └── home.php             # Trang chủ
├── badminton_booking.sql    # File sao lưu cơ sở dữ liệu
├── index.php                # Router điều hướng chính
└── README.md                # Tài liệu dự án
```

---

## 📄 Bản Quyền
Dự án được phát triển phục vụ mục đích học tập, nghiên cứu và quản lý sân thể thao. Mọi đóng góp (Pull Request) đều được chào đón!
