-- Migration: Add pricing_items table for calculator and admin pricing management
CREATE TABLE IF NOT EXISTS `pricing_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(150) NOT NULL,
  `item_name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `unit_label` VARCHAR(100) DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
