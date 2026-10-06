<?php
require_once __DIR__ . '/../../Backend/database.php';

header('Content-Type: application/json; charset=utf-8');

function localPackages(): array
{
    global $pdo;
    global $packageDestinationImages;

    $templates = [
        ['Kathmandu Heritage Explorer', 'Kathmandu', 'Cultural', 4, 18500, '../img/3.jpeg'],
        ['Pokhara Adventure Escape', 'Pokhara', 'Adventure', 5, 32500, '../img/6.jpeg'],
        ['Lumbini Pilgrimage Journey', 'Lumbini', 'Pilgrimage', 4, 22000, '../img/5.jpeg'],
        ['Chitwan Nature and Wildlife Safari', 'Chitwan', 'Nature', 3, 19500, '../img/2.jpeg'],
        ['Nagarkot Honeymoon Retreat', 'Nagarkot', 'Honeymoon', 3, 28000, '../img/8.jpeg'],
        ['Bhaktapur Cultural Weekend', 'Bhaktapur', 'Cultural', 2, 12500, '../img/4.jpeg'],
        ['Everest View Trek', 'Solukhumbu', 'Adventure', 8, 56000, '../img/9.jpeg'],
        ['Janakpur Spiritual Tour', 'Janakpur', 'Pilgrimage', 3, 16500, '../img/1.jpeg'],
        ['Bandipur Family Holiday', 'Bandipur', 'Family', 4, 24500, '../img/7.png'],
        ['Kathmandu Student Education Tour', 'Kathmandu', 'Education', 4, 18500, '../img/3.jpeg'],
    ];

    $starterPackages = array_map(static fn (array $package, int $index): array => [
        'id' => 'local-' . ($index + 1),
        'title' => $package[0],
        'destination' => $package[1],
        'category' => $package[2],
        'days' => $package[3],
        'duration_text' => $package[3] . ' Days',
        'image' => $package[5],
        'price' => $package[4],
        'discount_price' => null,
        'description' => 'A locally curated Nepal travel package with accommodation, transport, and experienced guides.',
        'full_description' => 'Explore Nepal with a flexible itinerary designed by local travel experts.',
        'included_services' => 'Accommodation, transport, guide, and itinerary support',
        'excluded_services' => 'Personal expenses and optional activities',
        'itinerary' => 'Daily guided travel experience with flexible sightseeing time',
    ], $templates, array_keys($templates));

    /*
     * The places page contains the site's curated, real Nepal destination
     * catalogue.  Turn those records into bookable packages instead of
     * showing only the small starter set above.  Each record retains its own
     * destination photograph and description, so the image on a package card
     * represents the place the traveller is browsing.
     */
    $cataloguePath = __DIR__ . '/../destinations.json';
    if (!is_readable($cataloguePath)) {
        return $starterPackages;
    }

    try {
        $catalogue = json_decode((string) file_get_contents($cataloguePath), true, 512, JSON_THROW_ON_ERROR);
    } catch (Throwable $e) {
        return $starterPackages;
    }

    $destinations = $catalogue['destinations'] ?? [];
    if (!is_array($destinations)) {
        return $starterPackages;
    }

    $destinationsByName = [];
    foreach ($destinations as $destination) {
        if (!is_array($destination)) continue;
        $name = trim((string) ($destination['name'] ?? ''));
        if ($name !== '') {
            $destinationsByName[strtolower($name)] = $destination;
        }
    }
    try {
        $managedPlaces = $pdo->query("SELECT name, place_category, province, district, description, best_time_to_visit, main_image FROM places WHERE status = 'active' ORDER BY is_featured DESC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($managedPlaces as $place) {
            $name = trim((string) ($place['name'] ?? ''));
            if ($name === '') continue;

            $key = strtolower($name);
            $destination = $destinationsByName[$key] ?? [
                'name' => $name,
                'slug' => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-')),
                'galleryImages' => [],
            ];
            $destination['name'] = $name;
            $destination['category'] = (string) ($place['place_category'] ?? $destination['category'] ?? '');
            $destination['province'] = (string) ($place['province'] ?? $destination['province'] ?? '');
            $destination['district'] = (string) ($place['district'] ?? $destination['district'] ?? '');
            $destination['description'] = (string) ($place['description'] ?? $destination['description'] ?? '');
            $destination['bestSeason'] = (string) ($place['best_time_to_visit'] ?? $destination['bestSeason'] ?? '');
            $destination['heroImage'] = resolveFrontendImageUrl($place['main_image'] ?? null, 'places', $destination['heroImage'] ?? '../img/1.jpeg');
            $destination['isManaged'] = true;
            $destinationsByName[$key] = $destination;
        }
    } catch (Throwable $e) {
        // Keep the built-in catalogue available when dashboard place data is absent.
    }
    $destinations = array_values($destinationsByName);

    $imageCandidates = [];
    foreach ($destinations as $index => $destination) {
        $candidates = array_merge(
            [(string) ($destination['heroImage'] ?? '')],
            !empty($destination['isManaged']) ? [] : (is_array($destination['galleryImages'] ?? null) ? $destination['galleryImages'] : [])
        );
        $imageCandidates[$index] = array_values(array_unique(array_filter(
            array_map('trim', $candidates),
            static fn (string $image): bool => $image !== ''
        )));
    }
    $imageOwners = [];
    $assignImage = null;
    $assignImage = static function (int $destinationIndex, array &$visited) use (&$assignImage, &$imageOwners, $imageCandidates): bool {
        foreach ($imageCandidates[$destinationIndex] ?? [] as $image) {
            if (isset($visited[$image])) continue;
            $visited[$image] = true;
            if (!isset($imageOwners[$image]) || $assignImage($imageOwners[$image], $visited)) {
                $imageOwners[$image] = $destinationIndex;
                return true;
            }
        }
        return false;
    };
    $destinationImages = [];
    foreach (array_keys($destinations) as $index) {
        $visited = [];
        $assignImage($index, $visited);
    }
    foreach ($imageOwners as $image => $index) {
        $destinationImages[$index] = $image;
    }
    $packageDestinationImages = [];
    foreach ($destinationImages as $index => $image) {
        $packageDestinationImages[strtolower(trim((string) ($destinations[$index]['name'] ?? '')))] = $image;
    }

    $categoryFor = static function (string $categories): string {
        $categories = strtolower($categories);
        if (str_contains($categories, 'pilgrimage') || str_contains($categories, 'religion')) return 'Pilgrimage';
        if (str_contains($categories, 'wildlife') || str_contains($categories, 'protected') || str_contains($categories, 'nature')) return 'Nature';
        if (str_contains($categories, 'adventure') || str_contains($categories, 'trek') || str_contains($categories, 'hills')) return 'Adventure';
        if (str_contains($categories, 'heritage') || str_contains($categories, 'culture') || str_contains($categories, 'cities')) return 'Cultural';
        return 'Family';
    };
    $daysFor = static function (array $destination, int $offset = 0): int {
        $duration = (string) ($destination['duration'] ?? '');
        preg_match('/\d+/', $duration, $match);
        $baseDays = isset($match[0]) ? (int) $match[0] : 3;
        if (stripos((string) ($destination['difficulty'] ?? ''), 'moderate') !== false) $baseDays++;
        return max(2, min(10, $baseDays + $offset));
    };
    $priceFor = static function (string $category, int $days): int {
        $dailyRate = ['Adventure' => 7900, 'Nature' => 6200, 'Pilgrimage' => 4900, 'Cultural' => 5400, 'Family' => 5900][$category] ?? 5500;
        return $dailyRate * $days;
    };
    $packageFromDestination = static function (array $destination, int $number) use ($categoryFor, $daysFor, $priceFor): ?array {
        $name = trim((string) ($destination['name'] ?? ''));
        $image = trim((string) ($destination['heroImage'] ?? ''));
        if ($name === '' || $image === '') return null;

        $category = $categoryFor((string) ($destination['category'] ?? ''));
        $days = $daysFor($destination);
        $suffix = $category === 'Adventure' ? 'Adventure Escape' : 'Explorer';
        $description = trim((string) ($destination['description'] ?? ''));
        return [
            'id' => 'destination-' . $number,
            'title' => $name . ' ' . $suffix,
            'destination' => $name,
            'category' => $category,
            'days' => $days,
            'duration_text' => $days . ' Days',
            'image' => $image,
            'image_credit' => $destination['heroImageCredit'] ?? '',
            'image_credit_url' => $destination['heroImageCreditUrl'] ?? '',
            'price' => $priceFor($category, $days),
            'discount_price' => null,
            'description' => $description,
            'full_description' => $description,
            'included_services' => 'Accommodation, local transport, guide, and itinerary support',
            'excluded_services' => 'Personal expenses and optional activities',
            'itinerary' => 'A locally guided itinerary with flexible sightseeing time.',
        ];
    };

    $cataloguePackages = [];
    foreach ($destinations as $index => $destination) {
        if (!is_array($destination)) continue;
        if (isset($destinationImages[$index])) {
            $destination['heroImage'] = $destinationImages[$index];
        }
        $package = $packageFromDestination($destination, $index + 1);
        if ($package) $cataloguePackages[] = $package;
    }

    return array_merge($starterPackages, $cataloguePackages);
}

