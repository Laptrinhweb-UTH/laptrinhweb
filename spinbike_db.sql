-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 17, 2026 lúc 01:10 PM
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
  `status` enum('holding','released','refunded','disputed') DEFAULT 'holding',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `released_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `escrows`
--

INSERT INTO `escrows` (`id`, `order_id`, `amount`, `status`, `created_at`, `released_at`) VALUES
(1, 1, 500000.00, 'holding', '2026-04-15 03:29:23', NULL),
(2, 2, 500000.00, 'holding', '2026-04-15 03:54:16', NULL),
(3, 3, 500000.00, 'holding', '2026-04-17 11:03:31', NULL);

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
  `status` enum('pending','paid','shipping','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `seller_id`, `product_id`, `amount`, `status`, `created_at`) VALUES
(1, 8, 1, 4, 500000.00, 'paid', '2026-04-15 03:29:23'),
(2, 8, 1, 4, 500000.00, 'paid', '2026-04-15 03:54:16'),
(3, 8, 1, 4, 500000.00, 'paid', '2026-04-17 11:03:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `frame_size` varchar(50) DEFAULT NULL,
  `condition_percent` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seller_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `title`, `brand`, `price`, `location`, `description`, `frame_size`, `condition_percent`, `created_at`, `seller_id`) VALUES
(3, 'xe đạp lách cách', 'Asama', 3000000.00, 'Tô Đắc Kỷ, Xã Hợp Tiến, Huyện Kim Bôi, Tỉnh Hoà Bình', 'xe cùi không thích dùng', NULL, NULL, '2026-04-13 08:09:24', 1),
(4, 'xe cùi', 'hãng ngu', 500000.00, 'đường lên dốc đá, Xã Quang Minh, Huyện Văn Yên, Tỉnh Yên Bái', 'xe cùi ko mua', NULL, NULL, '2026-04-13 09:14:42', 1);

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
(4, 3, 'https://res.cloudinary.com/dge3u1dzk/image/upload/v1776067769/xkrq39mlzulmzhcxyrwr.png', '2026-04-13 08:09:28'),
(5, 3, 'https://res.cloudinary.com/dge3u1dzk/image/upload/v1776067771/pj2bjyzknzondf6gfkhr.png', '2026-04-13 08:09:31'),
(6, 3, 'https://res.cloudinary.com/dge3u1dzk/image/upload/v1776067775/mynxfz6uqqb5a4dhwkih.png', '2026-04-13 08:09:35'),
(7, 3, 'https://res.cloudinary.com/dge3u1dzk/image/upload/v1776067779/vlopgjbrgzgndxnkarap.png', '2026-04-13 08:09:39'),
(8, 3, 'https://res.cloudinary.com/dge3u1dzk/image/upload/v1776067784/xv36eqijhtly2jgm8slg.png', '2026-04-13 08:09:44'),
(9, 4, 'https://res.cloudinary.com/dge3u1dzk/image/upload/v1776071686/y3xpqdvk75vwqueaieom.jpg', '2026-04-13 09:14:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `fee` decimal(15,2) DEFAULT 0.00,
  `type` enum('deposit','payment','earn','refund','fee') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `balance` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `avatar`, `role`, `created_at`, `balance`) VALUES
(1, 'Hoai Nam', 'user@gmail.com', '123456', '0987654321', 'default.png', 'user', '2026-03-26 04:30:13', 0.00),
(5, 'Hoài Namm', 'nguyennam2017an@gmail.com', '$2y$10$CGZ.0insc7YdYn7OR6flguys5R27q6hLcKE6vRZDH6I81rG0.HGN.', NULL, NULL, 'user', '2026-03-26 04:54:09', 0.00),
(7, 'ưegte5r', 'nguyennam22017an@gmail.com', '$2y$10$GPlvX3GgNEt4Z8WwRgGQgu9YVzRwgakR7gEr3O6hRJP7u2RVNNCL.', NULL, NULL, 'user', '2026-03-26 13:18:01', 0.00),
(8, '123', 'Namhoai810289@gmail.com', '$2y$10$blmxknXnw/5LWhQBC9O/2uH890Gn.jjE3dakFKd68Y7ufExTUu5SW', NULL, NULL, 'user', '2026-04-15 02:43:41', 0.00);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `escrows`
--
ALTER TABLE `escrows`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_seller` (`seller_id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `escrows`
--
ALTER TABLE `escrows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
