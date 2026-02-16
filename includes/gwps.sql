-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2026 at 06:17 AM
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
-- Database: `gwps`
--

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(1000) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `short_description`, `price`, `duration`, `category`, `image`, `created_by`, `created_at`) VALUES
(1, 'Weight Loss Program', '30-day weight loss challenge', NULL, 299.00, '30 days', 'weight_loss', NULL, 1, '2025-12-27 13:07:12'),
(2, 'Muscle Building', '12-week strength training', NULL, 199.00, '12 weeks', 'strength', NULL, 1, '2025-12-27 13:07:12'),
(5, 'bisep program', '6x pushup', 'szddc', 199.00, '30 days', 'muscle_building', NULL, 6, '2025-12-27 16:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'khalti',
  `status` enum('pending','completed') DEFAULT 'completed',
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `completed_workouts` int(11) DEFAULT 0,
  `total_workouts` int(11) DEFAULT 30,
  `last_workout_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `user_id`, `package_id`, `purchase_date`, `amount`, `transaction_id`, `payment_method`, `status`, `payment_status`, `is_active`, `deleted_at`, `completed_at`, `completed_workouts`, `total_workouts`, `last_workout_date`) VALUES
(11, 1, 5, '2025-12-28 14:27:28', 199.00, NULL, 'khalti', 'completed', 'pending', 0, '2026-02-11 15:53:15', NULL, 0, 30, NULL),
(13, 1, 1, '2026-02-12 18:32:22', 299.00, NULL, 'khalti', 'completed', 'pending', 0, '2026-02-12 18:32:26', NULL, 0, 30, NULL),
(14, 1, 1, '2026-02-14 17:04:20', 299.00, NULL, 'khalti', 'completed', 'pending', 0, '2026-02-14 17:05:39', NULL, 0, 30, NULL),
(16, 1, 5, '2026-02-14 17:32:02', 199.00, 'MOCK_6990b192a8693', 'mock', 'completed', 'completed', 0, '2026-02-14 17:32:32', NULL, 0, 30, NULL),
(17, 6, 5, '2026-02-14 17:34:12', 199.00, 'MOCK_6990b214baa7b', 'mock', 'completed', 'completed', 1, NULL, NULL, 0, 30, NULL),
(18, 1, 1, '2026-02-14 17:42:17', 299.00, 'MOCK_6990b3f9dec26', 'mock', 'completed', 'completed', 0, '2026-02-14 17:42:20', NULL, 0, 30, NULL),
(19, 1, 1, '2026-02-14 17:54:15', 299.00, 'MOCK_6990b6c7618ef_1771091655', 'mock', 'completed', 'completed', 0, '2026-02-14 17:54:25', NULL, 0, 30, NULL),
(20, 1, 1, '2026-02-14 17:57:08', 299.00, 'MOCK_6990b7742bad4_1771091828', 'mock', 'completed', 'completed', 1, NULL, NULL, 0, 30, NULL),
(21, 1, 5, '2026-02-14 18:05:46', 199.00, 'MOCK_6990b97ade751_1771092346', 'mock', 'completed', 'completed', 1, NULL, NULL, 0, 30, NULL),
(22, 1, 2, '2026-02-14 18:11:45', 199.00, 'MOCK_6990bae1963e2_1771092705', 'mock', 'completed', 'completed', 1, NULL, NULL, 1, 30, '2026-02-16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `created_at`, `role`) VALUES
(1, 'ridesh', 'Arkrideshmaharjan@gmail.com', '9800000000', '$2y$10$v7QTHUQNOsI9M0EZo90ph.q6FguV46app4ZYajAo5GBooo6h8o2IK', '2025-12-13 09:23:21', 'user'),
(6, 'ARK', 'adminridesh@gmail.com', '9821139366', '$2y$10$9nGbwNHD0A6b7l/X/V2qAeqRvVd.ZPdrb8ExKWPfJV/1j2cHdqz2a', '2025-12-18 08:25:35', 'admin'),
(7, 'Shreewong Tamang', 'Shewwong@gmail.com', '9700000000', '$2y$10$wfwHnwGf.9qxjg/KJ.FlDufQqNYOsPlkAbNkguAp21Gb.rDD0Wriy', '2026-02-16 05:10:42', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`);

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
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
