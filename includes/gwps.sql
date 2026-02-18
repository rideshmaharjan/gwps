-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2026 at 04:42 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `short_description`, `price`, `duration`, `category`, `image`, `created_by`, `created_at`, `is_active`) VALUES
(9, '30-Day Fat Burn Challenge', '🔥 30-DAY FAT BURN CHALLENGE - COMPLETE WORKOUT PLAN 🔥\r\n\r\nWEEK 1-2: FOUNDATION PHASE\r\n━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\nMONDAY - FULL BODY BURNER\r\n• Warm-up: Jumping jacks (2 min)\r\n• Squats: 4 sets x 15 reps\r\n• Push-ups: 3 sets x 10 reps\r\n• Lunges: 3 sets x 12 reps each leg\r\n• Plank: 3 sets x 45 seconds\r\n• Mountain climbers: 3 sets x 30 seconds\r\n• Cardio: 20 min jogging\r\n• Cool-down: Stretching (5 min)\r\n\r\nTUESDAY - ACTIVE RECOVERY\r\n• 30 min brisk walking\r\n• Full body stretching (15 min)\r\n\r\nWEDNESDAY - HIIT CARDIO\r\n• Warm-up: High knees (2 min)\r\n• Burpees: 3 sets x 10 reps\r\n• Jump squats: 3 sets x 12 reps\r\n• High knees: 3 sets x 30 seconds\r\n• Mountain climbers: 3 sets x 30 seconds\r\n• Jumping jacks: 3 sets x 30 seconds\r\n• Cool-down: Stretching (5 min)\r\n\r\nTHURSDAY - LOWER FOCUS\r\n• Warm-up: Leg swings (2 min)\r\n• Goblet squats: 4 sets x 15 reps\r\n• Walking lunges: 3 sets x 12 reps each leg\r\n• Glute bridges: 3 sets x 15 reps\r\n• Calf raises: 3 sets x 20 reps\r\n• Cardio: 20 min cycling\r\n• Cool-down: Hamstring stretch (5 min)\r\n\r\nFRIDAY - UPPER FOCUS\r\n• Warm-up: Arm circles (2 min)\r\n• Push-ups: 4 sets x 8-12 reps\r\n• Dumbbell rows: 3 sets x 12 reps each arm\r\n• Shoulder presses: 3 sets x 12 reps\r\n• Bicep curls: 3 sets x 12 reps\r\n• Tricep dips: 3 sets x 10 reps\r\n• Cardio: 15 min jump rope\r\n\r\nSATURDAY - FULL BODY + CARDIO\r\n• Repeat Monday&#039;s workout\r\n• Add 25 min jogging\r\n\r\nSUNDAY - REST DAY\r\n• Light stretching only\r\n• Hydrate and recover\r\n\r\nWEEK 3-4: INTENSIFICATION PHASE\r\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n• Increase all sets by 1\r\n• Increase cardio by 5-10 min\r\n• Decrease rest time between sets to 30 seconds\r\n• Add weight where possible\r\n\r\nNUTRITION GUIDELINES:\r\n• Drink 3-4 liters water daily\r\n• Protein with every meal\r\n• Cut processed sugars\r\n• Eat every 3-4 hours', 'A high-intensity 30-day program designed to maximize fat loss through strategic workouts and cardio. Perfect for beginners and intermediates looking to shed pounds fast.', 2999.00, '30 days', 'weight_loss', NULL, 11, '2026-02-16 15:57:08', 1),
(10, '12-Week Muscle Building Program', '💪 12-WEEK MUSCLE BUILDING PROGRAM - COMPLETE WORKOUT PLAN 💪\r\n\r\nPROGRAM STRUCTURE:\r\n• Phase 1 (Weeks 1-4): Strength Foundation\r\n• Phase 2 (Weeks 5-8): Hypertrophy Focus\r\n• Phase 3 (Weeks 9-12): Intensity Peak\r\n\r\n━━━━ PHASE 1: STRENGTH FOUNDATION (WEEKS 1-4) ━━━━\r\n\r\nMONDAY - CHEST &amp; TRICEPS\r\n• Barbell bench press: 4 sets x 8-10 reps\r\n• Incline dumbbell press: 3 sets x 10-12 reps\r\n• Cable crossovers: 3 sets x 12-15 reps\r\n• Close-grip bench press: 3 sets x 8-10 reps\r\n• Tricep pushdowns: 3 sets x 12-15 reps\r\n• Overhead tricep extensions: 3 sets x 10-12 reps\r\n\r\nTUESDAY - BACK &amp; BICEPS\r\n• Deadlifts: 4 sets x 5-8 reps\r\n• Pull-ups: 4 sets x max reps\r\n• Barbell rows: 3 sets x 8-10 reps\r\n• Lat pulldowns: 3 sets x 10-12 reps\r\n• Barbell curls: 3 sets x 8-10 reps\r\n• Hammer curls: 3 sets x 10-12 reps\r\n\r\nWEDNESDAY - REST/ACTIVE RECOVERY\r\n• 30 min light cardio\r\n• Stretching routine\r\n\r\nTHURSDAY - SHOULDERS &amp; ABS\r\n• Overhead press: 4 sets x 8-10 reps\r\n• Lateral raises: 3 sets x 12-15 reps\r\n• Front raises: 3 sets x 10-12 reps\r\n• Rear delt flys: 3 sets x 12-15 reps\r\n• Hanging leg raises: 3 sets x 15 reps\r\n• Russian twists: 3 sets x 20 reps\r\n\r\nFRIDAY - LEGS\r\n• Squats: 4 sets x 8-10 reps\r\n• Romanian deadlifts: 3 sets x 10-12 reps\r\n• Leg press: 3 sets x 12-15 reps\r\n• Lunges: 3 sets x 10 reps each leg\r\n• Calf raises: 4 sets x 15-20 reps\r\n\r\nSATURDAY - FULL BODY\r\n• Combination of compound lifts\r\n• 3-4 exercises, 3 sets each\r\n• Focus on form and mind-muscle connection\r\n\r\nSUNDAY - COMPLETE REST\r\n\r\n━━━━ PHASE 2: HYPERTROPHY FOCUS (WEEKS 5-8) ━━━━\r\n• Increase volume: 4-5 sets per exercise\r\n• Rep range: 10-15 reps\r\n• Rest: 60-90 seconds between sets\r\n• Add drop sets on final exercise\r\n\r\n━━━━ PHASE 3: INTENSITY PEAK (WEEKS 9-12) ━━━━\r\n• Incorporate supersets\r\n• Add intensity techniques:\r\n  - Rest-pause sets\r\n  - Negative reps\r\n  - Partial reps\r\n• Track all lifts for PR attempts\r\n\r\nNUTRITION GUIDE:\r\n• Protein: 1.6-2.2g per kg bodyweight\r\n• Carbs: 4-6g per kg bodyweight\r\n• Fats: 0.8-1g per kg bodyweight\r\n• Pre-workout: Carbs + protein 60 min before\r\n• Post-workout: Protein shake within 30 min\r\n\r\nSUPPLEMENT RECOMMENDATIONS:\r\n• Whey protein\r\n• Creatine monohydrate (5g daily)\r\n• BCAAs (optional)\r\n• Multivitamin', 'Science-based progressive overload program to build lean muscle mass. Includes detailed workout splits, nutrition guide, and progress tracking.', 3999.00, '12 weeks', 'muscle_building', NULL, 11, '2026-02-16 15:57:37', 1),
(11, 'Yoga &amp; Flexibility Mastery', '🧘 YOGA &amp; FLEXIBILITY MASTERY - COMPLETE 8-WEEK PROGRAM 🧘\r\n\r\nPROGRAM OVERVIEW:\r\n• 3 sessions per week\r\n• 45-60 minutes per session\r\n• Progressive difficulty\r\n• Includes breathing techniques (pranayama)\r\n\r\n━━━━ WEEK 1-2: FOUNDATION &amp; BASIC POSES ━━━━\r\n\r\nSESSION A - BEGINNER FLOW\r\n• Centering breath (5 min)\r\n• Cat-cow stretch: 5 rounds\r\n• Downward dog: hold 1 min\r\n• Child&#039;s pose: 1 min\r\n• Sun salutation A: 3 rounds\r\n• Warrior I: hold 30 sec each side\r\n• Warrior II: hold 30 sec each side\r\n• Triangle pose: hold 30 sec each side\r\n• Seated forward fold: 1 min\r\n• Savasana: 5 min\r\n\r\nSESSION B - HIP OPENERS\r\n• Butterfly pose: 2 min\r\n• Pigeon pose: 1 min each side\r\n• Lizard pose: 30 sec each side\r\n• Happy baby: 1 min\r\n• Reclined hamstring stretch: 30 sec each side\r\n• Supine twist: 1 min each side\r\n\r\nSESSION C - SPINAL HEALTH\r\n• Cobra pose: 3 reps x 15 sec\r\n• Locust pose: 3 reps x 15 sec\r\n• Bridge pose: 3 reps x 30 sec\r\n• Spinal twists: 1 min each side\r\n• Seated forward fold: 2 min\r\n\r\n━━━━ WEEK 3-4: BUILDING STRENGTH ━━━━\r\n• Add plank variations\r\n• Introduce boat pose\r\n• Crow pose preparation\r\n• Longer holds (45-60 sec)\r\n• Add sun salutation B\r\n\r\n━━━━ WEEK 5-6: DEEPENING PRACTICE ━━━━\r\n• Warrior III balance work\r\n• Half moon pose\r\n• Standing splits\r\n• Wheel pose preparation\r\n• Inversions introduction\r\n• Longer sequences\r\n\r\n━━━━ WEEK 7-8: ADVANCED FLOW ━━━━\r\n• Full primary series\r\n• Arm balances\r\n• Headstand preparation\r\n• 60-75 min sessions\r\n• Meditation integration (10 min)\r\n\r\nBREATHING TECHNIQUES:\r\n• Ujjayi breath (ocean breath)\r\n• Alternate nostril breathing\r\n• Kapalabhati (skull shining breath)\r\n• Box breathing for relaxation\r\n\r\nBENEFITS TRACKING:\r\n• Weekly flexibility measurements\r\n• Stress level journal\r\n• Energy level tracking\r\n• Sleep quality monitoring', 'Comprehensive 8-week yoga program for all levels. Improve flexibility, reduce stress, and build core strength through guided sessions.', 2499.00, '8 weeks', 'yoga', NULL, 11, '2026-02-16 15:58:03', 1),
(12, 'Cardio &amp; Endurance Booster', '🏃 CARDIO &amp; ENDURANCE BOOSTER - 6-WEEK PROGRAM 🏃\r\n\r\nPROGRAM STRUCTURE:\r\n• 5 workouts per week\r\n• Progressive intensity\r\n• Mix of steady-state and HIIT\r\n• Heart rate zone training included\r\n\r\n━━━━ WEEK 1-2: BUILDING AEROBIC BASE ━━━━\r\n\r\nMONDAY - STEADY STATE\r\n• 30 min jogging at 60-70% max heart rate\r\n• Pace: conversational\r\n\r\nTUESDAY - HIIT BEGINNER\r\n• Warm-up: 5 min brisk walk\r\n• 30 sec sprint / 90 sec walk x 6 rounds\r\n• Cool-down: 5 min walk\r\n\r\nWEDNESDAY - ACTIVE RECOVERY\r\n• 40 min brisk walking\r\n• Full body stretching (15 min)\r\n\r\nTHURSDAY - STEADY STATE\r\n• 35 min cycling at 65-75% max heart rate\r\n\r\nFRIDAY - HIIT\r\n• Warm-up: 5 min\r\n• 45 sec high intensity / 75 sec recovery x 8 rounds\r\n• Cool-down: 5 min\r\n\r\nSATURDAY - LONG CARDIO\r\n• 50 min jogging/walking mix\r\n• Focus on time, not pace\r\n\r\nSUNDAY - COMPLETE REST\r\n\r\n━━━━ WEEK 3-4: INCREASING INTENSITY ━━━━\r\n• Increase steady state to 40-45 min\r\n• HIIT: 45 sec work / 60 sec recovery\r\n• Add interval variations:\r\n  - Pyramid intervals\r\n  - Tabata (20/10) twice weekly\r\n• Long cardio: 60-70 min\r\n\r\n━━━━ WEEK 5-6: PEAK ENDURANCE ━━━━\r\n• Steady state: 50-60 min at 70-80% HR\r\n• HIIT: 60 sec work / 60 sec recovery x 10 rounds\r\n• Add stair climbing and jump rope\r\n• Long cardio: 75-90 min\r\n• Introduction to tempo runs\r\n\r\nHEART RATE ZONES:\r\n• Zone 2 (60-70%): Fat burning, conversational\r\n• Zone 3 (70-80%): Aerobic, slightly breathless\r\n• Zone 4 (80-90%): Threshold, uncomfortable\r\n• Zone 5 (90-100%): Maximum effort, sprint\r\n\r\nWORKOUT TRACKING:\r\n• Daily: Duration, distance, average HR\r\n• Weekly: Resting heart rate (morning)\r\n• Progress photos every 2 weeks\r\n• Timed 1-mile run at start/end of program\r\n\r\nNUTRITION FOR ENDURANCE:\r\n• Pre-workout: Light carbs 60-90 min before\r\n• During (&gt;60 min): Electrolytes, gels if needed\r\n• Post-workout: Protein + carbs within 30 min\r\n• Hydration: 500ml water 2 hours before', '6-week cardiovascular program to boost stamina, improve heart health, and increase athletic performance. Includes running, cycling, and HIIT workouts.', 2199.00, '6 weeks', 'cardio', NULL, 11, '2026-02-16 15:58:40', 1),
(13, '12-Week Muscle Building Program', 'test tyest asda', 'test', 2000.00, '30 days', 'yoga', NULL, 11, '2026-02-18 15:17:12', 1),
(14, 'leg day', 'asdasdasdas', 'asd', 2000.00, '1 day', 'beginner', NULL, 11, '2026-02-18 15:30:45', 1);

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
(24, 10, 12, '2026-02-16 16:01:07', 2199.00, 'MOCK_69933f436b9f3_1771257667', 'mock', 'completed', 'completed', 0, '2026-02-18 12:31:46', NULL, 0, 30, NULL),
(27, 10, 9, '2026-02-16 17:34:19', 2999.00, 'MOCK_6993551ba1063_1771263259', 'mock', 'completed', 'completed', 1, NULL, NULL, 0, 30, NULL),
(28, 10, 12, '2026-02-17 12:36:11', 2199.00, 'MOCK_699460bb2425b_1771331771', 'mock', 'completed', 'completed', 1, NULL, NULL, 0, 30, NULL),
(29, 10, 10, '2026-02-17 12:39:47', 3999.00, 'MOCK_6994619370c58_1771331987', 'mock', 'completed', 'completed', 1, NULL, '2026-02-18 15:15:56', 0, 30, NULL),
(30, 10, 13, '2026-02-18 15:17:25', 2000.00, 'MOCK_6995d805e15c2_1771427845', 'mock', '', 'completed', 0, NULL, NULL, 0, 30, NULL),
(31, 10, 14, '2026-02-18 15:30:57', 2000.00, 'MOCK_6995db3105388_1771428657', 'mock', '', 'completed', 0, '2026-02-18 15:31:33', NULL, 0, 30, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','processed','rejected') DEFAULT 'pending',
  `refund_method` varchar(50) DEFAULT NULL,
  `refund_transaction_id` varchar(100) DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_date` timestamp NULL DEFAULT NULL,
  `processed_date` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refunds`
--

INSERT INTO `refunds` (`id`, `purchase_id`, `user_id`, `amount`, `status`, `refund_method`, `refund_transaction_id`, `request_date`, `approved_date`, `processed_date`, `approved_by`, `notes`, `created_at`, `updated_at`) VALUES
(2, 31, 10, 2000.00, 'processed', 'original', 'test', '2026-02-18 15:31:07', '2026-02-18 15:31:15', '2026-02-18 15:31:33', 11, 'sadasdaasdasdasd', '2026-02-18 15:31:07', '2026-02-18 15:31:33');

-- --------------------------------------------------------

--
-- Table structure for table `refund_logs`
--

CREATE TABLE `refund_logs` (
  `id` int(11) NOT NULL,
  `refund_id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `package_details` text DEFAULT NULL,
  `amount_refunded` decimal(10,2) NOT NULL,
  `refund_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `refund_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refund_logs`
--

INSERT INTO `refund_logs` (`id`, `refund_id`, `purchase_id`, `user_id`, `package_id`, `package_name`, `package_details`, `amount_refunded`, `refund_date`, `refund_method`, `transaction_id`, `processed_by`) VALUES
(1, 2, 31, 10, 14, 'leg day', 'asdasdasdas', 2000.00, '2026-02-18 15:31:33', 'original', 'test', 11);

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
(10, 'test', 'test@example.com', '9800000000', '$2y$10$b/B3ylqiD8X/oEJhUMDgqO5OC3tM2F.NAlqckyntJlu3AoblYk/B2', '2026-02-16 14:14:52', 'user'),
(11, 'admin', 'admin@gmail.com', '9800000000', '$2y$10$6rfD1Md6y/Xdx7CTNKwoq.c9BcmXl/jsbrKhdgCRaUHILksgxfYzO', '2026-02-16 15:39:18', 'admin');

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
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_purchase_id` (`purchase_id`),
  ADD KEY `fk_refunds_approved_by` (`approved_by`),
  ADD KEY `idx_purchase_id` (`purchase_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_request_date` (`request_date`);

--
-- Indexes for table `refund_logs`
--
ALTER TABLE `refund_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `refund_id` (`refund_id`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `refund_logs`
--
ALTER TABLE `refund_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `fk_refunds_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_refunds_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_refunds_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
