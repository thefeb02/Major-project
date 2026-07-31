(() => {
    const destinations = [
        'Kathmandu', 'Pokhara', 'Chitwan', 'Lumbini', 'Bhaktapur', 'Patan', 'Nagarkot', 'Dhulikhel', 'Bandipur', 'Gorkha',
        'Ilam', 'Dharan', 'Biratnagar', 'Janakpur', 'Hetauda', 'Butwal', 'Tansen', 'Nepalgunj', 'Dhangadhi', 'Mahendranagar',
        'Mustang', 'Jomsom', 'Muktinath', 'Manang', 'Annapurna', 'Everest', 'Namche Bazaar', 'Lukla', 'Phaplu', 'Salleri',
        'Langtang', 'Rasuwa', 'Gosaikunda', 'Helambu', 'Melamchi', 'Dolakha', 'Charikot', 'Kalinchowk', 'Sindhuli', 'Bardibas',
        'Bardiya', 'Koshi Tappu', 'Sauraha', 'Meghauli', 'Nawalparasi', 'Devghat', 'Palpa', 'Rara Lake', 'Jumla', 'Mugu',
        'Dolpa', 'Shey Phoksundo', 'Surkhet', 'Dailekh', 'Rukum', 'Rolpa', 'Jajarkot', 'Humla', 'Simikot', 'Bajura',
        'Khaptad', 'Api Nampa', 'Baitadi', 'Dadeldhura', 'Kanchanpur', 'Damak', 'Kakarbhitta', 'Taplejung', 'Kanchenjunga', 'Pathibhara',
        'Sankhuwasabha', 'Makalu Barun', 'Bhojpur', 'Khotang', 'Okhaldhunga', 'Solukhumbu', 'Udayapur', 'Saptari', 'Siraha', 'Dhanusha',
        'Mahottari', 'Sarlahi', 'Rautahat', 'Bara', 'Parsa', 'Nuwakot', 'Trishuli', 'Kavrepalanchok', 'Panauti', 'Chitlang',
        'Makwanpur', 'Rupandehi', 'Kapilvastu', 'Arghakhanchi', 'Gulmi', 'Syangja', 'Kaski', 'Lamjung', 'Tanahun', 'Myagdi',
        'Baglung', 'Parbat', 'Ghandruk', 'Poon Hill', 'Damauli', 'Dhorpatan', 'Besisahar', 'Tinjure', 'Shivapuri', 'Godavari'
    ];
    const categories = ['Adventure', 'Cultural', 'Family', 'Honeymoon', 'Pilgrimage', 'Nature'];
    const images = ['../img/1.jpeg', '../img/2.jpeg', '../img/3.jpeg', '../img/4.jpeg', '../img/5.jpeg', '../img/6.jpeg', '../img/8.jpeg', '../img/9.jpeg'];
    const destinationSelect = document.getElementById('packageDestination');
    const categorySelect = document.getElementById('packageCategory');
    const durationSelect = document.getElementById('packageDuration');
    const grid = document.getElementById('tourPackagesGrid');
    const selected = document.getElementById('selectedDestination');
    const loadMore = document.getElementById('loadMorePackages');
    const selectedPackageBooking = document.getElementById('selectedPackageBooking');
    const selectedPackageSummary = document.getElementById('selectedPackageSummary');
    const bookSelectedPackage = document.getElementById('bookSelectedPackage');
    if (!destinationSelect || !grid || !categorySelect || !durationSelect || !selected || !loadMore) return;

    destinations.forEach(destination => destinationSelect.add(new Option(destination, destination)));
    const packages = destinations.map((destination, index) => {
        const category = categories[index % categories.length];
        const days = [2, 3, 4, 5, 6, 7, 9, 12, 14][index % 9];
        return { destination, category, days, image: images[index % images.length], price: 12000 + (days * 6200) + ((index % 5) * 1800) };
    });
    let visibleCount = 12;
    const escapeHtml = value => String(value).replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[char]);
    const durationMatches = (days, filter) => !filter || (filter === '2' && days <= 3) || (filter === '5' && days >= 4 && days <= 7) || (filter === '9' && days >= 8 && days <= 12) || (filter === '14' && days >= 13);

    function filteredPackages() {
        return packages.filter(item => (!destinationSelect.value || item.destination === destinationSelect.value) && (!categorySelect.value || item.category === categorySelect.value) && durationMatches(item.days, durationSelect.value));
    }

    function render() {
        const results = filteredPackages();
        const destinationText = destinationSelect.value ? `Selected destination: ${destinationSelect.value}.` : 'Showing packages for all destinations.';
        selected.textContent = `${destinationText} ${results.length} package${results.length === 1 ? '' : 's'} available.`;
        if (selectedPackageBooking && selectedPackageSummary && bookSelectedPackage) {
            const selectedDestination = destinationSelect.value || 'Nepal';
            const selectedCategory = categorySelect.value || 'Custom Tour';
            const durationText = durationSelect.value ? durationSelect.options[durationSelect.selectedIndex].text : 'Flexible duration';
            const packageName = `${selectedDestination} ${selectedCategory} Package — ${durationText}`;
            selectedPackageSummary.textContent = 'Book your package';
            selectedPackageSummary.title = packageName;
            bookSelectedPackage.dataset.bookingPackage = packageName;
            bookSelectedPackage.dataset.bookingImage = images[(Math.max(0, destinations.indexOf(selectedDestination))) % images.length];
        }
        grid.innerHTML = results.slice(0, visibleCount).map((item, index) => {
            const title = `${item.destination} ${item.category} Escape`;
            return `<article class="tour-card">
                <img class="tour-card-image" src="${item.image}" alt="${escapeHtml(title)}">
                <div class="tour-card-content"><span class="tour-card-badge">${escapeHtml(item.category)}</span><h3>${escapeHtml(title)}</h3>
                <p class="tour-card-destination"><i class="fa-solid fa-location-dot"></i> ${escapeHtml(item.destination)}, Nepal</p>
                <p>Guided travel, comfortable stays and a flexible itinerary for your group.</p>
                <div class="tour-card-meta"><span>${item.days} Days / ${item.days - 1} Nights</span><strong>NPR ${item.price.toLocaleString()} / person</strong></div>
                <div class="tour-actions"><button type="button" class="package-view" data-package-index="${packages.indexOf(item)}">View Package</button><button type="button" class="package-book" data-booking-package="${escapeHtml(title)}" data-booking-image="${item.image}">Book Package</button></div></div></article>`;
        }).join('') || '<p class="package-empty">No packages match these filters. Choose another destination or duration.</p>';
        loadMore.hidden = visibleCount >= results.length || results.length === 0;
    }

    [destinationSelect, categorySelect, durationSelect].forEach(control => control.addEventListener('change', () => { visibleCount = 12; render(); }));
    loadMore.addEventListener('click', () => { visibleCount += 12; render(); });
    const packageNav = document.querySelector('.package-nav-item');
    const packageToggle = document.querySelector('.package-nav-toggle');
    const packageNavMenu = document.getElementById('packageNavMenu');
    const openPackageSection = (destination = '') => {
            if (destination) destinationSelect.value = destination;
            visibleCount = 12;
            render();
            const section = document.getElementById('tour-packages');
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            window.setTimeout(() => section.focus({ preventScroll: true }), 500);
    };
    packageToggle?.addEventListener('click', () => {
        const isOpen = packageToggle.getAttribute('aria-expanded') === 'true';
        packageToggle.setAttribute('aria-expanded', String(!isOpen));
        packageNavMenu.hidden = isOpen;
    });
    packageNavMenu?.addEventListener('click', event => {
        const option = event.target.closest('[data-package-destination], .package-nav-all');
        if (!option) return;
        openPackageSection(option.dataset.packageDestination || '');
        packageToggle.setAttribute('aria-expanded', 'false');
        packageNavMenu.hidden = true;
    });
    document.addEventListener('click', event => {
        if (packageNav && !packageNav.contains(event.target)) {
            packageToggle?.setAttribute('aria-expanded', 'false');
            if (packageNavMenu) packageNavMenu.hidden = true;
        }
    });
    document.addEventListener('click', event => {
        const viewButton = event.target.closest('.package-view');
        if (!viewButton) return;
        const item = packages[Number(viewButton.dataset.packageIndex)];
        if (!item) return;
        const title = `${item.destination} ${item.category} Escape`;
        const modal = document.createElement('div');
        modal.className = 'package-details-modal';
        modal.innerHTML = `<div class="package-details-backdrop"></div><section class="package-details-dialog" role="dialog" aria-modal="true"><button class="package-details-close" aria-label="Close">&times;</button><img src="${item.image}" alt="${escapeHtml(title)}"><div><span>${escapeHtml(item.category)} package</span><h2>${escapeHtml(title)}</h2><p>Explore ${escapeHtml(item.destination)} with local guides, transport planning, accommodation support and flexible departure dates.</p><p><strong>${item.days} Days / ${item.days - 1} Nights</strong> · NPR ${item.price.toLocaleString()} per person</p><button type="button" class="package-book" data-booking-package="${escapeHtml(title)}" data-booking-image="${item.image}">Book this package</button></div></section>`;
        document.body.appendChild(modal);
        modal.addEventListener('click', closeEvent => { if (closeEvent.target === modal || closeEvent.target.closest('.package-details-backdrop, .package-details-close')) modal.remove(); });
    });
    render();
})();
