// Photography packages data
const photoData = {
    landscape: {
        id: "landscape",
        title: "Himalayan Landscape Expedition",
        duration: 12,
        image: "https://himalayaexpeditions.com/storage/homepage/firstsection.jpg",
        rating: 5,
        reviews: 85,
        difficulty: "Moderate",
        altitude: "4,000m (13,123 ft)",
        season: "March to May, September to November",
        groupSize: "4-8 people",
        description: "Capture the breathtaking beauty of the Himalayas. This expedition focuses on landscape photography, teaching you how to master light and composition in one of the most stunning environments on Earth.",
        highlights: [
            "Golden hour shots of Mt. Everest and Annapurna",
            "Landscape composition workshops with experts",
            "High-altitude photography techniques",
            "Night and star photography sessions",
            "Professional photography mentor throughout"
        ],
        itinerary: [
            { day: 1, title: "Arrival in Kathmandu", description: "Meet your photography mentor and group briefing." },
            { day: 2, title: "Flight to Lukla", description: "Start the journey with aerial photography of the Himalayas." },
            { day: 3, title: "Trek to Namche Bazaar", description: "Capturing the Dudh Koshi river and suspension bridges." },
            { day: 4, title: "Namche Bazaar", description: "Acclimatization and sunset shoot at Everest View Hotel." },
            { day: 5, title: "Namche to Tengboche", description: "Photographing the iconic Tengboche Monastery with Everest background." },
            { day: 6, title: "Tengboche to Dingboche", description: "Landscape changes to alpine meadows and dramatic peaks." },
            { day: 7, title: "Dingboche Exploration", description: "Day trip for panoramic shots of Ama Dablam and Lhotse." },
            { day: 8, title: "Dingboche to Lobuche", description: "Capturing the Khumbu Glacier and memorial shrines." },
            { day: 9, title: "Lobuche to Kala Patthar", description: "The ultimate sunset shoot of Mount Everest from Kala Patthar." },
            { day: 10, title: "Return to Pheriche", description: "Capturing the valley in different light conditions." },
            { day: 11, title: "Return to Namche", description: "Editing workshop and review of the best shots." },
            { day: 12, title: "Departure", description: "Flight back to Kathmandu and final group dinner." }
        ],
        whatToPack: [
            "DSLR or Mirrorless camera with extra batteries",
            "Wide-angle and telephoto lenses",
            "Sturdy tripod for long exposures",
            "Graduated ND and CPL filters",
            "Rain cover for camera gear",
            "Power banks and portable hard drives",
            "Warm layers and sturdy hiking boots"
        ],
        bestSeason: "Spring (blooming flowers) and Autumn (clearest skies for mountain views)",
        fitness: "Moderate fitness required for trekking to higher altitudes.",
        acclimatization: "Built-in rest days at Namche and Dingboche for safety and better photography."
    },
    cultural: {
        id: "cultural",
        title: "Kathmandu Cultural & Street Photography",
        duration: 5,
        image: "https://twistedsifter.com/wp-content/uploads/2020/07/kathmandu-street-photography-by-ashraful-arefin-12.jpg",
        rating: 5,
        reviews: 42,
        difficulty: "Easy",
        altitude: "1,400m (4,593 ft)",
        season: "All Year Round",
        groupSize: "2-10 people",
        description: "Immerse yourself in the vibrant colors and ancient traditions of Kathmandu. This tour is designed for street and cultural photographers who want to capture the soul of Nepal's capital.",
        highlights: [
            "UNESCO World Heritage sites photography",
            "Portraits of local artisans and monks",
            "Night photography in bustling Ason markets",
            "Traditional ceremony coverage at Pashupatinath",
            "Hidden alleyway exploration in Patan and Bhaktapur"
        ],
        itinerary: [
            { day: 1, title: "Kathmandu Durbar Square", description: "Introduction to Newari architecture and street life." },
            { day: 2, title: "Patan & Bhaktapur", description: "Capturing the 'City of Fine Arts' and ancient pottery traditions." },
            { day: 3, title: "Swayambhunath & Boudhanath", description: "Early morning shoot at the Monkey Temple and the Great Stupa." },
            { day: 4, title: "Pashupatinath & Ason", description: "Documenting rituals at the holy river and vibrant local markets." },
            { day: 5, title: "Departure", description: "Final photo review session and departure." }
        ],
        whatToPack: [
            "Camera with a versatile zoom lens (e.g., 24-70mm)",
            "Fast prime lens for low-light and portraits",
            "Comfortable walking shoes",
            "Extra memory cards",
            "Lens cleaning kit",
            "Discreet camera bag for street shooting"
        ],
        bestSeason: "October to March for the best light and festivals.",
        fitness: "Low; involves walking through city streets and temple complexes.",
        acclimatization: "Not required as the altitude is low."
    },
    wildlife: {
        id: "wildlife",
        title: "Wildlife & Nature Safari (Chitwan)",
        duration: 7,
        image: "https://lirp.cdn-website.com/ca5742e6/dms3rep/multi/opt/Chitwan_Elephant_Ride-1920w.jpg",
        rating: 4,
        reviews: 38,
        difficulty: "Easy",
        altitude: "150m (492 ft)",
        season: "October to March",
        groupSize: "2-8 people",
        description: "Venture into the heart of Chitwan National Park to photograph rare wildlife. From one-horned rhinos to elusive Bengal tigers, this safari is a dream for nature photographers.",
        highlights: [
            "Jungle safaris by private jeep and canoe",
            "Bird watching at dawn for exotic species",
            "Macro photography of tropical flora and insects",
            "Tharu village cultural portrait sessions",
            "Sunset photography by the Rapti River"
        ],
        itinerary: [
            { day: 1, title: "Travel to Chitwan", description: "Drive or fly to the subtropical lowlands of Nepal." },
            { day: 2, title: "Canoe Ride & Jungle Walk", description: "Photographing crocodiles and aquatic birds from the water." },
            { day: 3, title: "Full Day Jeep Safari", description: "Deep jungle exploration to find rhinos, deer, and tigers." },
            { day: 4, title: "Elephant Breeding Center", description: "Capturing the bond between mahouts and elephants." },
            { day: 5, title: "Tharu Village Tour", description: "Documenting the unique culture and lifestyle of the Tharu people." },
            { day: 6, title: "Bird Watching & Sunset", description: "Focusing on the 500+ species of birds in the park." },
            { day: 7, title: "Departure", description: "Early morning nature walk and return to Kathmandu." }
        ],
        whatToPack: [
            "Telephoto lens (at least 300mm recommended)",
            "Binoculars",
            "Camouflage or neutral-colored clothing",
            "Insect repellent",
            "Sun hat and sunscreen",
            "Waterproof bag for canoe trips"
        ],
        bestSeason: "November to February for the best wildlife sightings.",
        fitness: "Low; mostly jeep and boat based with light walking.",
        acclimatization: "Not required."
    },
    portraits: {
        id: "portraits",
        title: "People & Portraits of the Himalayas",
        duration: 8,
        image: "https://c8.alamy.com/comp/DEP2B8/nepal-people-himalaya-portrait-face-asian-tourism-person-trekking-DEP2B8.jpg",
        rating: 5,
        reviews: 56,
        difficulty: "Moderate",
        altitude: "2,500m (8,202 ft)",
        season: "Spring & Autumn",
        groupSize: "4-6 people",
        description: "Connect with the diverse ethnic groups of Nepal. This tour focuses on environmental portraiture and storytelling, helping you capture the human spirit of the Himalayas.",
        highlights: [
            "Environmental portrait sessions in remote villages",
            "Visits to local homes for authentic storytelling",
            "Cultural storytelling and ethics workshops",
            "Traditional costume and festival photography",
            "Local festival coverage (depending on dates)"
        ],
        itinerary: [
            { day: 1, title: "Arrival & Orientation", description: "Learning about cultural sensitivity and portrait ethics." },
            { day: 2, title: "Kathmandu Valley Villages", description: "Photographing the Tamang and Newar communities." },
            { day: 3, title: "Travel to Bandipur", description: "Capturing the hilltop town's preserved culture." },
            { day: 4, title: "Magar Village Visit", description: "Spending a day with the Magar community in the foothills." },
            { day: 5, title: "Travel to Ghandruk", description: "Photographing the Gurung people in their stone-house village." },
            { day: 6, title: "Ghandruk Life", description: "Early morning portraits with Annapurna views." },
            { day: 7, title: "Return to Pokhara", description: "Street photography along the lakeside." },
            { day: 8, title: "Departure", description: "Final portfolio review and departure." }
        ],
        whatToPack: [
            "Fast prime lenses (35mm, 50mm, or 85mm)",
            "Small reflector or portable light source",
            "Notebook for recording stories",
            "Gifts for local communities (as advised)",
            "Comfortable walking shoes"
        ],
        bestSeason: "March to May and September to November.",
        fitness: "Moderate; involves walking between villages.",
        acclimatization: "Not required at these altitudes."
    }
};

