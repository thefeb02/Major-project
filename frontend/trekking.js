// Trek packages data
const trekData = {
    everest: {
        id: "everest",
        title: "Everest Base Camp Trek",
        duration: 16,
        image: "https://www.trekkingtoeverest.com/wp-content/uploads/2022/06/Old-Everest-Base-Camp-Trek.jpg",
        rating: 5,
        reviews: 140,
        difficulty: "Moderate to Hard",
        altitude: "5,364m (17,598 ft)",
        season: "September to November, March to May",
        groupSize: "2-12 people",
        description: "Trek to the base camp of Mount Everest, the world's highest peak. This iconic trek offers stunning Himalayan views, visits to Sherpa villages, and an unforgettable experience at 5,364m.",
        highlights: [
            "Trek to the base camp of Mount Everest",
            "Visit Sherpa villages and monasteries",
            "Stunning views of Everest, Lhotse, and Nuptse",
            "Experience authentic Himalayan culture",
            "Professional guides and porters"
        ],
        itinerary: [
            { day: 1, title: "Kathmandu", description: "Arrive in Kathmandu. Rest and acclimatization." },
            { day: 2, title: "Kathmandu to Phakding", description: "Flight to Lukla (2,860m), trek to Phakding (2,610m). 5-6 hours trek." },
            { day: 3, title: "Phakding to Namche Bazaar", description: "Trek to Namche Bazaar (3,440m). 5-7 hours. Acclimatization day." },
            { day: 4, title: "Acclimatization at Namche", description: "Rest day. Explore Namche Bazaar and surrounding areas." },
            { day: 5, title: "Namche to Tengboche", description: "Trek to Tengboche (3,867m). 5-6 hours. Visit famous monastery." },
            { day: 6, title: "Tengboche to Dingboche", description: "Trek to Dingboche (4,410m). 5-6 hours. Acclimatization day." },
            { day: 7, title: "Acclimatization at Dingboche", description: "Rest day for acclimatization." },
            { day: 8, title: "Dingboche to Lobuche", description: "Trek to Lobuche (4,910m). 5-6 hours." },
            { day: 9, title: "Lobuche to Everest Base Camp", description: "Trek to Everest Base Camp (5,364m). 5-7 hours. Reach the base camp!" },
            { day: 10, title: "Everest Base Camp to Kala Patthar", description: "Early morning hike to Kala Patthar (5,545m) for sunrise views. Return to Gorak Shep." },
            { day: 11, title: "Gorak Shep to Pheriche", description: "Trek back to Pheriche (4,371m). 5-6 hours." },
            { day: 12, title: "Pheriche to Namche", description: "Trek to Namche Bazaar (3,440m). 5-7 hours." },
            { day: 13, title: "Namche to Phakding", description: "Trek to Phakding (2,610m). 5-6 hours." },
            { day: 14, title: "Phakding to Lukla", description: "Trek to Lukla (2,860m). 5-6 hours." },
            { day: 15, title: "Lukla to Kathmandu", description: "Flight back to Kathmandu." },
            { day: 16, title: "Departure", description: "Depart Kathmandu." }
        ],
        whatToPack: [
            "Warm layers and waterproof jacket",
            "Trekking boots and warm socks",
            "Sleeping bag (rated for -15°C)",
            "Sunscreen and sunglasses",
            "First aid kit and medications",
            "Trekking poles",
            "Headlamp with extra batteries"
        ],
        bestSeason: "September to November (clear skies, stable weather) and March to May (warm days, clear views)",
        fitness: "Good physical fitness required. Regular cardio and leg exercises recommended.",
        acclimatization: "Multiple acclimatization days included to help adjust to high altitude."
    },
    langtang: {
        id: "langtang",
        title: "Langtang Valley Trek",
        duration: 11,
        image: "https://www.nepal-trek.com/gallery/langtang-valley-trek32.jpg",
        rating: 5,
        reviews: 24,
        difficulty: "Moderate",
        altitude: "3,870m (12,697 ft)",
        season: "March to May, September to November",
        groupSize: "2-12 people",
        description: "Trek through the beautiful Langtang Valley, known for its lush forests, pristine wilderness, and traditional Tamang villages. A perfect trek for those seeking solitude and natural beauty.",
        highlights: [
            "Trek through pristine Langtang Valley",
            "Visit traditional Tamang villages",
            "Beautiful rhododendron forests",
            "Kyanjin Gompa monastery and cheese factory",
            "Less crowded than other major treks",
            "Stunning mountain views"
        ],
        itinerary: [
            { day: 1, title: "Kathmandu", description: "Arrive in Kathmandu. Rest and preparation." },
            { day: 2, title: "Kathmandu to Syabrubesi", description: "Drive to Syabrubesi (1,460m). 6-7 hours drive." },
            { day: 3, title: "Syabrubesi to Lama Hotel", description: "Trek to Lama Hotel (2,470m). 5-6 hours. Beautiful forest trail." },
            { day: 4, title: "Lama Hotel to Langtang Village", description: "Trek to Langtang Village (3,430m). 5-6 hours. Enter Langtang Valley." },
            { day: 5, title: "Langtang Village to Kyanjin Gompa", description: "Trek to Kyanjin Gompa (3,870m). 4-5 hours. Visit the monastery." },
            { day: 6, title: "Acclimatization at Kyanjin Gompa", description: "Rest day. Explore surrounding areas and local cheese factory." },
            { day: 7, title: "Kyanjin Gompa to Langtang Village", description: "Trek back to Langtang Village. 4-5 hours." },
            { day: 8, title: "Langtang Village to Lama Hotel", description: "Trek to Lama Hotel (2,470m). 5-6 hours." },
            { day: 9, title: "Lama Hotel to Syabrubesi", description: "Trek to Syabrubesi (1,460m). 5-6 hours." },
            { day: 10, title: "Syabrubesi to Kathmandu", description: "Drive back to Kathmandu. 6-7 hours drive." },
            { day: 11, title: "Departure", description: "Depart Kathmandu." }
        ],
        whatToPack: [
            "Warm layers and waterproof jacket",
            "Trekking boots and warm socks",
            "Sleeping bag (rated for -10°C)",
            "Sunscreen and sunglasses",
            "First aid kit",
            "Trekking poles",
            "Camera"
        ],
        bestSeason: "March to May (rhododendrons blooming) and September to November (clear skies)",
        fitness: "Moderate fitness required. Regular walking and light cardio recommended.",
        acclimatization: "One acclimatization day at Kyanjin Gompa to adjust to altitude."
    },
    annapurna: {
        id: "annapurna",
        title: "Annapurna Base Camp Trek",
        duration: 14,
        image: "https://paradisehimalayan.com/public/uploads/frontend/main/annapurna-base-camp-trek-1599407752461.jpg",
        rating: 5,
        reviews: 124,
        difficulty: "Moderate",
        altitude: "4,130m (13,550 ft)",
        season: "September to November, March to May",
        groupSize: "2-12 people",
        description: "Trek to the base camp of Annapurna I, surrounded by stunning mountain peaks. Experience diverse landscapes from rhododendron forests to alpine meadows.",
        highlights: [
            "Trek to Annapurna Base Camp at 4,130m",
            "Sunrise views from Poon Hill",
            "Diverse landscapes and ecosystems",
            "Rhododendron forests and alpine meadows",
            "Views of Annapurna I, II, III, and Machapuchare",
            "Authentic Gurung and Magar villages"
        ],
        itinerary: [
            { day: 1, title: "Kathmandu", description: "Arrive in Kathmandu. Rest and preparation." },
            { day: 2, title: "Kathmandu to Pokhara", description: "Drive to Pokhara (800m). 6-7 hours drive." },
            { day: 3, title: "Pokhara to Nayapul to Tikhedhunga", description: "Drive to Nayapul, trek to Tikhedhunga (1,570m). 3-4 hours trek." },
            { day: 4, title: "Tikhedhunga to Ghorepani", description: "Trek to Ghorepani (2,874m). 5-6 hours. Visit Poon Hill at sunrise." },
            { day: 5, title: "Ghorepani to Tadapani", description: "Trek to Tadapani (2,630m). 5-6 hours. Beautiful rhododendron forests." },
            { day: 6, title: "Tadapani to Chhomrong", description: "Trek to Chhomrong (2,110m). 5-6 hours." },
            { day: 7, title: "Chhomrong to ABC (Annapurna Base Camp)", description: "Trek to ABC (4,130m). 5-7 hours. Reach the base camp!" },
            { day: 8, title: "Acclimatization at ABC", description: "Rest day at Annapurna Base Camp. Explore and enjoy mountain views." },
            { day: 9, title: "ABC to Chhomrong", description: "Trek back to Chhomrong (2,110m). 5-7 hours." },
            { day: 10, title: "Chhomrong to Tadapani", description: "Trek to Tadapani (2,630m). 5-6 hours." },
            { day: 11, title: "Tadapani to Ghorepani", description: "Trek to Ghorepani (2,874m). 5-6 hours." },
            { day: 12, title: "Ghorepani to Nayapul", description: "Trek to Nayapul (1,070m). 5-6 hours." },
            { day: 13, title: "Nayapul to Pokhara", description: "Drive to Pokhara. 1-2 hours." },
            { day: 14, title: "Departure", description: "Depart Pokhara or return to Kathmandu." }
        ],
        whatToPack: [
            "Warm layers and waterproof jacket",
            "Trekking boots and warm socks",
            "Sleeping bag (rated for -10°C)",
            "Sunscreen and sunglasses",
            "First aid kit",
            "Trekking poles",
            "Camera"
        ],
        bestSeason: "September to November (clear skies, stable weather) and March to May (warm days, blooming rhododendrons)",
        fitness: "Moderate fitness required. Regular walking and light cardio recommended.",
        acclimatization: "One acclimatization day at ABC to adjust to high altitude."
    }
};

