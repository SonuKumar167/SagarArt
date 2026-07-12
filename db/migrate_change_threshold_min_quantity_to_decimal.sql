-- Migration: Change pricing_item_thresholds.min_quantity to DECIMAL to support fractional/area thresholds
-- Run this once against the database used by the application.

ALTER TABLE `pricing_item_thresholds`
  MODIFY COLUMN `min_quantity` DECIMAL(10,4) NOT NULL DEFAULT 0.0000;

-- Optional: verify the column type
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pricing_item_thresholds' AND COLUMN_NAME = 'min_quantity';
