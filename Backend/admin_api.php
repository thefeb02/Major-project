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
        $category = adminApiValue($input, 'category', 100);
        $duration = adminApiValue($input, 'duration', 60);
        if (!$title || !$destination || !$category || !$duration) throw new InvalidArgumentException('Complete all package fields.');
        $stmt = $pdo->prepare('INSERT INTO tour_packages (title, destination, category, duration, price, image_url, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $destination, $category, $duration, max(0, (float) ($input['price'] ?? 0)), adminApiValue($input, 'imageUrl', 500) ?: null, adminApiValue($input, 'description') ?: null]);
        adminApiResponse(['id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'create_gallery') {
        $title = adminApiValue($input, 'title', 190);
        $imageUrl = adminApiValue($input, 'imageUrl', 500);
        $imageData = (string) ($input['imageData'] ?? '');
        if ($imageData !== '') {
            if (!preg_match('#^data:image/(png|jpe?g|gif|webp);base64,(.+)$#s', $imageData, $matches)) throw new InvalidArgumentException('Upload a PNG, JPG, GIF, or WebP image.');
            $binary = base64_decode($matches[2], true);
            if ($binary === false || strlen($binary) > 5 * 1024 * 1024 || @getimagesizefromstring($binary) === false) throw new InvalidArgumentException('The uploaded image is invalid or larger than 5 MB.');
            $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
            $directory = __DIR__ . '/../img/gallery';
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new RuntimeException('Unable to prepare the gallery folder.');
            $filename = bin2hex(random_bytes(12)) . '.' . $extension;
            if (file_put_contents($directory . '/' . $filename, $binary, LOCK_EX) === false) throw new RuntimeException('Unable to save the image.');
            $imageUrl = '../img/gallery/' . $filename;
        }
        if (!$title || !$imageUrl || (!str_starts_with($imageUrl, '../img/') && !filter_var($imageUrl, FILTER_VALIDATE_URL))) throw new InvalidArgumentException('Provide a title and a valid image URL or upload an image.');
        $stmt = $pdo->prepare('INSERT INTO gallery_images (title, image_url, alt_text) VALUES (?, ?, ?)');
        $stmt->execute([$title, $imageUrl, adminApiValue($input, 'altText', 190) ?: null]);
        adminApiResponse(['id' => (int) $pdo->lastInsertId()]);
    }

    if ($action === 'delete_gallery') {
        $stmt = $pdo->prepare('DELETE FROM gallery_images WHERE id = ?');
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
        $status = $input['status'] ?? '';
        if (!in_array($status, ['pending', 'approved', 'rejected', 'confirmed', 'cancelled'], true)) throw new InvalidArgumentException('Invalid booking status.');
        $stmt = $pdo->prepare('UPDATE service_bookings SET status = ? WHERE id = ?');
        $stmt->execute([$status, (int) ($input['id'] ?? 0)]);
        adminApiResponse();
    }

    if ($action === 'update_message_status') {
        $status = $input['status'] ?? '';
        if (!in_array($status, ['new', 'read', 'replied', 'archived'], true)) throw new InvalidArgumentException('Invalid message status.');
        $stmt = $pdo->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
        $stmt->execute([$status, (int) ($input['id'] ?? 0)]);
        adminApiResponse();
    }

    if ($action === 'save_settings') {
        $settings = $input['settings'] ?? [];
        if (!is_array($settings)) throw new InvalidArgumentException('Invalid settings.');
        $allowed = ['site_name', 'contact_email', 'contact_phone', 'address', 'facebook_url', 'twitter_url', 'seo_title', 'seo_keywords', 'homepage_hero'];
        $stmt = $pdo->prepare('INSERT INTO website_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($allowed as $key) {
            if (array_key_exists($key, $settings)) $stmt->execute([$key, mb_substr(trim((string) $settings[$key]), 0, 2000)]);
        }
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
