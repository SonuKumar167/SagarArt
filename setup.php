<?php
require 'includes/config.php';

$adminPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);

$sql = [];
$sql[] = "CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `hero_title` VARCHAR(255) DEFAULT NULL,
  `hero_text` TEXT DEFAULT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `hero_media_type` VARCHAR(20) DEFAULT 'image',
  `hero_video_url` VARCHAR(255) DEFAULT NULL,
  `hero_bg_color` VARCHAR(7) DEFAULT '#4f46e5',
  `hero_text_color` VARCHAR(7) DEFAULT '#ffffff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$sql[] = "CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `summary` TEXT NOT NULL,
  `content` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$sql[] = "CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$sql[] = 'INSERT INTO `admin_users` (username, password_hash) VALUES ("admin", "' . $adminPasswordHash . '") ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)';
$sql[] = "INSERT INTO `pages` (slug, title, content, hero_title, hero_text, image_url) VALUES
  ('home', 'Home', 'Welcome to Sagar Art. We build polished digital experiences for modern brands.', 'Welcome to Sagar Art', 'We create websites, branding, and digital services that help businesses grow.', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80'),
  ('services', 'Services', 'Our services are crafted to solve business needs with speed and creativity.', 'Our Services', 'Explore the services we offer to help your business grow and stand out.', 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1200&q=80'),
  ('about', 'About', 'We are a creative agency focused on digital strategy and web experiences.', 'About Us', 'We combine design, development, and strategy to deliver memorable experiences.', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80'),
  ('contact', 'Contact', 'Reach out to discuss your next project and let us build something meaningful together.', 'Contact Us', 'Let us know about your ideas and we will be happy to help.', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80')
  ON DUPLICATE KEY UPDATE title=VALUES(title)";
$sql[] = "INSERT INTO `services` (title, slug, summary, content) VALUES
  ('Web Design', 'web-design', 'Beautiful and modern interfaces for your brand.', 'We design responsive websites that feel premium and work perfectly across all devices.'),
  ('Web Development', 'web-development', 'Reliable PHP and MySQL-based applications.', 'We develop custom PHP applications with fast load times and clean architecture.'),
  ('SEO Optimization', 'seo-optimization', 'Boost your online visibility and organic traffic.', 'We improve your website structure, content, and metadata to rank higher in search results.'),
  ('Brand Strategy', 'brand-strategy', 'Create a strong message and identity for your business.', 'We help shape your brand voice, positioning, and visual identity for lasting growth.')
  ON DUPLICATE KEY UPDATE title=VALUES(title)";

foreach ($sql as $query) {
    $conn->query($query);
}

$conn->close();
header('Location: index.php');
"}