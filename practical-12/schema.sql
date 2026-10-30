-- =============================================================================
-- Practical 12: Event Management with File Upload (Event Poster) & Admin Controls
-- Database: studenthub
-- File: practical-12/schema.sql
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub`;

-- -----------------------------------------------------------------------------
-- Table: events (extended with poster_path, category, created_by)
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
-- SEED TEST EVENTS WITH POSTER PATHS
-- -----------------------------------------------------------------------------
INSERT INTO `events` (`event_id`, `event_name`, `category`, `event_date`, `venue`, `organizer_club`, `max_seats`, `poster_path`, `description`) VALUES
(1, 'Git & GitHub Hands-on Workshop', 'Workshop', '2026-07-15', 'Lab 402, IT Building', 'Git & Open Source Club', 60, 'uploads/git_workshop.png', 'Hands-on branching, rebasing, pull requests, and collaborative open source workflows.'),
(2, 'Hackathon 2026 - CodeVerse', 'Technical', '2026-07-20', 'Main Auditorium, Block 3', 'Computer Engineering Dept', 150, 'uploads/codeverse.png', '24-hour national hackathon addressing real-world enterprise problem statements.'),
(3, 'Web Dev Seminar: Modern Fullstack', 'Seminar', '2026-08-05', 'Seminar Hall A', 'Developer Student Club', 100, 'uploads/web_seminar.png', 'Expert masterclass on modern fullstack microservices and responsive CSS layouts.'),
(4, 'Inter-College Coding Contest', 'Competition', '2026-08-18', 'Central Computing Facility', 'Competitive Programming Club', 120, 'uploads/coding_contest.png', 'Algorithmic problem solving challenge featuring C++, Java, and Python tracks.')
ON DUPLICATE KEY UPDATE `event_name` = VALUES(`event_name`);
