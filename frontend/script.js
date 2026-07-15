// ==================== Nepal Text Scroll Zoom & Navbar Hiding ==================== //
const nepaliText = document.querySelector('.nepali-text');
const navbar = document.querySelector('.navbar');
const maxScroll = 800;

window.addEventListener('scroll', function() {
    if (!nepaliText) return;
    
    const scrollY = window.scrollY;
    // Subtle zoom effect on scroll
    const progress = Math.min(scrollY, maxScroll) / maxScroll;
    const scale = 1 + progress * 0.6; // up to ~1.3x
    nepaliText.style.transform = `scale(${scale})`;
    nepaliText.style.opacity = `${1 - progress * 0.05}`;

    if (scrollY > 100) {
        navbar.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.2)';
    } else {
        navbar.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
    }
});
// ==================== Navbar Scroll Effect ==================== //
window.addEventListener("scroll", function () {
    const navbar = document.querySelector(".navbar");

    if(window.scrollY > 50){
        navbar.classList.add("scrolled");
    }else{
        navbar.classList.remove("scrolled");
    }
});
// ==================== Mobile Menu Toggle ==================== //
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');

    if (hamburger) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });
});

// ==================== Smooth Scrolling ==================== //
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ==================== Places Category Filter ==================== //
document.addEventListener('DOMContentLoaded', function() {
    const categoryBtns = document.querySelectorAll('.category-btn');
    const placeCards = document.querySelectorAll('.place-card');

    categoryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const selectedCategory = this.getAttribute('data-category');
            
            // Update active button
            categoryBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter place cards
            placeCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                const showCard = selectedCategory === 'all' || cardCategory === selectedCategory;

                card.style.display = showCard ? 'block' : 'none';
                if (showCard) {
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 10);
                }
            });
        });
    });
});

// ==================== Scroll Animation for Cards ==================== //
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.story-card, .place-card, .activity-card, .festival-card, .planning-step').forEach(element => {
    element.style.opacity = '0';
    element.style.transform = 'translateY(20px)';
    element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(element);
});

// ==================== Scroll to Top Button ==================== //
const scrollToTopBtn = document.getElementById('scrollToTop');

window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
        scrollToTopBtn.classList.add('show');
    } else {
        scrollToTopBtn.classList.remove('show');
    }
});

