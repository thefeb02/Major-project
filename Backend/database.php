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