try {
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM packages p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' ORDER BY p.is_featured DESC, p.created_at DESC");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $formatted = array_map(static function (array $package): array {
        $image = $package['main_image'] ?: '../img/1.jpeg';
        if (!preg_match('#^(https?://|/|\.\./)#', $image)) {
            $image = '../img/packages/' . $image;
        }
        return [
            'id' => $package['id'], 'title' => $package['title'], 'destination' => $package['destination'],
            'category' => $package['category_name'] ?? 'General',
            'days' => (int) filter_var($package['duration'], FILTER_SANITIZE_NUMBER_INT) ?: 1,
            'duration_text' => $package['duration'], 'image' => $image, 'price' => (float) $package['price'],
            'discount_price' => $package['discount_price'] ? (float) $package['discount_price'] : null,
            'description' => $package['short_description'], 'full_description' => $package['full_description'] ?? '',
            'included_services' => $package['included_services'] ?? '', 'excluded_services' => $package['excluded_services'] ?? '',
            'itinerary' => $package['itinerary'] ?? '',
        ];
    }, $packages);
} catch (Throwable $e) {
    $formatted = [];
}

// Keep one package per destination, preferring later dashboard entries, and
// keep its image aligned with the active destination record when available.
$packageMap = [];
foreach (array_merge(localPackages(), $formatted) as $package) {
    $destinationName = strtolower(trim((string) ($package['destination'] ?? '')));
    $packageTitle = strtolower(trim((string) ($package['title'] ?? '')));
    if ($destinationName === 'hello' && $packageTitle === 'hello explorer') continue;
    $destinationKey = match ($destinationName) {
        'lalitpur (patan)' => 'patan durbar square',
        'lumbini - birthplace of buddha' => 'lumbini',
        'muktinath temple' => 'muktinath',
        default => $destinationName,
    };
    if ($destinationKey === '') continue;
    if (
        $destinationName === 'janaki temple'
        && isset($packageMap['janakpur'])
        && strtolower(trim((string) ($packageMap['janakpur']['title'] ?? ''))) === 'janakpur spiritual tour'
    ) {
        continue;
    }
    if (isset($packageDestinationImages[$destinationKey])) {
        $package['image'] = $packageDestinationImages[$destinationKey];
    }
    $packageMap[$destinationKey] = $package;
}
$formatted = array_values($packageMap);

