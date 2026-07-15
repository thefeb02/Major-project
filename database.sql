-- Database schema for Nepal Tour and Travel login system
CREATE DATABASE IF NOT EXISTS tour_travel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tour_travel_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') NOT NULL DEFAULT 'user' AFTER password;

INSERT INTO users (name, email, password, role)
VALUES (
  'Admin',
  'admin@nepaltravel.com',
  '1234',
  'admin'
)
ON DUPLICATE KEY UPDATE role = 'admin';
