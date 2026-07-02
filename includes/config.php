<?php
session_start();

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'sagarart_db';

$conn = @new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS `sagarart_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbname);
$conn->set_charset('utf8');

$conn->query("CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `hero_title` VARCHAR(255) DEFAULT NULL,
  `hero_text` TEXT DEFAULT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `summary` TEXT NOT NULL,
  `content` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$adminPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("INSERT INTO `admin_users` (username, password_hash) VALUES ('admin', '$adminPasswordHash') ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
$conn->query("INSERT INTO `pages` (slug, title, content, hero_title, hero_text, image_url) VALUES
  ('home', 'Home', 'Welcome to Sagar Art. We build polished digital experiences for modern brands.', 'Welcome to Sagar Art', 'We create websites, branding, and digital services that help businesses grow.', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80'),
  ('services', 'Services', 'Our services are crafted to solve business needs with speed and creativity.', 'Our Services', 'Explore the services we offer to help your business grow and stand out.', 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1200&q=80'),
  ('about', 'About', 'We are a creative agency focused on digital strategy and web experiences.', 'About Us', 'We combine design, development, and strategy to deliver memorable experiences.', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80'),
  ('contact', 'Contact', 'Reach out to discuss your next project and let us build something meaningful together.', 'Contact Us', 'Let us know about your ideas and we will be happy to help.', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80')
  ON DUPLICATE KEY UPDATE title = VALUES(title)");
$conn->query("INSERT INTO `services` (title, slug, summary, content) VALUES
  ('Web Design', 'web-design', 'Beautiful and modern interfaces for your brand.', 'We design responsive websites that feel premium and work perfectly across all devices.'),
  ('Web Development', 'web-development', 'Reliable PHP and MySQL-based applications.', 'We develop custom PHP applications with fast load times and clean architecture.'),
  ('SEO Optimization', 'seo-optimization', 'Boost your online visibility and organic traffic.', 'We improve your website structure, content, and metadata to rank higher in search results.'),
  ('Brand Strategy', 'brand-strategy', 'Create a strong message and identity for your business.', 'We help shape your brand voice, positioning, and visual identity for lasting growth.')
  ON DUPLICATE KEY UPDATE title = VALUES(title)");

function getPageContent($conn, $slug) {
    $stmt = $conn->prepare('SELECT title, content, hero_title, hero_text, image_url FROM pages WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getServices($conn) {
    $result = $conn->query('SELECT id, title, slug, summary, content FROM services ORDER BY id ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getServiceBySlug($conn, $slug) {
    $stmt = $conn->prepare('SELECT id, title, slug, summary, content FROM services WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}
?>
