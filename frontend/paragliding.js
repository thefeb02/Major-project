// Paragliding packages data
const paraglidingData = {
    sarangkot: {
        id: "sarangkot",
        title: "Sarangkot Tandem Flight",
        duration: "30-45 mins",
        image: "https://www.mytrip.co.id/images/uploads/ltJmNFCe_1578382853.jpg",
        rating: 5,
        reviews: 210,
        difficulty: "Easy to Moderate",
        takeoff: "Sarangkot (1,592m)",
        landing: "Phewatal Lake Side",
        season: "September to May",
        groupSize: "1-10 people",
        description: "Experience the thrill of flying like a bird over the beautiful Pokhara valley. This tandem flight takes off from Sarangkot and offers breathtaking views of the Annapurna range and Machhapuchhre (Fishtail) mountain.",
        highlights: [
            "Tandem flight with certified international pilots",
            "Breathtaking views of Annapurna & Dhaulagiri ranges",
            "Aerial view of Phewa Lake and Pokhara city",
            "Safe and thrilling experience for beginners",
            "GoPro photos and videos included"
        ],
        itinerary: [
            { day: 1, title: "Pickup", description: "Pickup from your hotel in Lakeside, Pokhara." },
            { day: 2, title: "Drive to Sarangkot", description: "A scenic 30-minute drive to the takeoff point at Sarangkot." },
            { day: 3, title: "Briefing", description: "Safety briefing and equipment check by your pilot." },
            { day: 4, title: "Takeoff", description: "The exciting moment of takeoff into the Himalayan sky." },
            { day: 5, title: "The Flight", description: "30-45 minutes of soaring over the valley and lake." },
            { day: 6, title: "Landing", description: "Smooth landing near the shores of Phewa Lake." }
        ],
        whatToBring: [
            "Comfortable outdoor clothing",
            "Sturdy closed-toe shoes (sneakers or hiking boots)",
            "Sunglasses",
            "Sunscreen",
            "Light jacket (it can be chilly in the air)",
            "A spirit of adventure!"
        ],
        bestSeason: "September to November and March to May offer the most stable thermal conditions and clear views.",
        fitness: "Basic physical health. Must be able to run a few steps for takeoff.",
        safety: "We use world-class equipment and all our pilots are APPI/FAI certified with years of experience."
    },
    crosscountry: {
        id: "crosscountry",
        title: "Cross-Country Adventure",
        duration: "60-90 mins",
        image: "https://xcmag.com/wp-content/uploads/2022/09/Nova-Doubleskin-2500.jpg",
        rating: 5,
        reviews: 85,
        difficulty: "Moderate",
        takeoff: "Sarangkot / Toripani",
        landing: "Variable (based on thermals)",
        season: "February to April",
        groupSize: "1-5 people",
        description: "For those who want more than just a short flight. Our Cross-Country flight uses thermal currents to travel long distances across the ridges, providing an intensive and long-lasting flying experience.",
        highlights: [
            "Extended flight time using natural thermals",
            "Travel across different ridges and valleys",
            "Closer views of the high Himalayan peaks",
            "Advanced flying techniques shown by expert pilots",
            "Full flight track log provided"
        ],
        itinerary: [
            { day: 1, title: "Preparation", description: "Detailed weather briefing and flight path planning." },
            { day: 2, title: "Takeoff", description: "Mid-day takeoff when thermals are strongest." },
            { day: 3, title: "Thermic Soaring", description: "Climbing high using rising warm air currents." },
            { day: 4, title: "Ridge Transition", description: "Gliding from one mountain ridge to another." },
            { day: 5, title: "Landing & Retrieval", description: "Landing at a designated spot and drive back to Pokhara." }
        ],
        whatToBring: [
            "Warm layers (it gets colder at higher altitudes)",
            "Gloves",
            "Windproof jacket",
            "Sturdy boots",
            "Water and light snacks",
            "Camera with safety strap"
        ],
        bestSeason: "Spring (February to April) is the best time for cross-country flying due to strong and consistent thermals.",
        fitness: "Good physical health. Ability to stay comfortable in the air for a longer duration.",
        safety: "Equipped with reserve parachutes and satellite tracking for long-distance flights."
    },
    bandipur: {
        id: "bandipur",
        title: "Bandipur Scenic Flight",
        duration: "20-30 mins",
        image: "https://www.pigeontravels.com/wp-content/uploads/2019/03/bandipur-paragliding_190905011905.jpg",
        rating: 4,
        reviews: 42,
        difficulty: "Easy",
        takeoff: "Bandipur Ridge (1,030m)",
        landing: "Dumre Valley",
        season: "October to April",
        groupSize: "1-6 people",
        description: "Soar above the ancient 'living museum' of Bandipur. This flight offers a unique perspective of the traditional Newari architecture and the vast Marsyangdi river valley below.",
        highlights: [
            "Unique views of the historic Bandipur town",
            "Overlooking the Marsyangdi River valley",
            "Panoramic views of Manaslu and Ganesh Himal",
            "Peaceful and less crowded flying site",
            "Cultural experience combined with adventure"
        ],
        itinerary: [
            { day: 1, title: "Town Walk", description: "Brief walk through Bandipur's historic street to the takeoff site." },
            { day: 2, title: "Takeoff", description: "Launching from the ridge overlooking the valley." },
            { day: 3, title: "Valley Soaring", description: "Flying over the terraced fields and traditional villages." },
            { day: 4, title: "Landing", description: "Landing in the green fields of the valley below." },
            { day: 5, title: "Return", description: "Drive back up to the hilltop town of Bandipur." }
        ],
        whatToBring: [
            "Comfortable clothing",
            "Sneakers",
            "Sunglasses",
            "Sunscreen",
            "Light sweater"
        ],
        bestSeason: "Autumn and Winter months provide clear skies and gentle winds perfect for scenic flights.",
        fitness: "Suitable for all ages with basic mobility.",
        safety: "Regular site monitoring and experienced local pilots."
    }
};

