-- Migration: Add threshold pricing columns to pricing_items
ALTER TABLE `pricing_items`
  ADD COLUMN IF NOT EXISTS `threshold_quantity` INT NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `threshold_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00;
