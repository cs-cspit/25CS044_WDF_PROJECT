-- =============================================================================
-- Practical 11: Student Management CRUD with Soft Delete & CSV Export
-- Database: studenthub
-- File: practical-11/schema.sql
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- -----------------------------------------------------------------------------
-- Table: students (with soft delete status 'active' / 'inactive')
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
    `student_id` INT AUTO_INCREMENT PRIMARY KEY,
    `roll_no` VARCHAR(20) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(15) NOT NULL,
    `department` VARCHAR(50) NOT NULL,
    `course` VARCHAR(50) NOT NULL,
    `year_of_study` ENUM('1st Year', '2nd Year', '3rd Year', '4th Year') NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_course` (`course`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- SEED TEST STUDENT RECORDS
-- -----------------------------------------------------------------------------
INSERT INTO `students` (`student_id`, `roll_no`, `full_name`, `email`, `phone`, `department`, `course`, `year_of_study`, `status`) VALUES
(1, '25CS044', 'Shubham Mendpara', '25cs044@charusat.edu.in', '9876543210', 'Computer Engineering', 'B.Tech CSE', '2nd Year', 'active'),
(2, '25CS001', 'Aarav Patel', 'aarav.ce@charusat.edu.in', '9823456789', 'Computer Engineering', 'B.Tech CSE', '2nd Year', 'active'),
(3, '25IT014', 'Bhavya Shah', 'bhavya.it@charusat.edu.in', '9811223344', 'Information Technology', 'B.Tech IT', '3rd Year', 'active'),
(4, '25EC032', 'Diya Joshi', 'diya.ec@charusat.edu.in', '9877889900', 'Electronics & Comm.', 'B.Tech EC', '4th Year', 'active'),
(5, '25AI067', 'Harshil Dave', 'harshil.ai@charusat.edu.in', '9899001122', 'Computer Engineering', 'B.Tech AI-ML', '1st Year', 'active'),
(6, '25ME021', 'Rohan Raval', 'rohan.mech@charusat.edu.in', '9844556677', 'Mechanical Engineering', 'B.Tech ME', '4th Year', 'inactive')
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);
