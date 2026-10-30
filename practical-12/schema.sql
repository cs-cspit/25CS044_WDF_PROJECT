-- =============================================================================
-- Practical 12: Event Management with Real College Event Posters
-- Database: studenthub
-- File: practical-12/schema.sql
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- -----------------------------------------------------------------------------
-- Table: events
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_name` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'Technical',
    `event_date` DATE NOT NULL,
    `venue` VARCHAR(100) NOT NULL,
    `organizer_club` VARCHAR(100) NOT NULL,
    `max_seats` INT UNSIGNED NOT NULL DEFAULT 100,
    `poster_path` VARCHAR(255) DEFAULT 'uploads/default_poster.png',
    `description` TEXT DEFAULT NULL,
    `status` ENUM('active', 'cancelled', 'completed') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- SEED TEST EVENTS WITH ACTUAL CHARUSAT POSTER IMAGES
-- -----------------------------------------------------------------------------
INSERT INTO `events` (`event_id`, `event_name`, `category`, `event_date`, `venue`, `organizer_club`, `max_seats`, `poster_path`, `description`) VALUES
(1, 'Chess Tournament: Strategy, Focus, Victory', 'Sports', '2026-10-31', 'Dinsha Patel Sports Complex', 'CSE Dept, CSPIT, CHARUSAT', 64, 'uploads/chess_tournament.png', 'Official campus chess tournament organized by CSE Department, CSPIT. Open for all departments with standard time controls.'),
(2, 'Endowment Chair Activity: AI & Deep Learning Masterclass', 'Seminar', '2026-10-12', '2nd Floor Seminar Hall, CSPIT Campus', 'Prof. (Dr.) Subrata Nandi (NIT Durgapur) & CSE Dept', 150, 'uploads/subrata_nandi_ai.jpg', 'Keynote Endowment Chair lecture on Artificial Intelligence, Machine Learning, Deep Learning, IoT Sensing Systems, and Complex Network Theory.'),
(3, 'IoT Edge: From Sensors to Smart Solutions', 'Workshop', '2026-11-05', 'Central Computing Facility, CSPIT', 'Techymonk Automations & CSE Dept', 100, 'uploads/iot_edge_sensors.jpg', 'Hands-on exploration of IoT devices, sensors, real-world smart automation applications, edge computing, and live data telemetry.'),
(4, 'Applications of Theory of Computation', 'Expert Lecture', '2026-10-07', 'Seminar Hall, 1st Floor, A6 Building', 'Dr. Rohit Kumar (SVNIT Surat) & CSE Dept', 120, 'uploads/theory_of_computation.png', 'Expert session addressing practical implementations of automata, grammars, Turing machines, and formal language models in modern computing.')
ON DUPLICATE KEY UPDATE 
    `event_name` = VALUES(`event_name`),
    `category` = VALUES(`category`),
    `event_date` = VALUES(`event_date`),
    `venue` = VALUES(`venue`),
    `organizer_club` = VALUES(`organizer_club`),
    `max_seats` = VALUES(`max_seats`),
    `poster_path` = VALUES(`poster_path`),
    `description` = VALUES(`description`);
