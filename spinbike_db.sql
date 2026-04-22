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

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `avatar`, `role`, `balance`, `created_at`, `reset_token`, `reset_token_expire`) VALUES
(1, 'Admin SpinBike', 'admin@spinbike.local', '$2y$12$JQh8OnsH9xHBQUVTQ09TUuF1h/ZCom5uNhKlBi6I3NgkTS.araUOa', '0909000001', 'https://ui-avatars.com/api/?name=Admin+SpinBike&background=0f766e&color=ffffff&rounded=true', 'admin', 0.00, '2026-04-10 01:00:00', NULL, NULL),
(2, 'Nguyen Hoai Nam', 'nguyennam2017an@gmail.com', '123', '0909000002', 'https://ui-avatars.com/api/?name=Nguyen+Hoai+Nam&background=10b981&color=ffffff&rounded=true', 'user', 59565000.00, '2026-04-10 01:10:00', NULL, NULL),
(3, 'Doan Hoai An', 'seller2@spinbike.local', '$2y$12$JjOIFQdOSgirTasQhVGshes7BlKeAyTqJz9ZI/hmLoZQIOxLrqRHu', '0909000003', 'https://ui-avatars.com/api/?name=Doan+Hoai+An&background=0ea5e9&color=ffffff&rounded=true', 'user', 0.00, '2026-04-10 01:15:00', NULL, NULL),
(4, 'Nguyen Hoang Linh Tu', 'seller3@spinbike.local', '$2y$12$JjOIFQdOSgirTasQhVGshes7BlKeAyTqJz9ZI/hmLoZQIOxLrqRHu', '0909000004', 'https://ui-avatars.com/api/?name=Nguyen+Hoang+Linh+Tu&background=f59e0b&color=ffffff&rounded=true', 'user', 0.00, '2026-04-10 01:20:00', NULL, NULL),
(5, 'Tran Minh Buyer', 'buyer1@spinbike.local', '$2y$12$R0J5V0YqKdcoSEbrB9njB.WQhJKz.Jwukomkh6Nm/0CkBrnyfCHWi', '0909000005', 'https://ui-avatars.com/api/?name=Tran+Minh+Buyer&background=6366f1&color=ffffff&rounded=true', 'user', 18500000.00, '2026-04-10 01:25:00', NULL, NULL),
(6, 'Le Gia Buyer', 'buyer2@spinbike.local', '$2y$12$R0J5V0YqKdcoSEbrB9njB.WQhJKz.Jwukomkh6Nm/0CkBrnyfCHWi', '0909000006', 'https://ui-avatars.com/api/?name=Le+Gia+Buyer&background=ef4444&color=ffffff&rounded=true', 'user', 9200000.00, '2026-04-10 01:30:00', NULL, NULL),
(7, 'Demo User', 'demo@spinbike.local', '$2y$12$NPuKDIHq1n1kzN1MDXWQ/OI2oE.7og.M0j9yDMVoUQvZdPj3LQAyC', '0909000007', 'https://ui-avatars.com/api/?name=Demo+User&background=334155&color=ffffff&rounded=true', 'user', 0.00, '2026-04-10 01:35:00', NULL, NULL),
(8, 'Hoài Namm', 'nguyennam2017an1@gmail.com', '$2y$10$PpDM5RwDmZ9qhBno8a.vIeXixvsyiKRXlA4xCXAtYoNdN/kYUa5rG', '', 'https://res.cloudinary.com/dge3u1dzk/image/upload/v1776699709/gsj3dpemxwc6dxhc75ik.jpg', 'user', 0.00, '2026-04-20 15:41:20', NULL, NULL);

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
