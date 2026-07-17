// Culinary packages data
const culinaryData = {
    thakali: {
        id: "thakali",
        title: "Authentic Thakali Food Trail",
        duration: 5,
        image: "https://i.pinimg.com/originals/48/84/14/48841451e190c78648ec723eb5cc4a1b.jpg",
        rating: 5,
        reviews: 92,
        difficulty: "Easy",
        altitude: "2,800m (9,186 ft)",
        season: "All Year Round",
        groupSize: "2-8 people",
        description: "Master the art of the perfect Thakali Thali—the most celebrated meal in Nepal. This tour takes you through the culinary traditions of the Thakali people, known for their exceptional hospitality and delicious food.",
        highlights: [
            "Learn to make authentic Thakali Dal-Bhat",
            "Master the use of 'Jimbu' (Himalayan herb)",
            "Buckwheat 'Dhido' and 'Fapar' bread workshop",
            "Tasting local apple brandy from Mustang",
            "Traditional Thakali hospitality experience"
        ],
        itinerary: [
            { day: 1, title: "Arrival & Welcome Feast", description: "Introduction to Thakali culture and a grand welcome thali." },
            { day: 2, title: "The Secret of Spices", description: "Visiting local markets to source Jimbu, Timur, and local lentils." },
            { day: 3, title: "Dhido & Curry Workshop", description: "Hands-on session making buckwheat dhido and local mutton curry." },
            { day: 4, title: "Pickle & Chutney Making", description: "Learning to make fermented radish and spicy tomato chutneys." },
            { day: 5, title: "Final Celebration", description: "Cooking a full thali for the group and certificate presentation." }
        ],
        whatToPack: [
            "Comfortable clothes for cooking",
            "Notebook and pen for recipes",
            "Personal apron (optional)",
            "Camera for food photography",
            "Small containers for spice samples"
        ],
        bestSeason: "Spring and Autumn are best for travel, but available year-round.",
        fitness: "Low; involves standing during cooking classes and light walking.",
        acclimatization: "Not required at these altitudes."
    },
    newari: {
        id: "newari",
        title: "Taste of Kathmandu: Newari Feast",
        duration: 3,
        image: "https://i.pinimg.com/1200x/32/e7/ca/32e7ca45b9c7d73a18f5a4015802dfba.jpg",
        rating: 5,
        reviews: 65,
        difficulty: "Easy",
        altitude: "1,400m (4,593 ft)",
        season: "All Year Round",
        groupSize: "2-12 people",
        description: "Explore the rich and spicy flavors of traditional Newari cuisine. From the streets of Bhaktapur to the hidden courtyards of Patan, discover why Newari food is considered a culinary treasure.",
        highlights: [
            "Traditional 'Samay Baji' feast experience",
            "Yomari (sweet dumpling) making class",
            "Exploring ancient Newari spice markets",
            "Bara and Chatamari street food tour",
            "Dining in a restored Newari heritage house"
        ],
        itinerary: [
            { day: 1, title: "Street Food Safari", description: "Tasting Bara, Chatamari, and Choila in the backstreets of Patan." },
            { day: 2, title: "Yomari Masterclass", description: "Learning the delicate art of steaming sweet rice-flour dumplings." },
            { day: 3, title: "Ancient Feast", description: "A grand Samay Baji lunch and visit to Bhaktapur's curd makers." }
        ],
        whatToPack: [
            "Comfortable walking shoes",
            "Hand sanitizer",
            "Reusable water bottle",
            "Appetite for spicy food!",
            "Camera"
        ],
        bestSeason: "Year-round, especially during local festivals.",
        fitness: "Low; involves walking through city alleys.",
        acclimatization: "Not required."
    },
    momo: {
        id: "momo",
        title: "Momo & Dumpling Masterclass",
        duration: 4,
        image: "https://images.unsplash.com/photo-1625220194771-7ebdea0b70b9?auto=format&fit=crop&w=800&q=80",
        rating: 4,
        reviews: 110,
        difficulty: "Easy",
        altitude: "1,400m (4,593 ft)",
        season: "All Year Round",
        groupSize: "4-10 people",
        description: "Dedicated to Nepal's favorite snack! Learn the secrets of making the perfect Momo, from preparing the dough to mastering the various folding techniques and the famous spicy sauce.",
        highlights: [
            "Learn 5 different Momo folding styles",
            "Secret spicy tomato 'Achar' recipe",
            "Veg, Buff, and Chicken filling variations",
            "The Great Momo Crawl through Kathmandu",
            "Jhol Momo (soup style) specialty class"
        ],
        itinerary: [
            { day: 1, title: "Momo History & Tasting", description: "Introduction to the origins of momos and a tasting session." },
            { day: 2, title: "Dough & Filling Basics", description: "Learning to make the perfect wrappers and juicy fillings." },
            { day: 3, title: "Folding & Steaming", description: "Mastering the pleats and learning about soup momos." },
            { day: 4, title: "Momo Competition", description: "A friendly group competition and final feast." }
        ],
        whatToPack: [
            "Apron",
            "Recipe book",
            "Camera",
            "Comfortable shoes for the food crawl"
        ],
        bestSeason: "Year-round.",
        fitness: "Low.",
        acclimatization: "Not required."
    },
    farm: {
        id: "farm",
        title: "Farm-to-Table Village Experience",
        duration: 6,
        image: "https://i.pinimg.com/736x/6c/07/f2/6c07f221afce00a29e5fc88f9b21b176.jpg",
        rating: 5,
        reviews: 48,
        difficulty: "Easy",
        altitude: "2,000m (6,562 ft)",
        season: "September to May",
        groupSize: "2-6 people",
        description: "Live with a local family in a mountain village and learn to cook using fresh, organic ingredients straight from the fields. Experience the true essence of Himalayan rural life.",
        highlights: [
            "Harvesting organic vegetables from the farm",
            "Traditional wood-fire stove cooking",
            "Milking cows and making fresh ghee",
            "Grinding spices using a traditional 'Silauto'",
            "Staying in an authentic village homestay"
        ],
        itinerary: [
            { day: 1, title: "Travel to Village", description: "Drive to a scenic village and meet your host family." },
            { day: 2, title: "Morning Harvest", description: "Gathering seasonal greens and vegetables for lunch." },
            { day: 3, title: "Dairy & Grain", description: "Learning about local grains and traditional butter making." },
            { day: 4, title: "Wood-fire Cooking", description: "Mastering the art of temperature control on a clay stove." },
            { day: 5, title: "Village Market Day", description: "Visiting the local weekly market for unique ingredients." },
            { day: 6, title: "Farewell Brunch", description: "Preparing a final village feast and return to city." }
        ],
        whatToPack: [
            "Warm layers for evenings",
            "Flashlight or headlamp",
            "Sun hat and sunscreen",
            "Sturdy walking shoes",
            "Personal toiletries"
        ],
        bestSeason: "Autumn and Spring for the best harvest variety.",
        fitness: "Low to Moderate; involves light farm work and walking.",
        acclimatization: "Not required."
    }
};

