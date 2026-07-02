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
