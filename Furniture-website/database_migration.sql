-- ============================================================
-- FURNITURE WEBSITE - NEW FEATURES DATABASE MIGRATION
-- Run this SQL in your furniture_db database
-- ============================================================

-- 1. USERS TABLE (Customer Login/Register)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20),
  `password` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. ORDERS TABLE
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(20) NOT NULL UNIQUE,
  `user_id` INT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(150),
  `customer_phone` VARCHAR(20) NOT NULL,
  `customer_address` TEXT,
  `total_amount` DECIMAL(10,2) DEFAULT 0,
  `status` ENUM('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. ORDER ITEMS TABLE
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `variant_info` VARCHAR(200),
  `quantity` INT DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. ORDER STATUS HISTORY (for tracking)
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `note` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. PRODUCT VARIANTS TABLE (Color/Size)
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `variant_type` ENUM('color','size','material') NOT NULL,
  `variant_value` VARCHAR(100) NOT NULL,
  `color_code` VARCHAR(10) DEFAULT NULL,
  `price_modifier` DECIMAL(10,2) DEFAULT 0,
  `stock_qty` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. EMAIL SETTINGS (Admin config)
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default settings insert
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('admin_email', 'Santoshfurniture@gmail.com'),
('email_notifications', '1'),
('store_name', 'Santosh Furniture'),
('store_phone', '+91 8210187952'),
('store_address', 'Gopalganj, Bihar, India'),
('store_lat', '26.4699'),
('store_lng', '84.4378'),
('whatsapp_number', '918210187952');

-- ============================================================
-- DONE! Migration complete
-- ============================================================
