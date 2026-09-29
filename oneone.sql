-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2026 at 07:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `oneone`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL DEFAULT '',
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `size`, `quantity`) VALUES
(7, 8, 23, 'L', 1),
(8, 8, 17, '6', 1),
(9, 8, 15, 'L', 1),
(10, 8, 16, '8', 1),
(20, 11, 30, '8', 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Footwear', 'Running and training shoes'),
(2, 'Jerseys', 'Official team and training jerseys'),
(7, 'Top', NULL),
(8, 'Bottom', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `discount_percent` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_percent`, `expiry_date`, `is_active`) VALUES
(1, 'M50', 50, '2026-12-31', 1),
(2, 'M30', 30, '2026-12-31', 1),
(3, 'M99', 99, '2026-12-31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(30) NOT NULL DEFAULT 'cod',
  `payment_detail` varchar(100) DEFAULT NULL,
  `ship_name` varchar(100) DEFAULT NULL,
  `ship_phone` varchar(20) DEFAULT NULL,
  `ship_address` varchar(255) DEFAULT NULL,
  `ship_city` varchar(100) DEFAULT NULL,
  `ship_state` varchar(100) DEFAULT NULL,
  `ship_pincode` varchar(12) DEFAULT NULL,
  `order_status` enum('placed','packed','shipped','delivered','cancelled') DEFAULT 'placed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `coupon_id`, `total_amount`, `payment_method`, `payment_detail`, `ship_name`, `ship_phone`, `ship_address`, `ship_city`, `ship_state`, `ship_pincode`, `order_status`, `created_at`) VALUES
(3, 5, 1, 4999.00, 'cod', 'Cash on Delivery', 'Hardik', '9856324758', 'Jamjodhpur', 'Upleta', 'Gujarat', '361000', 'delivered', '2026-08-11 17:59:16'),
(4, 7, NULL, 2998.00, 'upi', 'UPI — jay@bank', 'Jay', '9653246859', 'Rajkot', 'Rajkot', 'Gujarat', '360004', 'delivered', '2026-08-11 18:00:39'),
(5, 8, NULL, 3499.00, 'cod', 'Cash on Delivery', 'Basar', '9795672534', 'Shapar', 'Rajkot', 'Gujarat', '360005', 'delivered', '2026-08-11 18:05:26'),
(6, 5, NULL, 23998.00, 'card', 'Card ending in 1496', 'Hardik', '9856324758', 'Kotecha Chowk', 'Rajkot', 'Gujarat', '360003', 'delivered', '2026-08-12 03:22:24'),
(7, 7, NULL, 11999.00, 'upi', 'UPI — axis@bank', 'Jay', '9795672534', 'Rajkot', 'Rajkot', 'Gujarat', '360001', 'shipped', '2026-08-12 03:23:21'),
(8, 7, 1, 5999.50, 'cod', 'Cash on Delivery', 'Jay', '9856324758', 'Rajkot', 'Rajkot', 'Gujarat', '360004', 'packed', '2026-08-24 03:12:56'),
(9, 11, 2, 8399.30, 'cod', 'Cash on Delivery', 'Maulik Solanki', '8238169003', 'Rajkot', 'Rajkot', 'Gujarat', '360004', 'placed', '2026-09-12 04:45:51');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL,
  `size` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`, `size`) VALUES
(3, 3, 17, 2, 4999.00, '6'),
(4, 4, 16, 2, 1499.00, '8'),
(5, 5, 23, 1, 3499.00, 'L'),
(6, 6, 30, 2, 11999.00, '8'),
(7, 7, 30, 1, 11999.00, '9'),
(8, 8, 30, 1, 11999.00, '8'),
(9, 9, 30, 1, 11999.00, '8');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `sport_type` varchar(50) NOT NULL DEFAULT 'Lifestyle',
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL,
  `gender` enum('men','women','unisex') NOT NULL DEFAULT 'unisex',
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `sport_type`, `name`, `description`, `price`, `original_price`, `mrp`, `stock_quantity`, `gender`, `image_url`, `created_at`) VALUES
(15, 2, 'Lifestyle', 'INDIA CRICKET WOMAN ODI JERSEY', 'Step onto the pitch with bold confidence in the India Cricket One Day International Jersey, crafted for those who play with passion and pride. This jersey is designed to deliver a blend of classic style and modern performance, making it a standout choice for cricket fans and athletes alike. Climacool+ technology helps you feel dry for a distraction-free experience. The knit construction offers a soft, breathable feel, suitable for long hours on the field or casual wear. The regular fit delivers comfort and freedom of movement, whether you’re cheering from the stands or facing the bowler. Embrace the spirit of sport performance with the adidas cricket jersey, where tradition meets rebellious optimism, and the details are designed to empower your game.', 2999.00, NULL, 9999.00, 143, 'women', 'uploads/1785696491_0_fc1_1.avif', '2026-08-02 18:48:11'),
(16, 1, 'Lifestyle', 'Nike Flex Train', 'Nike Flex Train', 1499.00, NULL, 2999.00, 0, 'unisex', 'uploads/1785698627_0_1.avif', '2026-08-02 19:23:47'),
(17, 1, 'Lifestyle', 'Nike Jordan Son of Mars Low ', 'Nike Jordan Son of Mars Low', 4999.00, NULL, 9999.00, 0, 'men', 'uploads/1785698886_0_1.avif', '2026-08-02 19:28:06'),
(23, 7, 'Lifestyle', 'Airfree Women Tennis Outfit', 'Two Piece Tennis Skirts Sets: skorts for woman skirts with racerback polo golf tank top, made of soft, breathable, 4-way strength fabric, UPF50+ 2 piece sets is your perfect tennis outfits\r\nBuilt-in Shorts /Bra : Pleated skirts for women athletic dress with shorts ensure your flexible movement without fear of being seen through.Built-in-Bra womens athletic set can providing both breathability and sufficient support.', 3499.00, 5499.00, NULL, 301, 'women', 'uploads/6a72d0fa403e5_0.jpg,uploads/6a72d0fa43584_1.jpg,uploads/6a72d0fa43e6d_2.jpg,uploads/6a72d0fa446eb_3.jpg,uploads/6a72d0fa450ba_4.jpg', '2026-08-05 05:58:18'),
(30, 1, 'Lifestyle', 'Nike Pegasus Premium', 'The Pegasus Premium supercharges responsive cushioning with a triple stack of our most powerful running technologies', 11999.00, NULL, 13999.00, 0, 'men', 'uploads/6a7be2cc9359b.avif,uploads/6a7be2cc93c59.avif,uploads/6a7be2cc94042.avif,uploads/6a7be2cc942d2.avif', '2026-08-12 03:04:44'),
(31, 1, 'Running', 'Pro Running Shoes X1', 'High-performance running shoes with breathable mesh.', 3999.00, NULL, 5999.00, 0, 'men', 'uploads/pro_running_shoes_x1_1789182788635.jpg', '2026-09-12 03:06:18'),
(34, 7, 'Lifestyle', 'Active Yoga Top 2', 'Comfortable and stretchy top perfect for yoga and gym.', 1299.00, NULL, 1999.00, 0, 'women', 'uploads/active_yoga_top_2_1789182806007.jpg', '2026-09-12 03:07:27'),
(35, 8, 'Running', 'Track Pants Core 2', 'Lightweight track pants with zip pockets.', 1599.00, NULL, 2499.00, 0, 'men', 'uploads/track_pants_core_2_1789182820639.jpg', '2026-09-12 03:07:28'),
(36, 2, 'Cricket', 'Team India Training Jersey 2', 'Official training jersey with moisture-wicking tech.', 2499.00, NULL, 3299.00, 0, 'men', 'uploads/team_india_jersey_1789182837053.jpg', '2026-09-12 03:07:29'),
(37, 1, 'Running', 'Women\'s Sprint Sneakers 2', 'Responsive cushioning for your daily runs.', 4299.00, NULL, 6499.00, 0, 'women', 'uploads/sprint_sneakers_2_1789182868850.jpg', '2026-09-12 03:07:29'),
(38, 7, 'Lifestyle', 'Casual Polo T-Shirt 2', 'Classic polo shirt in breathable cotton blend.', 1099.00, NULL, 1599.00, 0, 'men', 'uploads/polo_tshirt_2_1789182883287.jpg', '2026-09-12 03:07:30'),
(40, 8, 'Running', 'Athletic Shorts 2', 'Quick-dry shorts designed for intense workouts.', 899.00, NULL, 1299.00, 0, 'men', 'uploads/athletic_shorts_2_1789182894403.jpg', '2026-09-12 03:07:30'),
(41, 1, 'Lifestyle', 'Classic Canvas Shoes 2', 'Everyday lifestyle shoes with a timeless design.', 1999.00, NULL, 2999.00, 0, 'unisex', 'uploads/canvas_shoes_2_1789182909123.jpg', '2026-09-12 03:07:31'),
(42, 1, 'Lifestyle', 'pink shoes', 'best for the running', 2000.00, NULL, 4000.00, 0, 'women', 'uploads/a3f8679f7f45d19e3ed9561bc4847e1a.png', '2026-09-25 05:42:51'),
(43, 1, 'Lifestyle', 'orange elite', 'best for fast running', 4000.00, NULL, 6000.00, 0, 'unisex', 'uploads/a4abd20ffbca702fcb668a07770c7f58.png', '2026-09-25 05:43:54'),
(44, 1, 'Running', 'Green shoes', 'running shoes', 1400.00, NULL, 1999.00, 0, 'unisex', 'uploads/1f63ccc15bae3734ff94e517cea567c5.png', '2026-09-25 05:44:38'),
(45, 1, 'Lifestyle', 'Black Elite', 'best for party and running', 5000.00, NULL, 10000.00, 0, 'unisex', 'uploads/958d2a114f3c4d23d1f431dcdb0442e3.png', '2026-09-25 05:46:03'),
(46, 1, 'Lifestyle', 'Multi Grad', 'quick dry', 4000.00, NULL, 3997.00, 0, 'unisex', 'uploads/6c2e6c5f59011582148ecf47fad08d39.png', '2026-09-25 05:46:48'),
(47, 8, 'Lifestyle', 'Track', 'quick dry', 1000.00, NULL, 2000.00, 0, 'unisex', 'uploads/91f21f8b860c029eea38032dcfbd1bc6.png', '2026-09-25 05:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size`, `stock`) VALUES
(54, 15, 'XS', 10),
(55, 15, 'S', 18),
(56, 15, 'M', 35),
(57, 15, 'L', 48),
(58, 15, 'XL', 32),
(59, 16, '6', 25),
(60, 16, '7', 16),
(61, 16, '8', 47),
(62, 16, '9', 19),
(63, 16, '10', 23),
(99, 23, 'XS', 43),
(100, 23, 'S', 56),
(101, 23, 'M', 76),
(102, 23, 'L', 82),
(103, 23, 'XL', 43),
(104, 17, '6', 43),
(105, 30, '6', 40),
(106, 30, '7', 69),
(107, 30, '8', 52),
(108, 30, '9', 43),
(109, 30, '10', 21),
(110, 31, '6', 72),
(111, 31, '7', 84),
(112, 31, '8', 37),
(113, 31, '9', 63),
(114, 31, '10', 51),
(120, 34, 'XS', 50),
(121, 34, 'S', 82),
(122, 34, 'M', 20),
(123, 34, 'L', 89),
(124, 34, 'XL', 55),
(125, 35, 'XS', 26),
(126, 35, 'S', 52),
(127, 35, 'M', 61),
(128, 35, 'L', 16),
(129, 35, 'XL', 28),
(130, 36, 'XS', 75),
(131, 36, 'S', 52),
(132, 36, 'M', 10),
(133, 36, 'L', 39),
(134, 36, 'XL', 90),
(135, 37, '6', 13),
(136, 37, '7', 33),
(137, 37, '8', 58),
(138, 37, '9', 58),
(139, 37, '10', 31),
(140, 38, 'XS', 11),
(141, 38, 'S', 44),
(142, 38, 'M', 18),
(143, 38, 'L', 28),
(144, 38, 'XL', 41),
(145, 40, 'XS', 38),
(146, 40, 'S', 36),
(147, 40, 'M', 13),
(148, 40, 'L', 81),
(149, 40, 'XL', 48),
(150, 41, '6', 67),
(151, 41, '7', 55),
(152, 41, '8', 32),
(153, 41, '9', 89),
(154, 41, '10', 65),
(155, 47, 'L', 100),
(156, 47, 'XL', 100),
(157, 46, '6', 100),
(158, 46, '7', 100),
(159, 46, '8', 100),
(160, 46, '9', 100),
(161, 46, '10', 100),
(162, 45, '8', 100),
(163, 45, '9', 100),
(164, 45, '10', 100),
(165, 44, '7', 100),
(166, 44, '8', 100),
(167, 44, '9', 100),
(168, 44, '10', 100),
(169, 43, '6', 100),
(170, 43, '7', 100),
(171, 43, '8', 100),
(172, 43, '9', 100),
(173, 42, '6', 100),
(174, 42, '7', 100),
(175, 42, '8', 100),
(176, 42, '9', 100);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$2ilWrw/eYk.TxOIjVLkK0uIfmTAxW8JxPNq3.0MKKAlv.2/LtLmmu', 'admin', '2026-08-01 06:52:19'),
(5, 'hardik', 'hardik@gmail.com', '$2y$10$H95uSwO6IETfO92mpFEHS.vmOQp/MwTZnxRWUWt8IA4NvtElXO9BO', 'user', '2026-08-11 17:15:11'),
(6, 'jeel', 'jeel@gmail.com', '$2y$10$PGgKKQi6eatheEvbE6l6QeRU74bjPTfqDZjvF8IRWv5/PCjwvspXm', 'user', '2026-08-11 17:22:23'),
(7, 'Jay', 'jay@gmail.com', '$2y$10$n2VxXvL/5zF0ub7n.kAT0OnCMatKVxUhjHHf7dtHSPhHS55o8pzuu', 'user', '2026-08-11 17:22:53'),
(8, 'Basar', 'basar@gmail.com', '$2y$10$ZXb8GDtDGIQ.UxyJF5l7SOco.IYW.c/Ij.Vo.KCCbozC5c2R8lWr.', 'user', '2026-08-11 17:23:09'),
(9, 'abc', 'fgdf@g', '$2y$10$KtQd3MJkoyEKBsApQeCjWeAFTnNNhGXFLwftoV.RKBT9.9NopMMPK', 'user', '2026-08-14 04:07:34'),
(10, 'creationby11', 'admin@stockwise.com', '$2y$10$St5zDh9U80J0AB5EV0vB6e.WFsXZHcaWJ.9s6oSsN5k0MSqPAN/UO', 'user', '2026-08-14 04:07:48'),
(11, 'Maulik', 'maulik@gmail.com', '$2y$10$LBpHS2hxRpnfsdS.h7sIl.rb2y99o/GdPXlunOsVqI/ikm7y0M6Zq', 'user', '2026-09-12 04:43:50'),
(12, 'urmit sardhara', 'urmit@gmail.com', '$2y$10$gwK7NV9YmyXKbs/XVh.5/efaP9t2322ENFoJSPR/X17H6gtps1GEq', 'user', '2026-09-25 05:53:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product_size` (`user_id`,`product_id`,`size`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `coupon_id` (`coupon_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_size_unique` (`product_id`,`size`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
