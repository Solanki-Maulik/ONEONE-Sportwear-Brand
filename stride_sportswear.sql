-- STRIDE Sportswear — full project by Maulik
--
-- IMPORTANT — shared hosting (InfinityFree, etc.):
-- Free hosts give you ONE pre-made database and never let your DB user
-- create a new one, so this file does NOT include a CREATE DATABASE
-- statement. In phpMyAdmin, click on your existing database name in the
-- left sidebar FIRST, then use the Import tab — that way every CREATE
-- TABLE below runs inside that existing database instead of trying to
-- make a new one.
--
-- Local XAMPP: create an empty database yourself first (phpMyAdmin ->
-- New -> name it, e.g. "sportswear") then import into that the same way.

-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Footwear', 'Running and training shoes'),
(2, 'Jerseys', 'Official team and training jerseys');

-- --------------------------------------------------------
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `discount_percent` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `coupons` (`id`, `code`, `discount_percent`, `expiry_date`, `is_active`) VALUES
(1, 'M50', 50, '2026-12-31', 1),
(2, 'M30', 30, '2026-12-31', 1);

-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin account. Email: admin@gmail.com | Password: admin123
-- (Change this password after first login via the DB or by adding an admin-edit page.)
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('Admin', 'admin@gmail.com', '$2y$10$2ilWrw/eYk.TxOIjVLkK0uIfmTAxW8JxPNq3.0MKKAlv.2/LtLmmu', 'admin');

-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL,
  `gender` enum('men','women','unisex') NOT NULL DEFAULT 'unisex',
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample products, pointing at real image files already in img/. New
-- products added via the admin panel will save correctly into /uploads.
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `mrp`, `stock_quantity`, `gender`, `image_url`) VALUES
(1, 'Nike Flex Train', 'UK 8', 9999.00, 13999.00, 2, 'men', 'img/Nike Flex Train/1.avif'),
(1, 'Nike Jordan Son of Mars Low', 'UK 9', 12499.00, 15999.00, 3, 'men', 'img/Nike Jordan Son of Mars Low Men''s Shoe/1.avif');

-- --------------------------------------------------------
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `product_images` (`product_id`, `image_path`) VALUES
(1, 'img/Nike Flex Train/1.avif'),
(1, 'img/Nike Flex Train/2.avif'),
(1, 'img/Nike Flex Train/3.avif'),
(1, 'img/Nike Flex Train/4.avif'),
(2, 'img/Nike Jordan Son of Mars Low Men''s Shoe/1.avif'),
(2, 'img/Nike Jordan Son of Mars Low Men''s Shoe/2.avif'),
(2, 'img/Nike Jordan Son of Mars Low Men''s Shoe/3.avif'),
(2, 'img/Nike Jordan Son of Mars Low Men''s Shoe/4.avif');

-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `coupon_id` (`coupon_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Sizes available per product (admin picks via checkboxes 6-10).
-- A product with no rows here is treated as "one size" - no size
-- picker shown on its product page.
CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_size_unique` (`product_id`, `size`),
  CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `product_sizes` (`product_id`, `size`, `stock`) VALUES
(1, '7', 2), (1, '8', 3), (1, '9', 1),
(2, '8', 2), (2, '9', 2), (2, '10', 1);

-- --------------------------------------------------------
-- Each shopper's cart, tied to their account (not the browser session), so
-- it's still there if they log out and log back in later, or switch devices.
CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL DEFAULT '',
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product_size` (`user_id`, `product_id`, `size`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
