-- Migration: Create pricing_item_thresholds table (if missing)
CREATE TABLE IF NOT EXISTS `pricing_item_thresholds` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pricing_item_id` INT NOT NULL,
  `min_quantity` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` INT NOT NULL DEFAULT 0,
  UNIQUE KEY `pricing_item_thresholds_unique` (`pricing_item_id`, `min_quantity`),
  CONSTRAINT `fk_pricing_item_thresholds_item` FOREIGN KEY (`pricing_item_id`) REFERENCES `pricing_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
