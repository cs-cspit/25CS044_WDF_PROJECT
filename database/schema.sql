-- =============================================================================
-- Practical 8: Database Design (3NF Normalization) & Sample Data
-- Database: studenthub_db
-- Tables: students, events, registrations
-- Relationships: 
--   students (1) ---> (M) registrations (FK: student_id)
--   events   (1) ---> (M) registrations (FK: event_id)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `studenthub_db`;

-- Drop tables in reverse dependency order if already existing
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;

-- -----------------------------------------------------------------------------
-- 1. Table: students (3NF - Primary Key: student_id)
-- -----------------------------------------------------------------------------
CREATE TABLE `students` (
    `student_id` INT AUTO_INCREMENT PRIMARY KEY,
    `roll_no` VARCHAR(20) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(15) NOT NULL,
    `department` VARCHAR(50) NOT NULL,
    `year_of_study` ENUM('1st Year', '2nd Year', '3rd Year', '4th Year') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 2. Table: events (3NF - Primary Key: event_id)
-- -----------------------------------------------------------------------------
CREATE TABLE `events` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_code` VARCHAR(20) NOT NULL UNIQUE,
    `title` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `event_date` DATE NOT NULL,
    `venue` VARCHAR(100) NOT NULL,
    `organizer` VARCHAR(100) NOT NULL,
    `max_seats` INT UNSIGNED NOT NULL DEFAULT 100,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 3. Table: registrations (3NF - Linking Table with Foreign Keys)
-- -----------------------------------------------------------------------------
CREATE TABLE `registrations` (
    `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_no` VARCHAR(30) NOT NULL UNIQUE,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Confirmed', 'Waitlisted', 'Cancelled') DEFAULT 'Confirmed',
    
    -- Foreign Key Constraints with Referential Integrity
    CONSTRAINT `fk_reg_student` 
        FOREIGN KEY (`student_id`) 
        REFERENCES `students` (`student_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,

    CONSTRAINT `fk_reg_event` 
        FOREIGN KEY (`event_id`) 
        REFERENCES `events` (`event_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,

    -- Ensure a student cannot register for the exact same event twice
    UNIQUE KEY `unique_student_event` (`student_id`, `event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- SAMPLE TEST DATA INSERTIONS
-- =============================================================================

-- Insert Students
INSERT INTO `students` (`roll_no`, `full_name`, `email`, `phone`, `department`, `year_of_study`) VALUES
('25CS044', 'Shubham Mendpara', '25cs044@charusat.edu.in', '9876543210', 'Computer Engineering', '2nd Year'),
('25CS001', 'Aarav Patel', 'aarav.ce@charusat.edu.in', '9823456789', 'Computer Engineering', '2nd Year'),
('25IT014', 'Bhavya Shah', 'bhavya.it@charusat.edu.in', '9811223344', 'Information Technology', '3rd Year'),
('25EC032', 'Diya Joshi', 'diya.ec@charusat.edu.in', '9877889900', 'Electronics & Communication', '4th Year'),
('25AI067', 'Harshil Dave', 'harshil.ai@charusat.edu.in', '9899001122', 'Artificial Intelligence & ML', '1st Year');

-- Insert Events
INSERT INTO `events` (`event_code`, `title`, `category`, `event_date`, `venue`, `organizer`, `max_seats`) VALUES
('EVT-101', 'Hackathon 2026 - CodeVerse', 'Technical', '2026-07-20', 'Main Auditorium, Block 3', 'Computer Engineering Dept', 120),
('EVT-102', 'AI & Deep Learning Symposium', 'Technical', '2026-08-05', 'Seminar Hall A', 'AIML Research Group', 80),
('EVT-103', 'Annual Sports Meet - Spardha', 'Sports', '2026-08-18', 'University Sports Ground', 'Sports Council', 300),
('EVT-104', 'National Cyber Security Conclave', 'Technical', '2026-08-25', 'Auditorium 2', 'IT Department', 150),
('EVT-105', 'Cultural Fest - Vrundavan 2026', 'Cultural', '2026-09-10', 'Open Air Amphitheatre', 'Student Council', 500);

-- Insert Registrations
INSERT INTO `registrations` (`ticket_no`, `student_id`, `event_id`, `status`) VALUES
('TKT-94F1B0', 1, 1, 'Confirmed'),
('TKT-A2C871', 1, 2, 'Confirmed'),
('TKT-B3E942', 2, 1, 'Confirmed'),
('TKT-C8D129', 3, 4, 'Confirmed'),
('TKT-D7F450', 4, 3, 'Confirmed'),
('TKT-E1A334', 5, 2, 'Waitlisted');
