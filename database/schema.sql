-- =====================================================
-- DNA Management System — Database Schema (كامل)
-- PHP 8+ / MySQL 8+ / MySQLi
--
-- يشمل جميع الجداول والتعديلات (بما فيها individual_id)
-- الترتيب: users → families → individuals → family_members → dna → logs
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `dna_management`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `dna_management`;

-- -----------------------------------------------------
-- حذف الجداول (من الأبناء إلى الآباء)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `dna_test_attachments`;
DROP TABLE IF EXISTS `dna_tests`;
DROP TABLE IF EXISTS `family_members`;
DROP TABLE IF EXISTS `individuals`;
DROP TABLE IF EXISTS `families`;
DROP TABLE IF EXISTS `users`;

-- -----------------------------------------------------
-- USERS
-- -----------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'manager', 'data_entry', 'viewer') NOT NULL DEFAULT 'viewer',
  `remember_token` VARCHAR(64) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- مدير افتراضي: admin@dna.com / Admin@123
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES (
  'مدير النظام',
  'admin@dna.com',
  '$2y$10$3yCrCgJsIOCc/AfrWAqFCeaMOz8GqUmUrQcWWlVizcrX0iaicM9LC',
  'admin'
);

-- -----------------------------------------------------
-- FAMILIES
-- -----------------------------------------------------
CREATE TABLE `families` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `family_name` VARCHAR(200) NOT NULL,
  `family_code` VARCHAR(50) NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_families_code` (`family_code`),
  KEY `idx_families_name` (`family_name`),
  KEY `idx_families_deleted` (`deleted_at`),
  CONSTRAINT `fk_families_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- INDIVIDUALS (أفراد مستقلون — قد يرتبطون بعائلة)
-- -----------------------------------------------------
CREATE TABLE `individuals` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `national_id` VARCHAR(20) DEFAULT NULL,
  `blood_type` VARCHAR(5) DEFAULT NULL,
  `birth_date` DATE DEFAULT NULL,
  `gender` ENUM('male', 'female') NOT NULL,
  `family_id` INT UNSIGNED DEFAULT NULL,
  `id_card_image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('normal', 'missing', 'unidentified', 'deceased') NOT NULL DEFAULT 'normal',
  `D3S1358_1` VARCHAR(20) DEFAULT NULL,
  `D3S1358_2` VARCHAR(20) DEFAULT NULL,
  `vWA_1` VARCHAR(20) DEFAULT NULL,
  `vWA_2` VARCHAR(20) DEFAULT NULL,
  `FGA_1` VARCHAR(20) DEFAULT NULL,
  `FGA_2` VARCHAR(20) DEFAULT NULL,
  `D8S1179_1` VARCHAR(20) DEFAULT NULL,
  `D8S1179_2` VARCHAR(20) DEFAULT NULL,
  `D21S11_1` VARCHAR(20) DEFAULT NULL,
  `D21S11_2` VARCHAR(20) DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_individuals_national_id` (`national_id`),
  KEY `idx_individuals_name` (`name`),
  KEY `idx_individuals_status` (`status`),
  KEY `idx_individuals_family` (`family_id`),
  KEY `idx_individuals_deleted` (`deleted_at`),
  CONSTRAINT `fk_individuals_family` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_individuals_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- FAMILY MEMBERS (أب، أم، أبناء)
-- individual_id يربط الابن بسجل في جدول individuals عند النقل بين العائلات
-- -----------------------------------------------------
CREATE TABLE `family_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `family_id` INT UNSIGNED NOT NULL,
  `individual_id` INT UNSIGNED DEFAULT NULL,
  `role` ENUM('father', 'mother', 'child') NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `national_id` VARCHAR(20) DEFAULT NULL,
  `blood_type` VARCHAR(5) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `birth_date` DATE DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `gender` ENUM('male', 'female') DEFAULT NULL,
  `id_card_image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `D3S1358_1` VARCHAR(20) DEFAULT NULL,
  `D3S1358_2` VARCHAR(20) DEFAULT NULL,
  `vWA_1` VARCHAR(20) DEFAULT NULL,
  `vWA_2` VARCHAR(20) DEFAULT NULL,
  `FGA_1` VARCHAR(20) DEFAULT NULL,
  `FGA_2` VARCHAR(20) DEFAULT NULL,
  `D8S1179_1` VARCHAR(20) DEFAULT NULL,
  `D8S1179_2` VARCHAR(20) DEFAULT NULL,
  `D21S11_1` VARCHAR(20) DEFAULT NULL,
  `D21S11_2` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_members_national_id` (`national_id`),
  UNIQUE KEY `uk_members_phone` (`phone`),
  KEY `idx_members_family` (`family_id`),
  KEY `idx_members_individual` (`individual_id`),
  KEY `idx_members_role` (`role`),
  KEY `idx_members_deleted` (`deleted_at`),
  CONSTRAINT `fk_members_family` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_members_individual` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- DNA TESTS
-- -----------------------------------------------------
CREATE TABLE `dna_tests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `person_name` VARCHAR(200) NOT NULL,
  `sample_date` DATE DEFAULT NULL,
  `lab_name` VARCHAR(200) DEFAULT NULL,
  `lab_location` VARCHAR(200) DEFAULT NULL,
  `doctor_name` VARCHAR(200) DEFAULT NULL,
  `status` ENUM('completed', 'failed', 'pending') NOT NULL DEFAULT 'pending',
  `result_summary` TEXT DEFAULT NULL,
  `D3S1358_1` VARCHAR(20) DEFAULT NULL,
  `D3S1358_2` VARCHAR(20) DEFAULT NULL,
  `vWA_1` VARCHAR(20) DEFAULT NULL,
  `vWA_2` VARCHAR(20) DEFAULT NULL,
  `FGA_1` VARCHAR(20) DEFAULT NULL,
  `FGA_2` VARCHAR(20) DEFAULT NULL,
  `D8S1179_1` VARCHAR(20) DEFAULT NULL,
  `D8S1179_2` VARCHAR(20) DEFAULT NULL,
  `D21S11_1` VARCHAR(20) DEFAULT NULL,
  `D21S11_2` VARCHAR(20) DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dna_person` (`person_name`),
  KEY `idx_dna_status` (`status`),
  KEY `idx_dna_deleted` (`deleted_at`),
  CONSTRAINT `fk_dna_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- DNA TEST ATTACHMENTS
-- -----------------------------------------------------
CREATE TABLE `dna_test_attachments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dna_test_id` INT UNSIGNED NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attachments_test` (`dna_test_id`),
  CONSTRAINT `fk_attachments_test` FOREIGN KEY (`dna_test_id`) REFERENCES `dna_tests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- ACTIVITY LOGS
-- -----------------------------------------------------
CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT UNSIGNED DEFAULT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_user` (`user_id`),
  KEY `idx_logs_entity` (`entity_type`, `entity_id`),
  KEY `idx_logs_created` (`created_at`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
