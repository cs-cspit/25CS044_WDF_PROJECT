-- =============================================================================
-- Practical 15: StudentHub Consolidated Database Schema & Seed Data
-- Database Name: studenthub
-- Full Semester Integration: P8 (3NF Schema), P9 (Users & Auth), P10 (Sessions),
--                            P11 (Student CRUD), P12 (Events & Posters),
--                            P13 (JSON REST API), P14 (RBAC & Audit Trail)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- Drop tables in reverse foreign key dependency order
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;

-- -----------------------------------------------------------------------------
-- 1. Table: users (Authentication, Roles, and Account Status)
-- -----------------------------------------------------------------------------
CREATE TABLE `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'organizer', 'student') NOT NULL DEFAULT 'student',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. Table: students (Enrolled Student Roster in 3NF Normalization)
-- -----------------------------------------------------------------------------
CREATE TABLE `students` (
    `student_id` INT AUTO_INCREMENT PRIMARY KEY,
    `roll_no` VARCHAR(20) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(15) NOT NULL,
    `department` VARCHAR(50) NOT NULL,
    `course` VARCHAR(50) NOT NULL DEFAULT 'B.Tech CE',
    `year_of_study` ENUM('1st Year', '2nd Year', '3rd Year', '4th Year') NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_students_roll` (`roll_no`),
    INDEX `idx_students_course` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. Table: events (Campus Activities, Venues, Quotas, and Poster Paths)
-- -----------------------------------------------------------------------------
CREATE TABLE `events` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_name` VARCHAR(150) NOT NULL,
    `category` ENUM('Technical', 'Workshop', 'Seminar', 'Competition', 'Sports', 'Cultural') NOT NULL DEFAULT 'Technical',
    `event_date` DATE NOT NULL,
    `venue` VARCHAR(100) NOT NULL,
    `organizer_club` VARCHAR(100) NOT NULL,
    `max_seats` INT UNSIGNED NOT NULL DEFAULT 100,
    `poster_path` VARCHAR(255) DEFAULT 'images/chess_tournament.png',
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_events_category` (`category`),
    INDEX `idx_events_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. Table: registrations (M:N Associative Junction Table)
-- -----------------------------------------------------------------------------
CREATE TABLE `registrations` (
    `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    CONSTRAINT `uq_student_event_registration` UNIQUE (`student_id`, `event_id`),
    CONSTRAINT `fk_registrations_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_registrations_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. Table: audit_logs (Security, Traceability & Moderation Trail)
-- -----------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_action` (`action`),
    CONSTRAINT `fk_audit_user_p15` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SEED DATA (Indian College Names, CHARUSAT Emails, Test Accounts)
-- =============================================================================

-- Seed Users (Password for all test seeds: "StudentHub@2026")
INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin', 'admin@charusat.edu.in', '$2y$10$w85Vw6tXG5Pz8dY5Zq1Vse5gNf8q2L9PjZ1bA5aC2bF3dE4gH5iJu', 'admin', 'active'),
(2, 'Shubham Mendpara', 'shubham_25', '25cs044@charusat.edu.in', '$2y$10$tZ8Jp2Yv7Kq9Nx6M1Vw3Le6hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6P', 'student', 'active'),
(3, 'Aarav Patel', 'aarav_ce', 'aarav.ce@charusat.edu.in', '$2y$10$qR5Kp9L2M8Nx7V1W3Ye6Zu7hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6Q', 'student', 'active'),
(4, 'Prof. Subrata Nandi', 'subrata_chair', 'subrata.ai@charusat.edu.in', '$2y$10$kP7Jp2Yv7Kq9Nx6M1Vw3Le6hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6X', 'organizer', 'active'),
(5, 'Diya Joshi', 'diya_ec', 'diya.ec@charusat.edu.in', '$2y$10$rT9Kp9L2M8Nx7V1W3Ye6Zu7hO4p7A8Bs9CdE1fG2hI3jK4lM5nO6Z', 'student', 'inactive');

