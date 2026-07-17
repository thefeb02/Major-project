-- Database schema for Nepal Tour and Travel login system
-- Default admin: admin@nepaltravel.com / Admin@123
CREATE DATABASE IF NOT EXISTS tour_travel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tour_travel_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  admin_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  archived_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS travel_plans (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  destination VARCHAR(120) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  travelers INT UNSIGNED NOT NULL DEFAULT 1,
  notes TEXT NULL,
  status ENUM('pending', 'approved', 'rejected', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
  archived_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  CONSTRAINT fk_travel_plans_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS service_bookings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NULL,
  service_category VARCHAR(80) NOT NULL,
  service_name VARCHAR(190) NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  travel_date DATE NOT NULL,
  message TEXT NULL,
  status ENUM('pending', 'approved', 'rejected', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
  archived_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_service_bookings_user (user_id),
  CONSTRAINT fk_service_bookings_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') NOT NULL DEFAULT 'user' AFTER password;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS admin_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active' AFTER role,
  ADD COLUMN IF NOT EXISTS archived_at DATETIME NULL AFTER admin_status;

ALTER TABLE travel_plans
  ADD COLUMN IF NOT EXISTS status ENUM('pending', 'approved', 'rejected', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending' AFTER notes,
  ADD COLUMN IF NOT EXISTS archived_at DATETIME NULL AFTER status;

ALTER TABLE service_bookings
  ADD COLUMN IF NOT EXISTS status ENUM('pending', 'approved', 'rejected', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending' AFTER message,
  ADD COLUMN IF NOT EXISTS archived_at DATETIME NULL AFTER status;

INSERT INTO users (name, email, password, role)
VALUES (
  'Admin',
  'admin@nepaltravel.com',
  '$2y$10$2r2YKxW2EgKQUOLlrjpmY.p9.dW3B8M3Qy0xoKCbZsdMPUf1.teG2',
  'admin'
)
ON DUPLICATE KEY UPDATE password = VALUES(password), role = 'admin', admin_status = 'active';
