<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_name('nepal_tour_session');
    session_start();
}

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORTS', [3307, 3306]);
define('DB_NAME', 'nepal_travel_db');
define('DB_CREDENTIALS', [
    ['user' => 'root', 'pass' => ''],
    ['user' => 'tour_user', 'pass' => 'tour_pass_2026'],
]);

$pdo = null;
$lastDatabaseError = null;

foreach (DB_PORTS as $port) {
    foreach (DB_CREDENTIALS as $credential) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';port=' . $port . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                $credential['user'],
                $credential['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            break 2;
        } catch (PDOException $e) {
            $lastDatabaseError = $e;
        }
    }
}

if (!$pdo) {
    http_response_code(500);
    $testedUsers = array_map(static fn ($credential) => $credential['user'], DB_CREDENTIALS);
    echo 'Database connection failed. Tried MySQL ports: ' . htmlspecialchars(implode(', ', DB_PORTS));
    echo '<br>Users tried: ' . htmlspecialchars(implode(', ', $testedUsers));
    if ($lastDatabaseError) {
        echo '<br>Error: ' . htmlspecialchars($lastDatabaseError->getMessage());
    }
    exit;
}

function resolveFrontendImageUrl(?string $imageUrl, string $imageDirectory, string $fallback): string
{
    $imageUrl = trim((string) $imageUrl);
    if ($imageUrl === '') {
        return $fallback;
    }

    if (preg_match('#^(?:https?:)?//#i', $imageUrl)
        || str_starts_with($imageUrl, '/')
        || str_starts_with($imageUrl, '../')
        || str_starts_with($imageUrl, './')) {
        return $imageUrl;
    }

    if (str_starts_with($imageUrl, 'img/')) {
        return '../' . $imageUrl;
    }

    if (str_starts_with($imageUrl, $imageDirectory . '/')) {
        return '../img/' . $imageUrl;
    }

    return '../img/' . $imageDirectory . '/' . $imageUrl;
}

/** Keep older local databases compatible with the current authentication pages. */
function ensureUsersTable(): void
{
    global $pdo;

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(30) NOT NULL DEFAULT 'user',
                is_verified TINYINT(1) NOT NULL DEFAULT 1,
                verification_token VARCHAR(100) NULL,
                google_id VARCHAR(255) NULL,
                profile_pic VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $columns = [
            'is_verified' => 'ALTER TABLE users ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 1',
            'verification_token' => 'ALTER TABLE users ADD COLUMN verification_token VARCHAR(100) NULL',
            'google_id' => 'ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL',
            'profile_pic' => 'ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255) NULL',
        ];

        foreach ($columns as $sql) {
            try {
                $pdo->exec($sql);
            } catch (Throwable $e) {
                // The column already exists or the database is read-only.
            }
        }
    } catch (Throwable $e) {
        // Login pages can still show a controlled database error if MySQL is unavailable.
    }
}

ensureUsersTable();

function ensurePasswordResetTable(): void
{
    global $pdo;

    if (!$pdo) {
        return;
    }

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS password_reset_tokens (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT UNSIGNED NOT NULL,
                token VARCHAR(100) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                used_at DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_password_reset_tokens_user (user_id),
                CONSTRAINT fk_password_reset_tokens_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Throwable $e) {
        // Ignore table creation failures so the site stays available.
    }
}

ensurePasswordResetTable();

/**
 * Core catalogue tables are required by package browsing and booking. Older
 * local databases may contain only the users table, so create these tables
 * before the optional seed and repair routines run.
 */
