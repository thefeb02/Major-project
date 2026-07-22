-- Database schema for Nepal Tour and Travel login system
-- Default admin: admin@nepaltravel.com / Admin@123
CREATE DATABASE IF NOT EXISTS nepal_travel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nepal_travel_db;

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

-- Content and operations managed from the administrator dashboard.
CREATE TABLE IF NOT EXISTS tour_packages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(190) NOT NULL,
  destination VARCHAR(120) NOT NULL,
  category VARCHAR(100) NOT NULL,
  duration VARCHAR(60) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  image_url VARCHAR(500) NULL,
  description TEXT NULL,
  status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tour_packages_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gallery_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(190) NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  alt_text VARCHAR(190) NULL,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(190) NULL,
  message TEXT NOT NULL,
  status ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_messages_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id INT UNSIGNED NULL,
  customer_name VARCHAR(120) NOT NULL,
  type ENUM('Transaction', 'Invoice', 'Refund') NOT NULL DEFAULT 'Transaction',
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(40) NOT NULL DEFAULT 'Pending',
  payment_date DATE NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_payments_type (type),
  CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES service_bookings (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_name VARCHAR(120) NOT NULL,
  package_name VARCHAR(190) NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT NOT NULL,
  status ENUM('pending', 'published', 'hidden') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS website_settings (
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO website_settings (setting_key, setting_value) VALUES
  ('site_name', 'AddNepalTour & Travel'),
  ('contact_email', 'info@nepalitourtravel.com'),
  ('contact_phone', '+9779763658085'),
  ('address', 'Butwal, Nepal'),
  ('facebook_url', ''),
  ('twitter_url', ''),
  ('seo_title', 'Nepal Tour & Travel'),
  ('seo_keywords', 'Nepal, tours, travel, trekking'),
  ('homepage_hero', 'Discover Nepal with confidence')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

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
  'Sujit Kumar Mandal',
  'admin@nepaltravel.com',
  '$2y$10$2r2YKxW2EgKQUOLlrjpmY.p9.dW3B8M3Qy0xoKCbZsdMPUf1.teG2',
  'admin'
)
ON DUPLICATE KEY UPDATE name = VALUES(name), password = VALUES(password), role = 'admin', admin_status = 'active';

-- Physical dashboard tables, synchronized from the live website tables.
DROP VIEW IF EXISTS admins;
DROP VIEW IF EXISTS customers;
DROP VIEW IF EXISTS bookings;
DROP VIEW IF EXISTS gallery;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  admin_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customers (
  id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  admin_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  archived_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookings (
  id INT UNSIGNED NOT NULL,
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
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gallery (
  id INT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  alt_text VARCHAR(190) NULL,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admins (id, name, email, admin_status, created_at)
  SELECT id, name, email, admin_status, created_at FROM users WHERE role = 'admin'
ON DUPLICATE KEY UPDATE name = VALUES(name), email = VALUES(email), admin_status = VALUES(admin_status);
INSERT INTO customers (id, name, email, admin_status, archived_at, created_at)
  SELECT id, name, email, admin_status, archived_at, created_at FROM users WHERE role = 'user'
ON DUPLICATE KEY UPDATE name = VALUES(name), email = VALUES(email), admin_status = VALUES(admin_status), archived_at = VALUES(archived_at);
INSERT INTO bookings (id, user_id, service_category, service_name, full_name, email, phone, travel_date, message, status, archived_at, created_at)
  SELECT id, user_id, service_category, service_name, full_name, email, phone, travel_date, message, status, archived_at, created_at FROM service_bookings
ON DUPLICATE KEY UPDATE status = VALUES(status), message = VALUES(message), archived_at = VALUES(archived_at);
INSERT INTO gallery (id, title, image_url, alt_text, is_visible, created_at)
  SELECT id, title, image_url, alt_text, is_visible, created_at FROM gallery_images
ON DUPLICATE KEY UPDATE title = VALUES(title), image_url = VALUES(image_url), alt_text = VALUES(alt_text), is_visible = VALUES(is_visible);

DELIMITER //
DROP TRIGGER IF EXISTS sync_user_after_insert//
CREATE TRIGGER sync_user_after_insert AFTER INSERT ON users FOR EACH ROW
BEGIN
  IF NEW.role = 'admin' THEN
    INSERT INTO admins VALUES (NEW.id, NEW.name, NEW.email, NEW.admin_status, NEW.created_at);
  ELSE
    INSERT INTO customers VALUES (NEW.id, NEW.name, NEW.email, NEW.admin_status, NEW.archived_at, NEW.created_at);
  END IF;
END//
DROP TRIGGER IF EXISTS sync_user_after_update//
CREATE TRIGGER sync_user_after_update AFTER UPDATE ON users FOR EACH ROW
BEGIN
  DELETE FROM admins WHERE id = NEW.id;
  DELETE FROM customers WHERE id = NEW.id;
  IF NEW.role = 'admin' THEN
    INSERT INTO admins VALUES (NEW.id, NEW.name, NEW.email, NEW.admin_status, NEW.created_at);
  ELSE
    INSERT INTO customers VALUES (NEW.id, NEW.name, NEW.email, NEW.admin_status, NEW.archived_at, NEW.created_at);
  END IF;
END//
DROP TRIGGER IF EXISTS sync_booking_after_insert//
CREATE TRIGGER sync_booking_after_insert AFTER INSERT ON service_bookings FOR EACH ROW
  INSERT INTO bookings VALUES (NEW.id, NEW.user_id, NEW.service_category, NEW.service_name, NEW.full_name, NEW.email, NEW.phone, NEW.travel_date, NEW.message, NEW.status, NEW.archived_at, NEW.created_at)//
DROP TRIGGER IF EXISTS sync_booking_after_update//
CREATE TRIGGER sync_booking_after_update AFTER UPDATE ON service_bookings FOR EACH ROW
  REPLACE INTO bookings VALUES (NEW.id, NEW.user_id, NEW.service_category, NEW.service_name, NEW.full_name, NEW.email, NEW.phone, NEW.travel_date, NEW.message, NEW.status, NEW.archived_at, NEW.created_at)//
DROP TRIGGER IF EXISTS sync_gallery_after_insert//
CREATE TRIGGER sync_gallery_after_insert AFTER INSERT ON gallery_images FOR EACH ROW
  INSERT INTO gallery VALUES (NEW.id, NEW.title, NEW.image_url, NEW.alt_text, NEW.is_visible, NEW.created_at)//
DROP TRIGGER IF EXISTS sync_gallery_after_update//
CREATE TRIGGER sync_gallery_after_update AFTER UPDATE ON gallery_images FOR EACH ROW
  REPLACE INTO gallery VALUES (NEW.id, NEW.title, NEW.image_url, NEW.alt_text, NEW.is_visible, NEW.created_at)//
DELIMITER ;
