-- =============================================================================
-- Practical 8: StudentHub Database Design, 3NF Normalization & Stored Procedures
-- File: schema.sql
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- Drop objects in reverse dependency order if already existing
DROP PROCEDURE IF EXISTS `RegisterStudent`;
DROP VIEW IF EXISTS `view_registration_details`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;

-- =============================================================================
-- 1. TABLE: students
-- =============================================================================
-- 3NF NORMALIZATION ANALYSIS:
--   * 1NF: All attributes contain atomic values. No repeating groups or comma-separated lists.
--   * 2NF: The table has a single-column Primary Key (`student_id`). Therefore, no partial
--          dependencies can exist (all non-key columns depend on the entire PK).
--   * 3NF: No transitive dependencies exist. Every non-key attribute (full_name, email,
--          phone, department, year_of_study) directly depends solely upon the Primary Key (`student_id`).
-- =============================================================================
CREATE TABLE `students` (
    `student_id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(15) NOT NULL,
    `department` VARCHAR(50) NOT NULL,
    `year_of_study` ENUM('1st Year', '2nd Year', '3rd Year', '4th Year') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 2. TABLE: events
-- =============================================================================
-- 3NF NORMALIZATION ANALYSIS:
--   * 1NF: All fields (event_name, event_date, venue, description) are atomic.
--   * 2NF: Single-column Primary Key (`event_id`). Hence, no partial functional dependency exists.
--   * 3NF: All non-key columns directly depend solely on `event_id`. There are no transitive
--          dependencies between non-key fields.
-- =============================================================================
CREATE TABLE `events` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_name` VARCHAR(150) NOT NULL,
    `event_date` DATE NOT NULL,
    `venue` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 3. TABLE: registrations
-- =============================================================================
-- 3NF NORMALIZATION ANALYSIS:
--   * 1NF: Single atomic values stored per row.
--   * 2NF: Primary Key is `registration_id`. Non-key attribute (`registered_at`) depends
--          fully on `registration_id`.
--   * 3NF: Foreign Keys `student_id` and `event_id` model the Many-to-Many (M:N) relationship
--          between students and events. No transitive dependencies exist.
--          Composite UNIQUE constraint (student_id, event_id) guarantees no duplicate signups.
-- =============================================================================
CREATE TABLE `registrations` (
    `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Prevent duplicate registrations for the same event by the same student
    CONSTRAINT `uq_student_event` UNIQUE (`student_id`, `event_id`),

    -- Referential Integrity Constraints with CASCADE rules
    CONSTRAINT `fk_registrations_student`
        FOREIGN KEY (`student_id`)
        REFERENCES `students` (`student_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT `fk_registrations_event`
        FOREIGN KEY (`event_id`)
        REFERENCES `events` (`event_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SEED DATA
-- =============================================================================

-- Seed Students (At least 6 records)
INSERT INTO `students` (`full_name`, `email`, `phone`, `department`, `year_of_study`) VALUES
('Shubham Mendpara', '25cs044@charusat.edu.in', '9876543210', 'Computer Engineering', '2nd Year'),
('Aarav Patel', 'aarav.patel@charusat.edu.in', '9823456789', 'Computer Engineering', '2nd Year'),
('Bhavya Shah', 'bhavya.shah@charusat.edu.in', '9811223344', 'Information Technology', '3rd Year'),
('Diya Joshi', 'diya.joshi@charusat.edu.in', '9877889900', 'Electronics & Communication', '4th Year'),
('Harshil Dave', 'harshil.dave@charusat.edu.in', '9899001122', 'Computer Engineering', '1st Year'),
('Meera Bhatt', 'meera.bhatt@charusat.edu.in', '9833445566', 'Information Technology', '3rd Year'),
('Rohan Raval', 'rohan.raval@charusat.edu.in', '9844556677', 'Mechanical Engineering', '4th Year');

-- Seed Events (At least 4 records)
INSERT INTO `events` (`event_name`, `event_date`, `venue`, `description`) VALUES
('Hackathon 2026 - CodeVerse', '2026-07-20', 'Main Auditorium, Block 3', '24-hour national level software hackathon and design sprint.'),
('AI & Deep Learning Symposium', '2026-08-05', 'Seminar Hall A', 'Keynotes by industry researchers on generative AI and ML models.'),
('Annual Sports Meet - Spardha', '2026-08-18', 'University Sports Ground', 'Inter-department athletic tournaments, cricket, and badminton.'),
('National Cyber Security Conclave', '2026-08-25', 'Auditorium 2', 'Workshops on ethical hacking, network forensics, and defenses.');

-- Seed Registrations (At least 10 records)
INSERT INTO `registrations` (`student_id`, `event_id`, `registered_at`) VALUES
(1, 1, '2026-06-01 10:15:00'),
(1, 2, '2026-06-02 11:30:00'),
(2, 1, '2026-06-02 14:45:00'),
(2, 4, '2026-06-03 09:20:00'),
(3, 1, '2026-06-03 16:10:00'),
(3, 3, '2026-06-04 10:00:00'),
(4, 3, '2026-06-04 15:25:00'),
(4, 4, '2026-06-05 12:40:00'),
(5, 1, '2026-06-05 13:50:00'),
(5, 2, '2026-06-06 17:15:00'),
(6, 2, '2026-06-06 18:00:00'),
(7, 3, '2026-06-07 11:05:00');

-- =============================================================================
-- ADVANCED FEATURES: VIEW & STORED PROCEDURE
-- =============================================================================

-- View: Registration Details with Student and Event Joins
CREATE OR REPLACE VIEW `view_registration_details` AS
SELECT 
    r.registration_id,
    r.registered_at,
    s.student_id,
    s.full_name AS student_name,
    s.email AS student_email,
    s.department,
    e.event_id,
    e.event_name,
    e.event_date,
    e.venue
FROM `registrations` r
INNER JOIN `students` s ON r.student_id = s.student_id
INNER JOIN `events` e ON r.event_id = e.event_id;

-- Stored Procedure: RegisterStudent with Duplicate Prevention
DELIMITER //

CREATE PROCEDURE `RegisterStudent`(
    IN p_student_id INT,
    IN p_event_id INT,
    OUT p_status_code INT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_student_exists INT DEFAULT 0;
    DECLARE v_event_exists INT DEFAULT 0;
    DECLARE v_already_registered INT DEFAULT 0;

    -- 1. Validate student existence
    SELECT COUNT(*) INTO v_student_exists FROM `students` WHERE `student_id` = p_student_id;
    IF v_student_exists = 0 THEN
        SET p_status_code = 404;
        SET p_message = 'Error: Student does not exist.';
    ELSE
        -- 2. Validate event existence
        SELECT COUNT(*) INTO v_event_exists FROM `events` WHERE `event_id` = p_event_id;
        IF v_event_exists = 0 THEN
            SET p_status_code = 404;
            SET p_message = 'Error: Event does not exist.';
        ELSE
            -- 3. Check for duplicate registration
            SELECT COUNT(*) INTO v_already_registered 
            FROM `registrations` 
            WHERE `student_id` = p_student_id AND `event_id` = p_event_id;

            IF v_already_registered > 0 THEN
                SET p_status_code = 409;
                SET p_message = 'Notice: Student is already registered for this event.';
            ELSE
                -- 4. Perform Insert
                INSERT INTO `registrations` (`student_id`, `event_id`, `registered_at`)
                VALUES (p_student_id, p_event_id, NOW());
                
                SET p_status_code = 200;
                SET p_message = 'Success: Student registered successfully!';
            END IF;
        END IF;
    END IF;
END //

DELIMITER ;
