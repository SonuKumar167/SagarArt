-- Migration: Add catalog_item_id link to pricing_items
ALTER TABLE `pricing_items`
  ADD COLUMN IF NOT EXISTS `catalog_item_id` INT DEFAULT NULL;
