-- No schema change is required for the new Our Clients section because
-- page_sections and service_sections already support JSON settings.
-- This file exists as a deployment note for environments that want an explicit migration step.

-- Optional safety check: ensure the settings column exists on both tables.
-- If it does not, add it.
ALTER TABLE `page_sections` ADD COLUMN IF NOT EXISTS `settings` TEXT DEFAULT NULL;
ALTER TABLE `service_sections` ADD COLUMN IF NOT EXISTS `settings` TEXT DEFAULT NULL;

-- Example of the expected JSON payload for the new section type:
-- {"clients":["uploads/file_123.jpg","uploads/file_456.png"]}
