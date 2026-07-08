-- Migration: Add hero media support and section settings for pages and services
-- Run this on existing installations to update schema

ALTER TABLE `pages`
  ADD COLUMN IF NOT EXISTS `hero_title` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `hero_text` TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `hero_media_type` VARCHAR(20) DEFAULT 'image',
  ADD COLUMN IF NOT EXISTS `hero_video_url` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `hero_bg_color` VARCHAR(7) DEFAULT '#4f46e5',
  ADD COLUMN IF NOT EXISTS `hero_text_color` VARCHAR(7) DEFAULT '#ffffff';

ALTER TABLE `page_sections`
  ADD COLUMN IF NOT EXISTS `settings` TEXT DEFAULT NULL;

ALTER TABLE `services`
  ADD COLUMN IF NOT EXISTS `hero_title` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `hero_text` TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `hero_media_type` VARCHAR(20) DEFAULT 'image',
  ADD COLUMN IF NOT EXISTS `hero_video_url` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `hero_bg_color` VARCHAR(7) DEFAULT '#4f46e5',
  ADD COLUMN IF NOT EXISTS `hero_text_color` VARCHAR(7) DEFAULT '#ffffff',
  ADD COLUMN IF NOT EXISTS `show_in_menu` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `menu_order` INT NOT NULL DEFAULT 0;

ALTER TABLE `services`
  DROP COLUMN IF EXISTS `summary`,
  DROP COLUMN IF EXISTS `content`;

CREATE TABLE IF NOT EXISTS `service_sections` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_slug` VARCHAR(100) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `content` TEXT DEFAULT NULL,
  `section_type` VARCHAR(50) NOT NULL DEFAULT 'content',
  `image_url` VARCHAR(255) DEFAULT NULL,
  `video_url` VARCHAR(255) DEFAULT NULL,
  `button_text` VARCHAR(255) DEFAULT NULL,
  `button_link` VARCHAR(255) DEFAULT NULL,
  `settings` TEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
