-- ============================================
-- COMPLETE DATABASE SETUP FOR FitLife Gym
-- WITH ALL FINAL CHANGES
-- ============================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `gwps` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gwps`;

-- ============================================
-- TABLE: users
-- ============================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','admin') DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLE: packages
-- ============================================
CREATE TABLE `packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(1000) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLE: purchases (FINAL VERSION WITH COMPLETION TRACKING)
-- ============================================
CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'initial',
  `status` enum('pending','completed') DEFAULT 'completed',
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `package_status` enum('active','completed','paused') DEFAULT 'active',
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `package_id` (`package_id`),
  CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- REMOVE ALL UNWANTED COLUMNS (if they exist)
-- ============================================

-- Remove delete request columns
ALTER TABLE `purchases` 
DROP COLUMN IF EXISTS `delete_requested`,
DROP COLUMN IF EXISTS `delete_request_date`,
DROP COLUMN IF EXISTS `delete_approved`,
DROP COLUMN IF EXISTS `delete_approved_date`,
DROP COLUMN IF EXISTS `delete_approved_by`,
DROP COLUMN IF EXISTS `delete_reason`,
DROP COLUMN IF EXISTS `rejection_reason`,
DROP COLUMN IF EXISTS `auto_delete_date`;

-- Remove old tracking columns
ALTER TABLE `purchases` 
DROP COLUMN IF EXISTS `completed_workouts`,
DROP COLUMN IF EXISTS `total_workouts`,
DROP COLUMN IF EXISTS `last_workout_date`,
DROP COLUMN IF EXISTS `hidden_until`,
DROP COLUMN IF EXISTS `is_archived`;

-- Remove foreign key constraints if they exist
ALTER TABLE `purchases` 
DROP FOREIGN KEY IF EXISTS `purchases_ibfk_3`,
DROP FOREIGN KEY IF EXISTS `purchases_ibfk_4`;

-- ============================================
-- ADD NEW COLUMNS FOR PACKAGE COMPLETION
-- ============================================

-- Add package_status column (if not exists)
ALTER TABLE `purchases` 
ADD COLUMN IF NOT EXISTS `package_status` enum('active','completed','paused') DEFAULT 'active' AFTER `deleted_at`;

-- Add completed_at column (if not exists)
ALTER TABLE `purchases` 
ADD COLUMN IF NOT EXISTS `completed_at` timestamp NULL DEFAULT NULL AFTER `package_status`;

-- ============================================
-- SAMPLE DATA WITH CORRECT PASSWORD HASHES
-- ============================================

-- Truncate existing data (optional - be careful!)
-- TRUNCATE TABLE `purchases`;
-- TRUNCATE TABLE `packages`;
-- TRUNCATE TABLE `users`;

-- Insert users with correct password hashes
-- Password for user: password123
-- Password for admin: Admin@123
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `created_at`, `role`) VALUES
(1, 'Ridesh', 'Arkrideshmaharjan@gmail.com', '9800000000', '$2y$10$fE0iYoAHt1h.SfMuXtdvTenu5EgspvttTmX79UkBsKizFEhXfrR6yG', '2025-12-13 15:08:21', 'user'),
(6, 'ARK', 'adminridesh@gmail.com', '9821139366', '$2y$10$Jin3/Ay66o8GH3NCnmuuHuk/Zs9Ww7O12f4jTsVbFITMaTyWVbSq', '2025-12-18 14:10:35', 'admin');

-- Insert sample packages
INSERT INTO `packages` (`id`, `name`, `description`, `short_description`, `price`, `duration`, `category`, `image`, `created_by`, `created_at`) VALUES
(1, 'Weight Loss Program', 'Complete 30-day weight loss challenge with diet plans and cardio workouts', '30-day weight loss challenge', 299.00, '30 days', 'weight_loss', NULL, 1, '2025-12-27 18:52:12'),
(2, 'Muscle Building', '12-week strength training program for muscle gain', '12-week strength training', 199.00, '12 weeks', 'strength', NULL, 1, '2025-12-27 18:52:12'),
(5, 'Bicep Program', '6-week specialized bicep workout for arm definition', 'Bicep focused program', 199.00, '6 weeks', 'muscle_building', NULL, 6, '2025-12-27 21:51:34');

