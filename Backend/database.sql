-- Comprehensive Database Schema for Nepal Tour and Travel
CREATE DATABASE IF NOT EXISTS nepal_travel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nepal_travel_db;

-- 1. Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) NULL,
    role ENUM('superadmin', 'editor') NOT NULL DEFAULT 'superadmin',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (admin@admin.com / admin123)
INSERT INTO admins (name, email, password, role) 
VALUES ('Super Admin', 'admin@admin.com', '$2y$10$THmuw3UvG1TWZWRh9MapQOlTgkyAfhQGJdplHoZHMZxbTJvmoRtFy', 'superadmin')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 2. Places Management
CREATE TABLE IF NOT EXISTS places (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
  province VARCHAR(100) NOT NULL,
  place_category VARCHAR(50) NOT NULL DEFAULT 'provinces',
  district VARCHAR(100) NOT NULL,
    description TEXT NULL,
    history TEXT NULL,
    best_time_to_visit VARCHAR(150) NULL,
    entry_fee DECIMAL(10,2) DEFAULT 0.00,
    google_map TEXT NULL,
    main_image VARCHAR(500) NULL,
    is_featured TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Places Gallery
CREATE TABLE IF NOT EXISTS place_gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    place_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    caption VARCHAR(150) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tour Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('package', 'blog') NOT NULL DEFAULT 'package',
    image_url VARCHAR(255) NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Categories
INSERT IGNORE INTO categories (name, slug, type) VALUES 
('Adventure', 'adventure', 'package'),
('Cultural', 'cultural', 'package'),
('Nature & Wildlife', 'nature-wildlife', 'package'),
('Pilgrimage', 'pilgrimage', 'package');

INSERT IGNORE INTO categories (name, slug, type, image_url, description, sort_order, is_featured, status) VALUES
('Student Education Tour', 'student-education-tour', 'package', 'https://images.unsplash.com/photo-1524492514790-1f75cfe62d3d?auto=format&fit=crop&w=1200&q=80', 'Education-focused travel and learning experiences.', 1, 1, 'active'),
('Honeymoon', 'honeymoon', 'package', 'https://images.unsplash.com/photo-1503220317375-aaad61436b1b?auto=format&fit=crop&w=1200&q=80', 'Romantic escapes and premium couple packages.', 2, 1, 'active'),
('Pilgrimage', 'pilgrimage', 'package', 'https://images.unsplash.com/photo-1564550952752-04f0c98f1f66?auto=format&fit=crop&w=1200&q=80', 'Sacred journeys and spiritual destinations.', 3, 1, 'active');

-- 5. Tour Packages
CREATE TABLE IF NOT EXISTS packages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    destination VARCHAR(150) NOT NULL,
    category_id INT UNSIGNED NULL,
    duration VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2) NULL,
    short_description TEXT NOT NULL,
    full_description LONGTEXT NOT NULL,
    included_services TEXT NULL,
    excluded_services TEXT NULL,
    itinerary LONGTEXT NULL,
    map_location TEXT NULL,
    main_image VARCHAR(255) NULL,
    is_featured TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Package Gallery
CREATE TABLE IF NOT EXISTS package_gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    caption VARCHAR(150) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Bookings Management
CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    package_id INT UNSIGNED NULL,
    service_name VARCHAR(190) NULL,
    service_category VARCHAR(100) NULL,
    travelers INT UNSIGNED NOT NULL DEFAULT 1,
    travel_date DATE NOT NULL,
    message TEXT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Main Website Gallery
CREATE TABLE IF NOT EXISTS gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NULL,
    image_url VARCHAR(255) NOT NULL,
    category VARCHAR(100) NULL,
    is_featured TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Testimonials
CREATE TABLE IF NOT EXISTS testimonials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK(rating BETWEEN 1 AND 5),
    review TEXT NOT NULL,
    photo VARCHAR(255) NULL,
    is_approved TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Blog Posts
CREATE TABLE IF NOT EXISTS blogs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    category_id INT UNSIGNED NULL,
    tags VARCHAR(255) NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Contact Messages
CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    subject VARCHAR(190) NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Payments
CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NULL,
    customer_name VARCHAR(120) NULL,
    type VARCHAR(50) NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    payment_method VARCHAR(50) NULL,
    payment_date DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_payments_booking (booking_id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Website Settings
CREATE TABLE IF NOT EXISTS website_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO packages (title, destination, category_id, duration, price, main_image, short_description, full_description, included_services, excluded_services, itinerary, is_featured, status)
SELECT 'Kathmandu Student Education Tour', 'Kathmandu', c.id, '4 Days', 18500, 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80', 'A guided learning tour through Kathmandu Valley museums, heritage sites, and cultural workshops.', 'This package is designed for school and college groups who want a structured educational trip. Students visit UNESCO heritage sites, museums, traditional craft centers, and historic palaces while learning about Nepalese history, culture, architecture, and conservation.', 'Hotel stay, breakfast, private transport, guide, museum entry fees, and educational support materials', 'Lunch, personal expenses, optional activities, and drinks', 'Day 1: Arrival and orientation\nDay 2: UNESCO heritage site visits\nDay 3: Museum and workshop sessions\nDay 4: Departure and group review', 1, 'active'
FROM categories c WHERE c.name = 'Student Education Tour' AND c.type = 'package'
UNION ALL
SELECT 'Pokhara Honeymoon Escape', 'Pokhara', c.id, '5 Days', 32500, 'https://images.unsplash.com/photo-1503220317375-aaad61436b1b?auto=format&fit=crop&w=1200&q=80', 'A romantic lakeside escape with sunrise viewpoints, private stays, and scenic dining experiences.', 'Built for couples, this honeymoon package combines peaceful luxury with the best of Pokhara. Enjoy lakeside relaxation, Sarangkot sunrise views, private transfers, romantic dinners, and flexible free time for boating, spa sessions, or quiet walks around Phewa Lake.', '3-star or boutique hotel, daily breakfast, private vehicle, Sarangkot sunrise trip, airport pickup, and couple welcome amenities', 'Airfare, personal shopping, bar bills, and extra adventure activities', 'Day 1: Arrival and lakeside check-in\nDay 2: Pokhara city tour and boating\nDay 3: Sarangkot sunrise and leisure\nDay 4: Romantic dinner and free day\nDay 5: Departure', 1, 'active'
FROM categories c WHERE c.name = 'Honeymoon' AND c.type = 'package'
UNION ALL
SELECT 'Muktinath Pilgrimage Journey', 'Muktinath', c.id, '6 Days', 28500, 'https://images.unsplash.com/photo-1564550952752-04f0c98f1f66?auto=format&fit=crop&w=1200&q=80', 'A sacred journey to one of Nepal’s most important pilgrimage destinations with comfortable travel support.', 'This pilgrimage journey is ideal for families and spiritual travelers. The route includes cultural stops, scenic mountain travel, and dedicated time at Muktinath Temple. The package balances devotion, comfort, and a smooth overland travel experience through Nepal’s beautiful landscapes.', 'Accommodation, breakfast and dinner, jeep transport, permits if needed, and pilgrimage assistance', 'Lunch, donations, personal purchases, and emergency medical costs', 'Day 1: Departure from Kathmandu\nDay 2: Scenic travel to Pokhara\nDay 3: Drive to Jomsom/Muktinath region\nDay 4: Temple visit and rituals\nDay 5: Return journey\nDay 6: Arrival in Kathmandu', 1, 'active'
FROM categories c WHERE c.name = 'Pilgrimage' AND c.type = 'package'
ON DUPLICATE KEY UPDATE title = VALUES(title);
-- Default Settings
INSERT IGNORE INTO website_settings (setting_key, setting_value) VALUES
('site_name', 'Nepal Tour and Travel'),
('logo_url', ''),
('favicon_url', ''),
('hero_image_url', ''),
('contact_email', 'info@nepalitourtravel.com'),
('contact_phone', '+977 9763658085'),
('address', 'Butwal, Nepal'),
('facebook_url', ''),
('twitter_url', ''),
('instagram_url', ''),
('youtube_url', ''),
('seo_title', 'Nepal Tour & Travel - Discover the Magic of Nepal'),
('seo_description', 'Explore breathtaking mountains and vibrant festivals across the Himalayas.'),
('footer_text', 'Curated tours, mountain adventures, cultural escapes, and trusted local guidance for an unforgettable Nepal experience.');

-- 14. Homepage Sections Layout Config
CREATE TABLE IF NOT EXISTS homepage_sections (
    section_key VARCHAR(100) NOT NULL PRIMARY KEY,
    title VARCHAR(150) NULL,
    subtitle VARCHAR(255) NULL,
    is_enabled TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO homepage_sections (section_key, title, subtitle, sort_order) VALUES
('hero', 'Discover the Magic of Nepal', 'Explore breathtaking mountains...', 1),
('featured_packages', 'Tour Packages', 'Choose from our best packages', 2),
('featured_places', 'Places to Go', 'Explore stunning destinations', 3),
('activities', 'Things to Do', 'Endless activities for every traveler', 4),
('testimonials', 'What Our Clients Say', 'Real stories from real travelers', 5),
('gallery', 'Website Gallery', 'Moments curated by us', 6);

-- 15. Activity Logs (Audit Trail)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NULL,
    action VARCHAR(255) NOT NULL,
    module VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
