-- =============================================================================
-- Practical 14: Admin Dashboard with RBAC, Dynamic Menu, and Audit Log
-- File: practical-14/schema.sql
-- Database: studenthub
-- =============================================================================

USE `studenthub`;

-- 1. Table: users (with role, status active/inactive toggle support)
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'organizer', 'student') NOT NULL DEFAULT 'student',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: students (Enrolled students roster)
CREATE TABLE IF NOT EXISTS `students` (
    `student_id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(15) NOT NULL,
    `department` VARCHAR(50) NOT NULL,
    `year_of_study` ENUM('1st Year', '2nd Year', '3rd Year', '4th Year') NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: events (Campus activities)
CREATE TABLE IF NOT EXISTS `events` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_name` VARCHAR(150) NOT NULL,
    `category` ENUM('Technical', 'Workshop', 'Seminar', 'Competition', 'Sports', 'Cultural') NOT NULL DEFAULT 'Technical',
    `event_date` DATE NOT NULL,
    `venue` VARCHAR(100) NOT NULL,
    `organizer_club` VARCHAR(100) NOT NULL,
    `max_seats` INT UNSIGNED NOT NULL DEFAULT 100,
    `poster_path` VARCHAR(255) DEFAULT 'images/chess_tournament.png',
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table: registrations (Event signups by students)
CREATE TABLE IF NOT EXISTS `registrations` (
    `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    CONSTRAINT `uq_practical14_student_event` UNIQUE (`student_id`, `event_id`),
    CONSTRAINT `fk_p14_reg_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_p14_reg_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Table: audit_logs (Security & Admin Activity Tracking)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_action` (`action`),
    CONSTRAINT `fk_p14_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SEED DATA (Practical 14 Demonstration)
-- =============================================================================

-- Users Seeds
INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin', 'admin@charusat.edu.in', '$2y$10$w85Vw6tXG5Pz8dY5Zq1Vse5gNf8q2L9PjZ1bA5aC2bF3dE4gH5iJu', 'admin', 'active'),
(2, 'Shubham Mendpara', 'shubham_25', '25cs044@charusat.edu.in', '$2y$10$tZ8Jp2Yv7Kq9Nx6M1Vw3Le6hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6P', 'student', 'active'),
(3, 'Aarav Patel', 'aarav_ce', 'aarav.ce@charusat.edu.in', '$2y$10$qR5Kp9L2M8Nx7V1W3Ye6Zu7hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6Q', 'student', 'active'),
(4, 'Prof. Subrata Nandi', 'subrata_chair', 'subrata.ai@charusat.edu.in', '$2y$10$kP7Jp2Yv7Kq9Nx6M1Vw3Le6hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6X', 'organizer', 'active'),
(5, 'Diya Joshi', 'diya_ec', 'diya.ec@charusat.edu.in', '$2y$10$rT9Kp9L2M8Nx7V1W3Ye6Zu7hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6Z', 'student', 'inactive')
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`), `role` = VALUES(`role`), `status` = VALUES(`status`);

-- Students Seeds
INSERT INTO `students` (`student_id`, `full_name`, `email`, `phone`, `department`, `year_of_study`, `status`) VALUES
(1, 'Shubham Mendpara', '25cs044@charusat.edu.in', '9876543210', 'Computer Engineering', '2nd Year', 'active'),
(2, 'Aarav Patel', 'aarav.ce@charusat.edu.in', '9823456789', 'Computer Engineering', '2nd Year', 'active'),
(3, 'Bhavya Shah', 'bhavya.it@charusat.edu.in', '9811223344', 'Information Technology', '3rd Year', 'active'),
(4, 'Diya Joshi', 'diya.ec@charusat.edu.in', '9877889900', 'Electronics & Communication', '4th Year', 'inactive'),
(5, 'Harshil Dave', 'harshil.ai@charusat.edu.in', '9899001122', 'Computer Engineering', '1st Year', 'active'),
(6, 'Meera Bhatt', 'meera.it@charusat.edu.in', '9833445566', 'Information Technology', '3rd Year', 'active'),
(7, 'Rohan Raval', 'rohan.mech@charusat.edu.in', '9844556677', 'Mechanical Engineering', '4th Year', 'active')
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);

-- Events Seeds
INSERT INTO `events` (`event_id`, `event_name`, `category`, `event_date`, `venue`, `organizer_club`, `max_seats`, `poster_path`) VALUES
(1, 'Chess Tournament: Strategy, Focus, Victory', 'Sports', '2026-10-31', 'Dinsha Patel Sports Complex', 'CSE Dept, CSPIT', 64, 'images/chess_tournament.png'),
(2, 'Endowment Chair: AI & Deep Learning Masterclass', 'Seminar', '2026-10-12', '2nd Floor Seminar Hall', 'Prof. Subrata Nandi & CSE Dept', 150, 'images/subrata_nandi_ai.jpg'),
(3, 'IoT Edge: From Sensors to Smart Solutions', 'Workshop', '2026-11-05', 'Central Computing Facility', 'Developer Student Club', 100, 'images/iot_edge_sensors.jpg'),
(4, 'Hands-on Theory of Computation & Automata', 'Technical', '2026-11-15', 'Lab 302, CSPIT', 'Computer Engineering Dept', 80, 'images/theory_of_computation.png')
ON DUPLICATE KEY UPDATE `event_name` = VALUES(`event_name`);

-- Registrations Seeds (Monthly Distribution for Analytics Chart)
INSERT INTO `registrations` (`student_id`, `event_id`, `registered_at`, `status`) VALUES
(1, 1, '2026-07-05 10:15:00', 'registered'),
(2, 1, '2026-07-12 11:30:00', 'registered'),
(3, 2, '2026-08-01 14:20:00', 'registered'),
(4, 2, '2026-08-15 09:40:00', 'registered'),
(5, 3, '2026-09-02 16:10:00', 'registered'),
(6, 3, '2026-09-18 10:00:00', 'registered'),
(7, 4, '2026-10-02 15:25:00', 'registered'),
(1, 2, '2026-10-10 12:40:00', 'registered'),
(2, 3, '2026-10-20 13:50:00', 'registered'),
(3, 4, '2026-10-25 17:15:00', 'registered')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

-- Audit Logs Seeds
INSERT INTO `audit_logs` (`user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 'LOGIN_SUCCESS', 'Admin System Administrator logged in successfully via password_verify()', '127.0.0.1', '2026-10-30 09:00:00'),
(1, 'USER_STATUS_TOGGLE', 'Admin toggled user ID 5 (Diya Joshi) status to inactive', '127.0.0.1', '2026-10-30 09:15:20'),
(2, 'UNAUTHORIZED_ACCESS_BLOCKED', 'Student Shubham Mendpara blocked from accessing admin dashboard (HTTP 403)', '127.0.0.1', '2026-10-30 09:30:10'),
(1, 'EVENT_CREATE', 'Admin published campus event: Chess Tournament: Strategy, Focus, Victory', '127.0.0.1', '2026-10-30 10:05:00'),
(1, 'EVENT_UPDATE', 'Admin updated venue for event #3 to Central Computing Facility', '127.0.0.1', '2026-10-30 11:20:00');
