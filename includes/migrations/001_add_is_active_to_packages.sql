-- Migration: add is_active column to packages
-- Run this SQL once (e.g., via phpMyAdmin or mysql CLI)

ALTER TABLE `packages` 
ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `created_at`;

-- Optionally set some existing packages inactive (example):
-- UPDATE packages SET is_active = 0 WHERE id = 5;