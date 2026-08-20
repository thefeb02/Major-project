(async () => {
    let packages = [];
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

    try {
        const response = await fetch('api/packages.php');
        const json = await response.json();
        if (json.success) {
            packages = json.data;
        }
    } catch (e) {
        console.error('Failed to fetch packages:', e);
    }
    
    // Populate dynamic destination dropdown
    const uniqueDestinations = [...new Set(packages.map(p => p.destination))];
    uniqueDestinations.forEach(dest => destinationSelect.add(new Option(dest, dest)));

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
        const currentQuery = new URLSearchParams(window.location.search);
        if (!currentQuery.get('destination') && destinationSelect.value) {
            currentQuery.set('destination', destinationSelect.value);
            const newUrl = `${window.location.pathname}?${currentQuery.toString()}${window.location.hash}`;
            window.history.replaceState({}, '', newUrl);
        }
        if (selectedPackageBooking && selectedPackageSummary && bookSelectedPackage) {
            const selectedDestination = destinationSelect.value || 'Nepal';
            const selectedCategory = categorySelect.value || 'Custom Tour';
            const durationText = durationSelect.value ? durationSelect.options[durationSelect.selectedIndex].text : 'Flexible duration';
            const packageName = `${selectedDestination} ${selectedCategory} Package — ${durationText}`;
            selectedPackageSummary.textContent = 'Book your package';
            selectedPackageSummary.title = packageName;
            bookSelectedPackage.dataset.bookingPackage = packageName;
            bookSelectedPackage.dataset.bookingImage = images[(Math.max(0, destinations.indexOf(selectedDestination))) % images.length];
            bookSelectedPackage.dataset.bookingPrice = results[0]?.price ? String(results[0].price) : '12000';
        }
        grid.innerHTML = results.slice(0, visibleCount).map((item, index) => {
            const title = item.category === 'Education' ? `${item.destination} Student Education Tour` : `${item.destination} ${item.category} Escape`;
            return `<article class="tour-card">
                <img class="tour-card-image" src="${item.image}" alt="${escapeHtml(title)}">
                <div class="tour-card-content"><span class="tour-card-badge">${item.category === 'Education' ? 'Education' : escapeHtml(item.category)}</span><h3>${escapeHtml(title)}</h3>
                <p class="tour-card-destination"><i class="fa-solid fa-location-dot"></i> ${escapeHtml(item.destination)}, Nepal</p>
                <p>Guided travel, comfortable stays and a flexible itinerary for your group.</p>
                <div class="tour-card-meta"><span>${item.days} Days / ${item.days - 1} Nights</span><strong>NPR ${item.price.toLocaleString()} / person</strong></div>
                <div class="tour-card-duration">Duration: ${item.days} Days / ${item.days - 1} Nights</div>
                <div class="tour-actions"><button type="button" class="package-view" data-package-index="${packages.indexOf(item)}">View Package</button><button type="button" class="package-book" data-booking-package="${escapeHtml(title)}" data-booking-image="${item.image}" data-booking-price="${item.price}" data-booking-destination="${escapeHtml(item.destination)}" data-booking-category="${escapeHtml(item.category)}" data-booking-duration="${item.days} Days / ${item.days - 1} Nights">Book Package</button></div></div></article>`;
        }).join('') || '<p class="package-empty">No packages match these filters. Choose another destination or duration.</p>';
        loadMore.hidden = visibleCount >= results.length || results.length === 0;
    }

    [destinationSelect, categorySelect, durationSelect].forEach(control => control.addEventListener('change', () => { visibleCount = 12; render(); }));
    const params = new URLSearchParams(window.location.search);
    const initialDestination = params.get('destination');
    if (initialDestination) {
        destinationSelect.value = initialDestination;
    }
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
        const title = item.category === 'Education' ? `${item.destination} Student Education Tour` : `${item.destination} ${item.category} Escape`;
        const modal = document.createElement('div');
        modal.className = 'package-details-modal';
        modal.innerHTML = `<div class="package-details-backdrop"></div><section class="package-details-dialog" role="dialog" aria-modal="true"><button class="package-details-close" aria-label="Close">&times;</button><img src="${item.image}" alt="${escapeHtml(title)}"><div><span>${item.category === 'Education' ? 'Education' : escapeHtml(item.category)} package</span><h2>${escapeHtml(title)}</h2><p>Explore ${escapeHtml(item.destination)} with local guides, transport planning, accommodation support and flexible departure dates.</p><p><strong>${item.days} Days / ${item.days - 1} Nights</strong> · NPR ${item.price.toLocaleString()} per person</p><button type="button" class="package-book" data-booking-package="${escapeHtml(title)}" data-booking-image="${item.image}" data-booking-price="${item.price}" data-booking-destination="${escapeHtml(item.destination)}" data-booking-category="${escapeHtml(item.category)}" data-booking-duration="${item.days} Days / ${item.days - 1} Nights">Book this package</button></div></section>`;
        document.body.appendChild(modal);
        modal.addEventListener('click', closeEvent => { if (closeEvent.target === modal || closeEvent.target.closest('.package-details-backdrop, .package-details-close')) modal.remove(); });
    });
    render();
})();
