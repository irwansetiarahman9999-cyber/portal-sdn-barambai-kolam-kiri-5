-- SDN Barambai Kolam Kiri 5 - Database Schema (MySQL 8)
-- Phase 2 Implementation

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. AUTHENTICATION & AUTHORIZATION
-- --------------------------------------------------------

CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` CHAR(36) PRIMARY KEY, -- UUID
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role_id` INT NOT NULL,
  `status` ENUM('ACTIVE', 'INACTIVE', 'BANNED') DEFAULT 'ACTIVE',
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs` (
  `id` CHAR(36) PRIMARY KEY,
  `user_id` CHAR(36) NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity` VARCHAR(100) NOT NULL,
  `entity_id` VARCHAR(100) NULL,
  `ip_address` VARCHAR(45) NULL,
  `metadata` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. SCHOOL CORE DATA
-- --------------------------------------------------------

CREATE TABLE `school_profile` (
  `id` INT PRIMARY KEY DEFAULT 1,
  `school_name` VARCHAR(150) NOT NULL,
  `npsn` VARCHAR(20) NULL,
  `nss` VARCHAR(50) NULL,
  `address` TEXT NULL,
  `village` VARCHAR(100) NULL,
  `district` VARCHAR(100) NULL,
  `regency` VARCHAR(100) NULL,
  `province` VARCHAR(100) NULL,
  `postal_code` VARCHAR(10) NULL,
  `email` VARCHAR(100) NULL,
  `phone` VARCHAR(30) NULL,
  `principal_name` VARCHAR(150) NULL,
  `accreditation` VARCHAR(10) NULL,
  `founded_year` YEAR NULL,
  `vision` TEXT NULL,
  `mission` TEXT NULL,
  `principal_message` TEXT NULL,
  `logo_path` VARCHAR(255) NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `academic_years` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(20) NOT NULL,
  `is_active` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. STAFF & TEACHERS
-- --------------------------------------------------------

CREATE TABLE `staff` (
  `id` CHAR(36) PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `title` VARCHAR(50) NULL,
  `nip` VARCHAR(50) NULL UNIQUE,
  `nuptk` VARCHAR(50) NULL UNIQUE,
  `position` VARCHAR(100) NULL,
  `subject` VARCHAR(100) NULL,
  `education` VARCHAR(100) NULL,
  `employment_status` ENUM('PNS', 'PPPK', 'GTT', 'HONOR') NULL,
  `photo_path` VARCHAR(255) NULL,
  `public_profile` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. STUDENTS
-- --------------------------------------------------------

CREATE TABLE `students` (
  `id` CHAR(36) PRIMARY KEY,
  `internal_id` VARCHAR(50) NOT NULL UNIQUE,
  `nisn` VARCHAR(20) NULL UNIQUE,
  `full_name` VARCHAR(150) NOT NULL,
  `gender` ENUM('L', 'P') NOT NULL,
  `birth_place` VARCHAR(100) NULL,
  `birth_date` DATE NULL,
  `address` TEXT NULL,
  `blood_type` ENUM('A', 'B', 'AB', 'O', '-') DEFAULT '-',
  `photo_path` VARCHAR(255) NULL,
  `status` ENUM('ACTIVE', 'ALUMNI', 'TRANSFERRED', 'DROPOUT') DEFAULT 'ACTIVE',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_guardians` (
  `id` CHAR(36) PRIMARY KEY,
  `student_id` CHAR(36) NOT NULL,
  `relationship` VARCHAR(50) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `email` VARCHAR(100) NULL,
  `address` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
