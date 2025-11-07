-- Affiliate System Database Schema

-- Users/Affiliates Table
CREATE TABLE IF NOT EXISTS `affiliates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `promote_method` text,
  `status` enum('pending','active','inactive') DEFAULT 'pending',
  `is_special` tinyint(1) DEFAULT 0,
  `referred_by` int(11) DEFAULT NULL,
  `hubspot_token` text,
  `profile_picture` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `bio` text,
  `verification_token` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `slug` (`slug`),
  KEY `referred_by` (`referred_by`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Affiliate Clicks Tracking
CREATE TABLE IF NOT EXISTS `affiliate_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `referrer_url` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `affiliate_id` (`affiliate_id`),
  FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Leads Table
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `prefer_date` date DEFAULT NULL,
  `detail` text,
  `status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `sale_amount` decimal(10,2) DEFAULT 0.00,
  `feedback` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `affiliate_id` (`affiliate_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Commissions Table
CREATE TABLE IF NOT EXISTS `commissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `total_sale` decimal(10,2) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `commission_percent` decimal(5,2) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `status` enum('pending','confirmed','paid','cancelled') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliate_id` (`affiliate_id`),
  KEY `lead_id` (`lead_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `role` enum('admin','super_admin') DEFAULT 'admin',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (password: admin123 - change this!)
-- Password is MD5 hash of 'admin123'
INSERT INTO `admin_users` (`username`, `email`, `password`, `full_name`, `role`) 
VALUES ('admin', 'admin@example.com', MD5('admin123'), 'Administrator', 'super_admin');