// Array of photo IDs for easier iteration
const photoIds = Object.keys(photoData);

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

    photoIds.forEach(id => {
        const photo = photoData[id];
        const card = createPackageCard(photo);
        packagesGrid.appendChild(card);
    });
}

// Create package card element
function createPackageCard(photo) {
    const card = document.createElement('div');
    card.className = 'package-card';

    const starsHTML = Array(photo.rating).fill('<span class="star">★</span>').join('');

    card.innerHTML = `
        <div class="package-image">
            <img src="${photo.image}" alt="${photo.title}">
            <div class="duration-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>${photo.duration} Days</span>
            </div>
        </div>
        <div class="package-content">
            <h3 class="package-title">${photo.title}</h3>
            <div class="package-rating">
                <div class="stars">${starsHTML}</div>
                <span class="review-count">based on ${photo.reviews} reviews</span>
            </div>
<<<<<<< HEAD
            <button type="button" class="booking-card-action" data-booking-package="${photo.title}" data-booking-image="${photo.image}">Book Now</button>
=======
>>>>>>> af3557d8175212cd0a4ca4e444059f13103f5e95
            <a href="#" class="package-details-link" data-photo-id="${photo.id}">
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
        showPhotoDetails(photo.id);
    });

    const detailsLink = card.querySelector('.package-details-link');
    detailsLink.addEventListener('click', (e) => {
        e.preventDefault();
        showPhotoDetails(photo.id);
    });

    return card;
}

// Show photo details page
function showPhotoDetails(photoId) {
    const photo = photoData[photoId];
    if (!photo) return;

    detailsContent.innerHTML = renderPhotoDetails(photo);
    showDetailsPage();
    window.scrollTo(0, 0);
}

// Render photo details HTML
function renderPhotoDetails(photo) {
    const starsHTML = Array(photo.rating).fill('<span class="star">★</span>').join('');

    const itineraryHTML = photo.itinerary
        .map(item => `
            <li class="itinerary-item">
                <div class="itinerary-item-title">Day ${item.day}: ${item.title}</div>
                <div class="itinerary-item-desc">${item.description}</div>
            </li>
        `)
        .join('');

    const highlightsHTML = photo.highlights
        .map(highlight => `<li>${highlight}</li>`)
        .join('');

    const packingHTML = photo.whatToPack
        .map(item => `<div class="packing-item">${item}</div>`)
        .join('');

    return `
        <div class="details-header">
            <h1 class="details-title">${photo.title}</h1>
            <div class="details-meta">
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>${photo.duration} Days</span>
                </div>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>Max Altitude: ${photo.altitude}</span>
                </div>
                <div class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="12" x2="9" y2="9"></line>
                    </svg>
                    <span>Difficulty: ${photo.difficulty}</span>
                </div>
            </div>
        </div>

        <img src="${photo.image}" alt="${photo.title}" class="details-hero">

        <div class="details-grid">
            <div class="details-main">
                <div class="details-card">
                    <h3>Overview</h3>
                    <p>${photo.description}</p>
                    
                    <div class="overview-grid">
                        <div class="overview-item">
                            <div class="overview-label">Best Season</div>
                            <div class="overview-value">${photo.bestSeason}</div>
                        </div>
                        <div class="overview-item">
                            <div class="overview-label">Group Size</div>
                            <div class="overview-value">${photo.groupSize}</div>
                        </div>
                        <div class="overview-item">
                            <div class="overview-label">Fitness Level</div>
                            <div class="overview-value">${photo.fitness}</div>
                        </div>
                        <div class="overview-item">
                            <div class="overview-label">Acclimatization</div>
                            <div class="overview-value">${photo.acclimatization}</div>
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
                    <div class="price-tag">From $1,499</div>
<<<<<<< HEAD
                    <button class="btn-book-large" data-booking-package="${photo.title}" data-booking-image="${photo.image}">Check Availability</button>
=======
                    <button class="btn-book-large">Check Availability</button>
>>>>>>> af3557d8175212cd0a4ca4e444059f13103f5e95
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
