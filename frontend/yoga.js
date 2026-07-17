// Yoga & Meditation data
const yogaData = {
    kopan: {
        id: "kopan",
        title: "Kopan Monastery",
        location: "Kathmandu",
        image: "https://images.unsplash.com/photo-1545389336-cf090694435e?auto=format&fit=crop&w=800&q=80",
        focus: "Tibetan Buddhism & Meditation",
        atmosphere: "Traditional & Spiritual",
        description: "Perched on a hill overlooking the Kathmandu Valley, Kopan Monastery is a center for Tibetan Buddhist study and meditation. It offers a peaceful environment away from the city's hustle, perfect for those seeking spiritual growth and inner silence.",
        highlights: [
            "Daily meditation sessions led by experienced monks",
            "Panoramic views of the Kathmandu Valley",
            "Beautiful gardens and peaceful stupas",
            "Introduction to Tibetan Buddhist philosophy",
            "Vegetarian meals and simple, serene living"
        ],
        whyVisit: "Kopan is world-renowned for its 'November Course', but it remains a sanctuary for visitors year-round who want to experience the authentic life of a Tibetan Buddhist monastery."
    },
    atmashree: {
        id: "atmashree",
        title: "Atmashree Yoga Retreat",
        location: "Pokhara",
        image: "https://dynamic-media-cdn.tripadvisor.com/media/photo-o/29/9c/df/c5/yoga-teacher-training.jpg?w=700&h=-1&s=1",
        focus: "Hatha Yoga & Sound Healing",
        atmosphere: "Scenic & Rejuvenating",
        description: "Located near the tranquil Phewa Lake in Pokhara, Atmashree Yoga Retreat provides a holistic experience combining physical yoga, breathwork, and sound healing. The backdrop of the Annapurna range adds a majestic energy to your practice.",
        highlights: [
            "Yoga classes with views of the Himalayas",
            "Traditional Sound Healing sessions",
            "Pranayama (breathing) and Nidra Yoga",
            "Healthy organic Ayurvedic meals",
            "Nature walks and lake-side meditation"
        ],
        whyVisit: "It's the perfect place to balance your body and mind after a long trek or simply to recharge your spirit in one of the most beautiful cities in the world."
    },
    nepalyoga: {
        id: "nepalyoga",
        title: "Nepal Yoga Home",
        location: "Kathmandu",
        image: "https://tse3.mm.bing.net/th/id/OIP.370JGD33zUwsUrDdJszpnAHaFj?r=0&rs=1&pid=ImgDetMain&o=7&rm=3",
        focus: "Classical Yoga & Spirituality",
        atmosphere: "Authentic & Educational",
        description: "Nepal Yoga Home is dedicated to preserving the traditional roots of yoga. It offers a deep dive into Hatha, Ashtanga, and Kundalini yoga, guided by masters who focus on the spiritual essence of the practice.",
        highlights: [
            "Comprehensive yoga teacher training and retreats",
            "Authentic Vedic and Tantric teachings",
            "Daily cleansing rituals (Shatkarma)",
            "Chanting and spiritual discourse",
            "Community-focused environment"
        ],
        whyVisit: "If you are looking for a deeper understanding of yoga beyond just the physical postures, Nepal Yoga Home provides a truly authentic and educational experience."
    },
    lumbini: {
        id: "lumbini",
        title: "Lumbini Sacred Garden",
        location: "Lumbini",
        image: "https://th.bing.com/th/id/R.eb14767255f5c6729911a60a4f655438?rik=HYKRULBpBt84qQ&pid=ImgRaw&r=0",
        focus: "Mindfulness & Historical Peace",
        atmosphere: "Sacred & Profound",
        description: "Lumbini, the birthplace of Lord Buddha, is the ultimate destination for meditation. The Sacred Garden, housing the Maya Devi Temple and various international monasteries, offers a unique energy that promotes deep reflection and global peace.",
        highlights: [
            "Meditate at the exact birthplace of Buddha",
            "Explore monasteries from different Buddhist traditions",
            "Walk the peaceful Ashoka Pillar area",
            "Participate in global peace chanting",
            "Quiet reflection by the sacred pond"
        ],
        whyVisit: "Lumbini is not just a destination; it's a pilgrimage of the heart. The profound silence and historical significance make it a once-in-a-lifetime spot for meditation."
    }
};

// DOM Elements
const packagesGrid = document.getElementById('packagesGrid');
const detailsPage = document.getElementById('detailsPage');
const packagesPage = document.getElementById('packagesPage');
const detailsContent = document.getElementById('detailsContent');
const backBtn = document.getElementById('backBtn');

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    renderYogaSpots();
    setupEventListeners();
});

function setupEventListeners() {
    backBtn.addEventListener('click', (e) => {
        e.preventDefault();
        showPackagesPage();
    });
}

function renderYogaSpots() {
    packagesGrid.innerHTML = '';
    Object.values(yogaData).forEach(spot => {
        const card = document.createElement('div');
        card.className = 'package-card';
        card.innerHTML = `
            <div class="package-image">
                <img src="${spot.image}" alt="${spot.title}">
                <div class="focus-badge">${spot.focus}</div>
            </div>
            <div class="package-content">
                <h3 class="package-title">${spot.title}</h3>
                <p class="package-desc">${spot.description}</p>
                <span class="read-more">Learn More →</span>
            </div>
        `;
        card.addEventListener('click', () => showDetails(spot.id));
        packagesGrid.appendChild(card);
    });
}

function showDetails(id) {
    const spot = yogaData[id];
    detailsContent.innerHTML = `
        <div class="details-header">
            <h1 class="details-title">${spot.title}</h1>
            <div class="details-meta">
                <div class="meta-item">📍 ${spot.location}</div>
                <div class="meta-item">🧘 ${spot.focus}</div>
            </div>
        </div>
        
        <img src="${spot.image}" alt="${spot.title}" class="details-hero">
        
        <div class="details-grid">
            <div>
                <div class="content-card">
                    <h3>About the Experience</h3>
                    <p>${spot.description}</p>
                    <p>${spot.whyVisit}</p>
                </div>
            </div>
            <div>
                <div class="atmosphere-box">
                    <h4>Atmosphere</h4>
                    <p>${spot.atmosphere}</p>
                </div>
                <div class="content-card">
                    <h3>Highlights</h3>
                    <ul class="highlights-list">
                        ${spot.highlights.map(h => `<li>${h}</li>`).join('')}
                    </ul>
                </div>
            </div>
        </div>
    `;
    packagesPage.classList.remove('active');
    detailsPage.classList.add('active');
    window.scrollTo(0, 0);
}

function showPackagesPage() {
    detailsPage.classList.remove('active');
    packagesPage.classList.add('active');
}
