-- Database migration for new features
-- Run this script to add authentication, credit sales, and client data features

-- Create users table for authentication
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'admin',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `password_hash`, `email`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@montolis.com', 'admin');

-- Add payment fields to sales table
ALTER TABLE `sales`
ADD COLUMN `payment_type` enum('cash','credit') DEFAULT 'cash' AFTER `sale_type`,
ADD COLUMN `payment_status` enum('paid','pending','partial') DEFAULT 'paid' AFTER `payment_type`,
ADD COLUMN `remaining_balance` decimal(10,2) DEFAULT 0.00 AFTER `payment_status`;

-- Add client fields to inventory_movements table
ALTER TABLE `inventory_movements`
ADD COLUMN `client_name` varchar(100) DEFAULT NULL AFTER `reason`,
ADD COLUMN `client_contact` varchar(100) DEFAULT NULL AFTER `client_name`;

-- Update existing sales to have proper payment status
UPDATE `sales` SET `payment_status` = 'paid', `remaining_balance` = 0 WHERE `payment_type` = 'cash' OR `payment_type` IS NULL;
UPDATE `sales` SET `remaining_balance` = `sale_price` * `quantity_sold` WHERE `payment_type` = 'credit' AND `payment_status` != 'paid';