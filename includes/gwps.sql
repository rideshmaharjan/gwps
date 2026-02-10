-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2026 at 06:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `short_description`, `price`, `duration`, `category`, `created_by`, `created_at`) VALUES
(1, 'Weight Loss Program', '30-day weight loss challenge', '', 299.00, '30 days', 'weight_loss', 1, '2025-12-27 13:07:12'),
(2, 'Muscle Building', '12-week strength training', '', 199.00, '12 weeks', 'strength', 1, '2025-12-27 13:07:12'),
(5, 'bisep program', '6x pushup', '', 199.00, '7 days', 'muscle_building', 6, '2025-12-27 16:06:34'),
(9, 'Beginners Upper Body', 'The 7-Day Upper Body Beginner Package\r\nStructure: 3 workout days, 4 rest/recovery days.\r\n\r\nThe Workouts (Do These on Non-Consecutive Days, e.g., Mon, Wed, Fri)\r\nPerform each exercise for 3 sets of 10-12 repetitions. Rest for 60-90 seconds between sets.\r\n\r\nWorkout A: Push & Pull Foundation\r\n\r\nKnee Push-Ups (or Wall Push-Ups if needed): For chest, shoulders, and triceps.\r\n\r\nHow: On your knees, hands wider than shoulders. Keep your core tight and body in a straight line from knees to head.\r\n\r\nBent-Over Dumbbell Rows (use water bottles/cans if no dumbbells): For back and biceps.\r\n\r\nHow: Hinge at your hips, slight bend in knees, back straight. Pull the weight to your side, squeezing your shoulder blade.\r\n\r\nSeated Dumbbell Shoulder Press: For shoulders and triceps.\r\n\r\nHow: Sit on a sturdy chair, back straight. Press weights overhead until arms are straight (not locked).\r\n\r\nPlank (Hold): For core stability.\r\n\r\nHow: On forearms and toes (or knees), body in a straight line. Hold for 20-30 seconds for 3 sets.\r\n\r\nWorkout B: Strength & Stability\r\n\r\nDumbbell Chest Press on Floor: For chest and triceps.\r\n\r\nHow: Lie on your back, knees bent. Press weights up from your chest until arms are straight.\r\n\r\nLat Pulldowns (Resistance Band or Cable): For back and biceps.\r\n\r\nHow: Anchor a band overhead. Kneel and pull the band down to your chest, squeezing your back.\r\n\r\nDumbbell Bicep Curls: For biceps.\r\n\r\nHow: Stand tall, elbows at your sides. Curl the weights up toward your shoulders, control the descent.\r\n\r\nOverhead Triceps Extension: For triceps.\r\n\r\nHow: Hold one dumbbell with both hands. Extend arms overhead, then lower the weight behind your head by bending elbows.\r\n\r\n*(If you don\'t have equipment, replace #2 with more Bent-Over Rows and #4 with Bench Dips using a sturdy chair.)*\r\n\r\nSample 7-Day Schedule\r\nDay 1 (Monday): Workout A\r\n\r\nDay 2 (Tuesday): Active Recovery – Go for a 30-minute walk, do light stretching, or gentle yoga.\r\n\r\nDay 3 (Wednesday): Workout B\r\n\r\nDay 4 (Thursday): Rest Day – Complete rest or very light activity like walking.\r\n\r\nDay 5 (Friday): Workout A (You can switch to B if you want variety, but repeating A is great for beginners).\r\n\r\nDay 6 (Saturday): Active Recovery – A fun activity: bike ride, hike, play a sport.\r\n\r\nDay 7 (Sunday): Rest Day', 'this is simple effective 7-day upper body beginner', 400.00, '7 days', 'beginner', 6, '2026-02-10 17:20:35');

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
  `status` enum('pending','completed') DEFAULT 'completed',
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `user_id`, `package_id`, `purchase_date`, `amount`, `status`, `is_active`, `deleted_at`) VALUES
(11, 1, 5, '2025-12-28 14:27:28', 199.00, 'completed', 1, NULL),
(13, 1, 1, '2026-01-02 03:57:12', 299.00, 'completed', 1, NULL),
(14, 7, 5, '2026-02-10 14:38:06', 199.00, 'completed', 1, NULL),
(15, 1, 9, '2026-02-10 17:31:28', 400.00, 'completed', 1, NULL);

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
  `role` enum('user','admin') DEFAULT 'user',
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `created_at`, `role`, `created_by`) VALUES
(1, 'ridesh', 'Arkrideshmaharjan@gmail.com', '9000000000', '$2y$10$UUJG1T3z4hF.1TpGZzdgyeax9tXx7Pecf239khSmcjS4uGcLufIKG', '2025-12-13 09:23:21', 'user', NULL),
(6, 'ARK', 'adminridesh@gmail.com', '9821139366', '$2y$10$hdsLNbVzqChfMaPT4/Q2TOcJ3Gi3eJROUNEGNqxOZ8Yzldi9FekGi', '2025-12-18 08:25:35', 'admin', NULL),
(7, 'Shreewong Tamang', 'shreewongtmg@gmail.com', '9712345678', '$2y$10$mDLZkO9sHacd/lnX0epVou6jYpg/joWmX7NLa3q2D31xLKoT7SOKC', '2026-02-10 14:37:53', 'admin', NULL);

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
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
