-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 05:29 AM
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
-- Database: `kapetann`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`) VALUES
(1, 'admin', 'admin', 'admin@coffee.com'),
(2, 'dharmik', '123123123', 'dharmikpatel20062008@gmail.com'),
(3, 'netra', '10201020', 'netra123@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `product_image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_name`, `product_price`, `quantity`, `product_image`, `created_at`) VALUES
(45, NULL, 'Coffee-Frappuccino', 852.00, 1, 'images/Starbucks®.jpeg', '2025-08-21 19:54:56'),
(46, NULL, 'du', 541.00, 1, 'images/cart-item-8.png', '2025-08-21 19:55:23'),
(47, NULL, 'Mazagran', 950.00, 1, 'images/Mazagran.jpg', '2025-08-21 19:59:41'),
(48, NULL, 'Mazagran', 950.00, 1, 'images/Mazagran.jpg', '2025-08-21 20:53:02'),
(49, NULL, 'Mazagran', 950.00, 1, 'images/Mazagran.jpg', '2025-08-21 20:59:29'),
(50, NULL, 'Coffee-Frappuccino', 852.00, 1, 'images/Starbucks®.jpeg', '2025-08-21 22:44:40'),
(51, NULL, 'du', 541.00, 1, 'images/cart-item-8.png', '2025-08-22 10:38:26'),
(52, NULL, 'Cold Brew', 1200.00, 1, 'images/Vanilla-Sweet-Cream-Cold-Brew.jpg', '2025-08-22 10:38:58'),
(53, NULL, 'Coffee-Frappuccino', 852.00, 1, 'images/Starbucks®.jpeg', '2025-12-02 10:27:28');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'HOT COFFEE', '', '2025-12-12 06:39:51', '2025-12-12 06:39:51'),
(2, 'COLD COFFEE', '', '2025-12-12 06:40:45', '2025-12-12 06:40:45');

-- --------------------------------------------------------

--
-- Table structure for table `coffees`
--

CREATE TABLE `coffees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coffees`
--

INSERT INTO `coffees` (`id`, `name`, `price`, `old_price`, `description`, `image`, `category_id`) VALUES
(42, 'Mazagron', 140.00, 160.00, 'A refreshing cold espresso-based drink, perfect for summer.', 'istockphoto-1145612951-612x612.jpg', NULL),
(44, 'Laate', 399.00, 500.00, NULL, '12f952fc882dc54b861bc717aa515109.jpg', NULL),
(45, 'JAVA CHIP', 599.00, 700.00, NULL, '989d56d9e28d0cc08836527b7a5b3dd3.jpg', 1),
(46, 'Chocolate Frappe', 599.00, 899.00, NULL, 'a34c413b763037010399888ac5981e16.jpg', 2),
(47, 'Strawberry Frappe', 399.00, 599.00, NULL, 'c172e92b417d22c7d409e665a7cdb70a.jpg', 2),
(48, 'Oreo Frappe', 499.00, 599.00, NULL, '732b5a867ea297e70ea4189490d87786.jpg', 2),
(49, 'Pineapple Frappe', 399.00, 599.00, NULL, '085a203af9e3b483fbb3537f0198417c.jpg', 2),
(56, 'Blue Berry Cold Coffee', 349.00, 599.00, NULL, 'f175fcd7170d5da249145d6cc4d1ec15.jpg', 2),
(58, 'Laate Cold Coffee', 499.00, 599.00, NULL, 'e55f07f76dda830b9a968058f9b9c999.jpg', 2);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) DEFAULT 'unread',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `name`, `email`, `message`, `status`, `created_at`) VALUES
(1, 5, 'dharmik', 'dharmikpatel20062008@gmail.com', 'your coffee is so nice!!', 'read', '2025-08-21 22:10:28'),
(2, 5, 'dharmik', 'dharmikpatel20062008@gmail.com', 'nice coffee and website!', 'read', '2025-08-21 22:13:44'),
(3, 5, 'dharmik', 'dharmikpatel20062008@gmail.com', 'r4tg4fr3t', 'unread', '2025-08-21 22:35:22');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `title` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal_amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `user_id` int(11) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `price`, `title`, `quantity`, `subtotal_amount`, `date`, `invoice_number`, `status`, `user_id`, `address`, `phone`) VALUES
(121, 599.00, 'JAVA CHIP', 2, 1198.00, '2026-02-03', 'INV-980.91430797', 'Pending', 28, 'Service: Dining\nReservation/Table: 53\nName: divyesh\nEmail: divyesh@gmail.com', '7282924252'),
(122, 140.00, 'Mazagron', 1, 140.00, '2026-02-03', 'INV-980.91430797', 'Pending', 28, 'Service: Dining\nReservation/Table: 53\nName: divyesh\nEmail: divyesh@gmail.com', '7282924252'),
(123, 350.00, 'Table #2 Stay (11:05 AM for 35 mins)', 1, 350.00, '2026-02-03', 'INV-980.91430797', 'Pending', 28, 'Service: Dining\nReservation/Table: 53\nName: divyesh\nEmail: divyesh@gmail.com', '7282924252'),
(124, 399.00, 'Strawberry Frappe', 2, 798.00, '2026-02-03', 'INV-5e9.90926664', 'Pending', 28, 'Service: Takeaway\nName: divyesh\nEmail: divyesh@gmail.com', '7282924252'),
(125, 599.00, 'Chocolate Frappe', 1, 599.00, '2026-02-03', 'INV-5e9.90926664', 'Pending', 28, 'Service: Takeaway\nName: divyesh\nEmail: divyesh@gmail.com', '7282924252');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires`, `created_at`) VALUES
(10, 11, '0da8fb00a07f6ce1ae6b965856f92434', '2025-12-12 08:17:59', '2025-12-12 06:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `person` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `location` varchar(100) DEFAULT NULL,
  `booking_fee` decimal(10,2) DEFAULT 0.00,
  `duration_mins` int(11) DEFAULT 10,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `name`, `email`, `date`, `time`, `person`, `user_id`, `table_id`, `status`, `created_at`, `location`, `booking_fee`, `duration_mins`, `phone`) VALUES
