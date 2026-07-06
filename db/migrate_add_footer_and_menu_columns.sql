-- Migration: Add footer CTA and footer quick link support
-- Run this on existing installations to update schema

ALTER TABLE `site_settings`
  ADD COLUMN IF NOT EXISTS `footer_cta_heading` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `footer_cta_text` TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `footer_cta_button_text` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `footer_cta_button_link` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `menu_items`
  ADD COLUMN IF NOT EXISTS `show_in_footer` TINYINT(1) NOT NULL DEFAULT 0;

-- Add settings column to store JSON for page sections (selected services, etc.)
ALTER TABLE `page_sections`
  ADD COLUMN IF NOT EXISTS `settings` TEXT DEFAULT NULL;

-- Seed default footer CTA values if the row exists and values are empty
UPDATE `site_settings`
SET
  `footer_cta_heading` = COALESCE(NULLIF(`footer_cta_heading`, ''), 'Build memorable digital experiences with a modern agency feel.'),
  `footer_cta_text` = COALESCE(NULLIF(`footer_cta_text`, ''), 'Create more persuasive pages, polished service showcases, and faster contact flows with a website designed for conversions.'),
  `footer_cta_button_text` = COALESCE(NULLIF(`footer_cta_button_text`, ''), 'Get in touch'),
  `footer_cta_button_link` = COALESCE(NULLIF(`footer_cta_button_link`, ''), 'contact.php')
WHERE `id` = 1;
