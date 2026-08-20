<?php
require_once __DIR__ . '/../../Backend/database.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, name, province, district, description, history, best_time_to_visit, entry_fee, google_map, main_image, is_featured, status FROM places WHERE status = 'active' ORDER BY is_featured DESC, created_at DESC");
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for JS consumption
    $formatted = array_map(function($p) {
        return [
            'id' => $p['id'],
            'name' => $p['name'],
            'province' => $p['province'],
            'district' => $p['district'],
            'category' => strtolower($p['province']),
            'description' => $p['description'],
            'history' => $p['history'],
            'bestTimeToVisit' => $p['best_time_to_visit'],
            'entryFee' => (float) $p['entry_fee'],
            'googleMap' => $p['google_map'],
            // Map the old "heroImage" property to the editable image field
            'heroImage' => $p['main_image'] ? (str_starts_with($p['main_image'], '../') ? $p['main_image'] : '../img/places/' . $p['main_image']) : 'https://images.unsplash.com/photo-1544735716-392fe2489ffa',
            'slug' => strtolower(str_replace(' ', '-', $p['name'])),
            'province_id' => strtolower(str_replace(' ', '-', $p['province']))
        ];
    }, $places);
    
    echo json_encode(['success' => true, 'destinations' => $formatted]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
