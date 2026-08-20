<?php
require_once __DIR__ . '/../../Backend/database.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM packages p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' ORDER BY p.is_featured DESC, p.created_at DESC");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categoryStmt = $pdo->query("SELECT id, name, slug, image_url, description, sort_order, is_featured FROM categories WHERE type = 'package' AND status = 'active' ORDER BY is_featured DESC, sort_order ASC, name ASC");
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for JS consumption
    $formatted = array_map(function($p) {
        $image = $p['main_image'] ?: '';
        if ($image !== '' && !preg_match('#^(https?://|/|\.\./)#', $image)) {
            $image = '../img/packages/' . $image;
        }

        return [
            'id' => $p['id'],
            'title' => $p['title'],
            'destination' => $p['destination'],
            'category' => $p['category_name'] ?? 'General',
            'days' => (int) filter_var($p['duration'], FILTER_SANITIZE_NUMBER_INT) ?: 1, // extract number
            'duration_text' => $p['duration'],
            'image' => $image !== '' ? $image : '../img/1.jpeg',
            'price' => (float) $p['price'],
            'discount_price' => $p['discount_price'] ? (float) $p['discount_price'] : null,
            'description' => $p['short_description'],
            'full_description' => $p['full_description'] ?? '',
            'included_services' => $p['included_services'] ?? '',
            'excluded_services' => $p['excluded_services'] ?? '',
            'itinerary' => $p['itinerary'] ?? '',
        ];
    }, $packages);
    
    echo json_encode(['success' => true, 'data' => $formatted, 'categories' => $categories]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
