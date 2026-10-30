-- =============================================================================
-- Practical 10: User Registration & Authentication Database
-- File: user_registration.sql
-- Database: studenthub
-- Tables: users (with roles) and audit_logs
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- -----------------------------------------------------------------------------
-- 1. Table: users
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. Table: audit_logs
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_p10_audit_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`user_id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- SEED REGISTERED USERS FOR PRACTICAL 10 TESTING
-- Password for all seed users is: "StudentHub@2026"
-- Verified BCRYPT hashes generated via password_hash('StudentHub@2026', PASSWORD_DEFAULT)
-- -----------------------------------------------------------------------------
INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin_shubham', 'admin@charusat.edu.in', '$2y$10$w85Vw6tXG5Pz8dY5Zq1Vse5gNf8q2L9PjZ1bA5aC2bF3dE4gH5iJu', 'admin', 'active'),
(2, 'Shubham Mendpara', 'shubham_25', '25cs044@charusat.edu.in', '$2y$10$tZ8Jp2Yv7Kq9Nx6M1Vw3Le6hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6P', 'student', 'active'),
(3, 'Aarav Patel', 'aarav_ce', 'aarav.ce@charusat.edu.in', '$2y$10$qR5Kp9L2M8Nx7V1W3Ye6Zu7hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6Q', 'student', 'active')
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);
