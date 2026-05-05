-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 22, 2026 lúc 05:16 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `spinbike_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `escrows`
--

CREATE TABLE `escrows` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('holding','released','refunded','disputed') NOT NULL DEFAULT 'holding',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `released_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `escrows`
--

INSERT INTO `escrows` (`id`, `order_id`, `amount`, `status`, `created_at`, `released_at`) VALUES
(1, 1, 35000000.00, 'released', '2026-04-14 07:00:00', '2026-04-15 09:05:00'),
(2, 2, 14200000.00, 'holding', '2026-04-17 02:31:00', NULL),
(3, 3, 21500000.00, 'holding', '2026-04-17 10:31:00', NULL),
(4, 4, 9200000.00, 'refunded', '2026-04-16 05:46:00', '2026-04-16 11:00:00'),
(5, 5, 15600000.00, 'disputed', '2026-04-17 14:01:00', NULL),
(6, 6, 9200000.00, 'released', '2026-04-20 15:42:08', '2026-04-20 15:42:25'),
(7, 7, 11900000.00, 'holding', '2026-04-21 14:43:47', NULL),
(8, 8, 18500000.00, 'released', '2026-04-21 15:06:00', '2026-04-21 15:06:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending_payment','paid','seller_confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending_payment',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `seller_id`, `product_id`, `amount`, `status`, `created_at`) VALUES
(1, 5, 2, 5, 35000000.00, 'completed', '2026-04-14 07:00:00'),
(2, 6, 3, 2, 14200000.00, 'shipping', '2026-04-17 02:30:00'),
(3, 5, 4, 8, 21500000.00, 'seller_confirmed', '2026-04-17 10:30:00'),
(4, 6, 2, 6, 9200000.00, 'cancelled', '2026-04-16 05:45:00'),
(5, 5, 4, 10, 15600000.00, 'paid', '2026-04-17 14:00:00'),
(6, 8, 2, 6, 9200000.00, 'completed', '2026-04-20 15:42:08'),
(7, 8, 3, 9, 11900000.00, 'paid', '2026-04-21 14:43:47'),
(8, 8, 2, 1, 18500000.00, 'completed', '2026-04-21 15:06:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `bike_type` varchar(100) DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `frame_size` varchar(50) DEFAULT NULL,
  `condition_percent` int(11) DEFAULT NULL,
  `listing_status` enum('pending','approved','rejected','sold','hidden') NOT NULL DEFAULT 'pending',
  `approval_note` varchar(255) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `sold_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seller_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `title`, `brand`, `bike_type`, `price`, `location`, `description`, `frame_size`, `condition_percent`, `listing_status`, `approval_note`, `approved_at`, `sold_at`, `created_at`, `seller_id`) VALUES
(1, 'Trek Domane AL 2 2023', 'Trek', 'Road', 18500000.00, 'Quan 10, TP.HCM', 'Xe road frame nhom, di tour tot, da len full bo truyen dong Shimano Claris. Phu hop nguoi moi choi va can mot chiec xe on dinh de tap luyen hang ngay.', 'M', 92, 'sold', 'Tin hop le, hinh anh ro va thong tin day du.', '2026-04-12 02:00:00', '2026-04-21 15:06:00', '2026-04-11 09:00:00', 2),
(2, 'Giant XTC 820 2022', 'Giant', 'MTB', 14200000.00, 'Thu Duc, TP.HCM', 'Xe dia hinh con dep, phuoc nhun hoat dong tot, phu hop di duong hon hop va chay tap the duc cuoi tuan.', 'S', 88, 'approved', 'Tin hop le, cho phep hien thi.', '2026-04-12 02:10:00', NULL, '2026-04-11 09:20:00', 3),
(3, 'Twitter Gravel V3', 'Twitter', 'Gravel', 16800000.00, 'Quan Binh Thanh, TP.HCM', 'Xe gravel da nang, bo lop 700x40C, phu hop di pho va di tour gan. Nguoi ban vua cap nhat them anh thuc te.', 'M', 90, 'approved', 'Tin đã được admin duyệt hiển thị.', '2026-04-22 02:51:49', NULL, '2026-04-17 03:00:00', 4),
(4, 'Trinx Free 2.0', 'Trinx', 'Road', 8900000.00, 'Bien Hoa, Dong Nai', 'Tin dang nay dung anh tu catalog, chua co anh thuc te cua xe nen admin tam thoi tu choi.', 'M', 80, 'rejected', 'Can bo sung anh that cua xe va cap nhat tinh trang khung chi tiet hon.', NULL, NULL, '2026-04-16 06:00:00', 2),
(5, 'Specialized Allez Sport', 'Specialized', 'Road', 35000000.00, 'Quan 7, TP.HCM', 'Xe da giao dich thanh cong thong qua SpinBike. Nguoi mua da xac nhan nhan xe dung mo ta.', 'L', 95, 'sold', 'Da hoan tat giao dich.', '2026-04-11 02:30:00', '2026-04-15 09:00:00', '2026-04-10 08:30:00', 2),
(6, 'Asama MTB Pro', 'Asama', 'MTB', 9200000.00, 'Da Nang', 'Xe dang trong qua trinh xu ly hoan tien vi nguoi mua bao cao tinh trang phanh khong dung nhu mo ta.', 'M', 85, 'sold', 'Tin hop le, dang theo doi giao dich.', '2026-04-12 03:00:00', '2026-04-20 15:42:08', '2026-04-11 10:00:00', 2),
(7, 'Martin 107 Touring', 'Martin 107', 'Touring', 12500000.00, 'Can Tho', 'Nguoi ban tam an tin de cap nhat lai bo anh va gia ban.', 'L', 87, 'hidden', 'Tin tam an theo yeu cau nguoi ban.', '2026-04-13 04:00:00', NULL, '2026-04-12 07:00:00', 3),
(8, 'Cannondale Trail 6', 'Cannondale', 'MTB', 21500000.00, 'Go Vap, TP.HCM', 'Don hang da duoc dat va nguoi ban da xac nhan se gui xe qua don vi van chuyen trong ngay.', 'M', 91, 'sold', 'Tam khoa tin vi da phat sinh don hang.', '2026-04-12 04:00:00', '2026-04-17 10:30:00', '2026-04-12 09:00:00', 4),
(9, 'Java Siluro 3', 'Java', 'Road', 11900000.00, 'Tan Binh, TP.HCM', 'Xe road tam trung, phu hop nguoi can xe tap luyen hang ngay. Day du hinh thuc te va thong so co ban.', 'S', 89, 'sold', 'Tin hop le, san sang hien thi.', '2026-04-14 03:20:00', '2026-04-21 14:43:47', '2026-04-13 11:00:00', 3),
(10, 'Polygon Path 3', 'Polygon', 'Road', 15600000.00, 'Quan 3, TP.HCM', 'Don hang dang co khieu nai do nguoi mua cho rang xe co vet truot son lon hon mo ta. Admin dang tam giu tien de xu ly.', 'M', 88, 'sold', 'Tam khoa tin vi don hang dang tranh chap.', '2026-04-14 08:15:00', '2026-04-17 14:00:00', '2026-04-14 08:00:00', 4),
(11, 'Xe Đạp Đua XdS AD350 2024 ', 'XdS', 'Road', 0.00, '12123243, Xã Giáp Sơn, Huyện Lục Ngạn, Tỉnh Bắc Giang', 'guyjhgsfzvdbgnfhytfds', 'M', 95, 'approved', 'Tin đã được admin duyệt hiển thị.', '2026-04-22 02:51:51', NULL, '2026-04-22 02:49:36', 8);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `created_at`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80', '2026-04-11 09:01:00'),
(2, 1, 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?auto=format&fit=crop&w=1200&q=80', '2026-04-11 09:01:10'),
(3, 2, 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80', '2026-04-11 09:21:00'),
(4, 2, 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80', '2026-04-11 09:21:10'),
(5, 3, 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80', '2026-04-17 03:01:00'),
(6, 4, 'https://images.unsplash.com/photo-1502740479091-635887520276?auto=format&fit=crop&w=1200&q=80', '2026-04-16 06:01:00'),
(7, 5, 'https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?auto=format&fit=crop&w=1200&q=80', '2026-04-10 08:31:00'),
(8, 5, 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80', '2026-04-10 08:31:10'),
(9, 6, 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80', '2026-04-11 10:01:00'),
(10, 7, 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80', '2026-04-12 07:01:00'),
(11, 8, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=80', '2026-04-12 09:01:00'),
(12, 8, 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=1200&q=80', '2026-04-12 09:01:10'),
(13, 9, 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80', '2026-04-13 11:01:00'),
(14, 10, 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80', '2026-04-14 08:01:00'),
(15, 11, 'https://res.cloudinary.com/dge3u1dzk/image/upload/v1776826179/ytkfymlhxyb00f1isuxl.jpg', '2026-04-22 02:49:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `type` enum('deposit','payment','earn','refund','fee') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `order_id`, `amount`, `fee`, `type`, `created_at`) VALUES
(1, 5, 1, 35000000.00, 0.00, 'payment', '2026-04-14 07:01:00'),
(2, 2, 1, 33250000.00, 1750000.00, 'earn', '2026-04-15 09:05:00'),
(3, 6, 2, 14200000.00, 0.00, 'payment', '2026-04-17 02:31:30'),
(4, 5, 3, 21500000.00, 0.00, 'payment', '2026-04-17 10:31:30'),
(5, 6, 4, 9200000.00, 0.00, 'payment', '2026-04-16 05:47:00'),
(6, 6, 4, 9200000.00, 0.00, 'refund', '2026-04-16 11:00:00'),
(7, 5, 5, 15600000.00, 0.00, 'payment', '2026-04-17 14:01:30'),
(8, 8, 6, 9200000.00, 0.00, 'payment', '2026-04-20 15:42:08'),
(9, 2, 6, 8740000.00, 460000.00, 'earn', '2026-04-20 15:42:25'),
(10, 8, 7, 11900000.00, 0.00, 'payment', '2026-04-21 14:43:47'),
(11, 8, 8, 18500000.00, 0.00, 'payment', '2026-04-21 15:06:00'),
(12, 2, 8, 17575000.00, 925000.00, 'earn', '2026-04-21 15:06:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expire` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `avatar`, `role`, `balance`, `created_at`, `reset_token`, `reset_token_expire`) VALUES
(1, 'Admin SpinBike', 'admin@spinbike.local', '$2y$12$JQh8OnsH9xHBQUVTQ09TUuF1h/ZCom5uNhKlBi6I3NgkTS.araUOa', '0909000001', NULL, 'https://ui-avatars.com/api/?name=Admin+SpinBike&background=0f766e&color=ffffff&rounded=true', 'admin', 0.00, '2026-04-10 01:00:00', NULL, NULL),
(2, 'Nguyễn Hoài Nam', 'nguyennam2017an@gmail.com', '123', '0909000002', '123 Nguyễn Trãi, Quận 1, TP.HCM', 'https://ui-avatars.com/api/?name=Nguyen+Hoai+Nam&background=10b981&color=ffffff&rounded=true', 'user', 59565000.00, '2026-04-10 01:10:00', NULL, NULL),
(3, 'Đoàn Hoài Ân', 'seller2@spinbike.local', '$2y$12$JjOIFQdOSgirTasQhVGshes7BlKeAyTqJz9ZI/hmLoZQIOxLrqRHu', '0909000003', '45 Lê Văn Sỹ, Quận 3, TP.HCM', 'https://ui-avatars.com/api/?name=Doan+Hoai+An&background=0ea5e9&color=ffffff&rounded=true', 'user', 0.00, '2026-04-10 01:15:00', NULL, NULL),
(4, 'Nguyễn Hoàng Linh Tú', 'seller3@spinbike.local', '$2y$12$JjOIFQdOSgirTasQhVGshes7BlKeAyTqJz9ZI/hmLoZQIOxLrqRHu', '0909000004', '88 Đinh Tiên Hoàng, Bình Thạnh, TP.HCM', 'https://ui-avatars.com/api/?name=Nguyen+Hoang+Linh+Tu&background=f59e0b&color=ffffff&rounded=true', 'user', 0.00, '2026-04-10 01:20:00', NULL, NULL),
(5, 'Trần Minh Khoa', 'buyer1@spinbike.local', '$2y$12$R0J5V0YqKdcoSEbrB9njB.WQhJKz.Jwukomkh6Nm/0CkBrnyfCHWi', '0909000005', '12 Hoàng Diệu, Quận 4, TP.HCM', 'https://ui-avatars.com/api/?name=Tran+Minh+Khoa&background=6366f1&color=ffffff&rounded=true', 'user', 18500000.00, '2026-04-10 01:25:00', NULL, NULL),
(6, 'Lê Gia Bảo', 'buyer2@spinbike.local', '$2y$12$R0J5V0YqKdcoSEbrB9njB.WQhJKz.Jwukomkh6Nm/0CkBrnyfCHWi', '0909000006', '56 Trần Hưng Đạo, Quận 5, TP.HCM', 'https://ui-avatars.com/api/?name=Le+Gia+Bao&background=ef4444&color=ffffff&rounded=true', 'user', 9200000.00, '2026-04-10 01:30:00', NULL, NULL),
(7, 'Phạm Thanh Huy', 'demo@spinbike.local', '$2y$12$NPuKDIHq1n1kzN1MDXWQ/OI2oE.7og.M0j9yDMVoUQvZdPj3LQAyC', '0909000007', NULL, 'https://ui-avatars.com/api/?name=Pham+Thanh+Huy&background=334155&color=ffffff&rounded=true', 'user', 0.00, '2026-04-10 01:35:00', NULL, NULL),
(8, 'Hoài Nam', 'nguyennam2017an1@gmail.com', '$2y$10$PpDM5RwDmZ9qhBno8a.vIeXixvsyiKRXlA4xCXAtYoNdN/kYUa5rG', '', NULL, 'https://res.cloudinary.com/dge3u1dzk/image/upload/v1776699709/gsj3dpemxwc6dxhc75ik.jpg', 'user', 0.00, '2026-04-20 15:41:20', NULL, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `escrows`
--
ALTER TABLE `escrows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_escrows_order_id` (`order_id`),
  ADD KEY `idx_escrows_status` (`status`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_buyer_id` (`buyer_id`),
  ADD KEY `idx_orders_seller_id` (`seller_id`),
  ADD KEY `idx_orders_product_id` (`product_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_seller_id` (`seller_id`),
  ADD KEY `idx_products_listing_status` (`listing_status`),
  ADD KEY `idx_products_brand` (`brand`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_images_product_id` (`product_id`);

--
-- Chỉ mục cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transactions_user_id` (`user_id`),
  ADD KEY `idx_transactions_order_id` (`order_id`),
  ADD KEY `idx_transactions_type` (`type`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_users_email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `escrows`
--
ALTER TABLE `escrows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `escrows`
--
ALTER TABLE `escrows`
  ADD CONSTRAINT `fk_escrows_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_orders_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_orders_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

-- --------------------------------------------------------
-- Realistic marketplace polish seed
-- Makes seeded listings look like user-posted used bicycles.
-- --------------------------------------------------------

START TRANSACTION;

UPDATE products
SET
  title = CASE id
    WHEN 1 THEN 'Trek Domane AL 2, xe nhà đi kỹ cần bán'
    WHEN 2 THEN 'Giant XTC 820 màu đen, còn rất mới'
    WHEN 3 THEN 'Twitter Gravel V3 đi tour nhẹ, full ảnh thật'
    WHEN 4 THEN 'Trinx Free 2.0 cần bổ sung ảnh thực tế'
    WHEN 5 THEN 'Specialized Allez Sport đã bán qua SpinBike'
    WHEN 6 THEN 'Asama MTB Pro, xe đã giao dịch xong'
    WHEN 7 THEN 'Martin Touring 107 đang tạm ẩn để chỉnh giá'
    WHEN 8 THEN 'Cannondale Trail 6 phanh dầu, size M'
    WHEN 9 THEN 'Java Siluro 3 xe tập hằng ngày'
    WHEN 10 THEN 'Polygon Path 3 đang xử lý giao dịch'
    WHEN 11 THEN 'XDS AD350 2024 mới đi vài lần'
    ELSE title
  END,
  location = CASE id
    WHEN 1 THEN 'Phường Hòa Hưng, Quận 10, TP. Hồ Chí Minh'
    WHEN 2 THEN 'Phường Linh Trung, TP. Thủ Đức, TP. Hồ Chí Minh'
    WHEN 3 THEN 'Phường 25, Quận Bình Thạnh, TP. Hồ Chí Minh'
    WHEN 4 THEN 'Phường Trảng Dài, TP. Biên Hòa, Đồng Nai'
    WHEN 5 THEN 'Phường Tân Phong, Quận 7, TP. Hồ Chí Minh'
    WHEN 6 THEN 'Phường Hải Châu, Quận Hải Châu, Đà Nẵng'
    WHEN 7 THEN 'Phường Cái Khế, Quận Ninh Kiều, Cần Thơ'
    WHEN 8 THEN 'Phường 5, Quận Gò Vấp, TP. Hồ Chí Minh'
    WHEN 9 THEN 'Phường 4, Quận Tân Bình, TP. Hồ Chí Minh'
    WHEN 10 THEN 'Phường Võ Thị Sáu, Quận 3, TP. Hồ Chí Minh'
    WHEN 11 THEN 'Xã Giáp Sơn, Huyện Lục Ngạn, Bắc Giang'
    ELSE location
  END,
  description = CASE id
    WHEN 1 THEN 'Mình lên đời xe carbon nên bán lại Trek Domane AL 2. Xe đi tập buổi sáng là chính, khung không móp, sơn có vài vết xước nhỏ ở càng sau. Đã thay dây đề và bọc ghi đông gần đây, xem xe trực tiếp ở Quận 10.'
    WHEN 2 THEN 'Giant XTC 820 mua cuối 2022, phuộc còn nhún tốt, thắng ăn và sang số mượt. Xe hợp đi phố, đi đường xấu hoặc cuối tuần chạy công viên. Có trầy nhẹ ở tay đề do dựng xe trong bãi.'
    WHEN 3 THEN 'Xe gravel Twitter V3 mình dùng đi tour ngắn và đi làm. Lốp 700x40C còn dày, bộ truyền động sạch, không rơ cổ phốt. Bán vì chuyển qua size nhỏ hơn.'
    WHEN 4 THEN 'Tin này đang thiếu ảnh chụp thực tế nên để trạng thái từ chối. Người bán cần chụp rõ khung, bộ truyền động, bánh và các vết trầy nếu có.'
    WHEN 5 THEN 'Xe đã bán thành công qua SpinBike. Trước khi bán xe còn rất đẹp, bánh quay thẳng, group hoạt động ổn và người mua đã xác nhận đúng mô tả.'
    WHEN 6 THEN 'Asama MTB Pro đã qua sử dụng, phù hợp đi làm và tập thể dục. Giao dịch này đã hoàn tất trên hệ thống, giữ lại để demo lịch sử đơn hàng.'
    WHEN 7 THEN 'Người bán tạm ẩn tin để chụp lại ảnh ban ngày và cân nhắc giá. Xe touring gác baga chắc, hợp đi làm xa hoặc chở đồ nhẹ.'
    WHEN 8 THEN 'Cannondale Trail 6 size M, phanh dầu, vỏ còn khá mới. Xe đã có đơn nên hệ thống chuyển trạng thái đã bán, demo luồng đặt mua an toàn.'
    WHEN 9 THEN 'Java Siluro 3 mình dùng tập hằng ngày khoảng 8 tháng. Sang số ổn, thắng tốt, bánh chưa đảo. Có xước sơn nhỏ ở gióng ngang, không ảnh hưởng sử dụng.'
    WHEN 10 THEN 'Polygon Path 3 đang có giao dịch cần admin theo dõi. Mô tả này dùng để demo trường hợp đơn hàng phát sinh khiếu nại và hệ thống giữ tiền.'
    WHEN 11 THEN 'XDS AD350 mua mới đầu năm, mới chạy vài vòng khu nhà. Xe còn sạch, chưa té ngã, phù hợp bạn mới bắt đầu chơi road. Mình bán vì không hợp size.'
    ELSE description
  END
WHERE id BETWEEN 1 AND 11;

UPDATE products p
JOIN (
  SELECT
    id,
    CASE MOD(id, 18)
      WHEN 0 THEN CONCAT('Giant TCR Advanced, xe tập sáng còn đẹp - ', id)
      WHEN 1 THEN CONCAT('Trek Domane AL đi phố cuối tuần - ', id)
      WHEN 2 THEN CONCAT('Specialized Allez Sport cần bán nhanh - ', id)
      WHEN 3 THEN CONCAT('Cannondale Trail phanh dầu, size M - ', id)
      WHEN 4 THEN CONCAT('Merida Scultura lên vài món nhẹ - ', id)
      WHEN 5 THEN CONCAT('Scott Speedster màu xám, giấy tờ đủ - ', id)
      WHEN 6 THEN CONCAT('Java Siluro 3 cho người mới chơi - ', id)
      WHEN 7 THEN CONCAT('Twitter Gravel đi tour ngắn rất ổn - ', id)
      WHEN 8 THEN CONCAT('Polygon Path 3 đi làm hằng ngày - ', id)
      WHEN 9 THEN CONCAT('Trinx Free 2.0 xe nhà ít dùng - ', id)
      WHEN 10 THEN CONCAT('Asama MTB Pro bánh 27.5 - ', id)
      WHEN 11 THEN CONCAT('Martin Touring gác baga chắc chắn - ', id)
      WHEN 12 THEN CONCAT('Giant Escape dáng hybrid dễ đi - ', id)
      WHEN 13 THEN CONCAT('Trek Checkpoint ALR gravel size M - ', id)
      WHEN 14 THEN CONCAT('Specialized Diverge E5 đi cafe tour - ', id)
      WHEN 15 THEN CONCAT('Fixed gear khung nhôm, màu tối - ', id)
      WHEN 16 THEN CONCAT('Xe đạp city Nhật bãi còn zin - ', id)
      ELSE CONCAT('MTB Giant Talon cho sinh viên - ', id)
    END AS realistic_title,
    CASE MOD(id, 10)
      WHEN 0 THEN 'Road'
      WHEN 1 THEN 'Road'
      WHEN 2 THEN 'MTB'
      WHEN 3 THEN 'MTB'
      WHEN 4 THEN 'Gravel'
      WHEN 5 THEN 'Touring'
      WHEN 6 THEN 'Hybrid'
      WHEN 7 THEN 'City'
      WHEN 8 THEN 'Fixed'
      ELSE 'Road'
    END AS realistic_type,
    CASE MOD(id, 16)
      WHEN 0 THEN 'Phường Bến Thành, Quận 1, TP. Hồ Chí Minh'
      WHEN 1 THEN 'Phường An Phú, TP. Thủ Đức, TP. Hồ Chí Minh'
      WHEN 2 THEN 'Phường 25, Quận Bình Thạnh, TP. Hồ Chí Minh'
      WHEN 3 THEN 'Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội'
      WHEN 4 THEN 'Phường Hàng Bạc, Quận Hoàn Kiếm, Hà Nội'
      WHEN 5 THEN 'Phường Hải Châu, Quận Hải Châu, Đà Nẵng'
      WHEN 6 THEN 'Phường Cái Khế, Quận Ninh Kiều, Cần Thơ'
      WHEN 7 THEN 'Phường Tân Mai, TP. Biên Hòa, Đồng Nai'
      WHEN 8 THEN 'Phường Phú Hòa, TP. Thủ Dầu Một, Bình Dương'
      WHEN 9 THEN 'Phường Máy Tơ, Quận Ngô Quyền, Hải Phòng'
      WHEN 10 THEN 'Phường Ghềnh Ráng, TP. Quy Nhơn, Gia Lai'
      WHEN 11 THEN 'Phường Vĩnh Hòa, TP. Nha Trang, Khánh Hòa'
      WHEN 12 THEN 'Phường Thắng Tam, TP. Vũng Tàu, TP. Hồ Chí Minh'
      WHEN 13 THEN 'Phường Phú Hội, TP. Huế'
      WHEN 14 THEN 'Phường Tân Lợi, TP. Buôn Ma Thuột, Đắk Lắk'
      ELSE 'Phường Bãi Cháy, TP. Hạ Long, Quảng Ninh'
    END AS realistic_location,
    CASE MOD(id, 9)
      WHEN 0 THEN 'Mình bán vì mới lên đời xe khác. Xe đi tập buổi sáng là chính, để trong nhà, không dầm mưa. Có vài vết xước nhỏ do dựng xe chung nhưng khung không móp, sang số và thắng vẫn ổn.'
      WHEN 1 THEN 'Xe của nhà dùng đi làm gần, cuối tuần có chạy công viên. Vỏ còn dày, bánh quay thẳng, cổ phốt không rơ. Bạn nào cần xe gọn, dễ bảo dưỡng thì qua xem trực tiếp.'
      WHEN 2 THEN 'Mua lại từ người quen nên lịch sử xe khá rõ. Mình đã vệ sinh sên líp và chỉnh lại thắng trước khi đăng. Ngoại hình còn đẹp, có trầy nhẹ ở tay đề.'
      WHEN 3 THEN 'Xe phù hợp người mới chơi, không cần nâng cấp thêm nhiều. Bộ truyền động hoạt động bình thường, yên và ghi đông còn sạch. Bán vì không còn thời gian đạp.'
      WHEN 4 THEN 'Đã thay ruột sau và bọc lại tay nắm tháng trước. Xe chạy êm, không phát tiếng lạ. Khuyến khích xem ban ngày để kiểm tra kỹ màu sơn và phụ tùng.'
      WHEN 5 THEN 'Xe nữ trong nhà dùng nên khá giữ gìn. Có hóa đơn mua ban đầu, phụ kiện kèm theo gồm chân chống và bình nước. Giá còn thương lượng nhẹ cho bạn thiện chí.'
      WHEN 6 THEN 'Xe đã đi vài chuyến xa nên có dấu sử dụng thật, nhưng máy móc ổn. Mình mô tả đúng tình trạng, xem xe không ưng thì thoải mái bỏ qua.'
      WHEN 7 THEN 'Cần bán nhanh để dọn chỗ. Xe để lâu khoảng một tháng, trước khi bán đã bơm lốp và kiểm tra phanh. Phù hợp đi học, đi làm hoặc tập thể dục.'
      ELSE 'Xe còn sử dụng hằng ngày nên lịch xem xe hẹn trước giúp mình. Không lỗi nặng, chỉ có xước lặt vặt theo thời gian. Có thể test quanh khu vực gần nhà.'
    END AS realistic_description
  FROM products
  WHERE id >= 12
) seed ON seed.id = p.id
SET
  p.title = seed.realistic_title,
  p.bike_type = seed.realistic_type,
  p.location = seed.realistic_location,
  p.description = seed.realistic_description,
  p.price = CAST(
    CASE seed.realistic_type
      WHEN 'Road' THEN 8500000 + MOD(p.id * 930000, 38000000)
      WHEN 'MTB' THEN 5200000 + MOD(p.id * 710000, 22000000)
      WHEN 'Gravel' THEN 12000000 + MOD(p.id * 860000, 36000000)
      WHEN 'Touring' THEN 6500000 + MOD(p.id * 510000, 18000000)
      WHEN 'Hybrid' THEN 4800000 + MOD(p.id * 430000, 15000000)
      WHEN 'City' THEN 2800000 + MOD(p.id * 270000, 8500000)
      ELSE 3500000 + MOD(p.id * 330000, 12000000)
    END AS DECIMAL(15,2)
  ),
  p.condition_percent = 76 + MOD(p.id * 7, 23),
  p.frame_size = CASE MOD(p.id, 5)
    WHEN 0 THEN 'XS'
    WHEN 1 THEN 'S'
    WHEN 2 THEN 'M'
    WHEN 3 THEN 'L'
    ELSE 'XL'
  END;

DELETE FROM product_images;

INSERT INTO product_images (product_id, image_url, created_at)
SELECT
  p.id,
  CASE
    WHEN p.bike_type = 'MTB' THEN CASE MOD(p.id + image_seed.image_index, 6)
      WHEN 0 THEN 'https://images.pexels.com/photos/11219607/pexels-photo-11219607.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 1 THEN 'https://images.pexels.com/photos/27600453/pexels-photo-27600453.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 2 THEN 'https://images.pexels.com/photos/29382118/pexels-photo-29382118.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80'
      WHEN 4 THEN 'https://images.unsplash.com/photo-1502740479091-635887520276?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?auto=format&fit=crop&w=1200&q=80'
    END
    WHEN p.bike_type = 'Road' THEN CASE MOD(p.id + image_seed.image_index, 7)
      WHEN 0 THEN 'https://images.pexels.com/photos/13799193/pexels-photo-13799193.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 1 THEN 'https://images.pexels.com/photos/13799194/pexels-photo-13799194.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 2 THEN 'https://images.pexels.com/photos/18424630/pexels-photo-18424630.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80'
      WHEN 4 THEN 'https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?auto=format&fit=crop&w=1200&q=80'
      WHEN 5 THEN 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80'
    END
    WHEN p.bike_type = 'Gravel' THEN CASE MOD(p.id + image_seed.image_index, 5)
      WHEN 0 THEN 'https://images.pexels.com/photos/13799193/pexels-photo-13799193.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 1 THEN 'https://images.pexels.com/photos/13799194/pexels-photo-13799194.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 2 THEN 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80'
    END
    ELSE CASE MOD(p.id + image_seed.image_index, 6)
      WHEN 0 THEN 'https://images.pexels.com/photos/4061694/pexels-photo-4061694.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80'
      WHEN 2 THEN 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?auto=format&fit=crop&w=1200&q=80'
      WHEN 4 THEN 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80'
    END
  END AS image_url,
  DATE_ADD(p.created_at, INTERVAL image_seed.image_index MINUTE) AS created_at
FROM products p
JOIN (
  SELECT 1 AS image_index
  UNION ALL SELECT 2
  UNION ALL SELECT 3
) image_seed
WHERE image_seed.image_index <= CASE
  WHEN MOD(p.id, 4) = 0 THEN 3
  ELSE 2
END;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- --------------------------------------------------------
-- Extra SpinBike marketplace seed data
-- --------------------------------------------------------

SET NAMES utf8mb4;
START TRANSACTION;

SET @seed_start_product_id := (SELECT COALESCE(MAX(id), 0) + 1 FROM products);

CREATE TEMPORARY TABLE IF NOT EXISTS _spinbike_seed_nums (
  n INT PRIMARY KEY
);

DELETE FROM _spinbike_seed_nums;

INSERT INTO _spinbike_seed_nums (n) VALUES
(0),(1),(2),(3),(4),(5),(6),(7),(8),(9),
(10),(11),(12),(13),(14),(15),(16),(17),(18),(19),
(20),(21),(22),(23),(24),(25),(26),(27),(28),(29),
(30),(31),(32),(33),(34),(35),(36),(37),(38),(39),
(40),(41),(42),(43),(44),(45),(46),(47),(48),(49),
(50),(51),(52),(53),(54),(55),(56),(57),(58),(59),
(60),(61),(62),(63),(64),(65),(66),(67),(68),(69),
(70),(71),(72),(73),(74),(75),(76),(77),(78),(79),
(80),(81),(82),(83),(84),(85),(86),(87),(88),(89),
(90),(91),(92),(93),(94),(95),(96),(97),(98),(99);

INSERT INTO products (
  title,
  brand,
  bike_type,
  price,
  location,
  description,
  frame_size,
  condition_percent,
  listing_status,
  approval_note,
  approved_at,
  sold_at,
  created_at,
  seller_id
)
SELECT
  CONCAT(
    brand_name,
    ' ',
    model_name,
    ' ',
    2020 + MOD(n, 6)
  ) AS title,
  brand_name AS brand,
  bike_type,
  CAST(4500000 + MOD(n * 1370000, 52000000) AS DECIMAL(15,2)) AS price,
  location_name AS location,
  CONCAT(
    'Xe ',
    LOWER(bike_type),
    ' đã qua sử dụng, tình trạng còn tốt, khung sườn chắc chắn và vận hành ổn định. ',
    'Phù hợp cho nhu cầu đi làm, luyện tập cuối tuần hoặc nâng cấp từ xe phổ thông. ',
    'Người bán khuyến khích xem xe trực tiếp để kiểm tra size, phụ tùng và ngoại hình.'
  ) AS description,
  CASE MOD(n, 5)
    WHEN 0 THEN 'XS'
    WHEN 1 THEN 'S'
    WHEN 2 THEN 'M'
    WHEN 3 THEN 'L'
    ELSE 'XL'
  END AS frame_size,
  80 + MOD(n * 3, 20) AS condition_percent,
  listing_status,
  CASE listing_status
    WHEN 'approved' THEN 'Tin hợp lệ, hình ảnh rõ và thông tin đầy đủ.'
    WHEN 'pending' THEN 'Tin đang chờ quản trị viên kiểm tra.'
    WHEN 'rejected' THEN 'Cần bổ sung ảnh thực tế và mô tả chi tiết hơn.'
    WHEN 'hidden' THEN 'Tin đang được tạm ẩn theo yêu cầu người bán.'
    WHEN 'sold' THEN 'Xe đã phát sinh giao dịch hoặc đã bán.'
    ELSE NULL
  END AS approval_note,
  CASE
    WHEN listing_status IN ('approved', 'sold', 'hidden') THEN DATE_SUB(NOW(), INTERVAL (n + 2) HOUR)
    ELSE NULL
  END AS approved_at,
  CASE
    WHEN listing_status = 'sold' THEN DATE_SUB(NOW(), INTERVAL (n + 1) HOUR)
    ELSE NULL
  END AS sold_at,
  DATE_SUB(NOW(), INTERVAL (n * 3 + 5) HOUR) AS created_at,
  CASE MOD(n, 4)
    WHEN 0 THEN 3
    WHEN 1 THEN 4
    WHEN 2 THEN 7
    ELSE 8
  END AS seller_id
FROM (
  SELECT
    n,
    CASE MOD(n, 12)
      WHEN 0 THEN 'Giant'
      WHEN 1 THEN 'Trek'
      WHEN 2 THEN 'Specialized'
      WHEN 3 THEN 'Cannondale'
      WHEN 4 THEN 'Merida'
      WHEN 5 THEN 'Scott'
      WHEN 6 THEN 'Java'
      WHEN 7 THEN 'Twitter'
      WHEN 8 THEN 'Polygon'
      WHEN 9 THEN 'Trinx'
      WHEN 10 THEN 'Asama'
      ELSE 'Martin 107'
    END AS brand_name,
    CASE MOD(n, 5)
      WHEN 0 THEN 'Road'
      WHEN 1 THEN 'MTB'
      WHEN 2 THEN 'Gravel'
      WHEN 3 THEN 'Touring'
      ELSE 'Fixed'
    END AS bike_type,
    CASE MOD(n, 16)
      WHEN 0 THEN 'TCR Advanced'
      WHEN 1 THEN 'Domane AL'
      WHEN 2 THEN 'Allez Sport'
      WHEN 3 THEN 'Trail SE'
      WHEN 4 THEN 'Scultura'
      WHEN 5 THEN 'Speedster'
      WHEN 6 THEN 'Siluro'
      WHEN 7 THEN 'Gravel RS'
      WHEN 8 THEN 'Path 3'
      WHEN 9 THEN 'Free 2.0'
      WHEN 10 THEN 'MTB Pro'
      WHEN 11 THEN 'Touring City'
      WHEN 12 THEN 'XTC 820'
      WHEN 13 THEN 'Checkpoint ALR'
      WHEN 14 THEN 'Diverge E5'
      ELSE 'Urban Fixed'
    END AS model_name,
    CASE MOD(n, 20)
      WHEN 0 THEN 'pending'
      WHEN 1 THEN 'pending'
      WHEN 2 THEN 'hidden'
      WHEN 3 THEN 'rejected'
      WHEN 4 THEN 'sold'
      WHEN 5 THEN 'sold'
      WHEN 6 THEN 'sold'
      ELSE 'approved'
    END AS listing_status,
    CASE MOD(n, 18)
      WHEN 0 THEN 'Phường Bến Thành, TP. Hồ Chí Minh'
      WHEN 1 THEN 'Phường Thủ Đức, TP. Hồ Chí Minh'
      WHEN 2 THEN 'Phường Bình Thạnh, TP. Hồ Chí Minh'
      WHEN 3 THEN 'Phường Cầu Giấy, Hà Nội'
      WHEN 4 THEN 'Phường Hoàn Kiếm, Hà Nội'
      WHEN 5 THEN 'Phường Hải Châu, Đà Nẵng'
      WHEN 6 THEN 'Phường Ninh Kiều, Cần Thơ'
      WHEN 7 THEN 'Phường Biên Hòa, Đồng Nai'
      WHEN 8 THEN 'Phường Thủ Dầu Một, TP. Hồ Chí Minh'
      WHEN 9 THEN 'Phường Ngô Quyền, Hải Phòng'
      WHEN 10 THEN 'Phường Quy Nhơn, Gia Lai'
      WHEN 11 THEN 'Phường Nha Trang, Khánh Hòa'
      WHEN 12 THEN 'Phường Vũng Tàu, TP. Hồ Chí Minh'
      WHEN 13 THEN 'Phường Huế, TP. Huế'
      WHEN 14 THEN 'Phường Buôn Ma Thuột, Đắk Lắk'
      WHEN 15 THEN 'Phường Hạ Long, Quảng Ninh'
      WHEN 16 THEN 'Phường Long Xuyên, An Giang'
      ELSE 'Phường Mỹ Tho, Đồng Tháp'
    END AS location_name
  FROM _spinbike_seed_nums
) AS seed_source;

INSERT INTO product_images (product_id, image_url)
SELECT
  p.id,
  CASE image_seed.image_index
    WHEN 1 THEN CASE MOD(p.id, 8)
      WHEN 0 THEN 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80'
      WHEN 2 THEN 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?auto=format&fit=crop&w=1200&q=80'
      WHEN 4 THEN 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80'
      WHEN 5 THEN 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?auto=format&fit=crop&w=1200&q=80'
      WHEN 6 THEN 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=80'
    END
    WHEN 2 THEN CASE MOD(p.id, 6)
      WHEN 0 THEN 'https://images.unsplash.com/photo-1502740479091-635887520276?auto=format&fit=crop&w=1200&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80'
      WHEN 2 THEN 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?auto=format&fit=crop&w=1200&q=80'
      WHEN 4 THEN 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?auto=format&fit=crop&w=1200&q=80'
    END
    ELSE CASE MOD(p.id, 5)
      WHEN 0 THEN 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=1200&q=80'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80'
      WHEN 2 THEN 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=80'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1502740479091-635887520276?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80'
    END
  END AS image_url
FROM products p
JOIN (
  SELECT 1 AS image_index
  UNION ALL SELECT 2
  UNION ALL SELECT 3
) AS image_seed
WHERE p.id >= @seed_start_product_id
  AND p.id < @seed_start_product_id + 100
  AND image_seed.image_index <= CASE
    WHEN MOD(p.id, 3) = 0 THEN 3
    ELSE 2
  END;

DROP TEMPORARY TABLE IF EXISTS _spinbike_seed_nums;

COMMIT;

-- --------------------------------------------------------
-- Final realistic pass for all generated marketplace listings
-- --------------------------------------------------------

START TRANSACTION;

UPDATE products p
JOIN (
  SELECT
    id,
    CASE MOD(id, 18)
      WHEN 0 THEN CONCAT('Giant TCR Advanced xe tập sáng còn đẹp - ', id)
      WHEN 1 THEN CONCAT('Trek Domane AL đi phố cuối tuần - ', id)
      WHEN 2 THEN CONCAT('Specialized Allez Sport cần bán nhanh - ', id)
      WHEN 3 THEN CONCAT('Cannondale Trail phanh dầu, size M - ', id)
      WHEN 4 THEN CONCAT('Merida Scultura đã lên vỏ mới - ', id)
      WHEN 5 THEN CONCAT('Scott Speedster màu xám, giấy tờ đủ - ', id)
      WHEN 6 THEN CONCAT('Java Siluro 3 cho người mới chơi - ', id)
      WHEN 7 THEN CONCAT('Twitter Gravel đi tour ngắn rất ổn - ', id)
      WHEN 8 THEN CONCAT('Polygon Path 3 đi làm hằng ngày - ', id)
      WHEN 9 THEN CONCAT('Trinx Free 2.0 xe nhà ít dùng - ', id)
      WHEN 10 THEN CONCAT('Asama MTB Pro bánh 27.5 - ', id)
      WHEN 11 THEN CONCAT('Martin Touring gác baga chắc chắn - ', id)
      WHEN 12 THEN CONCAT('Giant Escape dáng hybrid dễ đi - ', id)
      WHEN 13 THEN CONCAT('Trek Checkpoint ALR gravel size M - ', id)
      WHEN 14 THEN CONCAT('Specialized Diverge E5 đi cafe tour - ', id)
      WHEN 15 THEN CONCAT('Fixed gear khung nhôm, màu tối - ', id)
      WHEN 16 THEN CONCAT('Xe đạp city Nhật bãi còn zin - ', id)
      ELSE CONCAT('MTB Giant Talon cho sinh viên - ', id)
    END AS realistic_title,
    CASE MOD(id, 10)
      WHEN 0 THEN 'Road'
      WHEN 1 THEN 'Road'
      WHEN 2 THEN 'MTB'
      WHEN 3 THEN 'MTB'
      WHEN 4 THEN 'Gravel'
      WHEN 5 THEN 'Touring'
      WHEN 6 THEN 'Hybrid'
      WHEN 7 THEN 'City'
      WHEN 8 THEN 'Fixed'
      ELSE 'Road'
    END AS realistic_type,
    CASE MOD(id, 16)
      WHEN 0 THEN 'Phường Bến Thành, Quận 1, TP. Hồ Chí Minh'
      WHEN 1 THEN 'Phường An Phú, TP. Thủ Đức, TP. Hồ Chí Minh'
      WHEN 2 THEN 'Phường 25, Quận Bình Thạnh, TP. Hồ Chí Minh'
      WHEN 3 THEN 'Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội'
      WHEN 4 THEN 'Phường Hàng Bạc, Quận Hoàn Kiếm, Hà Nội'
      WHEN 5 THEN 'Phường Hải Châu, Quận Hải Châu, Đà Nẵng'
      WHEN 6 THEN 'Phường Cái Khế, Quận Ninh Kiều, Cần Thơ'
      WHEN 7 THEN 'Phường Tân Mai, TP. Biên Hòa, Đồng Nai'
      WHEN 8 THEN 'Phường Phú Hòa, TP. Thủ Dầu Một, Bình Dương'
      WHEN 9 THEN 'Phường Máy Tơ, Quận Ngô Quyền, Hải Phòng'
      WHEN 10 THEN 'Phường Ghềnh Ráng, TP. Quy Nhơn, Gia Lai'
      WHEN 11 THEN 'Phường Vĩnh Hòa, TP. Nha Trang, Khánh Hòa'
      WHEN 12 THEN 'Phường Thắng Tam, TP. Vũng Tàu, TP. Hồ Chí Minh'
      WHEN 13 THEN 'Phường Phú Hội, TP. Huế'
      WHEN 14 THEN 'Phường Tân Lợi, TP. Buôn Ma Thuột, Đắk Lắk'
      ELSE 'Phường Bãi Cháy, TP. Hạ Long, Quảng Ninh'
    END AS realistic_location,
    CASE MOD(id, 9)
      WHEN 0 THEN 'Mình bán vì mới lên đời xe khác. Xe đi tập buổi sáng là chính, để trong nhà, không dầm mưa. Có vài vết xước nhỏ do dựng xe chung nhưng khung không móp, sang số và thắng vẫn ổn.'
      WHEN 1 THEN 'Xe của nhà dùng đi làm gần, cuối tuần có chạy công viên. Vỏ còn dày, bánh quay thẳng, cổ phốt không rơ. Bạn nào cần xe gọn, dễ bảo dưỡng thì qua xem trực tiếp.'
      WHEN 2 THEN 'Mua lại từ người quen nên lịch sử xe khá rõ. Mình đã vệ sinh sên líp và chỉnh lại thắng trước khi đăng. Ngoại hình còn đẹp, có trầy nhẹ ở tay đề.'
      WHEN 3 THEN 'Xe phù hợp người mới chơi, không cần nâng cấp thêm nhiều. Bộ truyền động hoạt động bình thường, yên và ghi đông còn sạch. Bán vì không còn thời gian đạp.'
      WHEN 4 THEN 'Đã thay ruột sau và bọc lại tay nắm tháng trước. Xe chạy êm, không phát tiếng lạ. Khuyến khích xem ban ngày để kiểm tra kỹ màu sơn và phụ tùng.'
      WHEN 5 THEN 'Xe trong nhà dùng nên khá giữ gìn. Có hóa đơn mua ban đầu, phụ kiện kèm theo gồm chân chống và bình nước. Giá còn thương lượng nhẹ cho bạn thiện chí.'
      WHEN 6 THEN 'Xe đã đi vài chuyến xa nên có dấu sử dụng thật, nhưng máy móc ổn. Mình mô tả đúng tình trạng, xem xe không ưng thì thoải mái bỏ qua.'
      WHEN 7 THEN 'Cần bán nhanh để dọn chỗ. Xe để lâu khoảng một tháng, trước khi bán đã bơm lốp và kiểm tra phanh. Phù hợp đi học, đi làm hoặc tập thể dục.'
      ELSE 'Xe còn sử dụng hằng ngày nên lịch xem xe hẹn trước giúp mình. Không lỗi nặng, chỉ có xước lặt vặt theo thời gian. Có thể test quanh khu vực gần nhà.'
    END AS realistic_description
  FROM products
  WHERE id >= 12
) seed ON seed.id = p.id
SET
  p.title = seed.realistic_title,
  p.bike_type = seed.realistic_type,
  p.location = seed.realistic_location,
  p.description = seed.realistic_description,
  p.price = CAST(
    CASE seed.realistic_type
      WHEN 'Road' THEN 8500000 + MOD(p.id * 930000, 38000000)
      WHEN 'MTB' THEN 5200000 + MOD(p.id * 710000, 22000000)
      WHEN 'Gravel' THEN 12000000 + MOD(p.id * 860000, 36000000)
      WHEN 'Touring' THEN 6500000 + MOD(p.id * 510000, 18000000)
      WHEN 'Hybrid' THEN 4800000 + MOD(p.id * 430000, 15000000)
      WHEN 'City' THEN 2800000 + MOD(p.id * 270000, 8500000)
      ELSE 3500000 + MOD(p.id * 330000, 12000000)
    END AS DECIMAL(15,2)
  ),
  p.condition_percent = 76 + MOD(p.id * 7, 23),
  p.frame_size = CASE MOD(p.id, 5)
    WHEN 0 THEN 'XS'
    WHEN 1 THEN 'S'
    WHEN 2 THEN 'M'
    WHEN 3 THEN 'L'
    ELSE 'XL'
  END;

DELETE FROM product_images;

INSERT INTO product_images (product_id, image_url, created_at)
SELECT
  p.id,
  CASE
    WHEN p.bike_type = 'MTB' THEN CASE MOD(p.id + image_seed.image_index, 6)
      WHEN 0 THEN 'https://images.pexels.com/photos/11219607/pexels-photo-11219607.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 1 THEN 'https://images.pexels.com/photos/27600453/pexels-photo-27600453.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 2 THEN 'https://images.pexels.com/photos/29382118/pexels-photo-29382118.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80'
      WHEN 4 THEN 'https://images.unsplash.com/photo-1502740479091-635887520276?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?auto=format&fit=crop&w=1200&q=80'
    END
    WHEN p.bike_type = 'Road' THEN CASE MOD(p.id + image_seed.image_index, 7)
      WHEN 0 THEN 'https://images.pexels.com/photos/13799193/pexels-photo-13799193.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 1 THEN 'https://images.pexels.com/photos/13799194/pexels-photo-13799194.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 2 THEN 'https://images.pexels.com/photos/18424630/pexels-photo-18424630.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80'
      WHEN 4 THEN 'https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?auto=format&fit=crop&w=1200&q=80'
      WHEN 5 THEN 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80'
    END
    WHEN p.bike_type = 'Gravel' THEN CASE MOD(p.id + image_seed.image_index, 5)
      WHEN 0 THEN 'https://images.pexels.com/photos/13799193/pexels-photo-13799193.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 1 THEN 'https://images.pexels.com/photos/13799194/pexels-photo-13799194.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 2 THEN 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80'
    END
    ELSE CASE MOD(p.id + image_seed.image_index, 6)
      WHEN 0 THEN 'https://images.pexels.com/photos/4061694/pexels-photo-4061694.jpeg?auto=compress&cs=tinysrgb&w=1200'
      WHEN 1 THEN 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80'
      WHEN 2 THEN 'https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=1200&q=80'
      WHEN 3 THEN 'https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?auto=format&fit=crop&w=1200&q=80'
      WHEN 4 THEN 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?auto=format&fit=crop&w=1200&q=80'
      ELSE 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80'
    END
  END AS image_url,
  DATE_ADD(p.created_at, INTERVAL image_seed.image_index MINUTE) AS created_at
FROM products p
JOIN (
  SELECT 1 AS image_index
  UNION ALL SELECT 2
  UNION ALL SELECT 3
) image_seed
WHERE image_seed.image_index <= CASE
  WHEN MOD(p.id, 4) = 0 THEN 3
  ELSE 2
END;

COMMIT;
