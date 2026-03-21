-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th3 21, 2026 lúc 07:04 PM
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
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Xe đạp thể thao đường phố');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `old_price` int(11) DEFAULT NULL,
  `discount` varchar(50) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `brand`, `price`, `old_price`, `discount`, `is_featured`, `created_at`) VALUES
(1, 'Xe Đạp Đường Phố Touring RAPTOR Mocha 2B - Bánh 26 Inches', 'RAPTOR', 5790000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(2, 'Xe Đạp Đường Phố Touring RAPTOR Mocha 1B - Bánh 24 Inches', 'RAPTOR', 5590000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(3, 'Xe Đạp Đường Phố Touring RAPTOR City - Phanh Đĩa, Bánh 700C', 'RAPTOR', 6390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(4, 'Xe Đạp Đường Phố Touring LIV Alight 2 Disc - Phanh Đĩa, Bánh 700C - 2025', 'LIV', 14790000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(5, 'Xe Đạp Đường Phố Touring LIV Alight 2 DD Disc – Phanh Đĩa, Bánh 700C – 2022', 'LIV', 12590000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(6, 'Xe Đạp Gấp Folding JAVA Neo-9S - Phanh Đĩa, Bánh 16Inch', 'JAVA', 14990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(7, 'Xe Đạp Gấp Folding JAVA Volta-7S - Phanh Đĩa, Bánh 16Inch', 'JAVA', 6390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(8, 'Xe Đạp Đường Phố Touring RAPTOR Eva 4 - Bánh 26 Inch', 'RAPTOR', 2390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(9, 'Xe Đạp Đường Phố Youth RAPTOR Eva 3 - Bánh 24 Inch', 'RAPTOR', 2290000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(10, 'Xe Đạp Đường Phố Touring GIANT Fastroad AR Advanced 1-Asia - Phanh Đĩa, Bánh 700C - 2026', 'GIANT', 46790000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(11, 'Xe Đạp Đường Phố Touring GIANT Roam 4 - Phanh Đĩa, Bánh 700C - 2026', 'GIANT', 12790000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(12, 'Xe Đạp Đường Phố Touring JAVA Sequoia-7S-City - Phanh Đĩa, Bánh 700C', 'JAVA', 6390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(13, 'Xe Đạp Địa Hình MTB RAPTOR Hunter 2B - Phanh Đĩa, Bánh 26 Inch', 'RAPTOR', 3390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(14, 'Xe Đạp Đường Phố Touring RAPTOR Lily 4 - Bánh 26 Inch', 'RAPTOR', 2290000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(15, 'Xe Đạp Đường Phố Touring RAPTOR Lily 3 - Bánh 24 Inch', 'RAPTOR', 2190000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(16, 'Xe Đạp Đường Phố Touring JAVA Wahoo City - Phanh Đĩa, Bánh 700C', 'JAVA', 6990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(17, 'Xe Đạp Đường Phố Touring JAVA Veloce City - Phanh Đĩa, Bánh 700C', 'JAVA', 8390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(18, 'Xe Đạp Đường Phố Touring RAPTOR Feliz 2B - Phanh Đĩa, Bánh 700C', 'RAPTOR', 4590000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(19, 'Xe Đạp Đường Phố Touring RAPTOR Feliz 2 - Phanh Đĩa, Bánh 700C', 'RAPTOR', 4390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(20, 'Xe Đạp Đường Phố Touring RAPTOR City - Phanh Đĩa, Bánh 700C', 'RAPTOR', 5790000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(21, 'Xe Đạp Đường Phố Touring MOMENTUM Ineed Latte 26 - Bánh 26 Inches - 2026', 'MOMENTUM', 11390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(22, 'Xe Đạp Đường Phố Touring RAPTOR Turbo 1B - Bánh 700C', 'RAPTOR', 3590000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(23, 'Xe Đạp Gấp Folding JAVA X2 16 - Phanh Đĩa, Bánh 16 Inches', 'JAVA', 6390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(24, 'Xe Đạp Đường Phố Touring RAPTOR Mocha 1 - Phanh Đĩa, Bánh 24 Inch', 'RAPTOR', 3990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(25, 'Xe Đạp Đường Phố Touring GIANT Escape 4 Disc - Phanh Đĩa, Bánh 700C - 2025', 'GIANT', 9590000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(26, 'Xe Đạp Đường Phố Touring JAVA Veloce City - Phanh Đĩa, Bánh 700C', 'JAVA', 8990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(27, 'Xe Đạp Đường Phố Touring LIV Alight 4 Disc - Phanh Đĩa, Bánh 700C - 2025', 'LIV', 10390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(28, 'Xe Đạp Đường Phố Touring GIANT Roam 3 - Phanh Đĩa, Bánh 700C - 2025', 'GIANT', 13990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(29, 'Xe Đạp Đường Phố Touring GIANT Roam 2 - Phanh Đĩa, Bánh 700C - 2025', 'GIANT', 17790000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(30, 'Xe Đạp Đường Phố Touring LIV Alight 3 Disc - Phanh Đĩa, Bánh 700C - 2025', 'LIV', 12390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(31, 'Xe Đạp Đường Phố Touring GIANT Roam 4 - Phanh Đĩa, Bánh 700C - 2025', 'GIANT', 10990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(32, 'Xe Đạp Đường Phố Touring GIANT Escape 3 Disc - Phanh Đĩa, Bánh 700C - 2025', 'GIANT', 11590000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(33, 'Xe Đạp Đường Phố Touring GIANT Escape 2 Disc - Phanh Đĩa, Bánh 700C - 2025', 'GIANT', 12990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(34, 'Xe Đạp Đường Phố Touring GIANT Escape 2 City Disc - Phanh Đĩa, Bánh 700C - 2025', 'GIANT', 13990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(35, 'Xe Đạp Đường Phố Touring RAPTOR Sky - Phanh Đĩa, Bánh 700C', 'RAPTOR', 5690000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(36, 'Xe Đạp Đường Phố Touring VINBIKE Lily 26 – Bánh 26 Inches', 'VINBIKE', 2190000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(37, 'Xe Đạp Đường Phố Touring VINBIKE Lily 24 – Bánh 24 Inches', 'VINBIKE', 2990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(38, 'Xe Đạp Đường Phố Touring GIANT Revolt-F 1 – Phanh Đĩa, Bánh 700C – 2022', 'GIANT', 21790000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(39, 'Xe Đạp Đường Phố Touring GIANT Roam 2 Disc, Phanh Đĩa, Bánh 700C – 2024', 'GIANT', 13390000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(40, 'Xe Đạp Đường Phố Touring TRINX Free 2.2 – Phanh Đĩa, Bánh 700C', 'TRINX', 5790000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(41, 'Xe Đạp Đường Phố Touring GIANT Escape 1 Disc – Phanh Đĩa, Bánh 700C – 2024', 'GIANT', 15990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(42, 'Xe Đạp Đường Phố Touring MAX BIKE Mocha – Bánh 26 Inches', 'MAX BIKE', 4590000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(43, 'Xe Đạp Đường Phố Touring VINBIKE Eva – Bánh 26 Inches', 'VINBIKE', 2990000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(44, 'Xe Đạp Đường Phố Fixed Gear VINBIKE Megatron – Bánh 700C', 'VINBIKE', 4490000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(45, 'Xe Đạp Đường Phố Fixed Gear VINBIKE Maximus – Bánh 700C', 'VINBIKE', 4490000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(46, 'Xe Đạp Đường Phố Touring TRINX Free 2.4 – Phanh Đĩa, Bánh 700C – 2023', 'TRINX', 6490000, NULL, NULL, 0, '2026-03-20 09:36:10'),
(47, 'Xe Đạp Đường Phố Touring GTIX Delta 1 – Phanh Đĩa, Bánh 700C', 'GTIX', 9890000, NULL, NULL, 0, '2026-03-20 09:36:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_category`
--

CREATE TABLE `product_category` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_category`
--

INSERT INTO `product_category` (`product_id`, `category_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `is_primary`, `sort_order`) VALUES
(1, 1, 'images/products/xedapthethao/xe0001.jpg', 1, 1),
(2, 2, 'images/products/xedapthethao/xe0002.jpg', 1, 1),
(3, 3, 'images/products/xedapthethao/xe0003.jpg', 1, 1),
(4, 4, 'images/products/xedapthethao/xe0004.jpg', 1, 1),
(5, 5, 'images/products/xedapthethao/xe0005.jpg', 1, 1),
(6, 6, 'images/products/xedapthethao/xe0006.jpg', 1, 1),
(7, 7, 'images/products/xedapthethao/xe0007.jpg', 1, 1),
(8, 8, 'images/products/xedapthethao/xe0008.jpg', 1, 1),
(9, 9, 'images/products/xedapthethao/xe0009.jpg', 1, 1),
(10, 10, 'images/products/xedapthethao/xe0010.jpg', 1, 1),
(11, 11, 'images/products/xedapthethao/xe0011.jpg', 1, 1),
(12, 12, 'images/products/xedapthethao/xe0012.jpg', 1, 1),
(13, 13, 'images/products/xedapthethao/xe0013.jpg', 1, 1),
(14, 14, 'images/products/xedapthethao/xe0014.jpg', 1, 1),
(15, 15, 'images/products/xedapthethao/xe0015.jpg', 1, 1),
(16, 16, 'images/products/xedapthethao/xe0016.jpg', 1, 1),
(17, 17, 'images/products/xedapthethao/xe0017.jpg', 1, 1),
(18, 18, 'images/products/xedapthethao/xe0018.jpg', 1, 1),
(19, 19, 'images/products/xedapthethao/xe0019.jpg', 1, 1),
(20, 20, 'images/products/xedapthethao/xe0020.jpg', 1, 1),
(21, 21, 'images/products/xedapthethao/xe0021.jpg', 1, 1),
(22, 22, 'images/products/xedapthethao/xe0022.jpg', 1, 1),
(23, 23, 'images/products/xedapthethao/xe0023.jpg', 1, 1),
(24, 24, 'images/products/xedapthethao/xe0024.jpg', 1, 1),
(25, 25, 'images/products/xedapthethao/xe0025.jpg', 1, 1),
(26, 26, 'images/products/xedapthethao/xe0026.jpg', 1, 1),
(27, 27, 'images/products/xedapthethao/xe0027.jpg', 1, 1),
(28, 28, 'images/products/xedapthethao/xe0028.jpg', 1, 1),
(29, 29, 'images/products/xedapthethao/xe0029.jpg', 1, 1),
(30, 30, 'images/products/xedapthethao/xe0030.jpg', 1, 1),
(31, 31, 'images/products/xedapthethao/xe0031.jpg', 1, 1),
(32, 32, 'images/products/xedapthethao/xe0032.jpg', 1, 1),
(33, 33, 'images/products/xedapthethao/xe0033.jpg', 1, 1),
(34, 34, 'images/products/xedapthethao/xe0034.jpg', 1, 1),
(35, 35, 'images/products/xedapthethao/xe0035.jpg', 1, 1),
(36, 36, 'images/products/xedapthethao/xe0036.jpg', 1, 1),
(37, 37, 'images/products/xedapthethao/xe0037.jpg', 1, 1),
(38, 38, 'images/products/xedapthethao/xe0038.jpg', 1, 1),
(39, 39, 'images/products/xedapthethao/xe0039.jpg', 1, 1),
(40, 40, 'images/products/xedapthethao/xe0040.jpg', 1, 1),
(41, 41, 'images/products/xedapthethao/xe0041.jpg', 1, 1),
(42, 42, 'images/products/xedapthethao/xe0042.jpg', 1, 1),
(43, 43, 'images/products/xedapthethao/xe0043.jpg', 1, 1),
(44, 44, 'images/products/xedapthethao/xe0044.jpg', 1, 1),
(45, 45, 'images/products/xedapthethao/xe0045.jpg', 1, 1),
(46, 46, 'images/products/xedapthethao/xe0046.jpg', 1, 1),
(47, 47, 'images/products/xedapthethao/xe0047.jpg', 1, 1);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `product_category`
--
ALTER TABLE `product_category`
  ADD CONSTRAINT `product_category_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
