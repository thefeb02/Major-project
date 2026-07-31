<?php
require_once __DIR__ . '/../Backend/database.php';

$title = trim($_GET['title'] ?? '');
$desc = trim($_GET['desc'] ?? '');
$img = trim($_GET['img'] ?? '');
$alt = trim($_GET['alt'] ?? '');
$topic = trim($_GET['topic'] ?? '');

function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$profiles = [
    'destination' => [
        'heading' => 'Destination Guidance',
        'items' => [
            'Top attractions and must-see sites',
            'Best time to visit and weather considerations',
            'Local customs and cultural etiquette',
            'Recommended duration of stay',
        ],
        'tips' => 'Choose destinations based on your interest: culture, mountains, wildlife, pilgrimage, city tour or peaceful hill views.',
    ],
    'duration' => [
        'heading' => 'Duration Guidance',
        'items' => [
            '3 to 5 days: Kathmandu, Pokhara, Nagarkot, Chitwan or short hill trips',
            '7 to 10 days: cultural circuits, Pokhara-Chitwan tours or short trekking routes',
            '2 weeks or more: Everest, Annapurna, remote lakes and multi-destination tours',
            'Add rest days for mountain travel and long road routes',
        ],
        'tips' => 'Keep extra time for weather delays, mountain roads, domestic flights and relaxed sightseeing.',
    ],
    'accommodation' => [
        'heading' => 'Accommodation Options',
        'items' => [
            'Budget guesthouses and hostels for simple stays',
            'Mid-range hotels with breakfast, Wi-Fi and city access',
            'Luxury resorts and boutique hotels near major attractions',
            'Tea houses and mountain lodges for trekking routes',
        ],
        'tips' => 'Book early during festival season and trekking season, especially in Kathmandu, Pokhara and mountain regions.',
    ],
    'transport' => [
        'heading' => 'Transport Options',
        'items' => [
            'Tourist buses for city-to-city travel',
            'Private vehicles for flexible family or group tours',
            'Domestic flights for long mountain routes',
            'Local taxis, jeeps and guided transfers for short routes',
        ],
        'tips' => 'Use private transport for comfort and tourist buses for budget travel. Mountain routes may need jeeps or flights.',
    ],
    'provinces' => [
        'heading' => 'Place Details',
        'items' => [
            'Explore famous city landmarks and local culture',
            'Try local food, markets and short sightseeing tours',
            'Good choice for family trips and first-time visitors',
            'Easy to combine with hotels, transport and guided tours',
        ],
        'tips' => 'Province destinations are flexible and can be planned as short or long tours depending on your time.',
    ],
    'heritage' => [
        'heading' => 'Heritage Details',
        'items' => [
            'Ancient palaces, temples, stupas and traditional squares',
            'Best explored with a local cultural guide',
            'Great for history, photography and architecture',
            'Respect temple rules, dress modestly and follow local customs',
        ],
        'tips' => 'Morning and late afternoon are usually best for heritage walks and photos.',
    ],
    'protected' => [
        'heading' => 'Nature and Wildlife Details',
        'items' => [
            'Jungle safari, bird watching, lakes and forest walks',
            'Protected-area permits may be required',
            'Best for families, nature lovers and peaceful travel',
            'Use local guides for safety and better wildlife spotting',
        ],
        'tips' => 'Pack light clothes for jungle areas and warm layers for high-altitude parks.',
    ],
    'cities' => [
        'heading' => 'City Travel Details',
        'items' => [
            'Sightseeing, shopping, restaurants and local markets',
            'Good hotels and easy transport access',
            'Best for short stays and comfortable travel',
            'Can be combined with nearby hill stations or heritage sites',
        ],
        'tips' => 'Cities are a good base for nearby day tours and easy travel planning.',
    ],
    'peaks' => [
        'heading' => 'Mountain Details',
        'items' => [
            'High Himalayan scenery and trekking routes',
            'Permits, guides and warm gear are important',
            'Plan for altitude, weather and physical fitness',
            'Best for adventure travelers and mountain lovers',
        ],
        'tips' => 'Never rush high-altitude travel. Add acclimatization days for a safer journey.',
    ],
    'pilgrimage' => [
        'heading' => 'Pilgrimage Details',
        'items' => [
            'Sacred temples, monasteries and peaceful spiritual sites',
            'Suitable for worship, meditation and family visits',
            'Some routes need early morning starts or special transport',
            'Respect rituals, local rules and quiet areas',
        ],
        'tips' => 'Pilgrimage routes are best planned with correct timing, transport and nearby accommodation.',
    ],
    'hills' => [
        'heading' => 'Hill Destination Details',
        'items' => [
            'Sunrise views, mountain panoramas and peaceful villages',
            'Great for short breaks from busy cities',
            'Good for photography, nature walks and local homestays',
            'Weather can change quickly, so carry light warm clothing',
        ],
        'tips' => 'Hill stations are ideal for one-night or weekend trips with relaxed travel plans.',
    ],
    'activity' => [
        'heading' => 'Activity Details',
        'items' => [
            'Choose activity based on season, fitness and safety needs',
            'Use experienced guides for adventure activities',
            'Check equipment, insurance and route conditions',
            'Combine activities with nearby destinations for better value',
        ],
        'tips' => 'Adventure activities should be planned with verified operators and proper safety checks.',
    ],
    'festival' => [
        'heading' => 'Festival Details',
        'items' => [
            'Learn the cultural meaning before joining celebrations',
            'Book accommodation early during major festivals',
            'Respect family spaces, temples and local traditions',
            'Great opportunity for photos, food and cultural learning',
        ],
        'tips' => 'Festival dates can shift each year, so confirm dates before booking your trip.',
    ],
    'destination-info' => [
        'heading' => 'Travel Information',
        'items' => [
            'Compare destinations by interest and travel style',
            'Match places with your available time and budget',
            'Use image details to understand what each area offers',
            'Then choose transport and accommodation',
        ],
        'tips' => 'This information helps you decide before booking a travel plan.',
    ],
];

