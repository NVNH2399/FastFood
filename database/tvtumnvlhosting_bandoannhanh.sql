-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 23, 2026 at 03:40 AM
-- Server version: 10.6.24-MariaDB-cll-lve-log
-- PHP Version: 8.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tvtumnvlhosting_bandoannhanh`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0 COMMENT '1-Default, 0-Not Default',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `idUser`, `address`, `phone`, `is_default`, `status`, `created_at`) VALUES
(1, 2, 'Ấp K11, Xã Phú Hiệp, Huyện Tam Nông, Tỉnh Đồng Tháp', '0374221390', 1, 'active', '2024-11-07 21:48:21'),
(2, 2, 'Khóm 3, Thị Trấn Tràm Chim, Huyện Tam Nông, Tỉnh Đồng Tháp', '0967326154', 0, 'inactive', '2024-11-20 22:14:24'),
(3, 2, 'Ấp K8, Xã Phú Đức, Huyện Tam Nông, Tỉnh Đồng Tháp', '0982269247', 0, 'active', '2024-11-21 23:15:09'),
(4, 5, '153 Trương Hán Siêu, Phường Mỹ Phú, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0374221390', 1, 'active', '2024-12-06 08:36:09'),
(5, 5, 'Phạm Hữu Lầu, Phường 6, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0967326154', 0, 'active', '2024-12-06 09:24:51'),
(6, 7, '153 Trương Hán Siêu, Phường Mỹ Phú, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0374221390', 1, 'active', '2024-12-25 21:48:56');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `banner_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT '#',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`banner_id`, `title`, `image`, `link`, `status`, `created_at`, `sort_order`) VALUES
(1, 'banner2', '../upload/banner/banner2.jpg', '#', 'active', '2024-11-23 16:07:05', 2),
(2, 'banner3', '../upload/banner/banner3.jpg', '#', 'active', '2024-11-23 16:08:24', 3),
(3, 'banner4', '../upload/banner/banner4.jpg', '#', 'active', '2024-11-23 16:08:45', 4),
(4, 'banner5', '../upload/banner/banner5.jpg', '#', 'active', '2024-11-23 16:08:58', 5),
(5, 'banner_start', '../upload/banner/banner1.jpg', '#', 'active', '2024-11-23 16:13:09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `idProduct` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `username`, `idProduct`, `quantity`) VALUES
(38, 'anhuy', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `image`) VALUES
(1, 'Cơm - Mỳ Ý', '../upload/1729920968_com_my.jpg'),
(2, 'Burger', '../upload/1729920980_burger.jpg'),
(3, 'Gà Rán', '../upload/1729920991_garan.jpg'),
(4, 'Thức Ăn Nhẹ', '../upload/1729921005_thucannhe.jpg'),
(5, 'Thức Uống', '../upload/1729921016_thucuong.jpg'),
(6, 'Phần Ăn Nhóm', '../upload/1729921029_phanannhom.jpg'),
(7, 'Gà Rán Phần', '../upload/1729921076_garanphan.jpg'),
(9, 'Combo', '../upload/1732371742_MENU_DAT_HANG_COMBO_m_i_2.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `contact_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `content` text NOT NULL,
  `status` enum('processed','unprocessed') DEFAULT 'unprocessed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUser` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`contact_id`, `fullname`, `email`, `phone`, `content`, `status`, `created_at`, `idUser`) VALUES
(1, 'Nguyễn Văn N Hãi', 'hai772038@gmail.com', '0954736213', 'Chân thành cảm ơn cửa hàng vì đã làm ra những món ăn rất ngon và tiện lợi, sẽ luôn ửng hộ cửa hàng, chúc cửa hàng ngày càng thành công và phát triển.', 'processed', '2024-11-20 16:24:14', 2);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `discount` int(10) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `expiry_date` datetime DEFAULT NULL,
  `max_usage` int(11) DEFAULT NULL COMMENT 'Số lần tối đa được sử dụng',
  `current_usage` int(11) DEFAULT 0 COMMENT 'Số lần đã được sử dụng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `code`, `discount`, `status`, `created_at`, `expiry_date`, `max_usage`, `current_usage`) VALUES
(1, 'FOODGZO63K', 10, 'active', '2024-11-21 22:57:06', NULL, 10, 0),
(2, 'FOOD1K6F6N', 9, 'active', '2024-11-21 22:57:34', NULL, 8, 0),
(3, 'FOODS93K4R', 15, 'active', '2024-11-21 23:02:33', NULL, NULL, 0),
(4, 'FOODK0CJS6', 5, 'active', '2024-11-21 23:02:52', NULL, 55, 0),
(5, 'FOODTIFYW5', 3, 'inactive', '2024-11-23 17:14:45', NULL, NULL, 0),
(6, 'FOODVI1C5A', 12, 'active', '2025-07-28 20:13:50', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `idProduct` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `status` enum('pending','delivering','completed','canceled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` int(11) DEFAULT 0,
  `discount` int(11) DEFAULT 0,
  `final_amount` int(11) DEFAULT 0,
  `payment_method` varchar(20) NOT NULL DEFAULT 'cod' COMMENT 'cod: Cash on Delivery, momo: MoMo Payment',
  `payment_status` enum('pending','pending_payment','completed','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `idUser`, `idProduct`, `address`, `phone`, `status`, `created_at`, `total_amount`, `discount`, `final_amount`, `payment_method`, `payment_status`) VALUES
(1, 2, 19, 'Ấp K11, Xã Phú Hiệp, Huyện Tam Nông, Tỉnh Đồng Tháp', '0374221390', 'completed', '2024-11-20 23:34:42', 326000, 0, 326000, 'cod', 'completed'),
(2, 2, 37, 'Ấp K8, Xã Phú Đức, Huyện Tam Nông, Tỉnh Đồng Tháp', '0374221390', 'pending', '2024-11-20 23:50:29', 2200000, 0, 2200000, 'momo', 'pending'),
(3, 2, 48, 'Ấp K11, Xã Phú Hiệp, Huyện Tam Nông, Tỉnh Đồng Tháp', '0374221390', 'pending', '2024-11-20 23:54:21', 418000, 0, 418000, 'momo', 'pending'),
(5, 2, 10, 'Ấp K11, Xã Phú Hiệp, Huyện Tam Nông, Tỉnh Đồng Tháp', '0374221390', 'canceled', '2024-11-21 00:01:53', 98000, 0, 98000, 'momo', 'completed'),
(6, 2, 28, 'Ấp K11, Xã Phú Hiệp, Huyện Tam Nông, Tỉnh Đồng Tháp', '0374221390', 'completed', '2024-11-21 23:13:15', 468000, 42120, 425880, 'momo', 'completed'),
(7, 2, 29, 'Ấp K8, Xã Phú Đức, Huyện Tam Nông, Tỉnh Đồng Tháp', '0982269247', 'completed', '2024-11-23 21:13:35', 1170000, 175500, 994500, 'momo', 'completed'),
(8, 2, 54, 'Số nhà 159, Ấp 3, Xã Tân Công Sính, Huyện Tam Nông, Tỉnh Đồng Tháp', '0982269247', 'canceled', '2024-11-23 21:30:54', 1134000, 113400, 1020600, 'cod', 'pending'),
(10, 2, 34, 'Ấp K11, Xã Phú Hiệp, Huyện Tam Nông, Tỉnh Đồng Tháp', '0374221390', 'pending', '2024-12-04 21:33:18', 145000, 7250, 137750, 'cod', 'pending'),
(11, 2, 30, 'Ấp K11, Xã Phú Hiệp, Huyện Tam Nông, Tỉnh Đồng Tháp', '0374221390', 'pending', '2024-12-06 08:03:39', 117000, 0, 117000, 'momo', 'completed'),
(12, 5, 36, '153 Trương Hán Siêu, Phường Mỹ Phú, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0374221390', 'completed', '2024-12-06 08:10:49', 442000, 66300, 375700, 'momo', 'completed'),
(13, 5, 28, '153 Trương Hán Siêu, Phường Mỹ Phú, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0374221390', 'completed', '2024-12-06 08:13:28', 117000, 0, 117000, 'momo', 'completed'),
(14, 5, 27, '153 Trương Hán Siêu, Phường Mỹ Phú, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0374221390', 'completed', '2024-12-06 08:36:21', 225000, 33750, 191250, 'momo', 'completed'),
(15, 5, 31, '153 Trương Hán Siêu, Phường Mỹ Phú, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0374221390', 'completed', '2024-12-06 08:43:08', 285000, 28500, 256500, 'momo', 'completed'),
(16, 2, 18, 'Ấp K8, Xã Phú Đức, Huyện Tam Nông, Tỉnh Đồng Tháp', '0982269247', 'canceled', '2024-12-14 13:26:50', 495000, 44550, 450450, 'cod', 'pending'),
(17, 5, 6, '153 Trương Hán Siêu, Phường Mỹ Phú, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0374221390', 'completed', '2024-12-18 08:59:37', 177000, 0, 177000, 'cod', 'completed'),
(18, 7, 54, '153 Trương Hán Siêu, Phường Mỹ Phú, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0374221390', 'completed', '2024-12-25 21:50:01', 1020000, 153000, 867000, 'cod', 'completed'),
(19, 5, 6, 'Phạm Hữu Lầu, Phường 6, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0967326154', 'delivering', '2025-07-18 09:29:58', 1044000, 93960, 950040, 'cod', 'pending'),
(20, 5, 11, '153 Trương Hán Siêu, Phường Mỹ Phú, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0374221390', 'completed', '2025-07-28 20:14:50', 158000, 15800, 142200, 'cod', 'completed'),
(21, 1, 10, 'gbfgfgfgb', '0374221390', 'completed', '2026-01-26 18:39:27', 441000, 0, 441000, 'cod', 'completed'),
(22, 5, 53, 'Phạm Hữu Lầu, Phường 6, Thành phố Cao Lãnh, Tỉnh Đồng Tháp', '0967326154', 'delivering', '2026-05-30 19:18:13', 545000, 0, 545000, 'momo', 'completed'),
(23, 2, 5, 'Ấp K8, Xã Phú Đức, Huyện Tam Nông, Tỉnh Đồng Tháp', '0982269247', 'pending', '2026-06-24 20:18:10', 480000, 0, 480000, 'cod', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_coupons`
--

CREATE TABLE `order_coupons` (
  `idOrder` int(11) NOT NULL,
  `idCoupon` int(11) NOT NULL,
  `percent` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_coupons`
--

INSERT INTO `order_coupons` (`idOrder`, `idCoupon`, `percent`) VALUES
(6, 2, 9),
(7, 3, 15),
(8, 1, 10),
(10, 4, 5),
(12, 3, 15),
(14, 3, 15),
(15, 1, 10),
(16, 2, 9),
(18, 3, 15),
(19, 2, 9),
(20, 1, 10);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `idOrder` int(11) NOT NULL,
  `idProduct` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit_price` int(11) NOT NULL DEFAULT 0,
  `total_amount` int(11) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`idOrder`, `idProduct`, `quantity`, `unit_price`) VALUES
(1, 6, 2, 36000),
(1, 11, 2, 45000),
(1, 19, 2, 46000),
(1, 26, 2, 36000),
(2, 37, 10, 220000),
(3, 3, 2, 200000),
(3, 48, 1, 18000),
(5, 10, 2, 49000),
(6, 28, 4, 117000),
(7, 29, 10, 117000),
(8, 52, 2, 72000),
(8, 53, 2, 109000),
(8, 54, 2, 102000),
(8, 55, 2, 84000),
(8, 57, 2, 74000),
(8, 58, 2, 58000),
(8, 59, 2, 68000),
(10, 34, 1, 145000),
(11, 30, 1, 117000),
(12, 35, 1, 145000),
(12, 36, 1, 195000),
(12, 54, 1, 102000),
(13, 28, 1, 117000),
(14, 27, 1, 225000),
(15, 31, 3, 95000),
(16, 18, 5, 46000),
(16, 21, 5, 53000),
(17, 1, 1, 88000),
(17, 6, 1, 36000),
(17, 21, 1, 53000),
(18, 54, 10, 102000),
(19, 6, 29, 36000),
(20, 11, 1, 45000),
(20, 25, 1, 41000),
(20, 39, 2, 36000),
(21, 10, 9, 49000),
(22, 53, 5, 109000),
(23, 3, 1, 200000),
(23, 5, 1, 280000);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `idCategory` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `price`, `image`, `description`, `idCategory`) VALUES
(1, 'Burger Mozzarella', 88000, '../upload/1729923117_burger_mozzarella.png', '', 2),
(2, 'Cơm bò sốt Buldak', 46000, '../upload/1730550083_menu_com_1_.jpg', '', 1),
(3, 'Gà Rán (6 miếng)', 200000, '../upload/1730550228_222286_4.png', '', 3),
(4, 'Gà Rán Phần', 87000, '../upload/1730550294_chickenset_fried_1_.png', '02 Gà rán\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 7),
(5, 'L4 Set', 280000, '../upload/1730550375_pack_l4_set_3_1_.png', '04 Gà rán\r\n01 Burger Bulgogi\r\n01 Burger Tôm\r\n01 Khoai tây lắc\r\n04 Pepsi (M)', 6),
(6, 'Mực Rán (5 miếng)', 36000, '../upload/1730550453_mucran5mieng.png', '', 4),
(7, 'Pepsi Zero (L)', 18000, '../upload/1730550497_drink_pepsi_zero_m_l_.png', '', 5),
(8, 'Màn Thầu Chiên', 22000, '../upload/1730550586_dessert_fried_mantou.png', '', 4),
(9, 'Burger LChicken', 52000, '../upload/1731396365_burger_l-chicken.png', '', 2),
(10, 'Burger Tôm', 49000, '../upload/1731396403_burger_shrimp_1_.png', '', 2),
(11, 'Burger Bò Teriyaki', 45000, '../upload/1731396440_burger_beef_teriyaki.png', '', 2),
(12, 'Burger Cá', 40000, '../upload/1731396537_burger_fish_5.png', '', 2),
(13, 'Burger Double Double', 78000, '../upload/1731396565_burger_d-double.png', '', 2),
(14, 'Burger Bò', 38000, '../upload/1731396810_burger_beef.png', '', 2),
(15, 'Burger Gà Sốt Buldak', 52000, '../upload/1731396850_menu_burger_2.jpg', '', 2),
(16, 'Cơm Bò Trứng Phô Mai', 46000, '../upload/1731397090_lr0061-online_4.png', '', 1),
(17, 'Cơm Gà Sốt Đậu', 46000, '../upload/1731397121_lr0007-online_5.png', '', 1),
(18, 'Cơm Gà Sốt Phô Mai', 46000, '../upload/1731397154_lr0087-online_5.png', '', 1),
(19, 'Cơm Gà Viên', 46000, '../upload/1731397212_lr0002-online_5.png', '', 1),
(20, 'Mì Ý Thịt Bò', 43000, '../upload/1731397257_lr0038_6.png', '', 1),
(21, 'Mì Ý Thịt Bò (L)', 53000, '../upload/1731397307_lr0090_6.png', '', 1),
(22, 'Mì Ý (L)', 43000, '../upload/1731397342_lr0091_6.png', '', 1),
(23, 'Cơm', 7000, '../upload/1731397389_lr0004_4.png', '', 1),
(24, 'Gà Rán (3 miếng)', 103000, '../upload/1731397438_222278_4.png', '', 3),
(25, 'Gà sốt Buldak', 41000, '../upload/1731397462_menu_1_mieng.jpg', '', 3),
(26, 'Gà Rán (1 miếng)', 36000, '../upload/1731397502_lc0001_4.png', '', 3),
(27, 'Gà Rán Sốt Đậu (6 miếng)', 225000, '../upload/1731397545_222288_4.png', '', 3),
(28, 'Gà Sốt Đậu (3 miếng)', 117000, '../upload/1731397609_222280_4.png', '', 3),
(29, 'Gà Sốt Phô Mai (3 miếng)', 117000, '../upload/1731397658_222281_4.png', '', 3),
(30, 'Gà Sốt HS (3 miếng)', 117000, '../upload/1731397684_222279_1.png', '', 3),
(31, 'Gà Sốt HS Phần', 95000, '../upload/1731397883_chickenset_h_s_1_.png', '02 Gà sốt HS\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 7),
(32, 'Gà Sốt Đậu Phần', 95000, '../upload/1731397987_chickenset_soybean_1_.png', '02 Gà sốt đậu\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 7),
(33, 'Gà Sốt Phô Mai Phần', 95000, '../upload/1731398034_chickenset_cheese_1_.png', '02 Gà sốt phô mai\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 7),
(34, 'Loy Set', 145000, '../upload/1731398090_pack_loy_set_2_.png', '02 Gà rán\r\n01 Burger Bulgogi\r\n01 Khoai tây chiên (M)\r\n02 Pepsi (M)', 6),
(35, 'Lody Set', 145000, '../upload/1731398165_pack_lody_set_4.png', '02 Gà rán\r\n01 Mì ý\r\n01 Phô mai que\r\n02 Pepsi (M)', 6),
(36, 'Lony Set', 195000, '../upload/1731398230_pack_lony_set_3_1_.png', '02 Gà rán\r\n01 Burger Bulgogi\r\n01 Burger Tôm\r\n01 Khoai tây lắc\r\n02 Pepsi (M)', 6),
(37, 'Loking Set', 220000, '../upload/1731398265_pack_loking_set_2_.png', '02 Gà rán\r\n01 Mì ý\r\n01 Burger Bulgogi\r\n01 Burger Tôm\r\n01 Khoai tây chiên (M)\r\n03 Pepsi (M)', 6),
(38, 'Khoai Tây Chiên (M)', 28000, '../upload/1731398310_dessert_french_fries_m_i.png', '', 4),
(39, 'Phô Mai Que', 36000, '../upload/1731398331_dessert_cheese_stick_1_.png', '', 4),
(40, 'Khoai Tây Lắc', 39000, '../upload/1731398355_dessert_shake_potato_ph_mai_.png', '', 4),
(41, 'Gà Lắc', 44000, '../upload/1731398381_dessert_shake_chicken_ph_mai_.png', '', 4),
(42, 'Mực Rán (3 miếng)', 28000, '../upload/1731398405_ls0020_5.png', '', 4),
(43, 'Cánh Gà Giòn (2 miếng)', 39000, '../upload/1731398504_ls0051_5.png', '', 4),
(44, 'Milkis', 22000, '../upload/1731398544_drink_milkis.png', '', 5),
(45, '7 UP (M)', 14000, '../upload/1731398571_drink_7up_m_l_.png', '', 5),
(46, 'Milo', 20000, '../upload/1731398596_ld0084_5.png', '', 5),
(47, 'Nước Cam', 27000, '../upload/1731398633_drink_organge_juice_1.png', '', 5),
(48, 'Trà Lipton', 18000, '../upload/1731398650_drink_lipton_tea.png', '', 5),
(49, 'Mirinda (M)', 14000, '../upload/1731398685_drink_mirinda_m_l__4.png', '', 5),
(50, 'Pepsi (M)', 14000, '../upload/1731398710_drink_pepsi_m_l_.png', '', 5),
(52, 'Combo Burger Bò Teriyaki', 72000, '../upload/1732371818_combo_update_beef_teriyaki_1.jpg', '01 Burger Bò Teriyaki\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 9),
(53, 'Combo Burger Mozzarella', 109000, '../upload/1732371861_combo_update_mozzarella.jpg', '01 Burger Mozzarella\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 9),
(54, 'Combo Burger Double Double', 102000, '../upload/1732371897_combo_update_d-double_1_.jpg', '01 Burger Double Double\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 9),
(55, 'Combo Burger LChicken', 84000, '../upload/1732371936_combo_update_l-chicken_.jpg', '01 LChicken Burger\r\n01 Fried Fries \r\n01 Pepsi (M)', 9),
(56, 'Combo Burger Bulgogi', 82000, '../upload/1732371973_combo_update_bulgogi_1.jpg', '01 Burger Bulgogi\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 9),
(57, 'Combo Burger Phô Mai', 74000, '../upload/1732372012_combo_update_cheese_2.jpg', '01 Burger Phô Mai\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 9),
(58, 'Combo Burger Bò', 58000, '../upload/1732372089_combo_update_beef.jpg', '01 Burger Beef\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 9),
(59, 'Combo Burger Cá', 68000, '../upload/1732372131_combo_burger_ca.jpg', '01 Burger Cá\r\n01 Khoai tây chiên (M)\r\n01 Pepsi (M)', 9);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `idProduct` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `idUser`, `fullname`, `idProduct`, `rating`, `content`) VALUES
(1, 2, 'Nguyễn V N Hãi', 5, 5, 'ngon nha, mọi người nên thử!'),
(2, 5, 'Nguyễn Công Bằng', 21, 5, 'món này hơi bị ngon'),
(3, 2, 'Nguyễn Văn Ngọc Hãi', 28, 5, 'very good'),
(4, 7, 'Trần Thái Duy Khang', 43, 5, 'very good!');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `rule` int(11) NOT NULL DEFAULT 2 COMMENT '1 - quyền admin, 2 - quyền thành viên,\r\n3 - quyền nhân viên',
  `status` enum('active','blocked') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `password`, `mail`, `rule`, `status`) VALUES
(1, 'admin', 'Nguyễn Hãi', '$2y$10$sKeNXEFGma8sxLa8n0Uam.Rqg3t48nBTERhScuIXDXiAX9Xef6uyO', '', 1, 'active'),
(2, 'nvnh', 'Nguyễn Văn Ngọc Hãi', '$2y$10$m79jhSlLpobHA5SkncGdkOQA11Zc/OE2Al4VDi152K9UACfa/04X2', 'hai772038@gmail.com', 2, 'active'),
(3, 'nhanvien1', 'Ngọc Hãi N V', '$2y$10$p0W60DmDowG4FP8oALM/n.oH/P36Pnvr73TzxBGbetGnfodY5n2jy', 'rikakinguyen2004@gmail.com', 3, 'active'),
(5, 'congbang', 'Nguyễn Công Bằng', '$2y$10$bdvq/YEfudkTLtCjBW2YWOJM6//GWYp4hB1M37/dPpJboOIAWXZdu', 'nguyenbanghai87@gmail.com', 2, 'active'),
(6, 'anhuy', 'Nguyễn An Huy', '$2y$10$zKirorGj.o/JjQaHnJXFr.zXCMFGRfszZN5E3XsxFwyaDiAtr.c9K', 'nguyenanhuyss@gmail.com', 2, 'active'),
(7, 'duykhang', 'Trần Thái Duy Khang', '$2y$10$yrywNjzZfTZggiBz3950G.SDjbpBnl20wheEHSoWT1XStGkvuK45.', 'duykhang2004@gmail.com', 2, 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idUser` (`idUser`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`banner_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `username` (`username`),
  ADD KEY `idProduct` (`idProduct`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `fk_contact_users` (`idUser`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idUser` (`idUser`),
  ADD KEY `idProduct` (`idProduct`);

--
-- Indexes for table `order_coupons`
--
ALTER TABLE `order_coupons`
  ADD PRIMARY KEY (`idOrder`,`idCoupon`),
  ADD KEY `idCoupon` (`idCoupon`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`idOrder`,`idProduct`),
  ADD KEY `idProduct` (`idProduct`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idCategory` (`idCategory`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`idUser`,`idProduct`),
  ADD KEY `idProduct` (`idProduct`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`idProduct`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `contact`
--
ALTER TABLE `contact`
  ADD CONSTRAINT `fk_contact_users` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`idProduct`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_coupons`
--
ALTER TABLE `order_coupons`
  ADD CONSTRAINT `order_coupons_ibfk_1` FOREIGN KEY (`idOrder`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_coupons_ibfk_2` FOREIGN KEY (`idCoupon`) REFERENCES `coupons` (`coupon_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`idOrder`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`idProduct`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`idCategory`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`idProduct`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
