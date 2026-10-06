<?php
require_once __DIR__ . '/database.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Administrator access is required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'POST requests only.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || !hash_equals($_SESSION['admin_csrf'] ?? '', (string) ($input['csrf'] ?? ''))) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Invalid request token.']);
    exit;
}

function adminApiValue(array $input, string $key, int $max = 0): string
{
    $value = trim((string) ($input[$key] ?? ''));
    return $max ? mb_substr($value, 0, $max) : $value;
}

function adminApiSlug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    return trim($slug, '-');
}

function adminApiCategorySlug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    return trim($slug, '-');
}

function adminApiCategoryName(array $input, string $key, int $max = 100): string
{
    $raw = $input[$key] ?? '';

    if (is_array($raw)) {
        $candidate = trim((string) ($raw['name'] ?? ''));
        return $max ? mb_substr($candidate, 0, $max) : $candidate;
    }

    $value = trim((string) $raw);
    if ($value === '') {
        return '';
    }

    $parsed = json_decode($value, true);
    if (is_array($parsed) && isset($parsed['name'])) {
        $value = trim((string) $parsed['name']);
    }

    return $max ? mb_substr($value, 0, $max) : $value;
}

function adminApiUploadImage(array $input, string $imageDataKey, string $imageUrlKey, string $directoryName): string
{
    $imageUrl = adminApiValue($input, $imageUrlKey, 500);
    $imageData = (string) ($input[$imageDataKey] ?? '');

    if ($imageData === '') {
        return $imageUrl;
    }

    if (!preg_match('#^data:image/(png|jpe?g|gif|webp);base64,(.+)$#s', $imageData, $matches)) {
        throw new InvalidArgumentException('Upload a PNG, JPG, GIF, or WebP image.');
    }

    $binary = base64_decode($matches[2], true);
    if ($binary === false || strlen($binary) > 5 * 1024 * 1024 || @getimagesizefromstring($binary) === false) {
        throw new InvalidArgumentException('The uploaded image is invalid or larger than 5 MB.');
    }

    $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
    $directory = __DIR__ . '/../img/' . $directoryName;
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to prepare the upload folder.');
    }

    $filename = bin2hex(random_bytes(12)) . '.' . $extension;
    if (file_put_contents($directory . '/' . $filename, $binary, LOCK_EX) === false) {
        throw new RuntimeException('Unable to save the image.');
    }

    return '../img/' . $directoryName . '/' . $filename;
}

function adminApiResponse(array $data = []): never
{
    echo json_encode(['ok' => true] + $data);
    exit;
}

