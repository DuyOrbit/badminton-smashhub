-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th10 05, 2026 lúc 09:29 AM
-- Phiên bản máy phục vụ: 5.7.31
-- Phiên bản PHP: 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `badminton_booking`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pitch_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `payment_status` enum('unpaid','deposited','paid') COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid',
  `services_note` text COLLATE utf8mb4_unicode_ci,
  `checkin_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `booking_type` enum('online_app','walk_in','fixed_monthly') COLLATE utf8mb4_unicode_ci DEFAULT 'online_app',
  `created_by_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pitch_id` (`pitch_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `bookings`
--

INSERT INTO `bookings` (`id`, `pitch_id`, `user_id`, `customer_name`, `customer_phone`, `booking_date`, `start_time`, `end_time`, `total_price`, `payment_method`, `payment_status`, `services_note`, `checkin_code`, `status`, `created_at`, `booking_type`, `created_by_user_id`) VALUES
(8, 7, 18, 'Nguyễn Minh Khoa (VĐV Beta)', '0987654321', '2026-10-05', '19:00:00', '21:00:00', '180000.00', 'cash', 'paid', 'Kèo Ghép Thông Minh Elo: Nguyễn Minh Khoa (VĐV Beta) (Vàng) vs Đặng Việt Anh (VĐV Alpha) (Vàng)', 'CKIN62B9F', 'completed', '2026-10-04 02:43:00', 'online_app', NULL),
(9, 7, NULL, 'Khách Vãng Lai VIP (Test POS)', '0988776655', '2026-10-04', '15:00:00', '16:00:00', '100000.00', 'cash', 'paid', 'Đơn đặt trực tiếp tại quầy Lễ tân | Kèm dịch vụ: 2 Nước suối (+20k)', 'CKINADF0', 'pending', '2026-10-04 02:56:00', 'walk_in', 15),
(10, 7, NULL, 'Khách vãng lai', '0900000000', '2026-10-04', '18:00:00', '19:00:00', '395000.00', 'cash', 'paid', 'Đơn đặt trực tiếp tại quầy Lễ tân | Kèm dịch vụ: 2 Nước suối (+20k), 1 Revive chanh muối (+15k), Thuê 2 vợt Yonex (+40k), 1 Ống cầu Hải Yến S90 (+220k)', 'CKIN306E', 'pending', '2026-10-04 02:57:44', 'walk_in', 16),
(11, 6, 13, '1', '1', '2026-10-06', '18:00:00', '19:00:00', '70000.00', 'vietqr', 'paid', '', 'CKIN719A4', 'completed', '2026-10-05 08:08:29', 'online_app', NULL),
(12, 2, 13, '1', '1', '2026-10-06', '18:00:00', '20:00:00', '190000.00', 'cash', 'unpaid', 'Kèo Ghép Thông Minh Elo: 1 (Đồng) vs Nguyễn Duy (Đồng)', 'CKIN98746', 'confirmed', '2026-10-05 08:15:33', 'online_app', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `matchmaking`
--

DROP TABLE IF EXISTS `matchmaking`;
CREATE TABLE IF NOT EXISTS `matchmaking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pitch_id` int(11) NOT NULL,
  `play_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `format` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Đánh Đôi' COMMENT 'Đánh Đơn, Đánh Đôi, Giao lưu nhóm',
  `skill_level` tinyint(1) NOT NULL DEFAULT '3' COMMENT '1: Mới chơi -> 5: Bán chuyên',
  `district` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('open','closed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `opponent_id` int(11) DEFAULT NULL,
  `match_result` enum('pending','host_won','guest_won','draw') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `host_paid` tinyint(1) DEFAULT '0',
  `guest_paid` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `pitch_id` (`pitch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `matchmaking`
--

INSERT INTO `matchmaking` (`id`, `user_id`, `pitch_id`, `play_date`, `start_time`, `end_time`, `format`, `skill_level`, `district`, `message`, `status`, `created_at`, `opponent_id`, `match_result`, `host_paid`, `guest_paid`) VALUES
(7, 18, 7, '2026-10-05', '19:00:00', '21:00:00', 'Đánh Đôi', 3, 'Tân Bình', 'Ghép trận thông minh tự động: Nguyễn Minh Khoa (VĐV Beta) vs Đặng Việt Anh (VĐV Alpha)', 'closed', '2026-10-04 02:43:00', 17, 'pending', 0, 0),
(8, 13, 2, '2026-10-06', '18:00:00', '20:00:00', 'Đánh Đơn', 3, 'Gò Vấp', 'Ghép trận thông minh tự động: 1 vs Nguyễn Duy', 'closed', '2026-10-05 08:15:33', 14, 'host_won', 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `match_tickets`
--

DROP TABLE IF EXISTS `match_tickets`;
CREATE TABLE IF NOT EXISTS `match_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `play_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `format` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Đánh Đôi',
  `district` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_lat` decimal(10,8) DEFAULT NULL,
  `user_lng` decimal(11,8) DEFAULT NULL,
  `radius_km` decimal(5,2) DEFAULT '10.00',
  `location_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `has_pitch` tinyint(1) DEFAULT '0',
  `specified_pitch_id` int(11) DEFAULT NULL,
  `elo_points` int(11) NOT NULL DEFAULT '100',
  `rank_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đồng',
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` enum('waiting','matched','confirmed','cancelled','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'waiting',
  `matched_ticket_id` int(11) DEFAULT NULL,
  `matched_pitch_id` int(11) DEFAULT NULL,
  `user_confirmed` tinyint(1) DEFAULT '0',
  `matched_user_confirmed` tinyint(1) DEFAULT '0',
  `booking_id` int(11) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `match_tickets`
--

INSERT INTO `match_tickets` (`id`, `user_id`, `play_date`, `start_time`, `end_time`, `format`, `district`, `user_lat`, `user_lng`, `radius_km`, `location_name`, `has_pitch`, `specified_pitch_id`, `elo_points`, `rank_name`, `note`, `status`, `matched_ticket_id`, `matched_pitch_id`, `user_confirmed`, `matched_user_confirmed`, `booking_id`, `expires_at`, `created_at`) VALUES
(8, 17, '2026-10-05', '19:00:00', '21:00:00', 'Đánh Đôi', 'Tân Phú', '10.79400000', '106.63400000', '10.00', 'Tân Phú, TP.HCM', 0, NULL, 650, 'Vàng', 'Giao lưu vui vẻ cọ xát kinh nghiệm', 'confirmed', 9, 7, 1, 1, 8, '2026-10-04 04:43:00', '2026-10-04 02:43:00'),
(9, 18, '2026-10-05', '19:00:00', '21:00:00', 'Đánh Đôi', 'Tân Bình', '10.79600000', '106.63600000', '10.00', 'Tân Bình, TP.HCM', 0, NULL, 620, 'Vàng', 'Đánh bền cầu chia đôi tiền sân', 'confirmed', 8, 7, 1, 1, 8, '2026-10-04 04:43:00', '2026-10-04 02:43:00'),
(10, 14, '2026-10-06', '18:00:00', '20:00:00', 'Đánh Đơn', 'Quận 10', '10.77822457', '106.66537745', '10.00', 'Tọa độ GPS cá nhân', 0, NULL, 100, 'Đồng', '', 'confirmed', 11, 2, 1, 1, 12, '2026-10-05 10:11:35', '2026-10-05 08:10:57'),
(11, 13, '2026-10-06', '18:00:00', '20:00:00', 'Đánh Đơn', 'Gò Vấp', '10.77822457', '106.66537745', '10.00', 'Tọa độ GPS cá nhân', 0, NULL, 100, 'Đồng', '', 'confirmed', 10, 2, 1, 1, 12, '2026-10-05 10:11:35', '2026-10-05 08:11:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `content`, `is_read`, `created_at`) VALUES
(24, 17, 18, '🏸 [THÔNG BÁO TÌM TRẬN THÀNH CÔNG] Hệ thống đã tìm thấy đối thủ tương đồng Elo cho bạn vào ngày 05/10 lúc 19:00 tại Sân Cầu Lông Tân Phú Arena (BWF Standards). Vui lòng vào trang \'Ghép Trận Thông Minh\' để XÁC NHẬN KÈO!', 0, '2026-10-04 02:43:00'),
(25, 18, 17, '🏸 [THÔNG BÁO TÌM TRẬN THÀNH CÔNG] Hệ thống đã tìm thấy đối thủ tương đồng Elo cho bạn vào ngày 05/10 lúc 19:00 tại Sân Cầu Lông Tân Phú Arena (BWF Standards). Vui lòng vào trang \'Ghép Trận Thông Minh\' để XÁC NHẬN KÈO!', 0, '2026-10-04 02:43:00'),
(26, 17, 18, '🎉 [KÈO ĐÃ ĐƯỢC KHÓA THÀNH CÔNG] Cả hai bên đã xác nhận! Sân Sân Cầu Lông Tân Phú Arena (BWF Standards) đã được đặt vào lúc 19:00 - 21:00 ngày 05/10/2026. Mã Check-in tại quầy: CKIN62B9F. Chúc bạn có trận đấu rực lửa!', 0, '2026-10-04 02:43:00'),
(27, 18, 17, '🎉 [KÈO ĐÃ ĐƯỢC KHÓA THÀNH CÔNG] Cả hai bên đã xác nhận! Sân Sân Cầu Lông Tân Phú Arena (BWF Standards) đã được đặt vào lúc 19:00 - 21:00 ngày 05/10/2026. Mã Check-in tại quầy: CKIN62B9F. Chúc bạn có trận đấu rực lửa!', 0, '2026-10-04 02:43:00'),
(28, 5, 18, '🎉 [BIẾN ĐỘNG ĐIỂM UY TÍN FAIR-PLAY] Điểm uy tín của bạn vừa thay đổi +2 điểm. Lý do: Hoàn tất thi đấu đúng hẹn tại sân. Điểm hiện tại: 100/100.', 0, '2026-10-05 08:09:31'),
(29, 5, 13, '🎉 [BIẾN ĐỘNG ĐIỂM UY TÍN FAIR-PLAY] Điểm uy tín của bạn vừa thay đổi +2 điểm. Lý do: Hoàn tất thi đấu đúng hẹn tại sân. Điểm hiện tại: 100/100.', 1, '2026-10-05 08:09:43'),
(30, 14, 13, '🏸 [THÔNG BÁO TÌM TRẬN THÀNH CÔNG] Hệ thống đã tìm thấy đối thủ tương đồng Elo cho bạn vào ngày 06/10 lúc 18:00 tại CLB Cầu Lông Hoàng Kim. Vui lòng vào trang \'Ghép Trận Thông Minh\' để XÁC NHẬN KÈO!', 1, '2026-10-05 08:11:35'),
(31, 13, 14, '🏸 [THÔNG BÁO TÌM TRẬN THÀNH CÔNG] Hệ thống đã tìm thấy đối thủ tương đồng Elo cho bạn vào ngày 06/10 lúc 18:00 tại CLB Cầu Lông Hoàng Kim. Vui lòng vào trang \'Ghép Trận Thông Minh\' để XÁC NHẬN KÈO!', 0, '2026-10-05 08:11:35'),
(32, 14, 13, '🎉 [KÈO ĐÃ ĐƯỢC KHÓA THÀNH CÔNG] Cả hai bên đã xác nhận! Sân CLB Cầu Lông Hoàng Kim đã được đặt vào lúc 18:00 - 20:00 ngày 06/10/2026. Mã Check-in tại quầy: CKIN98746. Chúc bạn có trận đấu rực lửa!', 1, '2026-10-05 08:15:33'),
(33, 13, 14, '🎉 [KÈO ĐÃ ĐƯỢC KHÓA THÀNH CÔNG] Cả hai bên đã xác nhận! Sân CLB Cầu Lông Hoàng Kim đã được đặt vào lúc 18:00 - 20:00 ngày 06/10/2026. Mã Check-in tại quầy: CKIN98746. Chúc bạn có trận đấu rực lửa!', 0, '2026-10-05 08:15:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `pitches`
--

DROP TABLE IF EXISTS `pitches`;
CREATE TABLE IF NOT EXISTS `pitches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `district` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'TP.HCM',
  `latitude` decimal(10,8) DEFAULT '10.80000000' COMMENT 'Tọa độ vĩ độ GPS',
  `longitude` decimal(11,8) DEFAULT '106.70000000' COMMENT 'Tọa độ kinh độ GPS',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Thảm PVC Tiêu Chuẩn BWF, Thảm PVC Yonex, Sân Gỗ Thi Đấu, Thảm Alite',
  `quantity` int(11) NOT NULL DEFAULT '1' COMMENT 'Số lượng sân con trong cụm',
  `price_per_hour` decimal(10,2) NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','locked') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `pitches`
--

INSERT INTO `pitches` (`id`, `owner_id`, `name`, `location`, `district`, `city`, `latitude`, `longitude`, `type`, `quantity`, `price_per_hour`, `image_url`, `description`, `status`) VALUES
(1, 6, 'Sân Cầu Lông Phương Đông (Thảm BWF)', '128 Đinh Bộ Lĩnh, Phường 26', 'Bình Thạnh', 'TP.HCM', '10.81235000', '106.71182000', 'Thảm PVC Tiêu Chuẩn BWF', 8, '90000.00', 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&q=80', 'Cụm 8 sân cầu lông thảm PVC BWF tiêu chuẩn thi đấu quốc tế. Đèn LED dịu mắt, quạt làm mát công nghiệp, phòng tắm nóng lạnh miễn phí, bãi đỗ xe rộng rãi.', 'active'),
(2, 8, 'CLB Cầu Lông Hoàng Kim', '34 Hoàng Hoa Thám, Phường 12', 'Tân Bình', 'TP.HCM', '10.79854000', '106.64951000', 'Thảm PVC Yonex', 6, '95000.00', 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&q=80', 'Hệ thống 6 sân thảm cao su đệm êm chống chấn thương. Đầy đủ dịch vụ đan cước lấy liền trong 30 phút, bán cầu lông Hải Yến chính hãng, nước giải khát lạnh.', 'active'),
(3, 6, 'Sân Cầu Lông Thanh Đa Riverside', '246 Bình Quới, Cư xá Thanh Đa', 'Bình Thạnh', 'TP.HCM', '10.82563000', '106.72891000', 'Thảm Alite', 4, '80000.00', 'https://images.unsplash.com/photo-1599474924187-334a4ae5bd3c?w=800&q=80', 'Không gian ven sông thoáng đãng, 4 sân thảm Alite đạt chuẩn. Phù hợp cho câu lạc bộ tập luyện và giao lưu nhóm cuối tuần.', 'active'),
(4, 8, 'Trung Tâm Cầu Lông Quang Trung Pro', '890 Quang Trung, Phường 8', 'Gò Vấp', 'TP.HCM', '10.84521000', '106.65874000', 'Sân Gỗ Thi Đấu', 10, '110000.00', 'https://images.unsplash.com/photo-1622163642998-1ea32b0bbc67?w=800&q=80', 'Tổ hợp 10 sân cầu lông quy mô lớn, sàn lót gỗ bên dưới thảm chống sốc, máy lạnh hỗ trợ các khung giờ cao điểm, phòng VIP máy lạnh riêng biệt.', 'active'),
(5, 15, 'Sân quận 10', '354 cao thắng', 'Quận 10', 'TP.HCM', '10.80000000', '106.70000000', 'Thảm PVC Tiêu Chuẩn BWF', 16, '70000.00', 'https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=800&q=80', 'Cụm sân cầu lông quy mô 16 sân thảm BWF tiêu chuẩn, trần cao thoáng đãng, đèn LED chống chói.', 'active'),
(6, 15, 'Sân Gò Vấp', '1122 Quang Trung', 'Quận Gò Vấp', 'Thành phố Hồ Chí Minh', '10.83807800', '106.65211000', 'Thảm PVC Tiêu Chuẩn BWF', 6, '70000.00', 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=800&q=80', 'Cụm 6 sân thảm PVC Yonex cao cấp, không gian rộng rãi, bãi gửi xe thuận tiện.', 'active'),
(7, 16, 'Sân Cầu Lông Tân Phú Arena (BWF Standards)', '88 Tân Sơn Nhì, Phường Tân Sơn Nhì', 'Tân Phú', 'TP.HCM', '10.79500000', '106.63500000', 'Thảm PVC Tiêu Chuẩn BWF', 6, '100000.00', 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&q=80', 'Cụm 6 sân thảm BWF chuẩn thi đấu quốc tế. Máy lạnh công nghiệp, đèn LED dịu mắt, bãi giữ xe ô tô rộng rãi.', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reputation_logs`
--

DROP TABLE IF EXISTS `reputation_logs`;
CREATE TABLE IF NOT EXISTS `reputation_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `change_amount` int(11) NOT NULL,
  `current_score` int(11) NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reputation_logs`
--

INSERT INTO `reputation_logs` (`id`, `user_id`, `booking_id`, `ticket_id`, `change_amount`, `current_score`, `reason`, `created_at`) VALUES
(1, 18, 8, NULL, 2, 100, 'Hoàn tất thi đấu đúng hẹn tại sân', '2026-10-05 08:09:31'),
(2, 13, 11, NULL, 2, 100, 'Hoàn tất thi đấu đúng hẹn tại sân', '2026-10-05 08:09:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pitch_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `pitch_id` (`pitch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `booking_id`, `user_id`, `pitch_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 7, 1, 5, 'Sân thảm BWF rất êm chân, trần cao không bị chói mắt dưới dàn đèn LED. Rất hài lòng!', '2026-03-23 10:11:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pitch_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'beverage',
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Chai',
  `stock` int(11) DEFAULT '100',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `services`
--

INSERT INTO `services` (`id`, `pitch_id`, `name`, `category`, `price`, `unit`, `stock`) VALUES
(1, 1, 'Nước giải khát Revive Chanh Muối', 'beverage', '15000.00', 'Chai', 120),
(2, 1, 'Nước điện giải Pocari Sweat 500ml', 'beverage', '20000.00', 'Chai', 80),
(3, 1, 'Ống cầu lông Hải Yến S90 (12 quả)', 'shuttlecock', '240000.00', 'Ống', 40),
(4, 1, 'Thuê vợt Yonex Astrox', 'racket', '30000.00', 'Cây/giờ', 15),
(5, 2, 'Nước tăng lực Redbull Thái', 'beverage', '18000.00', 'Lon', 60),
(6, 2, 'Ống cầu lông Thành Công Pro 77', 'shuttlecock', '230000.00', 'Ống', 50),
(7, 2, 'Dịch vụ đan cước Yonex BG65Ti (Lấy liền)', 'maintenance', '130000.00', 'Lần', 30);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','staff','owner','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skill_level` tinyint(1) NOT NULL DEFAULT '3' COMMENT '1: Mới chơi, 2: TB Yếu, 3: Trung bình, 4: Khá, 5: Bán chuyên',
  `preferred_format` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Đánh Đôi' COMMENT 'Đánh Đơn, Đánh Đôi, Giao lưu nhóm',
  `district` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Bình Thạnh',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `location_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `reputation_score` int(11) NOT NULL DEFAULT '100' COMMENT 'Thang điểm Uy Tín Fair-Play từ 0 - 100',
  `penalty_until` datetime DEFAULT NULL COMMENT 'Thời hạn tạm khóa tính năng tìm trận do vi phạm',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `owner_id` int(11) DEFAULT NULL COMMENT 'ID của Chủ sân quản lý',
  `assigned_pitch_id` int(11) DEFAULT NULL COMMENT 'ID của Cụm sân phụ trách',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `name`, `phone`, `avatar_url`, `skill_level`, `preferred_format`, `district`, `latitude`, `longitude`, `location_address`, `reputation_score`, `penalty_until`, `created_at`, `owner_id`, `assigned_pitch_id`) VALUES
(5, 'admin', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'admin', 'Quản Trị Viên', '0368653018', NULL, 4, 'Đánh Đôi', 'Bình Thạnh', NULL, NULL, '', 100, NULL, '2026-03-22 06:54:13', NULL, NULL),
(6, 'chusan1', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'owner', 'Lê Hữu Phương', '0368653018', NULL, 4, 'Đánh Đôi', 'Bình Thạnh', NULL, NULL, '', 100, NULL, '2026-03-22 06:54:25', NULL, NULL),
(7, 'user', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'user', 'Nguyễn Anh Tuấn', '0368653018', 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=150', 3, 'Đánh Đôi', 'Bình Thạnh', NULL, NULL, '', 100, NULL, '2026-03-22 06:54:38', NULL, NULL),
(8, 'chusanhoangkim', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'owner', 'Hoàng Kim Long', '0987654321', NULL, 3, 'Đánh Đơn', 'Tân Bình', NULL, NULL, '', 100, NULL, '2026-03-23 10:13:52', NULL, NULL),
(9, 'nhanvien1', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'staff', 'Trần Văn Hùng', '0933112233', NULL, 3, 'Đánh Đôi', 'Bình Thạnh', NULL, NULL, '', 100, NULL, '2026-03-23 10:14:12', NULL, NULL),
(10, 'tiendung', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'user', 'Trần Tiến Dũng', '0977112233', 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=150', 4, 'Đánh Đơn', 'Gò Vấp', NULL, NULL, '', 100, NULL, '2026-03-23 10:39:51', NULL, NULL),
(11, 'huonglan', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'user', 'Vũ Hương Lan', '0988223344', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150', 2, 'Giao lưu nhóm', 'Bình Thạnh', NULL, NULL, '', 100, NULL, '2026-03-23 11:49:51', NULL, NULL),
(12, 'q', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'user', 'q', 'q', NULL, 3, 'Đánh Đôi', 'Bình Thạnh', NULL, NULL, '', 100, NULL, '2026-09-26 04:36:10', NULL, NULL),
(13, '1', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'user', '1', '1', NULL, 3, 'Đánh Đôi', 'Bình Thạnh', NULL, NULL, '', 100, NULL, '2026-09-26 17:57:23', NULL, NULL),
(14, '2', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'user', 'Nguyễn Duy', '0368653018', 'assets/uploads/avatars/1790446643_tixung.jpg', 3, 'Đánh Đơn', 'Quận 10', NULL, NULL, '', 100, NULL, '2026-09-26 17:57:39', NULL, NULL),
(15, 'chusanquan10', '$2y$10$7H1Lsz/.J0D0ABBL9kUrt.yylwjZoWRC0J0mcLC1HKU82j8uHZ56G', 'owner', 'Chủ sân quận 10', '0368653018', NULL, 3, 'Đánh Đôi', 'Bình Thạnh', NULL, NULL, '', 100, NULL, '2026-09-28 00:28:10', NULL, NULL),
(16, 'chusan_demo', '$2y$10$izisn0sh2MTB/AeTa5TGKO/xOw7u6u49yJuu3Ma9SdR9zEMhuuKOa', 'owner', 'Hoàng Văn Nam (Chủ Sân Tân Phú)', '0908889999', NULL, 3, 'Đánh Đôi', 'Tân Phú', NULL, NULL, '', 100, NULL, '2026-10-04 02:43:00', NULL, NULL),
(17, 'khach_alpha', '$2y$10$izisn0sh2MTB/AeTa5TGKO/xOw7u6u49yJuu3Ma9SdR9zEMhuuKOa', 'owner', 'Đặng Việt Anh (VĐV Alpha)', '0912345678', NULL, 3, 'Đánh Đôi', 'Tân Phú', NULL, NULL, '', 100, NULL, '2026-10-04 02:43:00', NULL, NULL),
(18, 'khach_beta', '$2y$10$izisn0sh2MTB/AeTa5TGKO/xOw7u6u49yJuu3Ma9SdR9zEMhuuKOa', 'owner', 'Nguyễn Minh Khoa (VĐV Beta)', '0987654321', NULL, 3, 'Đánh Đôi', 'Tân Bình', NULL, NULL, '', 100, NULL, '2026-10-04 02:43:00', NULL, NULL),
(19, 'thungan', '$2y$10$SN9U2t9UyU7nyF5cew75kOR9Cnf7WCADy/hAZg.nJzonDHoeviojC', 'staff', 'thu ngân', '0123', NULL, 3, 'Đánh Đôi', 'Bình Thạnh', NULL, NULL, '', 100, NULL, '2026-10-04 03:10:11', NULL, NULL),
(20, 'nhanvien_tanphu', '$2y$10$FtxwGXi744CgDVzfTJWIR.IW247qP7nOJs1b6/GCb5IoR1h14uRWG', 'staff', 'Nguyễn Thị Lễ Tân', '0933889900', NULL, 3, 'Đánh Đôi', 'Bình Thạnh', NULL, NULL, '', 100, NULL, '2026-10-04 03:18:12', 16, 7);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_scores`
--

DROP TABLE IF EXISTS `user_scores`;
CREATE TABLE IF NOT EXISTS `user_scores` (
  `user_id` int(11) NOT NULL,
  `season` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int(11) DEFAULT '0',
  `highest_points` int(11) DEFAULT '0',
  PRIMARY KEY (`user_id`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user_scores`
--

INSERT INTO `user_scores` (`user_id`, `season`, `points`, `highest_points`) VALUES
(7, '2026-H1', 600, 600),
(10, '2026-H1', 1090, 1090),
(11, '2026-H1', 300, 300),
(13, '2026-H2', 30, 30),
(14, '2026-H2', 0, 0),
(17, '2026-H2', 650, 650),
(18, '2026-H2', 620, 620);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- --------------------------------------------------------
-- Cấu trúc bảng cho 'clubs' (Cộng Đồng Câu Lạc Bộ Cầu Lông)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `clubs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `leader_id` int(11) NOT NULL,
  `pitch_id` int(11) DEFAULT NULL,
  `schedule_info` varchar(255) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `level_requirement` varchar(100) DEFAULT 'Mọi trình độ (Giao lưu học hỏi)',
  `avatar_url` varchar(255) DEFAULT NULL,
  `banner_url` varchar(255) DEFAULT NULL,
  `member_count` int(11) DEFAULT 1,
  `is_recruiting` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng cho 'club_members' (Thành viên CLB)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `club_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('leader','admin','member') DEFAULT 'member',
  `status` enum('approved','pending','rejected') DEFAULT 'approved',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_club_user` (`club_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Cấu trúc bảng cho 'club_posts' (Bảng tin & Tuyển vãng lai CLB)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `club_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_type` enum('general','recruitment','announcement') DEFAULT 'general',
  `title` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `slot_needed` int(11) DEFAULT 0,
  `fee_per_slot` int(11) DEFAULT 0,
  `likes_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