-- Insert sample purchases with package_status
INSERT INTO `purchases` (`id`, `user_id`, `package_id`, `purchase_date`, `amount`, `transaction_id`, `payment_method`, `status`, `payment_status`, `is_active`, `deleted_at`, `package_status`, `completed_at`) VALUES
(11, 1, 5, '2026-02-14 20:12:28', 199.00, NULL, 'initial', 'completed', 'pending', 1, NULL, 'active', NULL),
(13, 1, 1, '2026-02-13 00:17:22', 299.00, NULL, 'initial', 'completed', 'pending', 1, NULL, 'completed', '2026-02-15 10:30:00'),
(14, 1, 1, '2026-02-14 22:49:20', 299.00, NULL, 'initial', 'completed', 'pending', 1, NULL, 'active', NULL),
(16, 1, 5, '2026-02-14 23:17:02', 199.00, 'MOCK_6990b192a8693', 'mock', 'completed', 'pending', 1, NULL, 'active', NULL),
(17, 1, 5, '2026-02-14 23:19:12', 199.00, 'MOCK_6990b214ba7b', 'mock', 'completed', 'pending', 1, NULL, 'active', NULL),
(18, 1, 1, '2026-02-14 23:27:17', 299.00, 'MOCK_6990b639f8e26', 'mock', 'completed', 'pending', 1, NULL, 'active', NULL),
(19, 1, 1, '2026-02-14 23:35:46', 299.00, 'MOCK_6990b67424bad4_1771091655', 'mock', 'completed', 'pending', 1, NULL, 'active', NULL),
(20, 1, 5, '2026-02-14 23:40:06', 199.00, 'MOCK_6990b67a5d751_1771092346', 'mock', 'completed', 'pending', 1, NULL, 'active', NULL),
(21, 1, 5, '2026-02-14 23:45:46', 199.00, 'MOCK_6990b68e1963e2_1771092705', 'mock', 'completed', 'pending', 1, NULL, 'active', NULL);

-- ============================================
-- UPDATE AUTO INCREMENT VALUES
-- ============================================
ALTER TABLE `users` AUTO_INCREMENT = 7;
ALTER TABLE `packages` AUTO_INCREMENT = 6;
ALTER TABLE `purchases` AUTO_INCREMENT = 22;

-- ============================================
-- VERIFY THE STRUCTURE
-- ============================================

-- Check users table
SELECT 'users' as table_name, COUNT(*) as record_count FROM users
UNION ALL
-- Check packages table
SELECT 'packages', COUNT(*) FROM packages
UNION ALL
-- Check purchases table
SELECT 'purchases', COUNT(*) FROM purchases;

-- Show purchases with package status
SELECT 
    p.id as purchase_id,
    u.email,
    pk.name as package_name,
    p.purchase_date,
    p.package_status,
    p.completed_at
FROM purchases p
JOIN users u ON p.user_id = u.id
JOIN packages pk ON p.package_id = pk.id
ORDER BY p.purchase_date DESC;

-- ============================================
-- USEFUL QUERIES FOR THE APPLICATION
-- ============================================

-- Get active packages count for a user
SELECT COUNT(*) as active_count 
FROM purchases 
WHERE user_id = 1 AND is_active = 1 AND (package_status != 'completed' OR package_status IS NULL);

-- Get completed packages count for a user
SELECT COUNT(*) as completed_count 
FROM purchases 
WHERE user_id = 1 AND is_active = 1 AND package_status = 'completed';

-- Mark a package as completed
UPDATE purchases 
SET package_status = 'completed', completed_at = NOW() 
WHERE id = 11 AND user_id = 1;

-- Get user's packages with status
SELECT 
    p.name,
    p.duration,
    p.short_description,
    pur.purchase_date,
    pur.package_status,
    pur.completed_at
FROM purchases pur
JOIN packages p ON pur.package_id = p.id
WHERE pur.user_id = 1 AND pur.is_active = 1
ORDER BY 
    CASE 
        WHEN pur.package_status = 'completed' THEN 2
        ELSE 1
    END,
    pur.purchase_date DESC;