// ==================== Nepal Text Scroll Zoom & Navbar Hiding ==================== //
const nepaliText = document.querySelector('.nepali-text');
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', function() {
    if (!nepaliText) return;
    
    const scrollY = window.scrollY;
    const maxScroll = 800;
    
    // 1. Zoom Effect
    const scale = 1 + (Math.min(scrollY, maxScroll) / maxScroll) * 3.5;
    nepaliText.style.transform = `scale(${scale})`;
    
    const opacity = 1 - (Math.min(scrollY, maxScroll) / maxScroll) * 0.6;
    nepaliText.style.opacity = opacity;

    // 2. Navbar Hiding Logic
    if (scrollY > 50 && scrollY < maxScroll) {
        navbar.classList.add('navbar-hidden');
    } else {
        navbar.classList.remove('navbar-hidden');
    }
    
    if (scrollY > 100) {
        navbar.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.2)';
    } else {
        navbar.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
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