// Match named packages to their actual featured place instead of category art.
$featuredPackageImages = [
    'kathmandu student education tour' => $packageDestinationImages['kathmandu city'] ?? '../img/8.jpeg',
    'lumbini pilgrimage journey' => '../img/places/lumbini-maya-devi-temple.jpg',
    'lumbini - birthplace of buddha explorer' => '../img/places/lumbini-maya-devi-temple.jpg',
    'muktinath pilgrimage journey' => '../img/places/muktinath-temple.jpg',
    'muktinath temple explorer' => '../img/places/muktinath-temple.jpg',
    'janakpur spiritual tour' => $packageDestinationImages['janaki temple'] ?? 'https://chinarinepal.com/wp-content/uploads/2022/03/JANAKI-MANDIR-1024x386.png',
];
$featuredPackageImageCredits = [
    '../img/places/lumbini-maya-devi-temple.jpg' => [
        'credit' => 'Rangan Datta Wiki / Wikimedia Commons (CC BY-SA 4.0)',
        'url' => 'https://commons.wikimedia.org/wiki/File:Lumbini_Maya_Devi_Temple_1.jpg',
    ],
    '../img/places/muktinath-temple.jpg' => [
        'credit' => 'Ushanpathak / Wikimedia Commons (CC BY-SA 4.0)',
        'url' => 'https://commons.wikimedia.org/wiki/File:Image_of_Muktinath_temple.jpg',
    ],
];
$reservedPackageImages = [];
foreach ($formatted as &$package) {
    $titleKey = strtolower(trim((string) ($package['title'] ?? '')));
    if (isset($featuredPackageImages[$titleKey])) {
        $package['image'] = $featuredPackageImages[$titleKey];
        $package['image_credit'] = $featuredPackageImageCredits[$package['image']]['credit'] ?? '';
        $package['image_credit_url'] = $featuredPackageImageCredits[$package['image']]['url'] ?? '';
        $reservedPackageImages[$package['image']] = true;
    }
}
unset($package);

// Some different destination records reuse the same source photo. Give those
// remaining cards distinct local photos rather than repeating the same image.
$usedImages = [];
$localImageFallbacks = ['../img/4.jpeg', '../img/6.jpeg', '../img/7.png', '../img/8.jpeg', '../img/9.jpeg', '../img/1.jpeg', '../img/2.jpeg', '../img/3.jpeg', '../img/5.jpeg'];
foreach ($formatted as &$package) {
    $image = trim((string) ($package['image'] ?? ''));
    $titleKey = strtolower(trim((string) ($package['title'] ?? '')));
    if (isset($featuredPackageImages[$titleKey])) {
        $usedImages[$image] = true;
        continue;
    }
    if (isset($usedImages[$image]) || isset($reservedPackageImages[$image])) {
        $categoryFallbacks = match ($package['category'] ?? '') {
            'Nature' => ['../img/2.jpeg', '../img/4.jpeg', '../img/6.jpeg', '../img/7.png'],
            'Adventure' => ['../img/6.jpeg', '../img/7.png', '../img/9.jpeg'],
            'Cultural', 'Pilgrimage' => ['../img/4.jpeg', '../img/7.png', '../img/8.jpeg'],
            default => $localImageFallbacks,
        };
        foreach (array_unique(array_merge($categoryFallbacks, $localImageFallbacks)) as $fallbackImage) {
            if (!isset($usedImages[$fallbackImage])) {
                $image = $fallbackImage;
                $package['image'] = $image;
                break;
            }
        }
    }
    $usedImages[$image] = true;
}
unset($package);

echo json_encode(['success' => true, 'data' => $formatted, 'source' => 'database-or-local-catalogue'], JSON_UNESCAPED_SLASHES);