try {
    $action = $input['action'] ?? '';

    if ($action === 'create_package') {
        $title = adminApiValue($input, 'title', 190);
        $destination = adminApiValue($input, 'destination', 120);
        $category = adminApiCategoryName($input, 'category', 100);
        $duration = adminApiValue($input, 'duration', 60);
        $price = max(0, (float) ($input['price'] ?? 0));
        $shortDesc = adminApiValue($input, 'shortDescription') ?: 'Package short description';
        $fullDesc = adminApiValue($input, 'fullDescription') ?: 'Package full description';

        $imageUrl = adminApiUploadImage($input, 'imageData', 'imageUrl', 'packages');

        if (!$title || !$destination || !$category || !$duration) throw new InvalidArgumentException('Complete all package fields.');
        
        $catStmt = $pdo->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
        $catStmt->execute([$category]);
        $catId = $catStmt->fetchColumn();
        if (!$catId) {
            $slug = adminApiSlug($category) ?: 'package-' . bin2hex(random_bytes(4));
            $insertCategory = $pdo->prepare('INSERT INTO categories (name, slug, type) VALUES (?, ?, \'package\') ON DUPLICATE KEY UPDATE name = VALUES(name)');
            $insertCategory->execute([$category, $slug]);
            $catStmt->execute([$category]);
            $catId = $catStmt->fetchColumn();
        }

        $stmt = $pdo->prepare('INSERT INTO packages (title, destination, category_id, duration, price, main_image, short_description, full_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $destination, $catId ?: null, $duration, $price, $imageUrl ?: null, $shortDesc, $fullDesc]);
        adminApiResponse(['id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'update_package') {
        $packageId = (int) ($input['id'] ?? 0);
        if ($packageId <= 0) throw new InvalidArgumentException('Package not found.');

        $title = adminApiValue($input, 'title', 190);
        $destination = adminApiValue($input, 'destination', 120);
        $category = adminApiCategoryName($input, 'category', 100);
        $duration = adminApiValue($input, 'duration', 60);
        $price = max(0, (float) ($input['price'] ?? 0));
        $shortDesc = adminApiValue($input, 'shortDescription') ?: 'Package short description';
        $fullDesc = adminApiValue($input, 'fullDescription') ?: 'Package full description';
        $status = $input['status'] ?? 'active';
        $isFeatured = !empty($input['isFeatured']) ? 1 : 0;

        if (!$title || !$destination || !$category || !$duration) throw new InvalidArgumentException('Complete all package fields.');

        $catStmt = $pdo->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
        $catStmt->execute([$category]);
        $catId = $catStmt->fetchColumn();
        if (!$catId) {
            $slug = adminApiSlug($category) ?: 'package-' . bin2hex(random_bytes(4));
            $insertCategory = $pdo->prepare('INSERT INTO categories (name, slug, type) VALUES (?, ?, \'package\') ON DUPLICATE KEY UPDATE name = VALUES(name)');
            $insertCategory->execute([$category, $slug]);
            $catStmt->execute([$category]);
            $catId = $catStmt->fetchColumn();
        }

        $imageUrl = adminApiValue($input, 'imageUrl', 500);
        if (!empty($input['imageData'] ?? '')) {
            $imageUrl = adminApiUploadImage($input, 'imageData', 'imageUrl', 'packages');
        }

        $stmt = $pdo->prepare('UPDATE packages SET title = ?, destination = ?, category_id = ?, duration = ?, price = ?, short_description = ?, full_description = ?, main_image = COALESCE(NULLIF(?, \'\'), main_image), status = ?, is_featured = ? WHERE id = ?');
        $stmt->execute([$title, $destination, $catId ?: null, $duration, $price, $shortDesc, $fullDesc, $imageUrl, in_array($status, ['active', 'inactive'], true) ? $status : 'active', $isFeatured, $packageId]);
        adminApiResponse(['id' => $packageId]);
    }

    if ($action === 'delete_package') {
        $stmt = $pdo->prepare('DELETE FROM packages WHERE id = ?');
        $stmt->execute([(int) ($input['id'] ?? 0)]);
        adminApiResponse();
    }

    if ($action === 'create_category') {
        $name = adminApiValue($input, 'name', 100);
        if ($name === '') throw new InvalidArgumentException('Provide a category name.');
        $slug = adminApiCategorySlug(adminApiValue($input, 'slug', 100) ?: $name) ?: 'category-' . bin2hex(random_bytes(4));
        $description = adminApiValue($input, 'description');
        $sortOrder = (int) ($input['sortOrder'] ?? 0);
        $isFeatured = !empty($input['isFeatured']) ? 1 : 0;
        $status = in_array(($input['status'] ?? 'active'), ['active', 'inactive'], true) ? (string) $input['status'] : 'active';
        $imageUrl = adminApiUploadImage($input, 'imageData', 'imageUrl', 'categories');

        $stmt = $pdo->prepare('INSERT INTO categories (name, slug, type, image_url, description, sort_order, is_featured, status) VALUES (?, ?, \'package\', ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $slug, $imageUrl ?: null, $description ?: null, $sortOrder, $isFeatured, $status]);
        adminApiResponse(['id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'update_category') {
        $categoryId = (int) ($input['id'] ?? 0);
        if ($categoryId <= 0) throw new InvalidArgumentException('Category not found.');
        $name = adminApiValue($input, 'name', 100);
        if ($name === '') throw new InvalidArgumentException('Provide a category name.');
        $slug = adminApiCategorySlug(adminApiValue($input, 'slug', 100) ?: $name) ?: 'category-' . bin2hex(random_bytes(4));
        $description = adminApiValue($input, 'description');
        $sortOrder = (int) ($input['sortOrder'] ?? 0);
        $isFeatured = !empty($input['isFeatured']) ? 1 : 0;
        $status = in_array(($input['status'] ?? 'active'), ['active', 'inactive'], true) ? (string) $input['status'] : 'active';
        $imageUrl = adminApiValue($input, 'imageUrl', 500);
        if (!empty($input['imageData'] ?? '')) {
            $imageUrl = adminApiUploadImage($input, 'imageData', 'imageUrl', 'categories');
        }

        $stmt = $pdo->prepare('UPDATE categories SET name = ?, slug = ?, image_url = COALESCE(NULLIF(?, \'\'), image_url), description = ?, sort_order = ?, is_featured = ?, status = ? WHERE id = ?');
        $stmt->execute([$name, $slug, $imageUrl, $description ?: null, $sortOrder, $isFeatured, $status, $categoryId]);
        adminApiResponse(['id' => $categoryId]);
    }

    if ($action === 'delete_category') {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([(int) ($input['id'] ?? 0)]);
        adminApiResponse();
    }

    if ($action === 'create_gallery') {
        $title = adminApiValue($input, 'title', 190);
        $imageUrl = adminApiUploadImage($input, 'imageData', 'imageUrl', 'gallery');
        if (!$title || !$imageUrl || (!str_starts_with($imageUrl, '../img/') && !filter_var($imageUrl, FILTER_VALIDATE_URL))) throw new InvalidArgumentException('Provide a title and a valid image URL or upload an image.');
        $stmt = $pdo->prepare('INSERT INTO gallery (title, image_url) VALUES (?, ?)');
        $stmt->execute([$title, $imageUrl]);
        adminApiResponse(['id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'update_gallery') {
        $galleryId = (int) ($input['id'] ?? 0);
        $title = adminApiValue($input, 'title', 190);
        $imageUrl = adminApiValue($input, 'imageUrl', 500);
        if (!empty($input['imageData'] ?? '')) {
            $imageUrl = adminApiUploadImage($input, 'imageData', 'imageUrl', 'gallery');
        }
        if ($galleryId <= 0 || !$title) throw new InvalidArgumentException('Provide a gallery title.');
        $stmt = $pdo->prepare('UPDATE gallery SET title = ?, image_url = COALESCE(NULLIF(?, \'\'), image_url), category = ? WHERE id = ?');
        $stmt->execute([$title, $imageUrl, adminApiValue($input, 'altText', 100), $galleryId]);
        adminApiResponse(['id' => $galleryId]);
    }

    if ($action === 'delete_website_image') {
        $relativePath = str_replace('\\', '/', adminApiValue($input, 'path', 500));
        if (!$relativePath || str_contains($relativePath, '..') || !preg_match('/\.(jpe?g|png|gif|webp)$/i', $relativePath)) {
            throw new InvalidArgumentException('Invalid website image path.');
        }

        $imageRoot = realpath(__DIR__ . '/../img');
        $imagePath = $imageRoot ? realpath($imageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath)) : false;
        $rootPrefix = $imageRoot ? rtrim(str_replace('\\', '/', $imageRoot), '/') . '/' : '';
        $normalizedImagePath = $imagePath ? str_replace('\\', '/', $imagePath) : '';

        if (!$imageRoot || !$imagePath || !str_starts_with($normalizedImagePath, $rootPrefix) || !is_file($imagePath)) {
            throw new InvalidArgumentException('Website image was not found.');
        }
        if (!unlink($imagePath)) throw new RuntimeException('Unable to delete the website image.');
        adminApiResponse();
    }

    if ($action === 'delete_gallery') {
        $stmt = $pdo->prepare('DELETE FROM gallery WHERE id = ?');
        $stmt->execute([(int) ($input['id'] ?? 0)]);
        adminApiResponse();
    }

    if ($action === 'create_payment') {
        $customer = adminApiValue($input, 'customer', 120);
        $type = $input['type'] ?? 'Transaction';
        if (!$customer || !in_array($type, ['Transaction', 'Invoice', 'Refund'], true)) throw new InvalidArgumentException('Provide valid payment details.');
        $stmt = $pdo->prepare('INSERT INTO payments (booking_id, customer_name, type, amount, status, payment_date) VALUES (?, ?, ?, ?, ?, CURDATE())');
        $stmt->execute([(int) ($input['bookingId'] ?? 0) ?: null, $customer, $type, max(0, (float) ($input['amount'] ?? 0)), 'Processed']);
        adminApiResponse(['id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'update_booking_status') {
        $status = strtolower((string) ($input['status'] ?? ''));
        $allowedStatuses = ['pending', 'confirmed', 'hold', 'cancelled', 'completed'];
        if (!in_array($status, $allowedStatuses, true)) throw new InvalidArgumentException('Invalid booking status.');

        $bookingId = (int) ($input['id'] ?? 0);
        $bookingStmt = $pdo->prepare('SELECT b.full_name, b.email, b.phone, p.title as service_name, b.travel_date, b.status AS current_status FROM bookings b LEFT JOIN packages p ON b.package_id = p.id WHERE b.id = ?');
        $bookingStmt->execute([$bookingId]);
        $booking = $bookingStmt->fetch();
        if (!$booking) throw new InvalidArgumentException('Booking not found.');

        $currentStatus = strtolower((string) ($booking['current_status'] ?? 'pending'));
        if ($currentStatus === 'confirmed' && $status !== 'confirmed') {
            throw new InvalidArgumentException('This booking is already confirmed and cannot be changed.');
        }

        $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $stmt->execute([$status, $bookingId]);

        $statusLabel = ucfirst($status);
        $subject = 'Booking status update: ' . ($booking['service_name'] ?? 'Your Booking');
        $message = "Hello {$booking['full_name']},\n\nYour booking for " . ($booking['service_name'] ?? 'your package') . " on {$booking['travel_date']} is now: {$statusLabel}.\n\nThank you,\nNepal Tour and Travel";
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nFrom: Nepal Tour and Travel <no-reply@localhost>\r\n";
        $emailSent = filter_var($booking['email'], FILTER_VALIDATE_EMAIL) ? @mail($booking['email'], $subject, $message, $headers) : false;
        adminApiResponse(['emailSent' => $emailSent, 'phone' => $booking['phone']]);
    }

    if ($action === 'update_message_status') {
        $status = $input['status'] ?? '';
        if (!in_array($status, ['unread', 'read', 'replied'], true)) throw new InvalidArgumentException('Invalid message status.');
        $stmt = $pdo->prepare('UPDATE contacts SET status = ? WHERE id = ?');
        $stmt->execute([$status, (int) ($input['id'] ?? 0)]);
        adminApiResponse();
    }

    if ($action === 'save_settings') {
        $settings = $input['settings'] ?? [];
        if (!is_array($settings)) throw new InvalidArgumentException('Invalid settings.');
        $allowed = ['site_name', 'logo_url', 'favicon_url', 'hero_image_url', 'contact_email', 'contact_phone', 'address', 'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url', 'seo_title', 'seo_description', 'seo_keywords', 'footer_text', 'homepage_hero'];
        $stmt = $pdo->prepare('INSERT INTO website_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($allowed as $key) {
            if (array_key_exists($key, $settings)) $stmt->execute([$key, mb_substr(trim((string) $settings[$key]), 0, 2000)]);
        }
        adminApiResponse();
    }

    if ($action === 'save_homepage_sections') {
        $sections = $input['sections'] ?? [];
        if (!is_array($sections)) throw new InvalidArgumentException('Invalid section payload.');
        $stmt = $pdo->prepare('INSERT INTO homepage_sections (section_key, title, subtitle, is_enabled, sort_order) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), subtitle = VALUES(subtitle), is_enabled = VALUES(is_enabled), sort_order = VALUES(sort_order)');
        foreach ($sections as $sectionKey => $section) {
            if (!is_array($section)) continue;
            $stmt->execute([
                mb_substr((string) $sectionKey, 0, 100),
                mb_substr(trim((string) ($section['title'] ?? '')), 0, 150),
                mb_substr(trim((string) ($section['subtitle'] ?? '')), 0, 255),
                !empty($section['is_enabled']) ? 1 : 0,
                (int) ($section['sort_order'] ?? 0),
            ]);
        }
        adminApiResponse();
    }

    if ($action === 'create_place') {
        $name = adminApiValue($input, 'name', 150);
        $province = adminApiValue($input, 'province', 100);
        $placeCategories = array_values(array_unique(array_filter(array_map('trim', explode(',', adminApiValue($input, 'placeCategory', 50) ?: 'provinces')))));
        $placeCategory = implode(',', $placeCategories);
        $district = adminApiValue($input, 'district', 100);
        $description = adminApiValue($input, 'description');
        $history = adminApiValue($input, 'history');
        $bestTime = adminApiValue($input, 'bestTimeToVisit', 150);
        $entryFee = max(0, (float) ($input['entryFee'] ?? 0));
        $googleMap = adminApiValue($input, 'googleMap');
        $imageUrl = adminApiUploadImage($input, 'imageData', 'imageUrl', 'places');
        $isFeatured = !empty($input['isFeatured']) ? 1 : 0;
        $status = in_array(($input['status'] ?? 'active'), ['active', 'inactive'], true) ? (string) $input['status'] : 'active';

        if (!$name) throw new InvalidArgumentException('Enter a place name.');
        $validProvinces = ['Koshi', 'Madhesh', 'Bagmati', 'Gandaki', 'Lumbini', 'Karnali', 'Sudurpashchim'];
        $validPlaceCategories = ['provinces', 'heritage', 'protected', 'cities', 'peaks', 'pilgrimage', 'hills'];
        if (!$placeCategories || array_diff($placeCategories, $validPlaceCategories)) throw new InvalidArgumentException('Select a valid Places to Go button.');
        if (in_array('provinces', $placeCategories, true) && !in_array($province, $validProvinces, true)) throw new InvalidArgumentException('Select one of Nepal\'s seven provinces.');
        if (!$province) $province = 'Nepal';

        $stmt = $pdo->prepare('INSERT INTO places (name, province, place_category, district, description, history, best_time_to_visit, entry_fee, google_map, main_image, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $province, $placeCategory, $district, $description ?: null, $history ?: null, $bestTime ?: null, $entryFee, $googleMap ?: null, $imageUrl ?: null, $isFeatured, $status]);
        adminApiResponse(['id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'update_place') {
        $placeId = (int) ($input['id'] ?? 0);
        if ($placeId <= 0) throw new InvalidArgumentException('Place not found.');

        $name = adminApiValue($input, 'name', 150);
        $province = adminApiValue($input, 'province', 100);
        $placeCategories = array_values(array_unique(array_filter(array_map('trim', explode(',', adminApiValue($input, 'placeCategory', 50) ?: 'provinces')))));
        $placeCategory = implode(',', $placeCategories);
        $district = adminApiValue($input, 'district', 100);
        $description = adminApiValue($input, 'description');
        $history = adminApiValue($input, 'history');
        $bestTime = adminApiValue($input, 'bestTimeToVisit', 150);
        $entryFee = max(0, (float) ($input['entryFee'] ?? 0));
        $googleMap = adminApiValue($input, 'googleMap');
        $imageUrl = adminApiValue($input, 'imageUrl', 500);
        if (!empty($input['imageData'] ?? '')) {
            $imageUrl = adminApiUploadImage($input, 'imageData', 'imageUrl', 'places');
        }
        $isFeatured = !empty($input['isFeatured']) ? 1 : 0;
        $status = in_array(($input['status'] ?? 'active'), ['active', 'inactive'], true) ? (string) $input['status'] : 'active';

        if (!$name) throw new InvalidArgumentException('Enter a place name.');
        $validProvinces = ['Koshi', 'Madhesh', 'Bagmati', 'Gandaki', 'Lumbini', 'Karnali', 'Sudurpashchim'];
        $validPlaceCategories = ['provinces', 'heritage', 'protected', 'cities', 'peaks', 'pilgrimage', 'hills'];
        if (!$placeCategories || array_diff($placeCategories, $validPlaceCategories)) throw new InvalidArgumentException('Select a valid Places to Go button.');
        if (in_array('provinces', $placeCategories, true) && !in_array($province, $validProvinces, true)) throw new InvalidArgumentException('Select one of Nepal\'s seven provinces.');
        if (!$province) $province = 'Nepal';

        $stmt = $pdo->prepare('UPDATE places SET name = ?, province = ?, place_category = ?, district = ?, description = ?, history = ?, best_time_to_visit = ?, entry_fee = ?, google_map = ?, main_image = COALESCE(NULLIF(?, \'\'), main_image), is_featured = ?, status = ? WHERE id = ?');
        $stmt->execute([$name, $province, $placeCategory, $district, $description ?: null, $history ?: null, $bestTime ?: null, $entryFee, $googleMap ?: null, $imageUrl, $isFeatured, $status, $placeId]);
        adminApiResponse(['id' => $placeId]);
    }

    if ($action === 'delete_place') {
        $stmt = $pdo->prepare('DELETE FROM places WHERE id = ?');
        $stmt->execute([(int) ($input['id'] ?? 0)]);
        adminApiResponse();
    }

    if ($action === 'create_activity' || $action === 'update_activity') {
        $activityId = (int) ($input['id'] ?? 0);
        $name = adminApiValue($input, 'name', 150);
        $category = adminApiValue($input, 'category', 100);
        $description = adminApiValue($input, 'description', 1000);
        $pageUrl = adminApiValue($input, 'pageUrl', 500);
        $status = in_array(($input['status'] ?? 'active'), ['active', 'inactive'], true) ? (string) $input['status'] : 'active';
        $imageUrl = $action === 'create_activity'
            ? adminApiUploadImage($input, 'imageData', 'imageUrl', 'activities')
            : adminApiValue($input, 'imageUrl', 500);
        if (!empty($input['imageData'] ?? '') && $action === 'update_activity') {
            $imageUrl = adminApiUploadImage($input, 'imageData', 'imageUrl', 'activities');
        }

        if (!$name || !$category || !$description) throw new InvalidArgumentException('Name, category, and description are required.');

        if ($action === 'create_activity') {
            $stmt = $pdo->prepare('INSERT INTO activities (name, category, description, image_url, page_url, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $category, $description, $imageUrl ?: null, $pageUrl ?: null, $status]);
            adminApiResponse(['id' => (int) $pdo->lastInsertId()]);
        }

        if ($activityId <= 0) throw new InvalidArgumentException('Activity not found.');
        $stmt = $pdo->prepare('UPDATE activities SET name = ?, category = ?, description = ?, image_url = COALESCE(NULLIF(?, \'\'), image_url), page_url = ?, status = ? WHERE id = ?');
        $stmt->execute([$name, $category, $description, $imageUrl, $pageUrl ?: null, $status, $activityId]);
        adminApiResponse(['id' => $activityId]);
    }

    if ($action === 'delete_activity') {
        $stmt = $pdo->prepare('DELETE FROM activities WHERE id = ?');
        $stmt->execute([(int) ($input['id'] ?? 0)]);
        adminApiResponse();
    }

    throw new InvalidArgumentException('Unknown admin action.');
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Unable to save this change. Import the latest database.sql and try again.']);
}