// Array of culinary IDs for easier iteration
const culinaryIds = Object.keys(culinaryData);

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

    culinaryIds.forEach(id => {
        const item = culinaryData[id];
        const card = createPackageCard(item);
        packagesGrid.appendChild(card);
    });
}

// Create package card element
function createPackageCard(item) {
    const card = document.createElement('div');
    card.className = 'package-card';

    const starsHTML = Array(item.rating).fill('<span class="star">★</span>').join('');

    card.innerHTML = `
        <div class="package-image">
            <img src="${item.image}" alt="${item.title}">
            <div class="duration-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>${item.duration} Days</span>
            </div>
        </div>
        <div class="package-content">
            <h3 class="package-title">${item.title}</h3>
            <div class="package-rating">
                <div class="stars">${starsHTML}</div>
                <span class="review-count">based on ${item.reviews} reviews</span>
            </div>
            <button type="button" class="booking-card-action" data-booking-package="${item.title}" data-booking-image="${item.image}">Book Now</button>
            <a href="#" class="package-details-link" data-id="${item.id}">
                Details <span>→</span>
            </a>
        </div>
    `;

    card.addEventListener('click', (event) => {
        if (event.target.closest('.booking-card-action')) return;
        showDetails(item.id);
    });

    const detailsLink = card.querySelector('.package-details-link');
    detailsLink.addEventListener('click', (e) => {
        e.preventDefault();
        showDetails(item.id);
    });

    return card;
}

