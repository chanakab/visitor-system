-- Database Schema for Smart Visitor Management System

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 
-- Database: `visitor_sys`
--
CREATE DATABASE IF NOT EXISTS `visitor_sys` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `visitor_sys`;

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nic_number` varchar(20) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nic_number_idx` (`nic_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `token_prefix` varchar(5) NOT NULL,
  `avg_service_time_min` int(11) DEFAULT 10,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `token_prefix`, `avg_service_time_min`) VALUES
(1, 'Land Registry', 'L', 15),
(2, 'Pension', 'P', 20),
(3, 'Samurdhi', 'S', 10),
(4, 'National ID', 'N', 12),
(5, 'General Inquiry', 'G', 5);

-- --------------------------------------------------------

--
-- Table structure for table `counters`
--

CREATE TABLE `counters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `counter_number` varchar(10) NOT NULL,
  `officer_name` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','break') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counters`
--

INSERT INTO `counters` (`id`, `counter_number`, `officer_name`, `status`) VALUES
(1, '01', 'Officer A', 'active'),
(2, '02', 'Officer B', 'active'),
(3, '03', 'Officer C', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `queue_tokens`
--

CREATE TABLE `queue_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `token_number` varchar(20) NOT NULL,
  `status` enum('pending','called','completed','skipped') DEFAULT 'pending',
  `assigned_counter_id` int(11) DEFAULT NULL,
  `generated_at` datetime DEFAULT current_timestamp(),
  `called_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visitor_id` (`visitor_id`),
  KEY `service_id` (`service_id`),
  KEY `assigned_counter_id` (`assigned_counter_id`),
  CONSTRAINT `queue_tokens_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `queue_tokens_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `queue_tokens_ibfk_3` FOREIGN KEY (`assigned_counter_id`) REFERENCES `counters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_token_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comments` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `queue_token_id` (`queue_token_id`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`queue_token_id`) REFERENCES `queue_tokens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
