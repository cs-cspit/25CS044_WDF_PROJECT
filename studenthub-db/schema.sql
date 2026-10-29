-- =============================================================================
-- Practical 8: StudentHub Database Design, 3NF Normalization & Event Registrations
-- College Event Portal (Workshops, Seminars, Hackathons, Club Sessions)
-- File: schema.sql
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- Drop database objects in reverse dependency order
DROP PROCEDURE IF EXISTS `RegisterStudent`;
DROP VIEW IF EXISTS `view_event_registrations`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;

-- =============================================================================
-- 1. TABLE: students
-- =============================================================================
-- 3NF NORMALIZATION ANALYSIS:
--   * 1NF: All column values are atomic (indivisible). No repeating groups.
--   * 2NF: Single-attribute Primary Key (`student_id`). No partial dependency can exist;
--          all non-key columns depend entirely on `student_id`.
--   * 3NF: No transitive functional dependencies exist (X -> Y and Y -> Z).
--          Every non-key attribute (full_name, email, phone, department, year_of_study)
--          depends directly and solely on `student_id`.
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
--   * 1NF: Every attribute holds atomic data.
--   * 2NF: Single-attribute Primary Key (`event_id`). No partial functional dependencies.
--   * 3NF: Non-key attributes (event_name, event_date, venue, organizer_club, max_seats,
--          description) depend directly and solely on `event_id`, satisfying 3NF.
-- =============================================================================
CREATE TABLE `events` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_name` VARCHAR(150) NOT NULL,
    `event_date` DATE NOT NULL,
    `venue` VARCHAR(100) NOT NULL,
    `organizer_club` VARCHAR(100) NOT NULL,
    `max_seats` INT UNSIGNED NOT NULL DEFAULT 100,
    `description` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 3. TABLE: registrations (JUNCTION / ASSOCIATIVE TABLE)
-- =============================================================================
-- MANY-TO-MANY (M:N) JUNCTION TABLE EXPLANATION:
--   * A student can register for multiple events (1 Student -> Many Registrations).
--   * An event can have multiple student attendees (1 Event -> Many Registrations).
--   * To model this M:N relationship in a relational schema, the `registrations` junction
--     table decomposes M:N into two 1:M relationships using Foreign Keys `student_id` and `event_id`.
--
-- 3NF NORMALIZATION ANALYSIS:
--   * 1NF: Single atomic values stored per column.
--   * 2NF: Primary Key is `registration_id`. Non-key attributes (`registered_at`, `status`)
--          fully depend on the complete Primary Key.
--   * 3NF: No transitive dependency exists. The composite UNIQUE (`student_id`, `event_id`)
--          prevents a student from accidentally registering twice for the same event.
--   * Referential Integrity: ON DELETE CASCADE and ON UPDATE CASCADE guarantee that
--          deleting a student or event automatically cleans up associated registrations.
-- =============================================================================
CREATE TABLE `registrations` (
    `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('registered', 'cancelled') DEFAULT 'registered',

    -- Ensure a student cannot register for the same event twice
    CONSTRAINT `uq_student_event` UNIQUE (`student_id`, `event_id`),

    -- Foreign Key to students table
    CONSTRAINT `fk_reg_student`
        FOREIGN KEY (`student_id`)
        REFERENCES `students` (`student_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    -- Foreign Key to events table
    CONSTRAINT `fk_reg_event`
        FOREIGN KEY (`event_id`)
        REFERENCES `events` (`event_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SEED DATA
-- =============================================================================

-- Seed Students (7 Students - Indian names, college emails and phone numbers)
INSERT INTO `students` (`full_name`, `email`, `phone`, `department`, `year_of_study`) VALUES
('Shubham Mendpara', '25cs044@charusat.edu.in', '9876543210', 'Computer Engineering', '2nd Year'),
('Aarav Patel', 'aarav.ce@charusat.edu.in', '9823456789', 'Computer Engineering', '2nd Year'),
('Bhavya Shah', 'bhavya.it@charusat.edu.in', '9811223344', 'Information Technology', '3rd Year'),
('Diya Joshi', 'diya.ec@charusat.edu.in', '9877889900', 'Electronics & Communication', '4th Year'),
('Harshil Dave', 'harshil.ai@charusat.edu.in', '9899001122', 'Computer Engineering', '1st Year'),
('Meera Bhatt', 'meera.it@charusat.edu.in', '9833445566', 'Information Technology', '3rd Year'),
('Rohan Raval', 'rohan.mech@charusat.edu.in', '9844556677', 'Mechanical Engineering', '4th Year');

-- Seed Events (4 Real-looking college events)
INSERT INTO `events` (`event_name`, `event_date`, `venue`, `organizer_club`, `max_seats`, `description`) VALUES
('Git & GitHub Hands-on Workshop', '2026-07-15', 'Lab 402, IT Building', 'Git & Open Source Club', 60, 'Interactive session covering Git branching, rebasing, pull requests, and open source workflows.'),
('Hackathon 2026 - CodeVerse', '2026-07-20', 'Main Auditorium, Block 3', 'Computer Engineering Dept', 150, '24-hour national level hackathon addressing real-world problem statements.'),
('Web Dev Seminar: Modern Fullstack', '2026-08-05', 'Seminar Hall A', 'Developer Student Club', 100, 'Expert keynote on modern web architecture, REST APIs, and responsive design systems.'),
('Inter-College Coding Contest', '2026-08-18', 'Central Computing Facility', 'Competitive Programming Club', 120, 'Competitive programming challenge featuring algorithmic problem solving in C++, Java, and Python.');

-- Seed Registrations (12 Event Registrations)
-- Multiple students per event, multiple events per student:
INSERT INTO `registrations` (`student_id`, `event_id`, `registered_at`, `status`) VALUES
(1, 1, '2026-06-01 10:15:00', 'registered'), -- Shubham in Git Workshop
(1, 2, '2026-06-02 11:30:00', 'registered'), -- Shubham in Hackathon 2026
(1, 4, '2026-06-03 14:00:00', 'registered'), -- Shubham in Coding Contest
(2, 1, '2026-06-02 14:45:00', 'registered'), -- Aarav in Git Workshop
(2, 2, '2026-06-03 09:20:00', 'registered'), -- Aarav in Hackathon 2026
(3, 2, '2026-06-03 16:10:00', 'registered'), -- Bhavya in Hackathon 2026
(3, 3, '2026-06-04 10:00:00', 'registered'), -- Bhavya in Web Dev Seminar
(4, 3, '2026-06-04 15:25:00', 'registered'), -- Diya in Web Dev Seminar
(4, 4, '2026-06-05 12:40:00', 'registered'), -- Diya in Coding Contest
(5, 1, '2026-06-05 13:50:00', 'registered'), -- Harshil in Git Workshop
(5, 4, '2026-06-06 17:15:00', 'registered'), -- Harshil in Coding Contest
(6, 3, '2026-06-06 18:00:00', 'registered'); -- Meera in Web Dev Seminar

-- =============================================================================
-- ADVANCED FEATURES: VIEW & STORED PROCEDURE
-- =============================================================================

-- View: List event registrations with student and event details (JOIN)
CREATE OR REPLACE VIEW `view_event_registrations` AS
SELECT 
    r.registration_id,
    s.student_id,
    s.full_name AS student_name,
    s.department,
    e.event_id,
    e.event_name,
    e.event_date,
    e.venue,
    e.organizer_club,
    r.registered_at,
    r.status
FROM `registrations` r
INNER JOIN `students` s ON r.student_id = s.student_id
INNER JOIN `events` e ON r.event_id = e.event_id;

-- Stored Procedure: RegisterStudent
-- Registers a student for an event, checks existence, and prevents duplicate registration
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

    -- 1. Check if student exists
    SELECT COUNT(*) INTO v_student_exists FROM `students` WHERE `student_id` = p_student_id;
    IF v_student_exists = 0 THEN
        SET p_status_code = 404;
        SET p_message = 'Error: Student does not exist.';
    ELSE
        -- 2. Check if event exists
        SELECT COUNT(*) INTO v_event_exists FROM `events` WHERE `event_id` = p_event_id;
        IF v_event_exists = 0 THEN
            SET p_status_code = 404;
            SET p_message = 'Error: Event does not exist.';
        ELSE
            -- 3. Check for existing duplicate registration
            SELECT COUNT(*) INTO v_already_registered 
            FROM `registrations` 
            WHERE `student_id` = p_student_id AND `event_id` = p_event_id;

            IF v_already_registered > 0 THEN
                SET p_status_code = 409;
                SET p_message = 'Notice: Student is already registered for this event.';
            ELSE
                -- 4. Perform Insert
                INSERT INTO `registrations` (`student_id`, `event_id`, `registered_at`, `status`)
                VALUES (p_student_id, p_event_id, NOW(), 'registered');
                
                SET p_status_code = 200;
                SET p_message = 'Success: Student registered for the event successfully!';
            END IF;
        END IF;
    END IF;
END //

DELIMITER ;
