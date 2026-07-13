-- Migration: Add category_id to pricing_items and create indexes/constraints
ALTER TABLE `pricing_items`
  ADD COLUMN IF NOT EXISTS `category_id` INT DEFAULT NULL;

-- Populate category_id from pricing_categories where names match
UPDATE `pricing_items` pi
JOIN `pricing_categories` pc ON TRIM(pc.name) = TRIM(pi.category)
SET pi.category_id = pc.id
WHERE pi.category_id IS NULL OR pi.category_id = 0;

-- Create index for faster lookups and enforce uniqueness per catalog item within a category
ALTER TABLE `pricing_items`
  ADD INDEX IF NOT EXISTS `idx_pricing_items_catalog_item` (`catalog_item_id`),
  ADD UNIQUE INDEX IF NOT EXISTS `uix_category_catalog_item` (`category_id`, `catalog_item_id`);

-- Add foreign key constraint if category_id values exist
ALTER TABLE `pricing_items`
  ADD CONSTRAINT IF NOT EXISTS `fk_pricing_items_category` FOREIGN KEY (`category_id`) REFERENCES `pricing_categories`(`id`) ON DELETE SET NULL;

-- Ensure thresholds table exists (safe idempotent create)
CREATE TABLE IF NOT EXISTS `pricing_item_thresholds` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pricing_item_id` INT NOT NULL,
  `min_quantity` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` INT NOT NULL DEFAULT 0,
  UNIQUE KEY `pricing_item_thresholds_unique` (`pricing_item_id`, `min_quantity`),
  CONSTRAINT `fk_pricing_item_thresholds_item` FOREIGN KEY (`pricing_item_id`) REFERENCES `pricing_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
