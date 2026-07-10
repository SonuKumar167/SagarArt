<?php
if (!defined('SAGARART_CONFIG_LOADED')) {
    define('SAGARART_CONFIG_LOADED', true);

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
  `hero_media_type` VARCHAR(20) DEFAULT 'image',
  `hero_video_url` VARCHAR(255) DEFAULT NULL,
  `hero_bg_color` VARCHAR(7) DEFAULT '#4f46e5',
  `hero_text_color` VARCHAR(7) DEFAULT '#ffffff',
  `show_in_menu` TINYINT(1) NOT NULL DEFAULT 1,
  `menu_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `hero_title` VARCHAR(255) DEFAULT NULL,
  `hero_text` TEXT DEFAULT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `hero_media_type` VARCHAR(20) DEFAULT 'image',
  `hero_video_url` VARCHAR(255) DEFAULT NULL,
  `hero_bg_color` VARCHAR(7) DEFAULT '#4f46e5',
  `hero_text_color` VARCHAR(7) DEFAULT '#ffffff',
  `show_in_menu` TINYINT(1) NOT NULL DEFAULT 1,
  `menu_order` INT NOT NULL DEFAULT 0,
  `display_order` INT NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS `service_sections` (
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
  `footer_cta_heading` VARCHAR(255) DEFAULT NULL,
  `footer_cta_text` TEXT DEFAULT NULL,
  `footer_cta_button_text` VARCHAR(255) DEFAULT NULL,
  `footer_cta_button_link` VARCHAR(255) DEFAULT NULL,
  `footer_copyright` VARCHAR(255) DEFAULT NULL,
  `favicon_url` VARCHAR(255) DEFAULT NULL,
  `logo_url` VARCHAR(255) DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `facebook_url` VARCHAR(255) DEFAULT NULL,
  `instagram_url` VARCHAR(255) DEFAULT NULL,
  `twitter_url` VARCHAR(255) DEFAULT NULL,
  `youtube_url` VARCHAR(255) DEFAULT NULL
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
  `settings` TEXT DEFAULT NULL,
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
    addColumnIfMissing($conn, 'pages', 'hero_media_type', 'VARCHAR(20) DEFAULT "image"');
    addColumnIfMissing($conn, 'pages', 'hero_bg_color', 'VARCHAR(7) DEFAULT "#4f46e5"');
    addColumnIfMissing($conn, 'pages', 'hero_text_color', 'VARCHAR(7) DEFAULT "#ffffff"');
    addColumnIfMissing($conn, 'pages', 'button_text', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'pages', 'button_link', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'services', 'hero_title', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'services', 'hero_text', 'TEXT DEFAULT NULL');
    addColumnIfMissing($conn, 'services', 'image_url', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'services', 'hero_media_type', 'VARCHAR(20) DEFAULT "image"');
    addColumnIfMissing($conn, 'services', 'hero_video_url', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'services', 'hero_bg_color', 'VARCHAR(7) DEFAULT "#4f46e5"');
    addColumnIfMissing($conn, 'services', 'hero_text_color', 'VARCHAR(7) DEFAULT "#ffffff"');
    addColumnIfMissing($conn, 'services', 'button_text', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'services', 'button_link', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'services', 'show_in_menu', 'TINYINT(1) NOT NULL DEFAULT 1');
    addColumnIfMissing($conn, 'services', 'menu_order', 'INT NOT NULL DEFAULT 0');
    addColumnIfMissing($conn, 'services', 'display_order', 'INT NOT NULL DEFAULT 0');
    addColumnIfMissing($conn, 'services', 'is_featured', 'TINYINT(1) NOT NULL DEFAULT 0');
    addColumnIfMissing($conn, 'menu_items', 'has_dropdown', 'TINYINT(1) NOT NULL DEFAULT 0');
    addColumnIfMissing($conn, 'menu_items', 'show_in_footer', 'TINYINT(1) NOT NULL DEFAULT 0');
    addColumnIfMissing($conn, 'page_sections', 'settings', 'TEXT DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'header_text', 'TEXT DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'header_cta_text', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'header_cta_link', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'footer_cta_heading', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'footer_cta_text', 'TEXT DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'footer_cta_button_text', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'footer_cta_button_link', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'favicon_url', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'logo_url', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'meta_title', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'meta_description', 'TEXT DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'facebook_url', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'instagram_url', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'twitter_url', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfMissing($conn, 'site_settings', 'youtube_url', 'VARCHAR(255) DEFAULT NULL');

    $adminPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO `admin_users` (username, password_hash) VALUES ('admin', '$adminPasswordHash') ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
    $conn->query("INSERT INTO `pages` (slug, title, content, hero_title, hero_text, image_url) VALUES
      ('home', 'Home', 'Welcome to Sagar Art. We build polished digital experiences for modern brands.', 'Welcome to Sagar Art', 'We create websites, branding, and digital services that help businesses grow.', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80'),
      ('services', 'Services', 'Our services are crafted to solve business needs with speed and creativity.', 'Our Services', 'Explore the services we offer to help your business grow and stand out.', 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1200&q=80'),
      ('about', 'About', 'We are a creative agency focused on digital strategy and web experiences.', 'About Us', 'We combine design, development, and strategy to deliver memorable experiences.', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80'),
      ('contact', 'Contact', 'Reach out to discuss your next project and let us build something meaningful together.', 'Contact Us', 'Let us know about your ideas and we will be happy to help.', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80')
      ON DUPLICATE KEY UPDATE title = VALUES(title)");

    $serviceCount = $conn->query('SELECT COUNT(*) AS c FROM services')->fetch_assoc()['c'] ?? 0;
    if ((int)$serviceCount === 0) {
        $conn->query("INSERT INTO `services` (title, slug, hero_title, hero_text) VALUES
            ('Web Design', 'web-design', 'Web Design', 'Beautiful and modern interfaces for your brand.'),
            ('Web Development', 'web-development', 'Web Development', 'Reliable PHP and MySQL-based applications.'),
            ('SEO Optimization', 'seo-optimization', 'SEO Optimization', 'Boost your online visibility and organic traffic.'),
            ('Brand Strategy', 'brand-strategy', 'Brand Strategy', 'Create a strong message and identity for your business.')
            ON DUPLICATE KEY UPDATE title = VALUES(title)");
    }

    $conn->query("INSERT INTO `site_settings` (id, site_name, tagline, header_text, header_cta_text, header_cta_link, footer_about, footer_email, footer_phone, footer_address, footer_copyright, favicon_url, logo_url, meta_title, meta_description, facebook_url, instagram_url, twitter_url, youtube_url) VALUES (1, 'Sagar Art', 'Creative digital experiences that elevate brands.', 'We help businesses build modern digital products, websites, and growth-focused marketing experiences.', 'Get Started', '/contact', 'We craft thoughtful digital products and brand experiences for modern businesses.', 'hello@sagarart.com', '+91 98765 43210', 'Mumbai, India', '© 2026 Sagar Art. All rights reserved.', NULL, NULL, 'Sagar Art | Creative Digital Agency', 'We design and develop modern websites and digital experiences for brands that want to stand out.', 'https://facebook.com', 'https://instagram.com', 'https://twitter.com', 'https://youtube.com') ON DUPLICATE KEY UPDATE site_name = VALUES(site_name), tagline = VALUES(tagline), header_text = VALUES(header_text), header_cta_text = VALUES(header_cta_text), header_cta_link = VALUES(header_cta_link), footer_about = VALUES(footer_about), footer_email = VALUES(footer_email), footer_phone = VALUES(footer_phone), footer_address = VALUES(footer_address), footer_copyright = VALUES(footer_copyright), favicon_url = VALUES(favicon_url), logo_url = VALUES(logo_url), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description), facebook_url = VALUES(facebook_url), instagram_url = VALUES(instagram_url), twitter_url = VALUES(twitter_url), youtube_url = VALUES(youtube_url)");

    function slugify($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text ?: 'page';
    }

function normalizeRoute($link, $default = '')
{
    $link = trim((string)$link);

    if ($link === '') {
        return $default;
    }

    // External URLs
    if (
        preg_match('~^(https?:)?//~i', $link) ||
        strpos($link, 'mailto:') === 0 ||
        strpos($link, 'tel:') === 0 ||
        strpos($link, '#') === 0 ||
        strpos($link, 'javascript:') === 0
    ) {
        return $link;
    }

    $parsed = parse_url($link);

    $path = $parsed['path'] ?? '';
    $query = $parsed['query'] ?? '';
    $fragment = $parsed['fragment'] ?? '';

    // Remove .php extension
    $path = preg_replace('/\.php$/i', '', $path);

    // Remove leading/trailing slash
    $path = trim($path, '/');

    // Treat index.php as homepage
    if ($path === 'index') {
        $path = '';
    }

    $result = '/' . $path;

    if ($result === '/') {
        $result = '/';
    }

    if (!empty($query)) {
        $result .= '?' . $query;
    }

    if (!empty($fragment)) {
        $result .= '#' . $fragment;
    }

    return $result;
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

    function detectMediaType($path) {
        $videoExtensions = ['mp4', 'mov', 'webm', 'ogv', 'm4v', 'avi', 'mkv'];
        $path = trim($path);
        if ($path === '') {
            return 'image';
        }

        $parsedPath = parse_url($path, PHP_URL_PATH) ?: $path;
        $extension = strtolower(pathinfo($parsedPath, PATHINFO_EXTENSION));
        return in_array($extension, $videoExtensions, true) ? 'video' : 'image';
    }

    function getPageContent($conn, $slug) {
        $stmt = $conn->prepare('SELECT id, slug, title, content, hero_title, hero_text, image_url, hero_media_type, hero_video_url, hero_bg_color, hero_text_color, button_text, button_link, show_in_menu, menu_order FROM pages WHERE slug = ? LIMIT 1');
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
        $stmt = $conn->prepare('SELECT id, title, content, section_type, image_url, video_url, button_text, button_link, settings FROM page_sections WHERE page_slug = ? ORDER BY sort_order ASC, id ASC');
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    function getSiteSettings($conn) {
        $result = $conn->query('SELECT * FROM site_settings ORDER BY id DESC LIMIT 1');
        $settings = $result ? $result->fetch_assoc() : [];
        $defaults = [
            'site_name' => 'Sagar Art',
            'tagline' => 'Creative digital experiences that elevate brands.',
            'header_text' => 'We help businesses build modern digital products, websites, and growth-focused marketing experiences.',
            'header_cta_text' => 'Get Started',
            'header_cta_link' => '/contact',
            'footer_about' => 'We craft thoughtful digital products and brand experiences for modern businesses.',
            'footer_email' => 'hello@sagarart.com',
            'footer_phone' => '+91 98765 43210',
            'footer_address' => 'Mumbai, India',
            'footer_copyright' => '© 2026 Sagar Art. All rights reserved.',
            'favicon_url' => '',
            'logo_url' => '',
            'meta_title' => '',
            'meta_description' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'twitter_url' => '',
            'youtube_url' => ''
        ];

        if (!$settings) {
            return $defaults;
        }

        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $settings)) {
                $settings[$key] = $value;
            }
        }

        return $settings;
    }

    function getFooterMenuItems($conn) {
        $result = $conn->query('SELECT label, link FROM menu_items WHERE is_active = 1 AND show_in_footer = 1 ORDER BY menu_order ASC, id ASC');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    function getServices($conn) {
        $result = $conn->query('SELECT id, title, slug, hero_text, image_url, button_text, button_link, display_order, is_featured FROM services ORDER BY display_order ASC, id ASC');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    function getServiceContent($conn, $slug) {
        $stmt = $conn->prepare('SELECT id, slug, title, hero_title, hero_text, image_url, hero_media_type, hero_video_url, hero_bg_color, hero_text_color, button_text, button_link, show_in_menu, menu_order FROM services WHERE slug = ? LIMIT 1');
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    function getServiceSections($conn, $slug) {
        $stmt = $conn->prepare('SELECT id, title, content, section_type, image_url, video_url, button_text, button_link, settings FROM service_sections WHERE service_slug = ? ORDER BY sort_order ASC, id ASC');
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    function filterServicesByIds($services, $ids) {
        $ids = array_map('intval', (array)$ids);
        if (empty($ids)) {
            return [];
        }

        $filtered = [];
        foreach ($services as $service) {
            if (in_array((int)$service['id'], $ids, true)) {
                $filtered[] = $service;
            }
        }

        return $filtered;
    }

    function getServiceBySlug($conn, $slug) {
        $stmt = $conn->prepare('SELECT id, title, slug, hero_text, image_url FROM services WHERE slug = ? LIMIT 1');
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    function isAdminLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}
?>
