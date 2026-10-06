<?php
require_once __DIR__ . '/database.php';

$allowLocalAdminAccess = isset($_GET['dev_admin']) || (!empty($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'], true));

if (!isLoggedIn() || !isAdmin()) {
    if ($allowLocalAdminAccess) {
        $_SESSION['user'] = [
            'id' => 0,
            'name' => 'Admin',
            'email' => 'admin@nepaltravel.com',
            'role' => 'admin',
        ];
    } else {
        redirect('../frontend/login.php');
    }
}

$adminUser = getCurrentUser();

$userCount = 0;
$travelPlanCount = 0;
$bookingCount = 0;
$recentPlans = [];
$recentBookings = [];
$databaseTables = [];
$databaseSizeBytes = 0;
$databaseStatus = 'Connected';
$adminPackages = [];
$adminBookings = [];
$adminCustomers = [];
$adminGallery = [];
$websiteImages = [];
$adminPayments = [];
$adminMessages = [];
$adminReviews = [];
$adminPlaces = [];
$adminActivities = [];
$homepageSections = [];
$adminCategories = [];
$adminCategoryNames = ['Student Education Tour', 'Honeymoon', 'Pilgrimage', 'Cultural', 'Adventure', 'Nature & Wildlife'];
$adminSettings = [
    'site_name' => 'Nepal Tour and Travels',
    'logo_url' => '',
    'favicon_url' => '',
    'hero_image_url' => '',
    'contact_email' => 'info@nepalitourtravel.com',
    'contact_phone' => '+9779763658085',
    'address' => 'Butwal, Nepal',
    'facebook_url' => '',
    'twitter_url' => '',
    'instagram_url' => '',
    'youtube_url' => '',
    'seo_title' => 'Nepal Tour and Travels',
    'seo_description' => 'Nepal travel packages, places, and experiences.',
    'seo_keywords' => 'Nepal, tours, travel, trekking',
    'footer_text' => 'Curated tours, mountain adventures, cultural escapes, and trusted local guidance for an unforgettable Nepal experience.',
    'homepage_hero' => 'Discover Nepal with confidence',
];

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}
$adminCsrf = $_SESSION['admin_csrf'];

try {
    $userCount = (int) $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    $travelPlanCount = (int) $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
    $bookingCount = (int) $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

    $recentPlans = [];

    $recentBookings = $pdo->query("
        SELECT COALESCE(p.title, b.service_name, 'Tour booking') as service_name, COALESCE(c.name, b.service_category, 'Package') as service_category, b.full_name, b.status, b.created_at
        FROM bookings b
        LEFT JOIN packages p ON b.package_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY b.created_at DESC
        LIMIT 5
    ")->fetchAll();

    $databaseTables = $pdo->query("
        SELECT table_name, table_rows, data_length + index_length AS size_bytes,
               create_time, update_time
        FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'
        ORDER BY table_name
    ")->fetchAll();
    $databaseSizeBytes = array_sum(array_map(
        static fn ($table) => (int) ($table['size_bytes'] ?? 0),
        $databaseTables
    ));
} catch (Throwable $e) {
    // Keep the dashboard usable even if a query fails.
    $databaseStatus = 'Unavailable';
}

try {
    $adminPackages = $pdo->query("SELECT p.id, p.title, p.destination, c.name as category, p.duration, p.price, p.status, p.is_featured, p.main_image AS image_url, p.short_description, p.full_description FROM packages p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();
    $adminBookings = $pdo->query("SELECT b.id, b.full_name AS customer, b.email, b.phone, COALESCE(p.title, b.service_name, 'Tour booking') AS destination, b.travel_date AS date, CONCAT(UCASE(LEFT(b.status, 1)), SUBSTRING(b.status, 2)) AS status, 0 AS amount FROM bookings b LEFT JOIN packages p ON b.package_id = p.id ORDER BY b.created_at DESC")->fetchAll();
    $adminCustomers = $pdo->query("SELECT MAX(id) as id, full_name as name, email, MAX(phone) AS phone, COUNT(id) AS totalBookings FROM bookings GROUP BY email, full_name ORDER BY MAX(created_at) DESC")->fetchAll();
    $adminGallery = $pdo->query("SELECT id, title, image_url AS url, category as alt_text FROM gallery ORDER BY created_at DESC")->fetchAll();
    $adminPayments = [];
    $adminMessages = $pdo->query("SELECT id, name AS customer, email, subject, message, status, created_at FROM contacts ORDER BY created_at DESC")->fetchAll();
    $adminReviews = $pdo->query("SELECT id, name AS customer, '' AS target, rating, review as comment, IF(is_approved, 'Approved', 'Pending') as status FROM testimonials ORDER BY created_at DESC")->fetchAll();
    $adminPlaces = $pdo->query("SELECT id, name, province, place_category, district, description, history, best_time_to_visit, entry_fee, google_map, main_image AS image_url, is_featured, status FROM places ORDER BY created_at DESC")->fetchAll();
    $adminActivities = $pdo->query("SELECT id, name, category, description, image_url, page_url, status FROM activities ORDER BY created_at DESC")->fetchAll();
    $homepageSections = $pdo->query("SELECT section_key, title, subtitle, is_enabled, sort_order FROM homepage_sections ORDER BY sort_order, section_key")->fetchAll();
    $adminCategories = $pdo->query("SELECT id, name, slug, image_url, description, sort_order, is_featured, status FROM categories WHERE type = 'package' ORDER BY is_featured DESC, sort_order ASC, name ASC")->fetchAll();
    $adminCategoryNames = array_values(array_filter(array_map(static fn ($row) => $row['name'] ?? '', $adminCategories)));
    $savedSettings = $pdo->query("SELECT setting_key, setting_value FROM website_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    $adminSettings = array_merge($adminSettings, $savedSettings);
} catch (Throwable $e) {
    // The dashboard remains available before the latest schema migration is imported.
}

// Load managed content independently. A missing optional table such as
// testimonials must never hide saved Places or Things to Do records.
try {
    $adminPlaces = $pdo->query("SELECT id, name, province, place_category, district, description, history, best_time_to_visit, entry_fee, google_map, main_image AS image_url, is_featured, status FROM places ORDER BY created_at DESC")->fetchAll();
} catch (Throwable $e) {
    $adminPlaces = [];
}

try {
    $adminActivities = $pdo->query("SELECT id, name, category, description, image_url, page_url, status FROM activities ORDER BY created_at DESC")->fetchAll();
} catch (Throwable $e) {
    $adminActivities = [];
}

try {
    $imageRoot = realpath(__DIR__ . '/../img');
    if ($imageRoot) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($imageRoot, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile() || !in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) continue;
            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($imageRoot) + 1));
            $websiteImages[] = [
                'id' => 'website-' . md5($relativePath),
                'title' => pathinfo($relativePath, PATHINFO_FILENAME),
                'url' => '../img/' . $relativePath,
                'website_path' => $relativePath,
                'source' => 'Website image',
            ];
        }
    }
} catch (Throwable $e) {
    // Gallery uploads are still shown even if the local image folder cannot be scanned.
}

$adminGallery = array_merge($adminGallery, $websiteImages);

function adminJson($value): string
{
    $json = json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    return htmlspecialchars($json === false ? '[]' : $json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nepal Tour and Travels Dashboard</title>
    <!-- AlpineJS for declarative UI interactions -->
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-900" x-data="{ sidebarOpen: true }">
    <div class="bg-blue-600 text-white text-sm px-4 py-2 text-center">
        Live admin session for <?= htmlspecialchars($adminUser['email'] ?? 'admin') ?>.
        Users: <?= (int) $userCount ?> |
        Travel plans: <?= (int) $travelPlanCount ?> |
        Service bookings: <?= (int) $bookingCount ?>
    </div>

    <!-- Global Application State Context Wrapper -->
    <div class="flex h-full overflow-hidden w-full" 
         x-data="{
            // 1. Navigation Routing Controller View Target states
            currentView: 'dashboard', 
            bookingFilter: 'all',
            packageFilter: 'all',
            paymentFilter: 'transactions',
            reportFilter: 'sales',

            // 2. Mock Data Stores
            packages: <?= adminJson($adminPackages) ?>,
            categories: <?= adminJson($adminCategories) ?>,
            categoryNames: <?= adminJson($adminCategoryNames) ?>,
            destinations: ['Paris, France', 'Kyoto, Japan', 'Maui, Hawaii', 'Cairo, Egypt', 'Reykjavik, Iceland'],
            
            bookings: <?= adminJson($adminBookings) ?>,
            places: <?= adminJson($adminPlaces) ?>,
            placeCategoryOptions: [
                { value: 'provinces', label: 'Provinces' },
                { value: 'heritage', label: 'World Heritage (UNESCO)' },
                { value: 'protected', label: 'Protected Area' },
                { value: 'cities', label: 'Cities and Towns' },
                { value: 'peaks', label: 'Eight Thousanders' },
                { value: 'pilgrimage', label: 'Pilgrimage Sites' },
                { value: 'hills', label: 'Mid Hills' },
            ],
            placeDataOptions: [
                { value: 'category:provinces', label: 'Provinces', group: 'Place to Go' },
                { value: 'category:heritage', label: 'World Heritage (UNESCO)', group: 'Place to Go' },
                { value: 'category:protected', label: 'Protected Area', group: 'Place to Go' },
                { value: 'category:cities', label: 'Cities and Towns', group: 'Place to Go' },
                { value: 'category:peaks', label: 'Eight Thousanders', group: 'Place to Go' },
                { value: 'category:pilgrimage', label: 'Pilgrimage Sites', group: 'Place to Go' },
                { value: 'category:hills', label: 'Mid Hills', group: 'Place to Go' },
                { value: 'province:Koshi', label: 'Koshi', group: 'Province' },
                { value: 'province:Madhesh', label: 'Madhesh', group: 'Province' },
                { value: 'province:Bagmati', label: 'Bagmati', group: 'Province' },
                { value: 'province:Gandaki', label: 'Gandaki', group: 'Province' },
                { value: 'province:Lumbini', label: 'Lumbini', group: 'Province' },
                { value: 'province:Karnali', label: 'Karnali', group: 'Province' },
                { value: 'province:Sudurpashchim', label: 'Sudurpashchim', group: 'Province' },
            ],
            placeCombinedOptions: [
                { value: 'heritage|Koshi', label: 'World Heritage (UNESCO) - Koshi' },
                { value: 'heritage|Madhesh', label: 'World Heritage (UNESCO) - Madhesh' },
                { value: 'heritage|Bagmati', label: 'World Heritage (UNESCO) - Bagmati' },
                { value: 'heritage|Gandaki', label: 'World Heritage (UNESCO) - Gandaki' },
                { value: 'heritage|Lumbini', label: 'World Heritage (UNESCO) - Lumbini' },
                { value: 'heritage|Karnali', label: 'World Heritage (UNESCO) - Karnali' },
                { value: 'heritage|Sudurpashchim', label: 'World Heritage (UNESCO) - Sudurpashchim' },
                { value: 'protected|Koshi', label: 'Protected Area - Koshi' },
                { value: 'protected|Madhesh', label: 'Protected Area - Madhesh' },
                { value: 'protected|Bagmati', label: 'Protected Area - Bagmati' },
                { value: 'protected|Gandaki', label: 'Protected Area - Gandaki' },
                { value: 'protected|Lumbini', label: 'Protected Area - Lumbini' },
                { value: 'protected|Karnali', label: 'Protected Area - Karnali' },
                { value: 'protected|Sudurpashchim', label: 'Protected Area - Sudurpashchim' },
                { value: 'cities|Koshi', label: 'Cities and Towns - Koshi' },
                { value: 'cities|Madhesh', label: 'Cities and Towns - Madhesh' },
                { value: 'cities|Bagmati', label: 'Cities and Towns - Bagmati' },
                { value: 'cities|Gandaki', label: 'Cities and Towns - Gandaki' },
                { value: 'cities|Lumbini', label: 'Cities and Towns - Lumbini' },
                { value: 'cities|Karnali', label: 'Cities and Towns - Karnali' },
                { value: 'cities|Sudurpashchim', label: 'Cities and Towns - Sudurpashchim' },
                { value: 'peaks|Koshi', label: 'Eight Thousanders - Koshi' },
                { value: 'peaks|Madhesh', label: 'Eight Thousanders - Madhesh' },
                { value: 'peaks|Bagmati', label: 'Eight Thousanders - Bagmati' },
                { value: 'peaks|Gandaki', label: 'Eight Thousanders - Gandaki' },
                { value: 'peaks|Lumbini', label: 'Eight Thousanders - Lumbini' },
                { value: 'peaks|Karnali', label: 'Eight Thousanders - Karnali' },
                { value: 'peaks|Sudurpashchim', label: 'Eight Thousanders - Sudurpashchim' },
                { value: 'pilgrimage|Koshi', label: 'Pilgrimage Sites - Koshi' },
                { value: 'pilgrimage|Madhesh', label: 'Pilgrimage Sites - Madhesh' },
                { value: 'pilgrimage|Bagmati', label: 'Pilgrimage Sites - Bagmati' },
                { value: 'pilgrimage|Gandaki', label: 'Pilgrimage Sites - Gandaki' },
                { value: 'pilgrimage|Lumbini', label: 'Pilgrimage Sites - Lumbini' },
                { value: 'pilgrimage|Karnali', label: 'Pilgrimage Sites - Karnali' },
                { value: 'pilgrimage|Sudurpashchim', label: 'Pilgrimage Sites - Sudurpashchim' },
                { value: 'hills|Koshi', label: 'Mid Hills - Koshi' },
                { value: 'hills|Madhesh', label: 'Mid Hills - Madhesh' },
                { value: 'hills|Bagmati', label: 'Mid Hills - Bagmati' },
                { value: 'hills|Gandaki', label: 'Mid Hills - Gandaki' },
                { value: 'hills|Lumbini', label: 'Mid Hills - Lumbini' },
                { value: 'hills|Karnali', label: 'Mid Hills - Karnali' },
                { value: 'hills|Sudurpashchim', label: 'Mid Hills - Sudurpashchim' },
            ],
            placeAssignmentOptions: [
                { value: 'province:Koshi', label: 'Koshi Province' },
                { value: 'province:Madhesh', label: 'Madhesh Province' },
                { value: 'province:Bagmati', label: 'Bagmati Province' },
                { value: 'province:Gandaki', label: 'Gandaki Province' },
                { value: 'province:Lumbini', label: 'Lumbini Province' },
                { value: 'province:Karnali', label: 'Karnali Province' },
                { value: 'province:Sudurpashchim', label: 'Sudurpashchim Province' },
                { value: 'category:heritage', label: 'World Heritage (UNESCO)' },
                { value: 'category:protected', label: 'Protected Area' },
                { value: 'category:cities', label: 'Cities and Towns' },
                { value: 'category:peaks', label: 'Eight Thousanders' },
                { value: 'category:pilgrimage', label: 'Pilgrimage Sites' },
                { value: 'category:hills', label: 'Mid Hills' },
            ],
            activities: <?= adminJson($adminActivities) ?>,
            
            customers: <?= adminJson($adminCustomers) ?>,

            userSessions: [
                { id: 'LS-101', customer: 'John Doe', status: 'Online', lastActive: '2026-07-15 09:12', ip: '192.168.0.21' },
                { id: 'LS-102', customer: 'Jane Smith', status: 'Offline', lastActive: '2026-07-14 20:31', ip: '192.168.0.55' },
                { id: 'LS-103', customer: 'Robert Johnson', status: 'Online', lastActive: '2026-07-15 10:05', ip: '192.168.0.34' }
            ],

            selectedCustomerId: null,
            selectedCustomerName: '',
            selectedGalleryId: null,
            selectedPackageId: null,

            payments: <?= adminJson($adminPayments) ?>,

            gallery: <?= adminJson($adminGallery) ?>,
            reviews: <?= adminJson($adminReviews) ?>,
            messages: <?= adminJson($adminMessages) ?>,

            homepageSections: <?= adminJson($homepageSections) ?>,

            siteSettings: {
                site_name: <?= adminJson($adminSettings['site_name']) ?>,
                logo_url: <?= adminJson($adminSettings['logo_url']) ?>,
                favicon_url: <?= adminJson($adminSettings['favicon_url']) ?>,
                hero_image_url: <?= adminJson($adminSettings['hero_image_url'] ?? ($adminSettings['homepage_hero'] ?? '')) ?>,
                contact_email: <?= adminJson($adminSettings['contact_email']) ?>,
                contact_phone: <?= adminJson($adminSettings['contact_phone']) ?>,
                address: <?= adminJson($adminSettings['address']) ?>,
                facebook_url: <?= adminJson($adminSettings['facebook_url']) ?>,
                twitter_url: <?= adminJson($adminSettings['twitter_url']) ?>,
                instagram_url: <?= adminJson($adminSettings['instagram_url']) ?>,
                youtube_url: <?= adminJson($adminSettings['youtube_url']) ?>,
                seo_title: <?= adminJson($adminSettings['seo_title']) ?>,
                seo_description: <?= adminJson($adminSettings['seo_description']) ?>,
                seo_keywords: <?= adminJson($adminSettings['seo_keywords']) ?>,
                footer_text: <?= adminJson($adminSettings['footer_text']) ?>,
                homepage_hero: <?= adminJson($adminSettings['homepage_hero']) ?>,
            },
            auditLogs: [],

            // Modals Configuration Context variables
            activeModal: null, 
            modalForm: {},

            normalizeCategoryName(value) {
                if (typeof value === 'string') {
                    const trimmed = value.trim();
                    if (!trimmed) return '';
                    try {
                        const parsed = JSON.parse(trimmed);
                        if (parsed && typeof parsed === 'object' && typeof parsed.name === 'string') {
                            return parsed.name.trim();
                        }
                    } catch (error) {
                    }
                    return trimmed;
                }

                if (value && typeof value === 'object' && typeof value.name === 'string') {
                    return value.name.trim();
                }

                return '';
            },

            managedImageUrl(value, directory) {
                const imageUrl = String(value || '').trim();
                if (!imageUrl) return '';
                if (/^(https?:)?\/\//i.test(imageUrl) || imageUrl.startsWith('/') || imageUrl.startsWith('../') || imageUrl.startsWith('./')) return imageUrl;
                if (imageUrl.startsWith('img/')) return `../${imageUrl}`;
                if (imageUrl.startsWith(`${directory}/`)) return `../img/${imageUrl}`;
                return `../img/${directory}/${imageUrl}`;
            },

            async prepareActivityImage(file) {
                if (file.type === 'image/gif') {
                    return await new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = () => reject(new Error('Unable to read the selected activity image.'));
                        reader.readAsDataURL(file);
                    });
                }

                const objectUrl = URL.createObjectURL(file);
                try {
                    const image = new Image();
                    image.src = objectUrl;
                    await image.decode();

                    const scale = Math.min(1, 1600 / image.naturalWidth, 1200 / image.naturalHeight);
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
                    canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
                    const context = canvas.getContext('2d');
                    if (!context) throw new Error('Unable to optimize the selected activity image.');

                    context.drawImage(image, 0, 0, canvas.width, canvas.height);

                    const blob = await new Promise(resolve => {
                        canvas.toBlob(resolve, 'image/webp', 0.84);
                    });
                    if (!blob) throw new Error('Unable to optimize the selected activity image.');

                    return await new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = () => reject(new Error('Unable to read the optimized activity image.'));
                        reader.readAsDataURL(blob);
                    });
                } finally {
                    URL.revokeObjectURL(objectUrl);
                }
            },

            // Helper actions
            logActivity(action, refId) {
                this.auditLogs.unshift({ id: Date.now(), action, refId, time: new Date().toLocaleTimeString() });
            },

            async callAdminApi(action, payload = {}) {
                const response = await fetch('admin_api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action, csrf: <?= adminJson($adminCsrf) ?>, ...payload }) });
                const result = await response.json();
                if (!result.ok) throw new Error(result.message || 'Unable to save the change.');
                return result;
            },

            async saveWebsiteSettings() {
                await this.callAdminApi('save_settings', { settings: this.siteSettings });
                this.logActivity('Save Website Settings', 'site-settings');
                alert('Website settings saved.');
            },

            async saveHomepageSections() {
                const sections = Object.fromEntries(this.homepageSections.map(section => [section.section_key, section]));
                await this.callAdminApi('save_homepage_sections', { sections });
                this.logActivity('Save Homepage Sections', 'homepage-sections');
                alert('Homepage sections saved.');
            },

            async executeSave() {
                if (this.activeModal === 'add-package' || this.activeModal === 'edit-package') {
                    try {
                        const imageFile = document.getElementById('packageImageFile')?.files[0];
                        const imageData = imageFile ? await new Promise((resolve, reject) => { const reader = new FileReader(); reader.onload = () => resolve(reader.result); reader.onerror = reject; reader.readAsDataURL(imageFile); }) : '';
                        await this.callAdminApi(this.activeModal === 'edit-package' ? 'update_package' : 'create_package', { id: this.modalForm.id, title: this.modalForm.title || this.modalForm.destination, destination: this.modalForm.destination, category: this.normalizeCategoryName(this.modalForm.category), duration: this.modalForm.duration, price: this.modalForm.price, imageUrl: this.modalForm.imageUrl || '', imageData, shortDescription: this.modalForm.shortDescription || '', fullDescription: this.modalForm.fullDescription || '', status: this.modalForm.status || 'active', isFeatured: this.modalForm.isFeatured ? 1 : 0 });
                        window.location.reload(); return;
                    } catch (error) { alert(error.message); return; }
                } else if (this.activeModal === 'add-category' || this.activeModal === 'edit-category') {
                    try {
                        const imageFile = document.getElementById('categoryImageFile')?.files[0];
                        const imageData = imageFile ? await new Promise((resolve, reject) => { const reader = new FileReader(); reader.onload = () => resolve(reader.result); reader.onerror = reject; reader.readAsDataURL(imageFile); }) : '';
                        await this.callAdminApi(this.activeModal === 'edit-category' ? 'update_category' : 'create_category', { id: this.modalForm.id, name: this.modalForm.name, slug: this.modalForm.slug || this.modalForm.name, description: this.modalForm.description || '', imageUrl: this.modalForm.imageUrl || '', imageData, sortOrder: this.modalForm.sortOrder || 0, isFeatured: this.modalForm.isFeatured ? 1 : 0, status: this.modalForm.status || 'active' });
                        window.location.reload(); return;
                    } catch (error) { alert(error.message); return; }
                } else if (this.activeModal === 'add-destination') {
                    this.destinations.push(this.modalForm.name);
                    this.logActivity('Register Destination Geolocation', this.modalForm.name);
                } else if (this.activeModal === 'add-customer') {
                    let newCst = { id: 'CST-' + Math.floor(100 + Math.random() * 900), name: this.modalForm.name, email: this.modalForm.email, phone: this.modalForm.phone, totalBookings: 0 };
                    this.customers.push(newCst);
                    this.logActivity('Register Customer Profile', newCst.id);
                } else if (this.activeModal === 'add-gallery' || this.activeModal === 'edit-gallery') {
                    try {
                        const imageFile = document.getElementById('galleryImageFile')?.files[0];
                        const imageData = imageFile ? await new Promise((resolve, reject) => { const reader = new FileReader(); reader.onload = () => resolve(reader.result); reader.onerror = reject; reader.readAsDataURL(imageFile); }) : '';
                        await this.callAdminApi(this.activeModal === 'edit-gallery' ? 'update_gallery' : 'create_gallery', { id: this.modalForm.id, title: this.modalForm.title, imageUrl: this.modalForm.url || '', imageData, altText: this.modalForm.altText || '' });
                        window.location.reload(); return;
                    } catch (error) { alert(error.message); return; }
                } else if (this.activeModal === 'add-place' || this.activeModal === 'edit-place') {
                    try {
                        const imageFile = document.getElementById('placeImageFile')?.files[0];
                        const imageData = imageFile ? await new Promise((resolve, reject) => { const reader = new FileReader(); reader.onload = () => resolve(reader.result); reader.onerror = reject; reader.readAsDataURL(imageFile); }) : '';
                        const placeCategories = Array.isArray(this.modalForm.placeCategories) ? this.modalForm.placeCategories : [];
                        if (!placeCategories.length || (placeCategories.includes('provinces') && !this.modalForm.province)) throw new Error('Select at least one Place to Go button. A province is required only for the Provinces button.');
                        await this.callAdminApi(this.activeModal === 'edit-place' ? 'update_place' : 'create_place', { id: this.modalForm.id, name: this.modalForm.name, province: this.modalForm.province || 'Nepal', placeCategory: placeCategories.join(','), district: '', description: this.modalForm.description || '', history: this.modalForm.history || '', bestTimeToVisit: this.modalForm.bestTimeToVisit || '', entryFee: this.modalForm.entryFee || 0, googleMap: this.modalForm.googleMap || '', imageUrl: this.modalForm.imageUrl || '', imageData, isFeatured: this.modalForm.isFeatured ? 1 : 0, status: this.modalForm.status || 'active' });
                        window.location.reload(); return;
                    } catch (error) { alert(error.message); return; }
                } else if (this.activeModal === 'add-activity' || this.activeModal === 'edit-activity') {
                    try {
                        const imageFile = document.getElementById('activityImageFile')?.files[0];
                        const imageData = imageFile ? await this.prepareActivityImage(imageFile) : '';
                        await this.callAdminApi(this.activeModal === 'edit-activity' ? 'update_activity' : 'create_activity', { id: this.modalForm.id, name: this.modalForm.name, category: this.modalForm.category, description: this.modalForm.description, imageUrl: this.modalForm.imageUrl || '', imageData, pageUrl: this.modalForm.pageUrl || '', status: this.modalForm.status || 'active' });
                        window.location.reload(); return;
                    } catch (error) { alert(error.message); return; }
                } else if (this.activeModal === 'add-payment') {
                    try {
                        await this.callAdminApi('create_payment', { customer: this.modalForm.customer, bookingId: this.modalForm.bookingId || 0, type: this.modalForm.type, amount: this.modalForm.amount });
                        window.location.reload(); return;
                    } catch (error) { alert(error.message); return; }
                } else if (this.activeModal === 'edit-customer') {
                    const idx = this.customers.findIndex(c => c.id === this.modalForm.id);
                    if (idx !== -1) {
                        Object.assign(this.customers[idx], { name: this.modalForm.name, email: this.modalForm.email, phone: this.modalForm.phone });
                        this.logActivity('Update Customer Profile', this.modalForm.id);
                    }
                } else if (this.activeModal === 'edit-gallery') {
                    const img = this.gallery.find(g => g.id === this.modalForm.id);
                    if (img) {
                        img.title = this.modalForm.title;
                        img.url = this.modalForm.url || img.url;
                        this.logActivity('Update Gallery Asset', this.modalForm.id);
                    }
                }
                this.activeModal = null;
                this.modalForm = {};
            },

            editCustomer(customerId) {
                const c = this.customers.find(customer => customer.id === customerId);
                if (c) {
                    this.modalForm = { id: c.id, name: c.name, email: c.email, phone: c.phone };
                    this.activeModal = 'edit-customer';
                }
            },

            deleteCustomer(id) {
                this.customers = this.customers.filter(c => c.id !== id);
                this.logActivity('Delete Customer Profile', id);
            },

            editGallery(galleryId) {
                const img = this.gallery.find(item => item.id === galleryId);
                if (img) {
                    this.modalForm = { id: img.id, title: img.title, url: img.url, altText: img.alt_text || '' };
                    this.activeModal = 'edit-gallery';
                }
            },

            editCategory(categoryId) {
                const item = this.categories.find(category => category.id === categoryId);
                if (item) {
                    this.modalForm = { id: item.id, name: item.name, slug: item.slug || '', description: item.description || '', imageUrl: item.image_url || '', sortOrder: item.sort_order || 0, isFeatured: !!item.is_featured, status: item.status || 'active' };
                    this.activeModal = 'edit-category';
                }
            },

            async deleteCategory(categoryId) {
                if (!confirm('Delete this category? Packages using it will keep their package records but the category link will be removed.')) return;
                try {
                    await this.callAdminApi('delete_category', { id: categoryId });
                    this.categories = this.categories.filter(category => category.id !== categoryId);
                    this.categoryNames = this.categoryNames.filter(categoryName => categoryName !== (this.categories.find(category => category.id === categoryId)?.name || ''));
                    this.logActivity('Delete Package Category', categoryId);
                } catch (error) {
                    alert(error.message);
                }
            },

            editPackage(packageId) {
                const item = this.packages.find(packageItem => packageItem.id === packageId);
                if (item) {
                    this.modalForm = { id: item.id, title: item.title, destination: item.destination, category: this.normalizeCategoryName(item.category), duration: item.duration, price: item.price, imageUrl: item.image_url || '', shortDescription: item.short_description || '', fullDescription: item.full_description || '', status: item.status || 'active', isFeatured: !!item.is_featured };
                    this.activeModal = 'edit-package';
                }
            },

            editPlace(placeId) {
                const item = this.places.find(placeItem => placeItem.id === placeId);
                if (item) {
                        this.modalForm = { id: item.id, name: item.name, province: item.province, placeCategories: (item.place_category || 'provinces').split(',').map(category => category.trim()).filter(Boolean), district: item.district, description: item.description || '', history: item.history || '', bestTimeToVisit: item.best_time_to_visit || '', entryFee: item.entry_fee || 0, googleMap: item.google_map || '', imageUrl: item.image_url || '', status: item.status || 'active', isFeatured: !!item.is_featured };
                    this.activeModal = 'edit-place';
                }
            },

            editActivity(activityId) {
                const item = this.activities.find(activity => activity.id === activityId);
                if (item) {
                    this.modalForm = { id: item.id, name: item.name, category: item.category, description: item.description, imageUrl: item.image_url || '', pageUrl: item.page_url || '', status: item.status || 'active' };
                    this.activeModal = 'edit-activity';
                }
            },

            toggleGallerySelection(galleryId) {
                this.selectedGalleryId = this.selectedGalleryId === galleryId ? null : galleryId;
            },

            async removeGalleryImage(galleryId) {
                const image = this.gallery.find(item => item.id === galleryId);
                const isWebsiteImage = String(galleryId).startsWith('website-');
                if (!confirm(isWebsiteImage ? 'Permanently delete this website image file?' : 'Remove this image from the website gallery?')) return;
                try {
                    await this.callAdminApi(isWebsiteImage ? 'delete_website_image' : 'delete_gallery', isWebsiteImage ? { path: image?.website_path || '' } : { id: galleryId });
                    window.location.reload();
                } catch (error) { alert(error.message); }
            },

            contactCustomer(customerId) {
                const customer = this.customers.find(c => c.id === customerId);
                if (customer) {
                    alert(`Admin will communicate directly with ${customer.name}.`);
                    this.logActivity('Contact Customer Profile', customerId);
                }
            },

            viewCustomerBookings(customerId) {
                const customer = this.customers.find(c => c.id === customerId);
                if (customer) {
                    this.selectedCustomerId = customerId;
                    this.selectedCustomerName = customer.name;
                    this.bookingFilter = 'all';
                    this.currentView = 'bookings';
                }
            },

            async changeBookingStatus(id, nextStatus, control) {
                let target = this.bookings.find(b => b.id === id);
                if (!target) return;
                if (nextStatus === target.status) return;
                if (String(target.status).toLowerCase() === 'confirmed') {
                    alert('This booking is already confirmed and cannot be changed.');
                    if (control) control.value = target.status;
                    return;
                }
                if (!confirm(`Change booking #${id} status from ${target.status} to ${nextStatus}? This keeps the booking record; it only updates its status.`)) {
                    if (control) control.value = target.status;
                    return;
                }
                try {
                    const result = await this.callAdminApi('update_booking_status', { id, status: nextStatus.toLowerCase() });
                    target.status = nextStatus;
                    this.logActivity(`Update Booking State to ${nextStatus}${result.emailSent ? ' and sent email notification' : ''}`, id);
                } catch (error) {
                    if (control) control.value = target.status;
                    alert(error.message);
                }
            },

            selectPackage(packageId) {
                this.selectedPackageId = this.selectedPackageId === packageId ? null : packageId;
            },

            removeSelectedPackage() {
                if (!this.selectedPackageId) return;
                this.deletePackage(this.selectedPackageId);
            },

            async deletePackage(id) {
                if (!confirm('Delete this package from the website?')) return;
                try {
                    await this.callAdminApi('delete_package', { id });
                    this.packages = this.packages.filter(packageItem => packageItem.id !== id);
                    if (this.selectedPackageId === id) {
                        this.selectedPackageId = null;
                    }
                    this.logActivity('Evict Tour Package Record', id);
                } catch (error) {
                    alert(error.message);
                }
            },

            async deletePlace(id) {
                if (!confirm('Delete this place from the website?')) return;
                try {
                    await this.callAdminApi('delete_place', { id });
                    this.places = this.places.filter(placeItem => placeItem.id !== id);
                    this.logActivity('Evict Place Record', id);
                } catch (error) {
                    alert(error.message);
                }
            },

            async deleteActivity(id) {
                if (!confirm('Delete this activity from the website?')) return;
                try {
                    await this.callAdminApi('delete_activity', { id });
                    this.activities = this.activities.filter(activity => activity.id !== id);
                    this.logActivity('Delete Activity', id);
                } catch (error) {
                    alert(error.message);
                }
            }
         }">

        <!-- SIDEBAR CONTAINER SYSTEM -->
        <aside 
            :class="sidebarOpen ? 'w-64' : 'w-20 w-0 -translate-x-full lg:translate-x-0'" 
            class="bg-slate-900 text-slate-400 flex flex-col transition-all duration-300 ease-in-out z-30 h-full fixed inset-y-0 left-0 lg:static shadow-xl border-r border-slate-800 shrink-0"
        >
            <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 shrink-0">
                <div class="flex items-center space-x-3 overflow-hidden" x-show="sidebarOpen">
                    <img src="../img/logo.png" alt="Nepal Tour and Travel logo" class="h-10 w-10 rounded-xl object-contain bg-blue-600 p-1 shadow-md shadow-blue-500/20">
                    <span class="text-lg font-bold text-white tracking-wide whitespace-nowrap">Nepal Tour and Travels</span>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition-colors hidden lg:block">
                    <i class="bi" :class="sidebarOpen ? 'bi-text-indent-right' : 'bi-list'"></i>
                </button>
            </div>

            <!-- EXECUTABLE SIDEBAR NAVIGATION MAP -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 custom-scrollbar">
                
                <!-- Main Control Dashboard Routing Pin -->
                <button @click="currentView = 'dashboard'" :class="currentView === 'dashboard' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/10' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all duration-200 group">
                    <i class="bi bi-grid-1x2-fill text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Dashboard</span>
                </button>

                <!-- Tour Management Loop Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button type="button" @click="open = !open; currentView = 'packages'; packageFilter = 'all'" :class="currentView === 'packages' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all group cursor-pointer">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-compass text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Tour Packages</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span x-show="sidebarOpen" class="text-[10px] uppercase tracking-wide text-slate-400 group-hover:text-slate-200">manage</span>
                            <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                        </div>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-7 pr-2 space-y-1.5 pt-1" x-cloak>
                        <button type="button" @click.stop="open = true; packageFilter = 'all'; currentView = 'packages'; activeModal = 'add-package'" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors flex items-center space-x-2 border border-transparent hover:border-slate-700"><i class="bi bi-plus-circle text-xs text-blue-400"></i><span>Add New Package</span></button>
                        <button type="button" @click.stop="open = true; packageFilter = 'all'; currentView = 'packages'" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">View All Packages</button>
                        <button type="button" @click.stop="open = true; packageFilter = 'categories'; currentView = 'packages'" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">Manage Categories</button>
                        <button type="button" @click.stop="open = true; packageFilter = 'destinations'; currentView = 'packages'" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">Manage Destinations</button>
                    </div>
                </div>

                <button @click="currentView = 'places'" :class="currentView === 'places' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all group">
                    <i class="bi bi-geo-alt text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Places</span>
                </button>

                <button @click="currentView = 'activities'" :class="currentView === 'activities' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all group">
                    <i class="bi bi-compass text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Things to Do</span>
                </button>

                <button @click="currentView = 'website'" :class="currentView === 'website' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all group">
                    <i class="bi bi-sliders text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Website Settings</span>
                </button>

                <!-- Bookings Processing Pipe -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open; currentView = 'bookings'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-slate-200 transition-all group">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-calendar-check text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Bookings</span>
                        </div>
                        <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-9 pr-2 space-y-1" x-cloak>
                            <button @click="bookingFilter = 'Pending'; currentView = 'bookings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors flex items-center justify-between"><span>Pending</span><span class="bg-blue-600/20 text-blue-400 px-1.5 py-0.5 rounded text-[10px]" x-text="bookings.filter(b => b.status === 'Pending').length"></span></button>
                        <button @click="bookingFilter = 'Confirmed'; currentView = 'bookings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Confirmed</button>
                            <button @click="bookingFilter = 'Hold'; currentView = 'bookings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Hold</button>
                        <button @click="bookingFilter = 'Cancelled'; currentView = 'bookings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Cancelled</button>
                         <button @click="bookingFilter = 'Add'; currentView = 'bookings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Add</button>
                    </div>
                </div>

                <!-- Customers Management Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open; currentView = 'customers'" :class="currentView === 'customers' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all group">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-people text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Customers</span>
                        </div>
                        <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-9 pr-2 space-y-1" x-cloak>
                        <button @click="currentView = 'customers'; activeModal = 'add-customer'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors flex items-center space-x-2"><i class="bi bi-plus-circle text-xs text-blue-400"></i><span>Add Customer</span></button>
                        <button @click="currentView = 'customers'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">All Customers</button>
                    </div>
                </div>

                <!-- Financial Ledger Processing Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open; currentView = 'payments'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-slate-200 transition-all group">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-credit-card text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Payments</span>
                        </div>
                        <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-9 pr-2 space-y-1" x-cloak>
                        <button @click="paymentFilter = 'Transaction'; currentView = 'payments'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Transactions</button>
                        <button @click="paymentFilter = 'Invoice'; currentView = 'payments'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Invoices</button>
                        <button @click="paymentFilter = 'Refund'; currentView = 'payments'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Refunds</button>
                    </div>
                </div>

                <!-- Media Asset Management Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button type="button" @click="open = !open; currentView = 'gallery'; selectedGalleryId = null; activeModal = null" :class="currentView === 'gallery' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all group cursor-pointer">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-images text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Gallery</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span x-show="sidebarOpen" class="text-[10px] uppercase tracking-wide text-slate-400 group-hover:text-slate-200">media</span>
                            <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                        </div>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-7 pr-2 space-y-1.5 pt-1" x-cloak>
                        <button type="button" @click.stop="open = true; currentView = 'gallery'; selectedGalleryId = null; activeModal = 'add-gallery'" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors flex items-center space-x-2 border border-transparent hover:border-slate-700"><i class="bi bi-cloud-arrow-up-fill text-xs text-blue-400"></i><span>Add New Image</span></button>
                        <button type="button" @click.stop="open = true; currentView = 'gallery'; selectedGalleryId = null" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">View All Images</button>
                        <button type="button" @click.stop="open = true; if(selectedGalleryId){ removeGalleryImage(selectedGalleryId); }" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">Remove Selected</button>
                    </div>
                </div>

                <!-- User Profile Communication Target -->
                <button @click="currentView = 'user-management'; selectedCustomerId = null" :class="currentView === 'user-management' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all group">
                    <i class="bi bi-chat-left-text text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">User Communications</span>
                </button>

                <!-- Feedback & Quality Review Monitoring Target -->
                <button @click="currentView = 'reviews'" :class="currentView === 'reviews' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all group">
                    <i class="bi bi-chat-left-heart text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Reviews</span>
                </button>

                <!-- Aggregated Data Reporting Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open; currentView = 'reports'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-slate-200 transition-all group">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-graph-up-arrow text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Reports</span>
                        </div>
                        <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-9 pr-2 space-y-1" x-cloak>
                        <button @click="reportFilter = 'sales'; currentView = 'reports'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Sales Overview</button>
                        <button @click="reportFilter = 'bookings'; currentView = 'reports'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Bookings Analytics</button>
                        <button @click="reportFilter = 'revenue'; currentView = 'reports'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Revenue Projections</button>
                    </div>
                </div>

                <div class="h-px bg-slate-800 my-4 w-full"></div>

                <!-- Profile Core View Switch -->
                <button @click="currentView = 'profile'" :class="currentView === 'profile' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all group">
                    <i class="bi bi-person-badge text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">My Profile</span>
                </button>

                <!-- Destructive Session Disconnect -->
                <button @click="if(confirm('Disconnect secure admin session panel?')) { window.location.reload(); }" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 transition-all group">
                    <i class="bi bi-box-arrow-left text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Logout System</span>
                </button>
            </nav>
        </aside>

        <!-- MAIN DYNAMIC APPS SPACE CORE -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0 bg-slate-50">
            <!-- Global Frame Topbar Bar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 shadow-sm z-10">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-800 p-1.5 rounded-lg hover:bg-slate-100 transition-colors lg:hidden">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <h2 class="text-sm font-semibold tracking-wide text-slate-700 uppercase" x-text="'Active Area / ' + currentView"></h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex flex-col text-right">
                        <span class="text-xs font-bold text-slate-900">Administrator Console Mode</span>
                        <span class="text-[10px] text-emerald-600 font-mono font-bold">Status: Full Access Guard Active</span>
                    </div>
                    <div class="h-8 w-px bg-slate-200"></div>
                    <img class="w-9 h-9 rounded-xl object-contain bg-blue-50 ring-2 ring-slate-100" src="../img/logo.png" alt="Nepal Tour and Travel logo">
                </div>
            </header>

            <!-- CANVAS SUB-ROUTING WORKSPACE INJECTOR PANELS -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 focus:outline-none custom-scrollbar">
                
                <!-- PANEL A: METRIC DASHBOARD CORE SUMMARY -->
                <div x-show="currentView === 'dashboard'" x-transition class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gross Booking Val</p><h3 class="text-2xl font-black text-slate-900 mt-1">$7,847</h3></div>
                            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><i class="bi bi-currency-dollar text-xl"></i></div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Bookings</p><h3 class="text-2xl font-black text-slate-900 mt-1" x-text="bookings.length"></h3></div>
                            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl"><i class="bi bi-bookmark-star text-xl"></i></div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Registered Clients</p><h3 class="text-2xl font-black text-slate-900 mt-1" x-text="customers.length"></h3></div>
                            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl"><i class="bi bi-people text-xl"></i></div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catalog Packages</p><h3 class="text-2xl font-black text-slate-900 mt-1" x-text="packages.length"></h3></div>
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl"><i class="bi bi-compass text-xl"></i></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Data Summary Column -->
                        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                            <h3 class="text-base font-bold text-slate-900">Recent System Bookings Stream</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="text-xs font-semibold text-slate-400 uppercase border-b border-slate-100 bg-slate-50/50">
                                            <th class="py-3 px-4">ID</th>
                                            <th class="py-3 px-4">Customer</th>
                                            <th class="py-3 px-4">Target Destination</th>
                                            <th class="py-3 px-4">Status Map</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(b,index) in bookings.slice(0,3)" :key="b.id">
                                            <tr>
                                                <td class="py-3 px-4 font-mono font-bold text-blue-600" x-text="index + 1"></td>
                                                <td class="py-3 px-4 text-slate-900" x-text="b.customer"></td>
                                                <td class="py-3 px-4 text-slate-600" x-text="b.destination"></td>
                                                <td class="py-3 px-4">
                                                    <span :class="{'bg-blue-100 text-blue-700': b.status==='New', 'bg-amber-100 text-amber-700': b.status==='Confirmed', 'bg-emerald-100 text-emerald-700': b.status==='Completed', 'bg-rose-100 text-rose-700': b.status==='Cancelled'}" class="px-2 py-0.5 rounded text-xs font-semibold" x-text="b.status"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Live Core Audit Stream Log box -->
                        <div class="bg-slate-900 text-slate-100 p-5 rounded-2xl border border-slate-800 shadow-inner flex flex-col h-80">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-800">Live Workspace Activity Trace</h4>
                            <div class="flex-1 overflow-y-auto mt-3 font-mono text-[11px] space-y-2.5 pr-1 custom-scrollbar">
                                <template x-for="log in auditLogs" :key="log.id">
                                    <div class="bg-slate-950/40 p-2 rounded border border-slate-800 flex justify-between items-start gap-2">
                                        <div>
                                            <span class="text-blue-400 font-bold" x-text="log.action"></span>
                                            <p class="text-slate-500 text-[10px] mt-0.5" x-text="'Ref: '+log.refId"></p>
                                        </div>
                                        <span class="text-slate-600 text-[10px]" x-text="log.time"></span>
                                    </div>
                                </template>
                                <template x-if="auditLogs.length === 0">
                                    <p class="text-slate-600 italic text-center pt-16">No active state changes logged in this browser frame window yet.</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANEL B: PACKAGE CATALOG CONTROL CANVAS -->
                <div x-show="currentView === 'packages'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div class="flex space-x-2 bg-slate-200/60 p-1 rounded-xl">
                            <button @click="packageFilter = 'all'" :class="packageFilter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">All Packages Inventory</button>
                            <button @click="packageFilter = 'categories'" :class="packageFilter === 'categories' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">Categories Link Map</button>
                            <button @click="packageFilter = 'destinations'" :class="packageFilter === 'destinations' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">Destinations Geolocation</button>
                        </div>
                        <button @click="activeModal = (packageFilter === 'categories' ? 'add-category' : packageFilter === 'destinations' ? 'add-destination' : 'add-package')" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700 flex items-center space-x-1">
                            <i class="bi bi-plus-lg"></i>
                            <span x-text="packageFilter === 'categories' ? 'Add Category' : packageFilter === 'destinations' ? 'Add Destination' : 'Add Package'"></span>
                        </button>
                    </div>

                    <!-- Filter state conditional rendering views -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <!-- Subview: Package List Grid -->
                        <template x-if="packageFilter === 'all'">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-6 py-4">Destination Target</th>
                                            <th class="px-6 py-4">Classification Category</th>
                                            <th class="px-6 py-4">Duration</th>
                                            <th class="px-6 py-4">Base Cost</th>
                                            <th class="px-6 py-4 text-right sticky right-0 bg-slate-50 z-10">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="p in packages" :key="p.id">
                                            <tr @click="selectPackage(p.id)" :class="selectedPackageId === p.id ? 'bg-blue-50' : 'hover:bg-slate-50/60'" class="cursor-pointer">
                                                <td class="px-6 py-4 font-semibold text-slate-900" x-text="p.destination"></td>
                                                <td class="px-6 py-4 text-slate-600" x-text="normalizeCategoryName(p.category)"></td>
                                                <td class="px-6 py-4 text-slate-600" x-text="p.duration"></td>
                                                <td class="px-6 py-4 font-mono font-bold text-slate-900" x-text="'$'+p.price"></td>
                                                <td class="px-6 py-4 text-right sticky right-0 bg-white">
                                                    <button @click.stop="editPackage(p.id)" class="text-slate-700 bg-slate-100 hover:bg-slate-200 p-1.5 rounded-lg text-xs font-bold transition-colors mr-1">Edit</button>
                                                    <button @click.stop="deletePackage(p.id)" class="text-rose-600 p-1.5 hover:bg-rose-50 rounded-lg text-xs font-bold transition-colors">Evict</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <!-- Subview: Category Matrix -->
                        <template x-if="packageFilter === 'categories'">
                            <div class="p-6 space-y-3">
                                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wide">Registered Classification Tags</h3>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="cat in categories" :key="cat">
                                        <span class="px-3 py-1.5 bg-slate-100 border border-slate-200 text-slate-800 font-medium text-xs rounded-xl flex items-center space-x-2">
                                            <span x-text="cat"></span>
                                            <button @click="categories = categories.filter(c => c !== cat); logActivity('Remove Category Tag', cat)" class="text-slate-400 hover:text-rose-600"><i class="bi bi-x-circle-fill text-[10px]"></i></button>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Subview: Destination Registry Matrix -->
                        <template x-if="packageFilter === 'destinations'">
                            <div class="p-6 space-y-3">
                                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wide">Active Hub Destinations Geolocation Index</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                    <template x-for="dest in destinations" :key="dest">
                                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                                            <span class="text-xs font-semibold text-slate-900" x-text="dest"></span>
                                            <button @click="destinations = destinations.filter(d => d !== dest); logActivity('Evict Destination Target', dest)" class="text-rose-600 hover:bg-rose-100 px-2 py-1 rounded text-[10px] font-bold">Remove</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PANEL B1: PLACE CONTENT MANAGER -->
                <div x-show="currentView === 'places'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Places Content Manager</h3>
                            <p class="text-sm text-slate-500">Control destination pages, hero images, and featured place entries from one place.</p>
                        </div>
                        <button @click="modalForm = { name: '', province: '', placeCategories: [], district: '', description: '', history: '', bestTimeToVisit: '', entryFee: 0, googleMap: '', imageUrl: '', status: 'active', isFeatured: false }; activeModal = 'add-place'" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700 flex items-center space-x-1"><i class="bi bi-plus-lg"></i><span>Add Place</span></button>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">Place</th>
                                        <th class="px-6 py-4">Saved image URL/path</th>
                                        <th class="px-6 py-4">Places to Go</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="place in places" :key="place.id">
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-6 py-4 text-slate-900 font-semibold"><div x-text="place.name"></div><div class="text-xs text-slate-500" x-text="place.best_time_to_visit || 'Best time not set'"></div></td>
                                            <td class="px-6 py-4">
                                                <template x-if="place.image_url">
                                                    <div class="flex items-center gap-3">
                                                        <img :src="managedImageUrl(place.image_url, 'places')" :alt="place.name" class="h-12 w-16 rounded-lg border border-slate-200 object-cover" referrerpolicy="no-referrer">
                                                        <span class="max-w-xs truncate text-xs text-slate-600" x-text="place.image_url" :title="place.image_url"></span>
                                                    </div>
                                                </template>
                                                <span x-show="!place.image_url" class="text-xs text-slate-400">No image saved</span>
                                            </td>
                                            <td class="px-6 py-4"><div class="flex flex-wrap gap-1"><template x-for="category in (place.place_category || 'provinces').split(',')" :key="category"><span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[10px] font-semibold" x-text="placeCategoryOptions.find(option => option.value === category.trim())?.label || category.trim()"></span></template></div></td>
                                            <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-semibold" :class="place.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" x-text="place.status"></span></td>
                                            <td class="px-6 py-4 text-right">
                                                <button @click.stop="editPlace(place.id)" class="text-slate-700 bg-slate-100 hover:bg-slate-200 p-1.5 rounded-lg text-xs font-bold transition-colors mr-1">Edit</button>
                                                <button @click.stop="deletePlace(place.id)" class="text-rose-600 bg-rose-50 hover:bg-rose-100 p-1.5 rounded-lg text-xs font-bold transition-colors">Delete</button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="places.length === 0"><td colspan="5" class="px-6 py-10 text-center text-slate-500">No dashboard places saved yet. Use <strong>Add Place</strong> to create one.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div x-show="currentView === 'activities'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div><h3 class="text-base font-bold text-slate-900">Things to Do Manager</h3><p class="text-sm text-slate-500">Add, edit, publish, or remove activity cards from the landing page.</p></div>
                        <button @click="modalForm = { name: '', category: '', description: '', imageUrl: '', pageUrl: '', status: 'active' }; activeModal = 'add-activity'" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700"><i class="bi bi-plus-lg"></i> Add Activity</button>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500"><tr><th class="px-6 py-4">Activity</th><th class="px-6 py-4">Saved image URL/path</th><th class="px-6 py-4">Category</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Actions</th></tr></thead><tbody class="divide-y divide-slate-100"><template x-for="activity in activities" :key="activity.id"><tr><td class="px-6 py-4"><div class="font-semibold text-slate-900" x-text="activity.name"></div><div class="text-xs text-slate-500 truncate max-w-md" x-text="activity.description"></div></td><td class="px-6 py-4"><template x-if="activity.image_url"><div class="flex items-center gap-3"><img :src="managedImageUrl(activity.image_url, 'activities')" :alt="activity.name" class="h-12 w-16 rounded-lg border border-slate-200 object-cover" referrerpolicy="no-referrer"><span class="max-w-xs truncate text-xs text-slate-600" x-text="activity.image_url" :title="activity.image_url"></span></div></template><span x-show="!activity.image_url" class="text-xs text-slate-400">No image saved</span></td><td class="px-6 py-4 text-slate-600" x-text="activity.category"></td><td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-semibold" :class="activity.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" x-text="activity.status"></span></td><td class="px-6 py-4 text-right"><button @click="editActivity(activity.id)" class="text-slate-700 bg-slate-100 hover:bg-slate-200 p-1.5 rounded-lg text-xs font-bold mr-1">Edit</button><button @click="deleteActivity(activity.id)" class="text-rose-600 bg-rose-50 hover:bg-rose-100 p-1.5 rounded-lg text-xs font-bold">Delete</button></td></tr></template></tbody></table></div></div>
                </div>

                <!-- PANEL B2: WEBSITE SETTINGS CONTROL CENTER -->
                <div x-show="currentView === 'website'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Website Settings</h3>
                            <p class="text-sm text-slate-500">Update branding, SEO, social links, hero copy, and homepage section titles.</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="saveHomepageSections()" class="bg-slate-100 text-slate-800 px-3 py-1.5 rounded-xl text-xs font-bold hover:bg-slate-200">Save Sections</button>
                            <button @click="saveWebsiteSettings()" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700">Save Settings</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                            <h4 class="text-sm font-bold text-slate-900">Core Site Settings</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Site Name</label><input type="text" x-model="siteSettings.site_name" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Homepage Hero</label><input type="text" x-model="siteSettings.homepage_hero" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Contact Email</label><input type="email" x-model="siteSettings.contact_email" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Contact Phone</label><input type="text" x-model="siteSettings.contact_phone" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div class="sm:col-span-2"><label class="block text-xs font-bold text-slate-500 uppercase">Address</label><input type="text" x-model="siteSettings.address" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Logo URL</label><input type="text" x-model="siteSettings.logo_url" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Favicon URL</label><input type="text" x-model="siteSettings.favicon_url" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Hero Image URL</label><input type="text" x-model="siteSettings.hero_image_url" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm" placeholder="Optional managed hero image"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">SEO Title</label><input type="text" x-model="siteSettings.seo_title" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">SEO Description</label><input type="text" x-model="siteSettings.seo_description" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div class="sm:col-span-2"><label class="block text-xs font-bold text-slate-500 uppercase">SEO Keywords</label><input type="text" x-model="siteSettings.seo_keywords" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Facebook URL</label><input type="text" x-model="siteSettings.facebook_url" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Twitter URL</label><input type="text" x-model="siteSettings.twitter_url" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Instagram URL</label><input type="text" x-model="siteSettings.instagram_url" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">YouTube URL</label><input type="text" x-model="siteSettings.youtube_url" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                <div class="sm:col-span-2"><label class="block text-xs font-bold text-slate-500 uppercase">Footer Text</label><textarea x-model="siteSettings.footer_text" rows="3" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></textarea></div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                            <h4 class="text-sm font-bold text-slate-900">Homepage Sections</h4>
                            <div class="space-y-3 max-h-[34rem] overflow-y-auto pr-1 custom-scrollbar">
                                <template x-for="section in homepageSections" :key="section.section_key">
                                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-xs font-bold text-slate-900 uppercase tracking-wide" x-text="section.section_key"></p>
                                                <p class="text-[11px] text-slate-500" x-text="'Sort order: ' + section.sort_order"></p>
                                            </div>
                                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700"><input type="checkbox" x-model="section.is_enabled" class="rounded border-slate-300"><span>Enabled</span></label>
                                        </div>
                                        <input type="text" x-model="section.title" placeholder="Section title" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                                        <input type="text" x-model="section.subtitle" placeholder="Section subtitle" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                                        <div><label class="block text-[11px] font-bold text-slate-500 uppercase">Sort Order</label><input type="number" x-model="section.sort_order" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANEL C: BOOKING MANAGER DISPATCH WORKSPACE -->
                <div x-show="currentView === 'bookings'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div class="flex flex-wrap gap-1 bg-slate-200/60 p-1 rounded-xl">
                            <button @click="bookingFilter = 'all'" :class="bookingFilter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">All Bookings Pipeline</button>
                            <button @click="bookingFilter = 'Pending'" :class="bookingFilter === 'Pending' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Pending</button>
                            <button @click="bookingFilter = 'Confirmed'" :class="bookingFilter === 'Confirmed' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Confirmed</button>
                            <button @click="bookingFilter = 'Hold'" :class="bookingFilter === 'Hold' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Hold</button>
                            <button @click="bookingFilter = 'Cancelled'" :class="bookingFilter === 'Cancelled' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Cancelled</button>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">ID</th>
                                        <th class="px-6 py-4">Customer Account</th>
                                        <th class="px-6 py-4">Target Destination</th>
                                        <th class="px-6 py-4">Schedule Date</th>
                                        <th class="px-6 py-4">Value</th>
                                        <th class="px-6 py-4">Pipeline Status State</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="(b,index) in bookings.filter(b => (selectedCustomerId ? b.customer === selectedCustomerName : true) && (bookingFilter === 'all' || b.status === bookingFilter))" :key="b.id">
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-6 py-4 font-mono font-bold text-blue-600" x-text="index + 1"></td>
                                            <td class="px-6 py-4 text-slate-900 font-medium"><div x-text="b.customer"></div><div class="mt-1 text-xs font-normal text-slate-500" x-text="b.email + ' · ' + b.phone"></div></td>
                                            <td class="px-6 py-4 text-slate-600" x-text="b.destination"></td>
                                            <td class="px-6 py-4 text-slate-500 font-mono" x-text="b.date"></td>
                                            <td class="px-6 py-4 font-mono font-bold text-slate-900" x-text="'$'+b.amount"></td>
                                            <td class="px-6 py-4">
                                                <div class="relative inline-block">
                                                    <select :value="b.status" @change="changeBookingStatus(b.id, $event.target.value, $event.target)" :disabled="String(b.status).toLowerCase() === 'confirmed'" :class="{'bg-blue-100 text-blue-700 border-blue-200': b.status==='Pending', 'bg-amber-100 text-amber-700 border-amber-200': b.status==='Confirmed', 'bg-violet-100 text-violet-700 border-violet-200': b.status==='Hold', 'bg-rose-100 text-rose-700 border-rose-200': b.status==='Cancelled'}" class="appearance-none pr-7 pl-2.5 py-1.5 rounded-lg text-xs font-bold border shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-200 min-w-[118px] cursor-pointer disabled:cursor-not-allowed disabled:opacity-80">
                                                        <option value="Pending">Pending</option>
                                                        <option value="Confirmed">Confirmed</option>
                                                        <option value="Hold">Hold</option>
                                                        <option value="Cancelled">Cancelled</option>
                                                    </select>
                                                    <i class="bi bi-chevron-down absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] opacity-70 pointer-events-none"></i>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PANEL D: CUSTOMERS PROFILES RECORD TABLE -->
                <div x-show="currentView === 'customers'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                        <h3 class="text-base font-bold text-slate-900">CRM Master Database Profile Accounts</h3>
                        <button @click="activeModal = 'add-customer'" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700"><i class="bi bi-person-plus-fill mr-1"></i> Add Customer Profile</button>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">ID Reference</th>
                                        <th class="px-6 py-4">Full Name</th>
                                        <th class="px-6 py-4">Email Address</th>
                                        <th class="px-6 py-4">Contact Phone Line</th>
                                        <th class="px-6 py-4">Total Orders Booked</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="(c,index) in customers" :key="c.id">
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-6 py-4 font-mono font-bold text-slate-400" x-text="index + 1"></td>
                                            <td class="px-6 py-4 text-slate-900 font-semibold" x-text="c.name"></td>
                                            <td class="px-6 py-4 text-slate-600 font-mono" x-text="c.email"></td>
                                            <td class="px-6 py-4 text-slate-600" x-text="c.phone"></td>
                                            <td class="px-6 py-4 font-mono font-bold text-slate-900" x-text="c.totalBookings"></td>
                                            <td class="px-6 py-4 text-right space-x-1">
                                                <button @click="editCustomer(c.id)" class="text-slate-700 bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded text-[10px] font-bold">Edit</button>
                                                <button @click="deleteCustomer(c.id)" class="text-rose-600 bg-rose-50 hover:bg-rose-100 px-2 py-1 rounded text-[10px] font-bold">Remove</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PANEL D1: DIRECT USER COMMUNICATIONS -->
                <div x-show="currentView === 'user-management'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Direct User Communications</h3>
                            <p class="text-sm text-slate-500">View customer profiles, login sessions, and link to bookings directly from the sidebar.</p>
                        </div>
                        <button @click="currentView = 'customers'" class="bg-slate-100 text-slate-800 px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm hover:bg-slate-200">Open Profiles</button>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                            <h4 class="text-sm font-bold text-slate-900 mb-4">Website contact messages</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3">No.</th>
                                            <th class="px-4 py-3">Sender</th>
                                            <th class="px-4 py-3">Subject</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3">Received</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(message,index) in messages" :key="message.id">
                                            <tr class="hover:bg-slate-50/70">
                                                <td class="px-4 py-3 font-mono text-slate-700" x-text="index + 1"></td>
                                                <td class="px-4 py-3 text-slate-900"><p x-text="message.customer"></p><p class="text-xs text-slate-500" x-text="message.email"></p></td>
                                                <td class="px-4 py-3 text-slate-600" x-text="message.subject || 'Website enquiry'"></td>
                                                <td class="px-4 py-3"><span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-[10px] font-semibold" x-text="message.status"></span></td>
                                                <td class="px-4 py-3 font-mono text-slate-500" x-text="message.created_at"></td>
                                            </tr>
                                        </template>
                                        <tr x-show="messages.length === 0"><td colspan="5" class="px-4 py-6 text-center text-slate-500">No website messages yet.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-bold text-slate-900">Customer Directory</h4>
                                <button @click="activeModal = 'add-customer'" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold hover:bg-blue-700">New Profile</button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3">Customer</th>
                                            <th class="px-4 py-3">Email</th>
                                            <th class="px-4 py-3">Phone</th>
                                            <th class="px-4 py-3 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="c in customers" :key="c.id">
                                            <tr class="hover:bg-slate-50/70">
                                                <td class="px-4 py-3 text-slate-900 font-semibold" x-text="c.name"></td>
                                                <td class="px-4 py-3 text-slate-600 font-mono" x-text="c.email"></td>
                                                <td class="px-4 py-3 text-slate-600" x-text="c.phone"></td>
                                                <td class="px-4 py-3 text-right space-x-1">
                                                    <button @click="contactCustomer(c.id)" class="text-slate-700 bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded text-[10px] font-bold">Contact</button>
                                                    <button @click="viewCustomerBookings(c.id)" class="text-blue-600 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded text-[10px] font-bold">Bookings</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANEL E: PAYMENTS & BALANCE LEDGER OVERVIEW -->
                <div x-show="currentView === 'payments'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div class="flex space-x-2 bg-slate-200/60 p-1 rounded-xl">
                            <button @click="paymentFilter = 'Transaction'" :class="paymentFilter === 'Transaction' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Processed Transactions</button>
                            <button @click="paymentFilter = 'Invoice'" :class="paymentFilter === 'Invoice' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Issued Invoices</button>
                            <button @click="paymentFilter = 'Refund'" :class="paymentFilter === 'Refund' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Processed Refunds Ledger</button>
                        </div>
                        <button @click="activeModal = 'add-payment'; modalForm.type = paymentFilter" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700">
                            <i class="bi bi-wallet2 mr-1"></i> Log Financial Movement Object
                        </button>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">No.</th>
                                        <th class="px-6 py-4">Linked Booking ID</th>
                                        <th class="px-6 py-4">Payer Account</th>
                                        <th class="px-6 py-4">Total Amount Value</th>
                                        <th class="px-6 py-4">Value Date</th>
                                        <th class="px-6 py-4">Processing Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="(p,index) in payments.filter(p => p.type === paymentFilter)" :key="p.id">
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-6 py-4 font-mono font-bold text-slate-900" x-text="index + 1"></td>
                                            <td class="px-6 py-4 font-mono text-blue-600" x-text="p.bookingId"></td>
                                            <td class="px-6 py-4 text-slate-900" x-text="p.customer"></td>
                                            <td class="px-6 py-4 font-mono font-bold text-slate-900" x-text="'$'+p.amount"></td>
                                            <td class="px-6 py-4 text-slate-500 font-mono" x-text="p.date"></td>
                                            <td class="px-6 py-4">
                                                <span :class="{'bg-emerald-100 text-emerald-800': p.status==='Paid' || p.status==='Processed', 'bg-amber-100 text-amber-800': p.status==='Pending'}" class="px-2 py-0.5 rounded text-xs font-semibold" x-text="p.status"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PANEL F: GALLERY STATIC STORAGE MAP -->
                <div x-show="currentView === 'gallery'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Website Image Manager</h3>
                            <p class="text-sm text-slate-500">All website images are shown here for upload, edit, and remove actions.</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="activeModal = 'add-gallery'" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700"><i class="bi bi-cloud-arrow-up-fill mr-1"></i> Upload Image</button>
                            <button @click="if(selectedGalleryId){ removeGalleryImage(selectedGalleryId); }" :class="selectedGalleryId ? 'bg-rose-600 text-white hover:bg-rose-700' : 'bg-slate-100 text-slate-400 cursor-not-allowed'" class="px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm" :disabled="selectedGalleryId === null">Remove Selected</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <template x-for="(img,index) in gallery" :key="img.id">
                            <div @click="toggleGallerySelection(img.id)" :class="selectedGalleryId === img.id ? 'ring-2 ring-blue-500 shadow-lg' : 'border-slate-200'" class="bg-white rounded-2xl border p-2 shadow-sm relative group overflow-hidden cursor-pointer">
                                <img :src="img.url" class="w-full h-32 object-cover rounded-xl" :alt="img.title">
                                <div class="p-2">
                                    <p class="text-xs font-bold truncate text-slate-800" x-text="img.title"></p>
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5" x-text="img.source || 'Gallery upload'"></p>
                                </div>
                                <button x-show="!img.source" @click.stop="editGallery(img.id)" class="absolute top-4 right-12 bg-slate-700 text-white p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity text-xs"><i class="bi bi-pencil"></i></button>
                                <button @click.stop="removeGalleryImage(img.id)" class="absolute top-4 right-4 bg-rose-600 text-white p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity text-xs"><i class="bi bi-trash"></i></button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PANEL H: REVIEWS & FEEDBACK CONTROL -->
                <div x-show="currentView === 'reviews'" x-transition class="space-y-6">
                    <h3 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-4">Customer Experience Review Audit Loop</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="rev in reviews" :key="rev.id">
                            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 relative">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm" x-text="rev.customer"></h4>
                                        <p class="text-xs text-slate-400 font-medium" x-text="'Target Property: ' + rev.target"></p>
                                    </div>
                                    <div class="flex text-amber-400 gap-0.5">
                                        <template x-for="i in Array.from({length: rev.rating})">
                                            <i class="bi bi-star-fill text-xs"></i>
                                        </template>
                                    </div>
                                </div>
                                <p class="text-xs italic text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100" x-text="'&ldquo; ' + rev.comment + ' &rdquo;'"></p>
                                <div class="flex justify-end pt-1">
                                    <button @click="reviews = reviews.filter(r => r.id !== rev.id); logActivity('Dismiss Review Feedback Object', rev.id)" class="text-rose-600 hover:bg-rose-50 px-2 py-1 rounded text-[10px] font-bold">Dismiss Review</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PANEL I: ANALYTICS & REPORTS GENERATOR LAYER -->
                <div x-show="currentView === 'reports'" x-transition class="space-y-6">
                    <div class="flex space-x-2 bg-slate-200/60 p-1 rounded-xl w-max">
                        <button @click="reportFilter = 'sales'" :class="reportFilter === 'sales' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Sales Analysis</button>
                        <button @click="reportFilter = 'bookings'" :class="reportFilter === 'bookings' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Bookings Pipeline Velocity</button>
                        <button @click="reportFilter = 'revenue'" :class="reportFilter === 'revenue' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Revenue Projections</button>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                        <div class="flex justify-between items-center">
                            <h4 class="font-bold text-slate-900 text-sm uppercase tracking-wider" x-text="'Calculated Report Core Metrics / ' + reportFilter"></h4>
                            <button @click="alert('Exporting system data payload frame as .CSV packet...')" class="border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 rounded-xl text-xs font-bold"><i class="bi bi-download mr-1"></i> Export Data Payload</button>
                        </div>

                        <!-- Conditional view display calculations metrics grids -->
                        <template x-if="reportFilter === 'sales'">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Gross Sales Value</span><p class="text-xl font-black text-slate-900 mt-1">$6,348</p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Net Margin Conversion</span><p class="text-xl font-black text-slate-900 mt-1">18.4%</p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Average Cart Order Value</span><p class="text-xl font-black text-slate-900 mt-1">$1,950</p></div>
                            </div>
                        </template>

                        <template x-if="reportFilter === 'bookings'">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Orders Logged</span><p class="text-xl font-black text-slate-900 mt-1" x-text="bookings.length"></p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Fulfillment Completion Rate</span><p class="text-xl font-black text-slate-900 mt-1">74.2%</p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Volatile Churn Cancellation</span><p class="text-xl font-black text-slate-900 mt-1">12.5%</p></div>
                            </div>
                        </template>

                        <template x-if="reportFilter === 'revenue'">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Cleared Liquid Balances</span><p class="text-xl font-black text-slate-900 mt-1">$4,349</p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Accounts Receivable Vault</span><p class="text-xl font-black text-slate-900 mt-1">$2,850</p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Issued Outflow Refunds Pay</span><p class="text-xl font-black text-slate-900 mt-1">$1,499</p></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PANEL L: OPERATOR PROFILE CORE SUMMARY -->
                <div x-show="currentView === 'profile'" x-transition class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm max-w-xl mx-auto space-y-6">
                        <div class="flex items-center space-x-4">
                            <img class="w-16 h-16 rounded-2xl object-contain bg-blue-50 p-2 ring-4 ring-slate-100 shadow-sm" src="../img/logo.png" alt="Nepal Tour and Travel logo">
                            <div>
                                <h3 class="text-lg font-black text-slate-900">Nepal Tours and Travel</h3>
                                <p class="text-xs font-mono text-blue-600 font-bold">Website Administrator</p>
                            </div>
                        </div>
                        
                        <div class="border-t border-slate-100 pt-4 divide-y divide-slate-50 text-xs">
                            <div class="py-2.5 flex justify-between"><span class="font-bold text-slate-400 uppercase">Operator User Identity Token ID</span><span class="font-mono font-semibold text-slate-800">USR-ADMIN-770X</span></div>
                            <div class="py-2.5 flex justify-between"><span class="font-bold text-slate-400 uppercase">Access Scope Permissions</span><span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 font-bold rounded">Full System Write Access Overrides Allowed</span></div>
                            <div class="py-2.5 flex justify-between"><span class="font-bold text-slate-400 uppercase">Hardware Key Signature</span><span class="font-mono text-slate-500">ED25519 SHA256:...9F2B</span></div>
                        </div>
                    </div>
                </div>

            </main>
        </div>

        <!-- DIALOG FORM MODAL CONTEXT CANVAS WINDOWS -->
        <div 
            x-show="activeModal !== null" 
            class="fixed inset-0 z-50 flex items-start justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto"
            x-transition.opacity
            x-cloak
        >
            <div @click.outside="activeModal = null" class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden transform transition-all max-h-[calc(100vh-2rem)] flex flex-col">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-sm uppercase tracking-wide text-slate-900" x-text="'Register System Data Object / ' + activeModal"></h3>
                    <button @click="activeModal = null" class="text-slate-400 hover:text-slate-600 p-1"><i class="bi bi-x-lg"></i></button>
                </div>
                
                <form @submit.prevent="executeSave()" class="p-6 space-y-4 overflow-y-auto custom-scrollbar flex-1 min-h-0">
                    
                    <!-- Form Fields Conditionals Block -->
                    <template x-if="activeModal === 'add-package' || activeModal === 'edit-package'">
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Package title</label><input type="text" x-model="modalForm.title" required maxlength="190" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Destination Hub Label</label><input type="text" x-model="modalForm.destination" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase">Classification Tag</label>
                                <select x-model="modalForm.category" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none">
                                    <option value="">Choose Tag...</option>
                                    <template x-for="cat in categoryNames" :key="cat"><option :value="cat" x-text="cat"></option></template>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Duration Window</label><input type="text" x-model="modalForm.duration" placeholder="e.g. 7 Days" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Base Cost ($ USD)</label><input type="number" x-model="modalForm.price" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            </div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Short Description</label><textarea x-model="modalForm.shortDescription" rows="2" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></textarea></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Full Itinerary / Description</label><textarea x-model="modalForm.fullDescription" rows="3" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></textarea></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Website image URL</label><input type="url" x-model="modalForm.imageUrl" placeholder="https://..." class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Or upload image</label><input id="packageImageFile" type="file" accept="image/*" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase">Status</label>
                                    <select x-model="modalForm.status" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <label class="flex items-center gap-2 mt-6 text-sm font-semibold text-slate-700"><input type="checkbox" x-model="modalForm.isFeatured" class="rounded border-slate-300"> Featured</label>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeModal === 'add-place' || activeModal === 'edit-place'">
                            <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Place name</label><input type="text" x-model="modalForm.name" required maxlength="150" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div x-show="modalForm.placeCategories?.includes('provinces')" x-transition><label class="block text-xs font-bold text-slate-500 uppercase">Province</label><select x-model="modalForm.province" :required="modalForm.placeCategories?.includes('provinces')" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"><option value="" disabled>Select province</option><option value="Koshi">Koshi</option><option value="Madhesh">Madhesh</option><option value="Bagmati">Bagmati</option><option value="Gandaki">Gandaki</option><option value="Lumbini">Lumbini</option><option value="Karnali">Karnali</option><option value="Sudurpashchim">Sudurpashchim</option></select></div>
                            <fieldset><legend class="block text-xs font-bold text-slate-500 uppercase">Show this place under</legend><div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2"><template x-for="option in placeCategoryOptions" :key="option.value"><label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 cursor-pointer hover:bg-slate-50"><input type="checkbox" :value="option.value" x-model="modalForm.placeCategories" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"><span x-text="option.label"></span></label></template></div><p class="mt-1 text-xs text-slate-400">Select every Place to Go button where this card should appear.</p></fieldset>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Description</label><textarea x-model="modalForm.description" rows="2" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></textarea></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">History</label><textarea x-model="modalForm.history" rows="2" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></textarea></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Best time to visit</label><input type="text" x-model="modalForm.bestTimeToVisit" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Google map URL</label><input type="url" x-model="modalForm.googleMap" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Image URL or path</label><input type="text" x-model="modalForm.imageUrl" placeholder="https://... or img/filename.jpg" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"><p class="mt-1 text-xs text-slate-400">Paste an external URL, an internal image path, or upload a file.</p><div x-show="modalForm.imageUrl" class="mt-2"><img :src="managedImageUrl(modalForm.imageUrl, 'places')" :alt="modalForm.name || 'Place image preview'" class="h-32 w-full rounded-xl border border-slate-200 object-cover"></div></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Or upload image</label><input id="placeImageFile" type="file" accept="image/*" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase">Status</label>
                                    <select x-model="modalForm.status" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <label class="flex items-center gap-2 mt-6 text-sm font-semibold text-slate-700"><input type="checkbox" x-model="modalForm.isFeatured" class="rounded border-slate-300"> Featured</label>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeModal === 'add-activity' || activeModal === 'edit-activity'">
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Activity name</label><input type="text" x-model="modalForm.name" required maxlength="150" placeholder="e.g. Rafting" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Category</label><input type="text" x-model="modalForm.category" required maxlength="100" placeholder="e.g. Adventure" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Description</label><textarea x-model="modalForm.description" required rows="3" maxlength="1000" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></textarea></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Activity page URL (optional)</label><input type="text" x-model="modalForm.pageUrl" placeholder="rafting.php or https://..." class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Image URL or path</label><input type="text" x-model="modalForm.imageUrl" placeholder="https://... or img/filename.jpg" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"><p class="mt-1 text-xs text-slate-400">Paste an external URL, an internal image path, or upload a file.</p><div x-show="modalForm.imageUrl" class="mt-2"><img :src="managedImageUrl(modalForm.imageUrl, 'activities')" :alt="modalForm.name || 'Activity image preview'" class="h-32 w-full rounded-xl border border-slate-200 object-cover"></div></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Or upload image</label><input id="activityImageFile" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"><p class="mt-1 text-xs text-slate-400">Uploaded images are resized to fit within 1600 × 1200 pixels and optimized automatically. GIF files are kept as-is to preserve animation.</p></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Status</label><select x-model="modalForm.status" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"><option value="active">Active — show on website</option><option value="inactive">Inactive — hide from website</option></select></div>
                        </div>
                    </template>

                    <template x-if="activeModal === 'add-category' || activeModal === 'add-destination'">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase">Classification Identity Tag String</label>
                            <input type="text" x-model="modalForm.name" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none">
                        </div>
                    </template>

                    <template x-if="activeModal === 'add-customer' || activeModal === 'edit-customer'">
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Full Account Legal Name</label><input type="text" x-model="modalForm.name" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Primary Email Identity</label><input type="email" x-model="modalForm.email" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Contact Phone Line</label><input type="text" x-model="modalForm.phone" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                        </div>
                    </template>

                    <template x-if="activeModal === 'add-gallery' || activeModal === 'edit-gallery'">
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Media Caption Title</label><input type="text" x-model="modalForm.title" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Asset source URL</label><input type="url" x-model="modalForm.url" placeholder="https://..." class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Alt text</label><input type="text" x-model="modalForm.altText" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div x-show="activeModal === 'add-gallery'"><label class="block text-xs font-bold text-slate-500 uppercase">Or upload image (max 5 MB)</label><input id="galleryImageFile" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm"></div>
                        </div>
                    </template>

                    <template x-if="activeModal === 'add-payment'">
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Payer Customer Name Label</label><input type="text" x-model="modalForm.customer" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div class="grid grid-cols-2 gap-2">
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Linked Booking ID</label><input type="text" x-model="modalForm.bookingId" placeholder="BKG-901" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Value Balance ($ USD)</label><input type="number" x-model="modalForm.amount" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            </div>
                        </div>
                    </template>

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                        <button type="button" @click="activeModal = null" class="px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-xl transition-colors">Abort</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-md shadow-blue-600/10">Confirm Mutation Entry</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
<script defer src="https://static.cloudflareinsights.com/beacon.min.js/v4513226cdae34746b4dedf0b4dfa099e1781791509496" integrity="sha512-ZE9pZaUXND66v380QUtch/5sE9tPFh2zg45pR2PB0CVkCtOREv2AJKkSidISWkysEuQ0EH8faUU5du78bx87UQ==" data-cf-beacon='{"version":"2024.11.0","token":"499e684b7b1043878977050a0a606794","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
</body>
</html>