(53, 'divyesh', 'divyesh@gmail.com', '2026-02-03', '11:05:00', 1, 28, 2, 'Completed', '2026-02-03 10:03:03', 'center', 350.00, 35, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key_name` varchar(100) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `table_number` varchar(20) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Available',
  `reserved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id`, `table_number`, `capacity`, `location`, `status`, `reserved_at`) VALUES
(1, '1', 5, 'Corner', 'Available', NULL),
(2, '2', 5, 'center', 'Available', NULL),
(3, '3', 2, 'near reception', 'Available', NULL),
(4, '4', 6, 'window side', 'Available', NULL),
(5, '5', 5, 'Middle', 'Available', NULL),
(6, '6', 6, 'Corner', 'Available', NULL),
(8, '7', 4, 'Terrace', 'Available', NULL),
(9, '8', 2, 'Window Side', 'Available', NULL),
(10, '9', 6, 'Private Nook', 'Available', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `table_locks`
--

CREATE TABLE `table_locks` (
  `id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) NOT NULL,
  `locked_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `create_datetime` datetime NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `create_datetime`, `reset_token`, `token_expiry`, `phone`, `address`, `full_name`) VALUES
(2, 'admin', 'admin@gmail.com', '21232f297a57a5a743894a0e4a801fc3', '2023-04-18 11:00:40', NULL, NULL, NULL, NULL, NULL),
(5, 'dharmik', 'dharmikpatel20062008@gmail.com', 'f5bb0c8de146c67b44babbf4e6584cc0', '2025-08-07 18:47:11', '98cd437883401c0d6b4585154fe28d468c9e70a56c7fe3cf1a6bc6739f4a6d6c', '2025-08-20 16:23:41', NULL, NULL, NULL),
(17, 'viraj', 'viraj@gmail.com', '$2y$10$Vuoh18qlmElOdwG87PLyXOPTRwnl5p6ok1nvGym.4YA', '2025-12-15 13:45:29', NULL, NULL, NULL, NULL, NULL),
(20, 'jay', 'dangar@gmail.com', '$2y$10$q1JCYmLE.WiEXHwN4icHeu5phDcw0MF5jp3DGsPmtm0B/XwoYBx7O', '2025-12-17 14:14:08', NULL, NULL, '123456789', 'cdfegbrhtjmykulik,umjynhtbgrfveds', 'jay'),
(21, 'admin', 'jaydangar006@gmail.com', '$2y$10$TciRuPU.3SJbCVfx5wobEelP3sEgt/grww5xcwaX0gk9dN/PIkAxq', '2025-12-18 07:25:15', NULL, NULL, NULL, NULL, NULL),
(24, 'Test', 'jaydangar099@gmail.com', '$2y$10$hrwVQ9eni4I0OfHlxfs18eS2I0f/QdlLsdXn/YbjVR9e/LnPdEcha', '2026-01-09 07:20:24', NULL, NULL, NULL, NULL, NULL),
(28, 'divyesh', 'divyesh@gmail.com', '$2y$10$Q8LWumeZGGaAXfRqHzjbpusgDRJWDhxfy5zNYJGXzRIkzgxGfNkEm', '2026-01-15 05:37:26', NULL, NULL, '7282924252', NULL, 'divyesh'),
(29, 'prince', 'prince@gmail.com', '$2y$10$.Z2S00Es04qFXBai9/8jJu2f5m2yP9UqqK8TANyh1Dfbi20HoVMnS', '2026-01-15 05:39:10', NULL, NULL, NULL, NULL, NULL),
(30, 'kp', 'kp@gmail.com', '$2y$10$mGhFXeXzJcXR0Kevu9a6XOR612RRunVbI.fSFvGOqPdKsF12bqMiy', '2026-01-15 05:41:33', NULL, NULL, '1234567890', 'sjdgsihfjsfksjfk', 'kp');

-- --------------------------------------------------------

--
-- Table structure for table `user_cart`
--

CREATE TABLE `user_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_cart`
--

INSERT INTO `user_cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(2, 27, 58, 5, '2026-01-13 06:53:36'),
(50, 28, 58, 3, '2026-01-15 07:02:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name_unique` (`name`);

--
-- Indexes for table `coffees`
--
ALTER TABLE `coffees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_number` (`invoice_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key_name`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `table_locks`
--
ALTER TABLE `table_locks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid_pid_unique` (`user_id`,`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coffees`
--
ALTER TABLE `coffees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `table_locks`
--
ALTER TABLE `table_locks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `coffees`
--
ALTER TABLE `coffees`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