-- Seed Students
INSERT INTO `students` (`student_id`, `roll_no`, `full_name`, `email`, `phone`, `department`, `course`, `year_of_study`, `status`) VALUES
(1, '25CS044', 'Shubham Mendpara', '25cs044@charusat.edu.in', '9876543210', 'Computer Engineering', 'B.Tech CE', '2nd Year', 'active'),
(2, '25CS045', 'Aarav Patel', 'aarav.ce@charusat.edu.in', '9823456789', 'Computer Engineering', 'B.Tech CE', '2nd Year', 'active'),
(3, '25IT012', 'Bhavya Shah', 'bhavya.it@charusat.edu.in', '9811223344', 'Information Technology', 'B.Tech IT', '3rd Year', 'active'),
(4, '25EC008', 'Diya Joshi', 'diya.ec@charusat.edu.in', '9877889900', 'Electronics & Comm.', 'B.Tech EC', '4th Year', 'inactive'),
(5, '25AI021', 'Harshil Dave', 'harshil.ai@charusat.edu.in', '9899001122', 'Computer Engineering', 'B.Tech AI', '1st Year', 'active'),
(6, '25IT040', 'Meera Bhatt', 'meera.it@charusat.edu.in', '9833445566', 'Information Technology', 'B.Tech IT', '3rd Year', 'active'),
(7, '25ME015', 'Rohan Raval', 'rohan.mech@charusat.edu.in', '9844556677', 'Mechanical Engineering', 'B.Tech ME', '4th Year', 'active');

-- Seed Events
INSERT INTO `events` (`event_id`, `event_name`, `category`, `event_date`, `venue`, `organizer_club`, `max_seats`, `poster_path`, `description`) VALUES
(1, 'Chess Tournament: Strategy, Focus, Victory', 'Sports', '2026-10-31', 'Dinsha Patel Sports Complex', 'CSE Dept, CSPIT', 64, 'images/chess_tournament.png', 'Intra-university annual chess championship.'),
(2, 'Endowment Chair: AI & Deep Learning Masterclass', 'Seminar', '2026-10-12', '2nd Floor Seminar Hall', 'Prof. Subrata Nandi & CSE Dept', 150, 'images/subrata_nandi_ai.jpg', 'Special keynote on modern generative AI foundations.'),
(3, 'IoT Edge: From Sensors to Smart Solutions', 'Workshop', '2026-11-05', 'Central Computing Facility', 'Developer Student Club', 100, 'images/iot_edge_sensors.jpg', 'Hands-on embedded systems and MQTT broker setup.'),
(4, 'Hands-on Theory of Computation & Automata', 'Technical', '2026-11-15', 'Lab 302, CSPIT', 'Computer Engineering Dept', 80, 'images/theory_of_computation.png', 'Deep-dive into Turing machines and grammars.');

-- Seed Registrations
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
(3, 4, '2026-10-25 17:15:00', 'registered');

-- Seed Audit Logs
INSERT INTO `audit_logs` (`user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 'SYSTEM_INTEGRATION_INIT', 'Practical 15 master integration completed & verified', '127.0.0.1', '2026-10-30 08:30:00'),
(1, 'LOGIN_SUCCESS', 'Admin System Administrator logged in successfully via password_verify()', '127.0.0.1', '2026-10-30 09:00:00'),
(1, 'USER_STATUS_TOGGLE', 'Admin toggled user ID 5 (Diya Joshi) status to inactive', '127.0.0.1', '2026-10-30 09:15:20'),
(2, 'UNAUTHORIZED_ACCESS_BLOCKED', 'Student Shubham Mendpara blocked from accessing admin dashboard (HTTP 403)', '127.0.0.1', '2026-10-30 09:30:10'),
(1, 'EVENT_CREATE', 'Admin published campus event: Chess Tournament: Strategy, Focus, Victory', '127.0.0.1', '2026-10-30 10:05:00'),
(1, 'EVENT_UPDATE', 'Admin updated venue for event #3 to Central Computing Facility', '127.0.0.1', '2026-10-30 11:20:00');
