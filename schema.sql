-- Centralized Visitor System Schema (Multi-Tenant) - Clean Reset

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `visitor_sys` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `visitor_sys`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `queue_tokens`;
DROP TABLE IF EXISTS `visitors`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `institutes`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Institutes
CREATE TABLE `institutes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Users (RBAC)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institute_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','officer') NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `counter_number` varchar(10) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `institute_id` (`institute_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Services (Added Icon Column)
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institute_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `token_prefix` varchar(5) NOT NULL,
  `avg_service_time_min` int(11) DEFAULT 10,
  `icon` varchar(50) DEFAULT 'file-text',  -- New Column
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `institute_id` (`institute_id`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Visitors
CREATE TABLE `visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nic_number` varchar(20) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nic_number_idx` (`nic_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Queue Tokens
CREATE TABLE `queue_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institute_id` int(11) NOT NULL,
  `visitor_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `token_number` varchar(20) NOT NULL,
  `status` enum('pending','called','completed','skipped') DEFAULT 'pending',
  `assigned_user_id` int(11) DEFAULT NULL,
  `generated_at` datetime DEFAULT current_timestamp(),
  `called_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `institute_id` (`institute_id`),
  KEY `visitor_id` (`visitor_id`),
  KEY `service_id` (`service_id`),
  KEY `assigned_user_id` (`assigned_user_id`),
  CONSTRAINT `queue_tokens_ibfk_1` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `queue_tokens_ibfk_2` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `queue_tokens_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `queue_tokens_ibfk_4` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