// Array of trek IDs for easier iteration
const trekIds = Object.keys(trekData);

// DOM Elements
const app = document.getElementById('app');
const packagesPage = document.getElementById('packagesPage');
const detailsPage = document.getElementById('detailsPage');
const packagesGrid = document.getElementById('packagesGrid');
const detailsContent = document.getElementById('detailsContent');
const backBtn = document.getElementById('backBtn');

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    renderPackages();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    backBtn.addEventListener('click', (e) => {
        e.preventDefault();
        showPackagesPage();
    });
}

// Render packages on home page
function renderPackages() {
    packagesGrid.innerHTML = '';

    trekIds.forEach(id => {
        const trek = trekData[id];
        const card = createPackageCard(trek);
        packagesGrid.appendChild(card);
    });
}

// Create package card element
function createPackageCard(trek) {
    const card = document.createElement('div');
    card.className = 'package-card';

    const starsHTML = Array(trek.rating).fill('<span class="star">★</span>').join('');

    card.innerHTML = `
        <div class="package-image">
            <img src="${trek.image}" alt="${trek.title}">
            <div class="duration-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>${trek.duration} Days</span>
            </div>
        </div>
        <div class="package-content">
            <h3 class="package-title">${trek.title}</h3>
            <div class="package-rating">
                <div class="stars">${starsHTML}</div>
                <span class="review-count">based on ${trek.reviews} reviews</span>
            </div>
<<<<<<< HEAD
            <button type="button" class="booking-card-action" data-booking-package="${trek.title}" data-booking-image="${trek.image}">Book Now</button>
=======
>>>>>>> af3557d8175212cd0a4ca4e444059f13103f5e95
            <a href="#" class="package-details-link" data-trek-id="${trek.id}">
                Details <span>→</span>
            </a>
        </div>
    `;

<<<<<<< HEAD
    card.addEventListener('click', (event) => {
        if (event.target.closest('.booking-card-action')) return;
=======
    card.addEventListener('click', () => {
>>>>>>> af3557d8175212cd0a4ca4e444059f13103f5e95
        showTrekDetails(trek.id);
    });

    const detailsLink = card.querySelector('.package-details-link');
    detailsLink.addEventListener('click', (e) => {
        e.preventDefault();
        showTrekDetails(trek.id);
    });

    return card;
}

// Show trek details page
function showTrekDetails(trekId) {
    const trek = trekData[trekId];
    if (!trek) return;

    detailsContent.innerHTML = renderTrekDetails(trek);
    showDetailsPage();
    window.scrollTo(0, 0);
}

// Render trek details HTML
function renderTrekDetails(trek) {
    const starsHTML = Array(trek.rating).fill('<span class="star">★</span>').join('');

    const itineraryHTML = trek.itinerary
        .map(item => `
            <li class="itinerary-item">
                <div class="itinerary-item-title">Day ${item.day}: ${item.title}</div>
                <div class="itinerary-item-desc">${item.description}</div>
            </li>
        `)
        .join('');

    const highlightsHTML = trek.highlights
        .map(highlight => `<li>${highlight}</li>`)
        .join('');

    const packingHTML = trek.whatToPack
        .map(item => `<div class="packing-item">${item}</div>`)
        .join('');

    return `
        <div class="details-header">
            <h1 class="details-title">${trek.title}</h1>
            <div class="details-meta">
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>${trek.duration} Days</span>
                </div>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>Max Altitude: ${trek.altitude}</span>
                </div>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="12" x2="9" y2="9"></line>
                    </svg>
                    <span>Difficulty: ${trek.difficulty}</span>
                </div>
            </div>
        </div>

        <img src="${trek.image}" alt="${trek.title}" class="details-hero">

        <div class="details-grid">
            <div>
                <!-- Overview Card -->
                <div class="details-card">
                    <h3>Overview</h3>
                    <p>${trek.description}</p>
                    <div class="overview-grid">
                        <div class="overview-item">
                            <div class="overview-label">Best Season</div>
                            <div class="overview-value">${trek.season}</div>
                        </div>
                        <div class="overview-item">
                            <div class="overview-label">Group Size</div>
                            <div class="overview-value">${trek.groupSize}</div>
                        </div>
                    </div>
                </div>

                <!-- Highlights Card -->
                <div class="details-card">
                    <h3>Highlights</h3>
                    <ul class="highlights-list">
                        ${highlightsHTML}
                    </ul>
                </div>

                <!-- Itinerary Card -->
                <div class="details-card">
                    <h3>Day-by-Day Itinerary</h3>
                    <ul class="itinerary-list">
                        ${itineraryHTML}
                    </ul>
                </div>

                <!-- What to Pack Card -->
                <div class="details-card">
                    <h3>What to Pack</h3>
                    <div class="packing-grid">
                        ${packingHTML}
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <div class="sidebar-card">
                    <h3>Trek Information</h3>

                    <div class="info-item">
                        <div class="info-label">Duration</div>
                        <div class="info-value">${trek.duration} Days</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Difficulty Level</div>
                        <div class="info-value">${trek.difficulty}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Maximum Altitude</div>
                        <div class="info-value">${trek.altitude}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Fitness Required</div>
                        <div class="info-text">${trek.fitness}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Acclimatization</div>
                        <div class="info-text">${trek.acclimatization}</div>
                    </div>

                    <div class="button-group">
<<<<<<< HEAD
                        <button class="btn btn-primary booking-trigger" data-booking-package="${trek.title}" data-booking-image="${trek.image}">Book This Trek</button>
=======
                        <button class="btn btn-primary">Book This Trek</button>
>>>>>>> af3557d8175212cd0a4ca4e444059f13103f5e95
                        <button class="btn btn-secondary">Contact Guide</button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Show packages page
function showPackagesPage() {
    packagesPage.classList.add('active');
    detailsPage.classList.remove('active');
}

// Show details page
function showDetailsPage() {
    packagesPage.classList.remove('active');
    detailsPage.classList.add('active');
}
<<<<<<< HEAD
=======

>>>>>>> af3557d8175212cd0a4ca4e444059f13103f5e95