$profile = $profiles[$topic] ?? $profiles['destination'];
$pageTitle = $title ?: 'Travel Detail';
$pageDesc = $desc ?: 'Explore helpful travel details, suggested activities, timing and planning guidance for this section.';
$bookingCategory = $topic === 'activity' ? 'Adventure activity' : ($topic === 'heritage' ? 'Cultural tour' : ($topic === 'peaks' ? 'Trekking' : 'Tour'));
$isMountainTrek = $topic === 'peaks';
$isHeritage = $topic === 'heritage';
$isAdventureActivity = $topic === 'activity';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= esc($pageTitle) ?> | Nepal Tour & Travel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="booking-form.css">
    <style>
    .detail-shell{max-width:1120px;margin:0 auto;padding:28px 18px 70px}
    .detail-back{display:inline-block;margin-bottom:22px;color:#0f2340;text-decoration:none;font-size:18px}
    .detail-hero{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(320px,.95fr);gap:34px;align-items:center;margin-bottom:34px}
    .detail-title{font-size:42px;line-height:1.15;margin:0 0 16px;color:#0f2340}
    .detail-desc{font-size:20px;line-height:1.7;color:#1f2937}
    .detail-image{border-radius:8px;overflow:hidden;box-shadow:0 18px 45px rgba(15,35,64,.18);background:#eef2f7;min-height:320px}
    .detail-image img{width:100%;height:100%;min-height:320px;max-height:520px;object-fit:cover;display:block}
    .detail-booking-card{margin-top:18px;padding:20px 22px;background:#0f2340;border-radius:8px;color:#fff;box-shadow:0 14px 28px rgba(15,35,64,.18)}
    .detail-booking-card p{margin:0 0 5px;color:#cfe4ff;font-size:15px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
    .detail-booking-card h2{margin:0 0 14px;font-size:24px}
    .detail-book-button{width:100%;padding:14px 18px;border:0;border-radius:6px;background:#f59e0b;color:#111827;font:700 17px inherit;cursor:pointer}
    .detail-book-button:hover{background:#fbbf24}
    .detail-placeholder{min-height:320px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#eef6ff,#f5fff7);color:#0f2340;font-size:28px;font-weight:800;text-align:center;padding:28px}
    .detail-more{margin-top:28px}
    .detail-more h2{font-size:28px;margin:0 0 12px;color:#0f2340}
    .detail-more p{font-size:18px;line-height:1.7}
    .meta-ctas{margin-top:22px;display:flex;gap:14px;flex-wrap:wrap}
    .meta-ctas a{display:inline-block;padding:16px 22px;background:#005fbd;color:#fff;border-radius:6px;text-decoration:none;font-size:18px}
    .meta-ctas a.secondary{background:#eee;color:#111827}
    .detail-divider{margin:28px 0;border:0;border-top:1px solid #b9b9b9}
    .guidance-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,.65fr);gap:28px}
    .guidance-panel h3{font-size:24px;margin:0 0 10px;color:#0f2340}
    .guidance-panel ul{font-size:18px;line-height:1.65;margin:0;padding-left:24px}
    .quick-facts{background:#f8fbff;border:1px solid #dce7f5;border-radius:8px;padding:22px}
    .quick-facts h3{font-size:22px;margin:0 0 12px;color:#0f2340}
    .quick-facts p{font-size:17px;line-height:1.7;margin:0}
    .trek-safety{margin-top:34px;padding:30px;border:1px solid #d8e5f4;border-radius:12px;background:linear-gradient(135deg,#f6fbff,#fff);box-shadow:0 10px 28px rgba(15,35,64,.08)}
    .trek-safety-head{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:22px}
    .trek-safety h2{margin:0;color:#0f2340;font-size:28px}.trek-safety-head p{margin:6px 0 0;font-size:16px;color:#506176}
    .safety-status{display:inline-flex;align-items:center;gap:7px;white-space:nowrap;background:#e8f7ef;color:#087443;padding:7px 11px;border-radius:999px;font-size:14px;font-weight:700}.safety-status:before{content:'';width:8px;height:8px;border-radius:50%;background:#10b981}
    .safety-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:22px}.safety-metric{padding:15px;border-radius:9px;background:#fff;border:1px solid #e4edf6}.safety-metric span{display:block;font-size:13px;color:#64748b;margin-bottom:3px}.safety-metric strong{font-size:18px;color:#0f2340}
    .safety-content{display:grid;grid-template-columns:1.15fr .85fr;gap:22px}.safety-list{list-style:none;margin:0;padding:0}.safety-list li{position:relative;padding:0 0 12px 27px;color:#334155;line-height:1.5}.safety-list li:before{content:'✓';position:absolute;left:0;color:#087443;font-weight:800}.checkin-box{padding:18px;background:#0f2340;border-radius:9px;color:#fff}.checkin-box h3{font-size:18px;margin:0 0 6px}.checkin-box p{font-size:14px;line-height:1.5;color:#cbdcf1;margin:0 0 14px}.checkin-btn{border:0;border-radius:6px;padding:10px 14px;background:#f59e0b;color:#172033;font-weight:800;cursor:pointer}.checkin-message{display:none;margin-top:10px;font-size:13px;color:#b7f7d5}.checkin-message.is-visible{display:block}
    .heritage-data{margin-top:34px;padding:30px;border-radius:12px;background:#fffaf0;border:1px solid #f0dfb7}.heritage-data h2{margin:0 0 6px;color:#0f2340;font-size:28px}.heritage-intro{margin:0 0 20px;font-size:16px;color:#5f5545}.heritage-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.heritage-item{background:#fff;padding:18px;border-radius:9px;border:1px solid #f3e7ce}.heritage-icon{display:block;font-size:22px;margin-bottom:7px}.heritage-item h3{font-size:17px;color:#0f2340;margin:0 0 5px}.heritage-item p{font-size:14px;line-height:1.55;color:#655d52;margin:0}.heritage-note{margin:18px 0 0;padding:13px 15px;border-left:4px solid #d97706;background:#fff;color:#594719;font-size:15px}
    .adventure-data{margin-top:26px;padding:22px;border-radius:10px;background:#f4f8ff;border:1px solid #d9e5f5}.adventure-data h2{font-size:23px;color:#0f2340;margin:0 0 14px}.adventure-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:11px}.adventure-item{padding:12px 13px;background:#fff;border-radius:7px;border:1px solid #e4ebf5}.adventure-item span{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b}.adventure-item strong{display:block;margin-top:3px;font-size:15px;color:#172b4d}.adventure-safety{margin:14px 0 0;color:#40536b;font-size:14px;line-height:1.55}
    @media (max-width:850px){
        .detail-hero,.guidance-grid,.safety-content{grid-template-columns:1fr}
        .detail-title{font-size:34px}
        .detail-desc{font-size:18px}
    }
    @media (max-width:600px){.trek-safety{padding:22px 18px}.trek-safety-head{flex-direction:column}.safety-metrics{grid-template-columns:1fr 1fr}.safety-metric{padding:12px}}
    @media (max-width:850px){.heritage-grid{grid-template-columns:1fr 1fr}}@media (max-width:500px){.heritage-data{padding:22px 18px}.heritage-grid{grid-template-columns:1fr}}
    @media (max-width:500px){.adventure-grid{grid-template-columns:1fr}}
    </style>
</head>
<body data-booking-category="<?= esc($bookingCategory) ?>">
    <main class="detail-shell">
        <a class="detail-back" href="index.php">&larr; Back to home</a>

        <section class="detail-hero">
            <div>
                <h1 class="detail-title"><?= esc($pageTitle) ?></h1>
                <div class="detail-desc"><?= esc($pageDesc) ?></div>
                <?php if ($isAdventureActivity): ?>
                <section class="adventure-data" aria-labelledby="adventure-data-title">
                    <h2 id="adventure-data-title">Adventure at a glance</h2>
                    <div class="adventure-grid">
                        <div class="adventure-item"><span>Popular choices</span><strong>Rafting, paragliding, bungee &amp; hiking</strong></div>
                        <div class="adventure-item"><span>Typical session</span><strong>Half-day to full-day experiences</strong></div>
                        <div class="adventure-item"><span>Who can join</span><strong>Beginners to experienced travelers</strong></div>
                        <div class="adventure-item"><span>Before you go</span><strong>Confirm weather, fitness &amp; age rules</strong></div>
                    </div>
                    <p class="adventure-safety"><strong>Safety first:</strong> Choose a licensed operator, use fitted safety equipment and follow the instructor's briefing at all times.</p>
                </section>
                <?php endif; ?>
            </div>
            <div>
            <div class="detail-image">
                <?php if ($img): ?>
                    <img src="<?= esc($img) ?>" alt="<?= esc($alt ?: $pageTitle) ?>">
                <?php else: ?>
                    <div class="detail-placeholder"><?= esc($pageTitle) ?></div>
                <?php endif; ?>
            </div>
            <aside class="detail-booking-card">
                <p>Ready to travel?</p>
                <h2>Book this experience</h2>
                <button type="button" class="detail-book-button" data-booking-package="<?= esc($pageTitle) ?>" data-booking-image="<?= esc($img) ?>">Book Now</button>
            </aside>
            </div>
        </section>

        <section class="detail-more">
            <h2>More</h2>
            <p>Interested in this topic? Create a travel plan or explore related options below.</p>
            <div class="meta-ctas">
                <a href="index.php#plan">Create Travel Plan</a>
                <a class="secondary" href="index.php">Back to Home</a>
            </div>
        </section>

        <?php if ($isHeritage): ?>
        <section class="heritage-data" aria-labelledby="heritage-data-title">
            <h2 id="heritage-data-title">Heritage Visit Guide</h2>
            <p class="heritage-intro">A little local knowledge helps make every temple, stupa and historic square more meaningful.</p>
            <div class="heritage-grid">
                <article class="heritage-item"><span class="heritage-icon" aria-hidden="true">&#9728;</span><h3>Best visit time</h3><p>Arrive in the morning or late afternoon for softer light, cooler walks and active daily rituals.</p></article>
                <article class="heritage-item"><span class="heritage-icon" aria-hidden="true">&#8987;</span><h3>Suggested duration</h3><p>Allow 1–2 hours for one major site, or a half day to explore a heritage area at an easy pace.</p></article>
                <article class="heritage-item"><span class="heritage-icon" aria-hidden="true">&#8962;</span><h3>What to discover</h3><p>Temples, stupas, monasteries, carved courtyards, local markets and living craft traditions.</p></article>
                <article class="heritage-item"><span class="heritage-icon" aria-hidden="true">&#9673;</span><h3>Visitor etiquette</h3><p>Dress modestly, remove shoes where requested, ask before photographing people and respect quiet spaces.</p></article>
            </div>
            <p class="heritage-note"><strong>Travel tip:</strong> A local guide can explain the stories, symbols and customs that are easy to miss when visiting on your own.</p>
        </section>
        <?php endif; ?>

        <?php if ($isMountainTrek): ?>
        <section class="trek-safety" aria-labelledby="trek-safety-title">
            <div class="trek-safety-head">
                <div>
                    <h2 id="trek-safety-title">Trek Safety &amp; Route Tracking</h2>
                    <p>Plan each Himalayan day with clear altitude, weather and check-in information.</p>
                </div>
                <span class="safety-status">Safety plan ready</span>
            </div>
            <div class="safety-metrics" aria-label="Trek safety information">
                <div class="safety-metric"><span>Altitude approach</span><strong>Gradual ascent</strong></div>
                <div class="safety-metric"><span>Daily check-in</span><strong>Guide-led</strong></div>
                <div class="safety-metric"><span>Weather review</span><strong>Every morning</strong></div>
                <div class="safety-metric"><span>Emergency support</span><strong>Route plan</strong></div>
            </div>
            <div class="safety-content">
                <div>
                    <ul class="safety-list">
                        <li>Guides record the planned route, overnight stop and expected arrival time before each trekking day.</li>
                        <li>Altitude progress is reviewed daily; rest or descent is prioritised if anyone shows signs of altitude illness.</li>
                        <li>Carry warm layers, water treatment, a headlamp, first-aid supplies and an offline map for changing conditions.</li>
                        <li>Share your itinerary and emergency contact with a trusted person before leaving the trailhead.</li>
                    </ul>
                </div>
                <aside class="checkin-box">
                    <h3>Personal trek check-in</h3>
                    <p>Save a quick “prepared” check-in on this device before you book or begin your trek.</p>
                    <button type="button" class="checkin-btn" id="trekCheckin">Mark myself prepared</button>
                    <div class="checkin-message" id="checkinMessage" role="status"></div>
                </aside>
            </div>
        </section>
        <?php endif; ?>

        <hr class="detail-divider">

        <section class="guidance-grid">
            <div class="guidance-panel">
                <h3><?= esc($profile['heading']) ?></h3>
                <ul>
                    <?php foreach ($profile['items'] as $item): ?>
                        <li><?= esc($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <aside class="quick-facts">
                <h3>Helpful Tip</h3>
                <p><?= esc($profile['tips']) ?></p>
            </aside>
        </section>
    </main>
    <script src="booking-form.js"></script>
    <?php if ($isMountainTrek): ?>
    <script>
    (function () {
        var button = document.getElementById('trekCheckin');
        var message = document.getElementById('checkinMessage');
        if (!button || !message) return;
        button.addEventListener('click', function () {
            var checkedAt = new Date().toLocaleString();
            try { localStorage.setItem('nepalTrekSafetyCheckin', checkedAt); } catch (e) {}
            message.textContent = 'Prepared check-in saved: ' + checkedAt;
            message.classList.add('is-visible');
            button.textContent = 'Check-in saved';
        });
    }());
    </script>
    <?php endif; ?>
</body>
</html>
