-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 05, 2026 at 08:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sagarart_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`) VALUES
(1, 'admin', '$2y$10$pNg.CWr8h8T9APquiLxax.xwz0PcpRbfxOexJaG7jyyIbuOTd81Ri');

-- --------------------------------------------------------

--
-- Table structure for table `contact_submissions`
--

CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_submissions`
--

INSERT INTO `contact_submissions` (`id`, `name`, `email`, `phone`, `message`, `submitted_at`) VALUES
(1, 'sonu prakash', 'sonuprakash167@gmail.com', '+917239907130', 'need wall mount wallpaper with high resolution', '2026-07-05 12:04:08');

-- --------------------------------------------------------

--
-- Table structure for table `menu_children`
--

CREATE TABLE `menu_children` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_children`
--

INSERT INTO `menu_children` (`id`, `parent_id`, `label`, `link`, `menu_order`, `is_active`) VALUES
(1, 3, 'Web Design', 'service.php?slug=web-design', 0, 1),
(2, 3, 'Web Development', 'service.php?slug=web-development', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `has_dropdown` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `label`, `link`, `menu_order`, `is_active`, `has_dropdown`) VALUES
(1, 'Home', '/index.php', 0, 1, 0),
(2, 'Login', '/admin/login.php', 4, 1, 0),
(3, 'Services', '/services.php', 1, 1, 1),
(4, 'About', '/about.php', 2, 1, 0),
(5, 'Contact Us', '/contact.php', 3, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_text` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `show_in_menu` tinyint(1) NOT NULL DEFAULT 1,
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `hero_video_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `hero_title`, `hero_text`, `image_url`, `show_in_menu`, `menu_order`, `hero_video_url`) VALUES
(1, 'home', 'Home', 'Welcome to Sagar Art. We build polished digital experiences for modern brands.', 'Welcome to Sagar Art', 'We create websites, branding, and digital services that help businesses grow.', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80', 1, 0, NULL),
(2, 'services', 'Services', 'Our services are crafted to solve business needs with speed and creativity.', 'Our Services', 'Explore the services we offer to help your business grow and stand out. Sagar Art', 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1200&q=80', 1, 0, NULL),
(3, 'about', 'About', 'We are a creative agency focused on digital strategy and web experiences.', 'About Us', 'We combine design, development, and strategy to deliver memorable experiences.', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80', 1, 0, NULL),
(4, 'contact', 'Contact', 'Reach out to discuss your next project and let us build something meaningful together.', 'Contact Us', 'Let us know about your ideas and we will be happy to help.', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `page_sections`
--

CREATE TABLE `page_sections` (
  `id` int(11) NOT NULL,
  `page_slug` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `section_type` varchar(50) NOT NULL DEFAULT 'content',
  `image_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_sections`
--

INSERT INTO `page_sections` (`id`, `page_slug`, `title`, `content`, `section_type`, `image_url`, `video_url`, `button_text`, `button_link`, `sort_order`) VALUES
(1, 'home', 'Slider', 'Show all photos', 'slider', 'uploads/file_6a48c7387e37a4.17621791.jpg', '', 'All Photos', '/services.php', 1);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `summary` text NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `title`, `slug`, `summary`, `content`, `image_url`, `display_order`, `is_featured`) VALUES
(1, 'Web Design', 'web-design', 'Beautiful and modern interfaces for your brand.', 'We design responsive websites that feel premium and work perfectly across all devices.', '', 1, 0),
(2, 'Web Development', 'web-development', 'Reliable PHP and MySQL-based applications.', 'We develop custom PHP applications with fast load times and clean architecture.', NULL, 0, 0),
(3, 'SEO Optimization', 'seo-optimization', 'Boost your online visibility and organic traffic.', 'We improve your website structure, content, and metadata to rank higher in search results.', NULL, 0, 0),
(4, 'Brand Strategy', 'brand-strategy', 'Create a strong message and identity for your business.', 'We help shape your brand voice, positioning, and visual identity for lasting growth.', NULL, 0, 0),
(110, 'Team Manage', 'Manage all team', 'Manage all team', 'Manage all team', 'uploads/file_6a48c52dd0b855.26502104.jpg', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(255) NOT NULL DEFAULT 'Sagar Art',
  `tagline` varchar(255) DEFAULT NULL,
  `footer_about` text DEFAULT NULL,
  `footer_email` varchar(255) DEFAULT NULL,
  `footer_phone` varchar(255) DEFAULT NULL,
  `footer_address` text DEFAULT NULL,
  `footer_copyright` varchar(255) DEFAULT NULL,
  `header_text` text DEFAULT NULL,
  `header_cta_text` varchar(255) DEFAULT NULL,
  `header_cta_link` varchar(255) DEFAULT NULL,
  `favicon_url` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `site_name`, `tagline`, `footer_about`, `footer_email`, `footer_phone`, `footer_address`, `footer_copyright`, `header_text`, `header_cta_text`, `header_cta_link`, `favicon_url`, `logo_url`, `meta_title`, `meta_description`, `facebook_url`, `instagram_url`, `twitter_url`, `youtube_url`) VALUES
(1, 'Sagar Art', 'Creative digital experiences that elevate brands.', 'We craft thoughtful digital products and brand experiences for modern businesses.', 'hello@sagarart.com', '+91 98765 43210', 'Mumbai, India', '© 2026 Sagar Art. All rights reserved.', 'We help businesses build modern digital products, websites, and growth-focused marketing experiences.', 'Get Started', 'contact.php', NULL, NULL, 'Sagar Art | Creative Digital Agency', 'We design and develop modern websites and digital experiences for brands that want to stand out.', 'https://facebook.com', 'https://instagram.com', 'https://twitter.com', 'https://youtube.com'),
(2, 'Sagar Art', 'Creative digital experiences that elevate brands.', 'We craft thoughtful digital products and brand experiences for modern businesses.', 'hello@sagarart.com', '+91 98765 43210', 'Mumbai, India', '© 2026 Sagar Art. All rights reserved.', 'We help businesses build modern digital products, websites, and growth-focused marketing experiences.', 'Get Started', 'contact.php', '', '', 'Sagar Art | Creative Digital Agency', 'We design and develop modern websites and digital experiences for brands that want to stand out.', 'https://facebook.com', 'https://instagram.com', 'https://twitter.com', 'https://youtube.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_children`
--
ALTER TABLE `menu_children`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `page_sections`
--
ALTER TABLE `page_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menu_children`
--
ALTER TABLE `menu_children`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=955;

--
-- AUTO_INCREMENT for table `page_sections`
--
ALTER TABLE `page_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=956;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_children`
--
ALTER TABLE `menu_children`
  ADD CONSTRAINT `menu_children_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
