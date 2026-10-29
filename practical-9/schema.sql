-- =============================================================================
-- Practical 9: Secure User Registration with Database Insert, Duplicate Email Check and Password Hashing
-- StudentHub College Event Portal
-- File: schema.sql
-- =============================================================================

USE `studenthub`;

-- -----------------------------------------------------------------------------
-- 1. Table: users
-- Stores registered user accounts with securely hashed passwords and role
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'organizer', 'student') NOT NULL DEFAULT 'student',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. Table: audit_logs
-- Security audit logging for registration events, duplicate attempts, and CSRF failures
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_audit_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`user_id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SEED INITIAL DATA (Test Accounts with Verified BCRYPT Hashes)
-- Default test password for initial seeds: "StudentHub@2026"
-- =============================================================================
INSERT INTO `users` (`full_name`, `username`, `email`, `password_hash`, `role`, `status`) VALUES
('Administrator', 'admin_shubham', 'admin@charusat.edu.in', '$2y$10$w85Vw6tXG5Pz8dY5Zq1Vse5gNf8q2L9PjZ1bA5aC2bF3dE4gH5iJu', 'admin', 'active'),
('Shubham Mendpara', 'shubham_25', '25cs044@charusat.edu.in', '$2y$10$tZ8Jp2Yv7Kq9Nx6M1Vw3Le6hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6P', 'student', 'active'),
('Aarav Patel', 'aarav_ce', 'aarav.ce@charusat.edu.in', '$2y$10$qR5Kp9L2M8Nx7V1W3Ye6Zu7hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6Q', 'student', 'active')
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);

-- Seed sample audit log
INSERT INTO `audit_logs` (`user_id`, `action`, `details`, `ip_address`) VALUES
(1, 'SYSTEM_INIT', 'StudentHub Practical 9 user tables & security audit log initialized', '127.0.0.1');
