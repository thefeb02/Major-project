<?php
require_once __DIR__ . '/../../Backend/database.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * The JSON file is the built-in public destination catalogue. It keeps every
 * category tab usable on a fresh install, before optional dashboard data has
 * been added to MySQL.
 */
function localDestinations(): array
{
    $catalogPath = __DIR__ . '/../destinations.json';
    if (!is_file($catalogPath)) {
        return [];
    }

    $catalog = json_decode((string) file_get_contents($catalogPath), true);
    return is_array($catalog['destinations'] ?? null) ? $catalog['destinations'] : [];
}

function localProvinces(): array
{
    $catalogPath = __DIR__ . '/../destinations.json';
    if (!is_file($catalogPath)) {
        return [];
    }

    $catalog = json_decode((string) file_get_contents($catalogPath), true);
    return is_array($catalog['provinces'] ?? null) ? $catalog['provinces'] : [];
}

try {
    $stmt = $pdo->query("SELECT id, name, province, place_category, district, description, history, best_time_to_visit, entry_fee, google_map, main_image, is_featured, status FROM places WHERE status = 'active' ORDER BY is_featured DESC, created_at DESC");
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $destinations = array_map(static function (array $place): array {
        return [
            'id' => $place['id'],
            'isManaged' => true,
            'name' => $place['name'],
            'province' => $place['province'],
            'district' => $place['district'],
            'category' => $place['place_category'] ?: 'provinces',
            'description' => $place['description'],
            'history' => $place['history'],
            'bestTimeToVisit' => $place['best_time_to_visit'],
            'entryFee' => (float) $place['entry_fee'],
            'googleMap' => $place['google_map'],
            'heroImage' => resolveFrontendImageUrl($place['main_image'] ?? null, 'places', '../img/1.jpeg'),
            'slug' => strtolower(str_replace(' ', '-', $place['name'])),
            'province_id' => strtolower(str_replace(' ', '-', $place['province'])),
        ];
    }, $places);
} catch (Throwable $e) {
    $destinations = [];
}

// Keep built-in destination details, but let a managed record override a
// matching destination so its admin-selected image appears without duplicates.
$catalogue = [];
foreach (localDestinations() as $destination) {
    $slug = strtolower((string) ($destination['slug'] ?? ''));
    if ($slug !== '') {
        $catalogue[$slug] = $destination;
    }
}
foreach ($destinations as $destination) {
    $slug = strtolower((string) ($destination['slug'] ?? ''));
    if ($slug === '') {
        continue;
    }
    $catalogue[$slug] = array_replace($catalogue[$slug] ?? [], $destination);
}
$destinations = array_values($catalogue);

echo json_encode([
    'success' => true,
    'source' => $destinations ? 'database-or-local-catalogue' : 'empty',
    'provinces' => localProvinces(),
    'destinations' => $destinations,
], JSON_UNESCAPED_SLASHES);