// Array of package IDs
const packageIds = Object.keys(paraglidingData);

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

    packageIds.forEach(id => {
        const pkg = paraglidingData[id];
        const card = createPackageCard(pkg);
        packagesGrid.appendChild(card);
    });
}

// Create package card element
function createPackageCard(pkg) {
    const card = document.createElement('div');
    card.className = 'package-card';

    const starsHTML = Array(pkg.rating).fill('<span class="star">★</span>').join('');

    card.innerHTML = `
        <div class="package-image">
            <img src="${pkg.image}" alt="${pkg.title}">
            <div class="duration-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>${pkg.duration}</span>
            </div>
        </div>
        <div class="package-content">
            <h3 class="package-title">${pkg.title}</h3>
            <div class="package-rating">
                <div class="stars">${starsHTML}</div>
                <span class="review-count">based on ${pkg.reviews} reviews</span>
            </div>
            <a href="#" class="package-details-link" data-id="${pkg.id}">
                Details <span>→</span>
            </a>
        </div>
    `;

    card.addEventListener('click', () => {
        showPackageDetails(pkg.id);
    });

    const detailsLink = card.querySelector('.package-details-link');
    detailsLink.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        showPackageDetails(pkg.id);
    });

    return card;
}

// Show package details page
function showPackageDetails(id) {
    const pkg = paraglidingData[id];
    if (!pkg) return;

    detailsContent.innerHTML = renderPackageDetails(pkg);
    showDetailsPage();
    window.scrollTo(0, 0);
}

// Render package details HTML
function renderPackageDetails(pkg) {
    const itineraryHTML = pkg.itinerary
        .map(item => `
            <li class="itinerary-item">
                <div class="itinerary-item-title">${item.title}</div>
                <div class="itinerary-item-desc">${item.description}</div>
            </li>
        `)
        .join('');

    const highlightsHTML = pkg.highlights
        .map(highlight => `<li>${highlight}</li>`)
        .join('');

    const packingHTML = pkg.whatToBring
        .map(item => `<div class="packing-item">${item}</div>`)
        .join('');

    return `
        <div class="details-header">
            <h1 class="details-title">${pkg.title}</h1>
            <div class="details-meta">
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>${pkg.duration}</span>
                </div>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>Takeoff: ${pkg.takeoff}</span>
                </div>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="12" x2="9" y2="9"></line>
                    </svg>
                    <span>Difficulty: ${pkg.difficulty}</span>
                </div>
            </div>
        </div>

        <img src="${pkg.image}" alt="${pkg.title}" class="details-hero">

        <div class="details-grid">
            <div>
                <!-- Overview Card -->
                <div class="details-card">
                    <h3>Overview</h3>
                    <p>${pkg.description}</p>
                    <div class="overview-grid">
                        <div class="overview-item">
                            <div class="overview-label">Best Season</div>
                            <div class="overview-value">${pkg.season}</div>
                        </div>
                        <div class="overview-item">
                            <div class="overview-label">Group Size</div>
                            <div class="overview-value">${pkg.groupSize}</div>
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

                <!-- Experience Steps Card -->
                <div class="details-card">
                    <h3>Experience Steps</h3>
                    <ul class="itinerary-list">
                        ${itineraryHTML}
                    </ul>
                </div>

                <!-- What to Bring Card -->
                <div class="details-card">
                    <h3>What to Bring</h3>
                    <div class="packing-grid">
                        ${packingHTML}
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <div class="sidebar-card">
                    <h3>Flight Information</h3>

                    <div class="info-item">
                        <div class="info-label">Flight Duration</div>
                        <div class="info-value">${pkg.duration}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Takeoff Point</div>
                        <div class="info-value">${pkg.takeoff}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Landing Point</div>
                        <div class="info-value">${pkg.landing}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Fitness Required</div>
                        <div class="info-text">${pkg.fitness}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Safety Standards</div>
                        <div class="info-text">${pkg.safety}</div>
                    </div>

                    <div class="button-group">
                        <button class="btn btn-primary">Book This Flight</button>
                        <button class="btn btn-secondary">Inquire Now</button>
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
