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
  `image_url` VARCHAR(255) DEFAULT NULL,
  `hero_video_url` VARCHAR(255) DEFAULT NULL,
  `show_in_menu` TINYINT(1) NOT NULL DEFAULT 1,
  `menu_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `summary` TEXT NOT NULL,
  `content` TEXT NOT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `display_order` INT NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `site_name` VARCHAR(255) NOT NULL DEFAULT 'Sagar Art',
  `tagline` VARCHAR(255) DEFAULT NULL,
  `header_text` TEXT DEFAULT NULL,
  `header_cta_text` VARCHAR(255) DEFAULT NULL,
  `header_cta_link` VARCHAR(255) DEFAULT NULL,
  `footer_about` TEXT DEFAULT NULL,
  `footer_email` VARCHAR(255) DEFAULT NULL,
  `footer_phone` VARCHAR(255) DEFAULT NULL,
  `footer_address` TEXT DEFAULT NULL,
  `footer_copyright` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `page_sections` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `page_slug` VARCHAR(100) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `content` TEXT DEFAULT NULL,
  `section_type` VARCHAR(50) NOT NULL DEFAULT 'content',
  `image_url` VARCHAR(255) DEFAULT NULL,
  `video_url` VARCHAR(255) DEFAULT NULL,
  `button_text` VARCHAR(255) DEFAULT NULL,
  `button_link` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `contact_submissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `label` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) NOT NULL,
  `menu_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `has_dropdown` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `menu_children` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `parent_id` INT NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) NOT NULL,
  `menu_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function addColumnIfMissing($conn, $table, $column, $definition) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

addColumnIfMissing($conn, 'pages', 'show_in_menu', 'TINYINT(1) NOT NULL DEFAULT 1');
addColumnIfMissing($conn, 'pages', 'menu_order', 'INT NOT NULL DEFAULT 0');
addColumnIfMissing($conn, 'pages', 'hero_video_url', 'VARCHAR(255) DEFAULT NULL');
addColumnIfMissing($conn, 'services', 'image_url', 'VARCHAR(255) DEFAULT NULL');
addColumnIfMissing($conn, 'services', 'display_order', 'INT NOT NULL DEFAULT 0');
addColumnIfMissing($conn, 'services', 'is_featured', 'TINYINT(1) NOT NULL DEFAULT 0');
addColumnIfMissing($conn, 'menu_items', 'has_dropdown', 'TINYINT(1) NOT NULL DEFAULT 0');
addColumnIfMissing($conn, 'site_settings', 'header_text', 'TEXT DEFAULT NULL');
addColumnIfMissing($conn, 'site_settings', 'header_cta_text', 'VARCHAR(255) DEFAULT NULL');
addColumnIfMissing($conn, 'site_settings', 'header_cta_link', 'VARCHAR(255) DEFAULT NULL');

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
$conn->query("INSERT INTO `site_settings` (id, site_name, tagline, header_text, header_cta_text, header_cta_link, footer_about, footer_email, footer_phone, footer_address, footer_copyright) VALUES (1, 'Sagar Art', 'Creative digital experiences that elevate brands.', 'We help businesses build modern digital products, websites, and growth-focused marketing experiences.', 'Get Started', 'contact.php', 'We craft thoughtful digital products and brand experiences for modern businesses.', 'hello@sagarart.com', '+91 98765 43210', 'Mumbai, India', '© 2026 Sagar Art. All rights reserved.') ON DUPLICATE KEY UPDATE site_name = VALUES(site_name), tagline = VALUES(tagline), header_text = VALUES(header_text), header_cta_text = VALUES(header_cta_text), header_cta_link = VALUES(header_cta_link), footer_about = VALUES(footer_about), footer_email = VALUES(footer_email), footer_phone = VALUES(footer_phone), footer_address = VALUES(footer_address), footer_copyright = VALUES(footer_copyright)");

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'page';
}

function uploadFile($file) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return '';
    }

    $targetDir = realpath(__DIR__ . '/../uploads');
    if ($targetDir === false) {
        $targetDir = __DIR__ . '/../uploads';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('file_', true) . ($extension ? '.' . $extension : '');
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . $filename;
    }

    return '';
}

function getPageContent($conn, $slug) {
    $stmt = $conn->prepare('SELECT id, slug, title, content, hero_title, hero_text, image_url, hero_video_url, show_in_menu, menu_order FROM pages WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getNavigationPages($conn) {
    $result = $conn->query('SELECT slug, title FROM pages WHERE show_in_menu = 1 ORDER BY menu_order ASC, id ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getHeaderMenuItems($conn) {
    $result = $conn->query('SELECT id, label, link, has_dropdown FROM menu_items WHERE is_active = 1 ORDER BY menu_order ASC, id ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getMenuChildren($conn, $parentId) {
    $stmt = $conn->prepare('SELECT id, label, link FROM menu_children WHERE parent_id = ? AND is_active = 1 ORDER BY menu_order ASC, id ASC');
    $stmt->bind_param('i', $parentId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getPageSections($conn, $slug) {
    $stmt = $conn->prepare('SELECT id, title, content, section_type, image_url, video_url, button_text, button_link FROM page_sections WHERE page_slug = ? ORDER BY sort_order ASC, id ASC');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getSiteSettings($conn) {
    $result = $conn->query('SELECT * FROM site_settings ORDER BY id DESC LIMIT 1');
    $settings = $result ? $result->fetch_assoc() : [];

    return $settings ?: [
        'site_name' => 'Sagar Art',
        'tagline' => 'Creative digital experiences that elevate brands.',
        'header_text' => 'We help businesses build modern digital products, websites, and growth-focused marketing experiences.',
        'header_cta_text' => 'Get Started',
        'header_cta_link' => 'contact.php',
        'footer_about' => 'We craft thoughtful digital products and brand experiences for modern businesses.',
        'footer_email' => 'hello@sagarart.com',
        'footer_phone' => '+91 98765 43210',
        'footer_address' => 'Mumbai, India',
        'footer_copyright' => '© 2026 Sagar Art. All rights reserved.'
    ];
}

function getServices($conn) {
    $result = $conn->query('SELECT id, title, slug, summary, content, image_url, display_order, is_featured FROM services ORDER BY display_order ASC, id ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getServiceBySlug($conn, $slug) {
    $stmt = $conn->prepare('SELECT id, title, slug, summary, content, image_url FROM services WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}
?>