function ensureCoreTravelTables(): void
{
    global $pdo;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            type ENUM('package', 'blog') NOT NULL DEFAULT 'package',
            image_url VARCHAR(255) NULL,
            description TEXT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(190) NOT NULL,
            destination VARCHAR(150) NOT NULL,
            category_id INT UNSIGNED NULL,
            duration VARCHAR(50) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            discount_price DECIMAL(10,2) NULL,
            short_description TEXT NOT NULL,
            full_description LONGTEXT NOT NULL,
            included_services TEXT NULL,
            excluded_services TEXT NULL,
            itinerary LONGTEXT NULL,
            map_location TEXT NULL,
            main_image VARCHAR(255) NULL,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Destinations managed from the administrator Places screen.  This
        // must exist on fresh installations before the dashboard can add a
        // place or the homepage can list it.
        $pdo->exec("CREATE TABLE IF NOT EXISTS places (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(150) NOT NULL,
            province VARCHAR(100) NOT NULL,
            place_category VARCHAR(50) NOT NULL DEFAULT 'provinces',
            district VARCHAR(100) NOT NULL,
            description TEXT NULL,
            history TEXT NULL,
            best_time_to_visit VARCHAR(150) NULL,
            entry_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            google_map TEXT NULL,
            main_image VARCHAR(500) NULL,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_places_public (status, is_featured, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        try {
            $pdo->exec("ALTER TABLE places ADD COLUMN place_category VARCHAR(50) NOT NULL DEFAULT 'provinces' AFTER province");
        } catch (Throwable $e) {
            // Existing installations already have this column.
        }

        $placeImageColumn = $pdo->query("SHOW COLUMNS FROM places LIKE 'main_image'")->fetch();
        if ($placeImageColumn && preg_match('/^varchar\((\d+)\)$/i', $placeImageColumn['Type'] ?? '', $matches) && (int) $matches[1] < 500) {
            try {
                $pdo->exec('ALTER TABLE places MODIFY COLUMN main_image VARCHAR(500) NULL');
            } catch (Throwable $e) {
                error_log('Unable to expand places.main_image for managed image URLs: ' . $e->getMessage());
            }
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS activities (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(150) NOT NULL,
            category VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            image_url VARCHAR(500) NULL,
            page_url VARCHAR(500) NULL,
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_activities_public (status, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            full_name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            package_id INT UNSIGNED NULL,
            service_name VARCHAR(190) NULL,
            service_category VARCHAR(100) NULL,
            travelers INT UNSIGNED NOT NULL DEFAULT 1,
            travel_date DATE NOT NULL,
            message TEXT NULL,
            status ENUM('pending', 'confirmed', 'hold', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        try {
            $pdo->exec("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'hold', 'cancelled', 'completed') NOT NULL DEFAULT 'pending'");
        } catch (Throwable $e) {
            // Existing installations may already have the older enum or a different status set.
        }

        // Upgrade the earlier bookings table used by the first version of the
        // project. It used service_name fields but did not have package_id or
        // traveler count, which caused booking inserts to fail.
        foreach ([
            'package_id' => 'ALTER TABLE bookings ADD COLUMN package_id INT UNSIGNED NULL AFTER phone',
            'service_name' => 'ALTER TABLE bookings ADD COLUMN service_name VARCHAR(190) NULL AFTER package_id',
            'service_category' => 'ALTER TABLE bookings ADD COLUMN service_category VARCHAR(100) NULL AFTER service_name',
            'travelers' => 'ALTER TABLE bookings ADD COLUMN travelers INT UNSIGNED NOT NULL DEFAULT 1 AFTER package_id',
            'updated_at' => 'ALTER TABLE bookings ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
        ] as $sql) {
            try {
                $pdo->exec($sql);
            } catch (Throwable $e) {
                // The column already exists in newer databases.
            }
        }
    } catch (Throwable $e) {
        // Individual pages return a friendly error if the database is unavailable.
    }
}

ensureCoreTravelTables();

function ensurePaymentsTable(): void
{
    global $pdo;

    if (!$pdo) {
        return;
    }

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS payments (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                booking_id INT UNSIGNED NULL,
                customer_name VARCHAR(120) NULL,
                type VARCHAR(50) NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                status VARCHAR(50) NOT NULL DEFAULT 'Pending',
                payment_method VARCHAR(50) NULL,
                payment_date DATE NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_payments_booking (booking_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $requiredColumns = [
            'booking_id' => "ALTER TABLE payments ADD COLUMN booking_id INT UNSIGNED NULL AFTER id",
            'customer_name' => "ALTER TABLE payments ADD COLUMN customer_name VARCHAR(120) NULL AFTER booking_id",
            'type' => "ALTER TABLE payments ADD COLUMN type VARCHAR(50) NULL AFTER customer_name",
            'amount' => "ALTER TABLE payments ADD COLUMN amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER type",
            'status' => "ALTER TABLE payments ADD COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Pending' AFTER amount",
            'payment_method' => "ALTER TABLE payments ADD COLUMN payment_method VARCHAR(50) NULL AFTER status",
            'payment_date' => "ALTER TABLE payments ADD COLUMN payment_date DATE NULL AFTER payment_method",
            'created_at' => "ALTER TABLE payments ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER payment_date",
            'updated_at' => "ALTER TABLE payments ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
        ];

        $columnStmt = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments'");
        $existingColumns = array_flip(array_map(static fn ($row) => $row['COLUMN_NAME'], $columnStmt->fetchAll()));

        foreach ($requiredColumns as $columnName => $alterSql) {
            if (!isset($existingColumns[$columnName])) {
                try {
                    $pdo->exec($alterSql);
                } catch (Throwable $e) {
                    // Keep the application available even if a column addition fails.
                }
            }
        }
    } catch (Throwable $e) {
        // Ignore table creation failures so the site stays available.
    }
}

ensurePaymentsTable();

function ensureCategoriesTable(): void
{
    global $pdo;

    if (!$pdo) {
        return;
    }

    try {
        $pdo->exec("ALTER TABLE categories ADD COLUMN image_url VARCHAR(255) NULL");
    } catch (Throwable $e) {
    }

    try {
        $pdo->exec("ALTER TABLE categories ADD COLUMN description TEXT NULL");
    } catch (Throwable $e) {
    }

    try {
        $pdo->exec("ALTER TABLE categories ADD COLUMN sort_order INT NOT NULL DEFAULT 0");
    } catch (Throwable $e) {
    }

    try {
        $pdo->exec("ALTER TABLE categories ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0");
    } catch (Throwable $e) {
    }

    try {
        $pdo->exec("ALTER TABLE categories ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
    } catch (Throwable $e) {
    }

    try {
        $defaults = [
            ['Student Education Tour', 'student-education-tour', 'https://images.unsplash.com/photo-1524492514790-1f75cfe62d3d?auto=format&fit=crop&w=1200&q=80', 'Education-focused travel and learning experiences.', 1],
            ['Honeymoon', 'honeymoon', 'https://images.unsplash.com/photo-1503220317375-aaad61436b1b?auto=format&fit=crop&w=1200&q=80', 'Romantic escapes and premium couple packages.', 2],
            ['Pilgrimage', 'pilgrimage', 'https://images.unsplash.com/photo-1564550952752-04f0c98f1f66?auto=format&fit=crop&w=1200&q=80', 'Sacred journeys and spiritual destinations.', 3],
            ['Cultural', 'cultural', 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80', 'Heritage and culture-rich travel experiences.', 4],
            ['Adventure', 'adventure', 'https://images.unsplash.com/photo-1519608487953-e999c86e7455?auto=format&fit=crop&w=1200&q=80', 'Adventure and trekking packages.', 5],
            ['Nature & Wildlife', 'nature-wildlife', 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80', 'Nature, safari, and wildlife getaways.', 6],
        ];

        $stmt = $pdo->prepare('INSERT INTO categories (name, slug, type, image_url, description, sort_order, is_featured, status) VALUES (?, ?, \'package\', ?, ?, ?, 1, \'active\') ON DUPLICATE KEY UPDATE image_url = COALESCE(NULLIF(VALUES(image_url), \'\'), image_url), description = COALESCE(NULLIF(VALUES(description), \'\'), description), sort_order = VALUES(sort_order), status = VALUES(status)');
        foreach ($defaults as $defaultCategory) {
            $stmt->execute($defaultCategory);
        }
    } catch (Throwable $e) {
    }
}

ensureCategoriesTable();

function ensurePackageSeedData(): void
{
    global $pdo;

    if (!$pdo) {
        return;
    }

    try {
        $packageCount = (int) $pdo->query('SELECT COUNT(*) FROM packages')->fetchColumn();
        if ($packageCount > 0) {
            return;
        }

        $categoryStmt = $pdo->query("SELECT id, name FROM categories WHERE type = 'package'");
        $categoryMap = [];
        foreach ($categoryStmt->fetchAll() as $row) {
            $categoryMap[$row['name']] = (int) $row['id'];
        }

        $seedPackages = [
            [
                'title' => 'Kathmandu Student Education Tour',
                'destination' => 'Kathmandu',
                'category' => 'Student Education Tour',
                'duration' => '4 Days',
                'price' => 18500,
                'short_description' => 'A guided learning tour through Kathmandu Valley museums, heritage sites, and cultural workshops.',
                'full_description' => 'This package is designed for school and college groups who want a structured educational trip. Students visit UNESCO heritage sites, museums, traditional craft centers, and historic palaces while learning about Nepalese history, culture, architecture, and conservation.',
                'included_services' => 'Hotel stay, breakfast, private transport, guide, museum entry fees, and educational support materials',
                'excluded_services' => 'Lunch, personal expenses, optional activities, and drinks',
                'itinerary' => "Day 1: Arrival and orientation\nDay 2: UNESCO heritage site visits\nDay 3: Museum and workshop sessions\nDay 4: Departure and group review",
                'main_image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Pokhara Honeymoon Escape',
                'destination' => 'Pokhara',
                'category' => 'Honeymoon',
                'duration' => '5 Days',
                'price' => 32500,
                'short_description' => 'A romantic lakeside escape with sunrise viewpoints, private stays, and scenic dining experiences.',
                'full_description' => 'Built for couples, this honeymoon package combines peaceful luxury with the best of Pokhara. Enjoy lakeside relaxation, Sarangkot sunrise views, private transfers, romantic dinners, and flexible free time for boating, spa sessions, or quiet walks around Phewa Lake.',
                'included_services' => '3-star or boutique hotel, daily breakfast, private vehicle, Sarangkot sunrise trip, airport pickup, and couple welcome amenities',
                'excluded_services' => 'Airfare, personal shopping, bar bills, and extra adventure activities',
                'itinerary' => "Day 1: Arrival and lakeside check-in\nDay 2: Pokhara city tour and boating\nDay 3: Sarangkot sunrise and leisure\nDay 4: Romantic dinner and free day\nDay 5: Departure",
                'main_image' => 'https://images.unsplash.com/photo-1503220317375-aaad61436b1b?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Muktinath Pilgrimage Journey',
                'destination' => 'Muktinath',
                'category' => 'Pilgrimage',
                'duration' => '6 Days',
                'price' => 28500,
                'short_description' => 'A sacred journey to one of Nepal’s most important pilgrimage destinations with comfortable travel support.',
                'full_description' => 'This pilgrimage journey is ideal for families and spiritual travelers. The route includes cultural stops, scenic mountain travel, and dedicated time at Muktinath Temple. The package balances devotion, comfort, and a smooth overland travel experience through Nepal’s beautiful landscapes.',
                'included_services' => 'Accommodation, breakfast and dinner, jeep transport, permits if needed, and pilgrimage assistance',
                'excluded_services' => 'Lunch, donations, personal purchases, and emergency medical costs',
                'itinerary' => "Day 1: Departure from Kathmandu\nDay 2: Scenic travel to Pokhara\nDay 3: Drive to Jomsom/Muktinath region\nDay 4: Temple visit and rituals\nDay 5: Return journey\nDay 6: Arrival in Kathmandu",
                'main_image' => 'https://images.unsplash.com/photo-1564550952752-04f0c98f1f66?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        $stmt = $pdo->prepare('INSERT INTO packages (title, destination, category_id, duration, price, main_image, short_description, full_description, included_services, excluded_services, itinerary, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, "active")');
        foreach ($seedPackages as $seedPackage) {
            $stmt->execute([
                $seedPackage['title'],
                $seedPackage['destination'],
                $categoryMap[$seedPackage['category']] ?? null,
                $seedPackage['duration'],
                $seedPackage['price'],
                $seedPackage['main_image'],
                $seedPackage['short_description'],
                $seedPackage['full_description'],
                $seedPackage['included_services'],
                $seedPackage['excluded_services'],
                $seedPackage['itinerary'],
            ]);
        }
    } catch (Throwable $e) {
        // Keep the site available even if the seed insert fails.
    }
}

ensurePackageSeedData();

function ensureCategoryDataIntegrity(): void
{
    global $pdo;

    if (!$pdo) {
        return;
    }

    try {
        $rows = $pdo->query("SELECT id, name FROM categories WHERE type = 'package' AND name LIKE '{%'")->fetchAll();
        if (!$rows) {
            return;
        }

        $findCategoryByName = $pdo->prepare("SELECT id FROM categories WHERE type = 'package' AND name = ? LIMIT 1");
        $updatePackageCategory = $pdo->prepare('UPDATE packages SET category_id = ? WHERE category_id = ?');
        $renameCategory = $pdo->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?');
        $deleteCategory = $pdo->prepare('DELETE FROM categories WHERE id = ?');

        foreach ($rows as $row) {
            $rawName = trim((string) ($row['name'] ?? ''));
            $parsed = json_decode($rawName, true);
            $cleanName = trim((string) ($parsed['name'] ?? ''));

            if ($cleanName === '') {
                continue;
            }

            $findCategoryByName->execute([$cleanName]);
            $existingId = (int) ($findCategoryByName->fetchColumn() ?: 0);
            $currentId = (int) $row['id'];

            if ($existingId > 0 && $existingId !== $currentId) {
                $updatePackageCategory->execute([$existingId, $currentId]);
                $deleteCategory->execute([$currentId]);
                continue;
            }

            $slug = strtolower(trim($cleanName));
            $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
            $slug = trim($slug, '-');
            if ($slug === '') {
                $slug = 'package-' . $currentId;
            }

            $renameCategory->execute([$cleanName, $slug, $currentId]);
        }
    } catch (Throwable $e) {
        // Keep the site available even if automatic repair cannot run.
    }
}

ensureCategoryDataIntegrity();

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function isLoggedIn()
{
    return !empty($_SESSION['user']);
}

function getCurrentUser()
{
    return $_SESSION['user'] ?? null;
}

function isAdmin()
{
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }

    if (!empty($user['role']) && strtolower($user['role']) === 'admin') {
        return true;
    }

    return strtolower($user['email'] ?? '') === 'admin@nepaltravel.com';
}
