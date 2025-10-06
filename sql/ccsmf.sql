-- Full database setup for CCSMF app (schema + sample data)
-- Import this in phpMyAdmin or with: mysql -u root < sql/ccsmf.sql

-- 1) Create database
CREATE DATABASE IF NOT EXISTS `ccsmf` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ccsmf`;

-- 2) Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL UNIQUE,
  `role` ENUM('student','admin','ccsc') NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Seed sample users (password: password123)
-- Hash generated via PHP password_hash('password123', PASSWORD_DEFAULT)
SET @pwd := '$2y$12$KYSsK1iiELgUMhpWm3X9Yu3zWskQuPpBkpEV5/ozuIFxoLC2jMzx6';

INSERT INTO `users` (`name`, `email`, `role`, `password_hash`) VALUES
('Alice Admin', 'admin@example.com', 'admin', @pwd),
('Sam Student', 'student@example.com', 'student', @pwd),
('Chris CCSC', 'ccsc@example.com', 'ccsc', @pwd);

-- 4) Create SMF transactions table
CREATE TABLE IF NOT EXISTS `smf_transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `student_name` VARCHAR(120) NOT NULL,
  `student_identifier` VARCHAR(50) NOT NULL,
  `program` ENUM('BSBA','BSIS','BMMA','BSA','BSTM','BSED','BEED','BCAED') NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `photo_path` VARCHAR(255) NULL,
  `status` ENUM('pending','under_review','approved','rejected','updated') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_smf_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If upgrading an existing database, run this statement manually:
-- ALTER TABLE `smf_transactions` ADD COLUMN `status` ENUM('pending','under_review','approved','rejected','updated') NOT NULL DEFAULT 'pending' AFTER `photo_path`;
-- ALTER TABLE `smf_transactions` ADD COLUMN `program` ENUM('BSBA','BSIS','BMMA','BSA','BSTM','BSED','BEED','BCAED') NULL AFTER `student_identifier`;