scrollToTopBtn?.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// ==================== Services Page Interactions ==================== //
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.service-tab');
    const panels = document.querySelectorAll('.service-section');
    const searchInput = document.getElementById('serviceSearch');
    const searchBtn = document.getElementById('serviceSearchBtn');
    const liveTrackBtn = document.getElementById('liveTrackBtn');
    const feedback = document.getElementById('serviceFeedback');

    if (!tabs.length || !panels.length) return;

    const fallbackServiceData = {
        flights: [
            { name: 'Buddha Air Mountain Flight', location: 'Kathmandu', rating: '4.8', description: 'One-hour Himalayan sightseeing flight with early morning departures.', price: 'From NPR 13,500', contact: '+977-1-5550001' },
            { name: 'Kathmandu-Pokhara Flight', location: 'Kathmandu / Pokhara', rating: '4.7', description: 'Fast domestic connection for lake city trips and Annapurna departures.', price: 'From NPR 5,200', contact: '+977-1-5550002' },
            { name: 'Kathmandu-Bhairahawa Flight', location: 'Kathmandu / Lumbini', rating: '4.6', description: 'Convenient route for Lumbini visits and western Nepal travel plans.', price: 'From NPR 4,900', contact: '+977-1-5550003' }
        ],
        hotels: [
            { name: 'Hotel Himalaya', location: 'Kathmandu', rating: '4.8', description: 'Mountain-view suites with spa and breakfast.', price: 'From NPR 8,500', contact: '+977-1-5550101' },
            { name: 'Lake Side Inn', location: 'Pokhara', rating: '4.7', description: 'Relaxing lakeside stay with premium rooms.', price: 'From NPR 6,000', contact: '+977-61-555101' },
            { name: 'Chitwan Jungle Resort', location: 'Chitwan', rating: '4.9', description: 'Luxury jungle villas and safari tours.', price: 'From NPR 9,500', contact: '+977-56-555201' }
        ],
        buses: [
            { name: 'Deluxe Kathmandu-Pokhara', location: 'Kathmandu / Pokhara', rating: '4.6', description: 'Comfort seats and restroom stops.', price: 'NPR 1,500', contact: '+977-9800001111' },
            { name: 'Express Butwal-Bhairahawa', location: 'Butwal / Bhairahawa', rating: '4.5', description: 'Fast route with evening departures.', price: 'NPR 350', contact: '+977-9800002222' },
            { name: 'Kathmandu-Chitwan Tourist Coach', location: 'Kathmandu / Chitwan', rating: '4.7', description: 'Morning coach for jungle safari travelers.', price: 'NPR 1,200', contact: '+977-9800003333' }
        ],
        rentals: [
            { name: 'Adventure Bike Hub', location: 'Pokhara', rating: '4.8', description: 'Scooters and bikes for mountain roads.', price: 'From NPR 1,200/day', contact: '+977-61-555303' },
            { name: 'City Car Rentals', location: 'Kathmandu', rating: '4.7', description: 'Sedans and SUVs for city and valley trips.', price: 'From NPR 5,000/day', contact: '+977-1-5550303' }
        ],
        malls: [
            { name: 'Civil Mall', location: 'Kathmandu', rating: '4.6', description: 'Shopping, dining, and entertainment in the city.', price: 'Shopping tour available', contact: '+977-1-5550404' },
            { name: 'Phewa Mall', location: 'Pokhara', rating: '4.5', description: 'Modern retail and food court experience.', price: 'Shopping tour available', contact: '+977-61-555404' }
        ]
    };

    const serviceData = { ...fallbackServiceData };
    Object.entries(window.serviceCatalogData || {}).forEach(([category, items]) => {
        if (Array.isArray(items) && items.length) serviceData[category] = items;
    });

    const gridIds = {
        flights: 'flightsGrid',
        hotels: 'hotelsGrid',
        buses: 'busesGrid',
        rentals: 'rentalGrid',
        malls: 'mallGrid'
    };

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));

    const renderCards = (category) => {
        const container = document.getElementById(gridIds[category] || `${category}Grid`);
        if (!container) return;

        const query = (searchInput?.value || '').trim().toLowerCase();
        let items = serviceData[category] || [];

        if (query) {
            items = items.filter(item =>
                [item.name, item.location, item.description, item.price, item.contact].join(' ').toLowerCase().includes(query)
            );
        }

        if (!items.length) {
            container.innerHTML = '<div class="empty-state">No matching services found. Try a broader search.</div>';
            return;
        }

        container.innerHTML = items.map(item => `
            <article class="service-card-item">
                ${item.image ? `<img class="service-card-thumb" src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">` : ''}
                <div class="service-card-header">
                    <h3>${escapeHtml(item.name)}</h3>
                    <span class="service-rating">Star ${escapeHtml(item.rating || '4.6')}</span>
                </div>
                <p>${escapeHtml(item.description)}</p>
                <div class="service-card-footer">
                    <span>${escapeHtml(item.location)}</span>
                    <span>${escapeHtml(item.price || '')}</span>
                </div>
                <div class="service-card-footer">
                    <span>${escapeHtml(item.contact || 'Contact after request')}</span>
                    <a href="#serviceBooking" class="service-link service-book-button" data-service-name="${escapeHtml(item.name)}" data-service-category="${escapeHtml(category)}">Book Now</a>
                </div>
            </article>
        `).join('');
    };

    const setActiveTab = (category) => {
        tabs.forEach(tab => tab.classList.toggle('active', tab.dataset.serviceTab === category));
        panels.forEach(panel => panel.classList.toggle('active', panel.dataset.servicePanel === category));
        renderCards(category);
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', () => setActiveTab(tab.dataset.serviceTab));
    });

    searchBtn?.addEventListener('click', () => {
        const active = document.querySelector('.service-tab.active')?.dataset.serviceTab || 'hotels';
        renderCards(active);
        if (feedback) feedback.textContent = 'Search refreshed for your selected service category.';
    });

    searchInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchBtn?.click();
        }
    });

    liveTrackBtn?.addEventListener('click', () => {
        const tracking = document.getElementById('liveTracking');
        if (tracking) {
            tracking.textContent = 'Live Tracking: Bus KTMT-101 - 12 km from Pokhara, ETA: 18 mins';
            if (feedback) feedback.textContent = 'Live tracking updated.';
        }
    });

    document.body.addEventListener('click', (event) => {
        const bookButton = event.target.closest('.service-book-button');
        if (!bookButton) return;
        event.preventDefault();

        const serviceName = bookButton.dataset.serviceName || '';
        const serviceCategory = bookButton.dataset.serviceCategory || 'hotels';
        const categorySelect = document.getElementById('serviceCategory');
        const serviceNameInput = document.getElementById('serviceName');

        if (categorySelect) categorySelect.value = serviceCategory;
        if (serviceNameInput) serviceNameInput.value = serviceName;
        setActiveTab(serviceCategory);
        document.getElementById('serviceBooking')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (feedback) feedback.textContent = `Preparing booking form for ${serviceName}.`;
    });

    const params = new URLSearchParams(window.location.search);
    const requestedCategory = params.get('service') || params.get('category') || 'hotels';
    const requestedName = params.get('name') || '';
    setActiveTab(serviceData[requestedCategory] ? requestedCategory : 'hotels');
    if (requestedName) {
        const serviceNameInput = document.getElementById('serviceName');
        if (serviceNameInput) serviceNameInput.value = requestedName;
        document.getElementById('serviceBooking')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});