// Show details page
function showDetails(id) {
    const item = culinaryData[id];
    if (!item) return;

    detailsContent.innerHTML = renderDetails(item);
    showDetailsPage();
    window.scrollTo(0, 0);
}

// Render details HTML
function renderDetails(item) {
    const starsHTML = Array(item.rating).fill('<span class="star">★</span>').join('');

    const itineraryHTML = item.itinerary
        .map(day => `
            <li class="itinerary-item">
                <div class="itinerary-item-title">Day ${day.day}: ${day.title}</div>
                <div class="itinerary-item-desc">${day.description}</div>
            </li>
        `)
        .join('');

    const highlightsHTML = item.highlights
        .map(h => `<li>${h}</li>`)
        .join('');

    const packingHTML = item.whatToPack
        .map(p => `<div class="packing-item">${p}</div>`)
        .join('');

    return `
        <div class="details-header">
            <h1 class="details-title">${item.title}</h1>
            <div class="details-meta">
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>${item.duration} Days</span>
                </div>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>Max Altitude: ${item.altitude}</span>
                </div>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="12" x2="9" y2="9"></line>
                    </svg>
                    <span>Difficulty: ${item.difficulty}</span>
                </div>
            </div>
        </div>

        <img src="${item.image}" alt="${item.title}" class="details-hero">

        <div class="details-grid">
            <div class="details-main">
                <div class="details-card">
                    <h3>Overview</h3>
                    <p>${item.description}</p>
                    
                    <div class="overview-grid">
                        <div class="overview-item">
                            <div class="overview-label">Best Season</div>
                            <div class="overview-value">${item.bestSeason}</div>
                        </div>
                        <div class="overview-item">
                            <div class="overview-label">Group Size</div>
                            <div class="overview-value">${item.groupSize}</div>
                        </div>
                        <div class="overview-item">
                            <div class="overview-label">Fitness Level</div>
                            <div class="overview-value">${item.fitness}</div>
                        </div>
                        <div class="overview-item">
                            <div class="overview-label">Acclimatization</div>
                            <div class="overview-value">${item.acclimatization}</div>
                        </div>
                    </div>
                </div>

                <div class="details-card">
                    <h3>Highlights</h3>
                    <ul class="highlights-list">
                        ${highlightsHTML}
                    </ul>
                </div>

                <div class="details-card">
                    <h3>Itinerary</h3>
                    <ul class="itinerary-list">
                        ${itineraryHTML}
                    </ul>
                </div>
            </div>

            <div class="details-sidebar">
                <div class="details-card booking-card">
                    <h3>Book This Tour</h3>
                    <div class="price-tag">From $799</div>
                    <button class="btn-book-large" data-booking-package="${item.title}" data-booking-image="${item.image}">Check Availability</button>
                    <p class="booking-note">* Price depends on group size and season</p>
                </div>

                <div class="details-card">
                    <h3>What to Pack</h3>
                    <div class="packing-grid">
                        ${packingHTML}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function showPackagesPage() {
    packagesPage.classList.add('active');
    detailsPage.classList.remove('active');
}

function showDetailsPage() {
    packagesPage.classList.remove('active');
    detailsPage.classList.add('active');
}
