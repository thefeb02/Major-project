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
    const existingCategories = new Set([...categorySelect.options].map(option => option.value));
    [...new Set(packages.map(p => p.category).filter(Boolean))].forEach(category => {
        if (!existingCategories.has(category)) {
            categorySelect.add(new Option(category, category));
        }
    });

    let visibleCount = 12;
    const escapeHtml = value => String(value).replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[char]);
    const durationMatches = (days, filter) => !filter || (filter === '2' && days <= 3) || (filter === '5' && days >= 4 && days <= 7) || (filter === '9' && days >= 8 && days <= 12) || (filter === '14' && days >= 13);
    const imageCreditMarkup = item => item.image_credit && item.image_credit_url
        ? `<a class="tour-card-image-credit" href="${escapeHtml(item.image_credit_url)}" target="_blank" rel="noopener noreferrer">${escapeHtml(item.image_credit)}</a>`
        : '';
    const fallbackImageFor = item => {
        const destination = String(item.destination || '').toLowerCase();
        if (/chitwan|bardia|shuklaphanta/.test(destination)) return '../img/2.jpeg';
        if (/everest|sagarmatha|solukhumbu|annapurna|mustang|manang/.test(destination)) return '../img/3.jpeg';
        if (/kathmandu|bhaktapur|patan|janakpur|lumbini|temple|durbar|monastery/.test(destination)) return '../img/4.jpeg';
        if (/pokhara|phewa|rara|phoksundo|bandipur|lake/.test(destination)) return '../img/1.jpeg';

        return item.category === 'Nature' ? '../img/2.jpeg' :
            item.category === 'Adventure' ? '../img/3.jpeg' :
            ['Cultural', 'Pilgrimage'].includes(item.category) ? '../img/4.jpeg' :
            '../img/9.jpeg';
    };
    const addImageFallback = image => {
        image.addEventListener('error', () => {
            const fallbackImage = image.dataset.fallbackImage;
            if (fallbackImage && image.src !== new URL(fallbackImage, window.location.href).href) {
                image.src = fallbackImage;
            }
        }, { once: true });
    };

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
            const selectedPackage = results[0];
            selectedPackageSummary.textContent = selectedPackage ? 'Book your package' : 'No matching packages';
            selectedPackageSummary.title = selectedPackage?.title || '';
            bookSelectedPackage.disabled = !selectedPackage;
            bookSelectedPackage.dataset.bookingPackage = selectedPackage?.title || '';
            bookSelectedPackage.dataset.bookingImage = selectedPackage?.image || '';
            bookSelectedPackage.dataset.bookingPrice = selectedPackage?.price ? String(selectedPackage.price) : '';
            bookSelectedPackage.dataset.bookingDestination = selectedPackage?.destination || '';
            bookSelectedPackage.dataset.bookingCategory = selectedPackage?.category || '';
            bookSelectedPackage.dataset.bookingDuration = selectedPackage?.duration_text || '';
            bookSelectedPackage.dataset.bookingPackageId = /^\d+$/.test(String(selectedPackage?.id ?? '')) ? String(selectedPackage.id) : '';
        }
        grid.innerHTML = results.slice(0, visibleCount).map((item, index) => {
            const title = item.title || `${item.destination} ${item.category} Package`;
            const fallbackImage = fallbackImageFor(item);
            return `<article class="tour-card">
                <img class="tour-card-image" src="${escapeHtml(item.image || fallbackImage)}" data-fallback-image="${escapeHtml(fallbackImage)}" alt="${escapeHtml(title)}" loading="lazy">
                ${imageCreditMarkup(item)}
                <div class="tour-card-content"><span class="tour-card-badge">${item.category === 'Education' ? 'Education' : escapeHtml(item.category)}</span><h3>${escapeHtml(title)}</h3>
                <p class="tour-card-destination"><i class="fa-solid fa-location-dot"></i> ${escapeHtml(item.destination)}, Nepal</p>
                <p>${escapeHtml(item.description || 'Guided travel, comfortable stays and a flexible itinerary for your group.')}</p>
                <div class="tour-card-meta"><span>${item.days} Days / ${item.days - 1} Nights</span><strong>NPR ${item.price.toLocaleString()} / person</strong></div>
                <div class="tour-card-duration">Duration: ${item.days} Days / ${item.days - 1} Nights</div>
                <div class="tour-actions"><button type="button" class="package-view" data-package-index="${packages.indexOf(item)}">View Package</button><button type="button" class="package-book" data-booking-package="${escapeHtml(title)}" data-booking-image="${escapeHtml(item.image)}" data-booking-price="${item.price}" data-booking-destination="${escapeHtml(item.destination)}" data-booking-category="${escapeHtml(item.category)}" data-booking-duration="${escapeHtml(item.duration_text || `${item.days} Days`)}" data-booking-package-id="${/^\d+$/.test(String(item.id ?? '')) ? escapeHtml(item.id) : ''}">Book Package</button></div></div></article>`;
        }).join('') || '<p class="package-empty">No packages match these filters. Choose another destination or duration.</p>';
        grid.querySelectorAll('.tour-card-image').forEach(addImageFallback);
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
        const title = item.title || `${item.destination} ${item.category} Package`;
        const fallbackImage = fallbackImageFor(item);
        const modal = document.createElement('div');
        modal.className = 'package-details-modal';
        modal.innerHTML = `<div class="package-details-backdrop"></div><section class="package-details-dialog" role="dialog" aria-modal="true"><button class="package-details-close" aria-label="Close">&times;</button><img data-fallback-image="${escapeHtml(fallbackImage)}" src="${escapeHtml(item.image || fallbackImage)}" alt="${escapeHtml(title)}"><div><span>${item.category === 'Education' ? 'Education' : escapeHtml(item.category)} package</span><h2>${escapeHtml(title)}</h2>${imageCreditMarkup(item)}<p>${escapeHtml(item.full_description || item.description || `Explore ${item.destination} with local guides, transport planning, accommodation support and flexible departure dates.`)}</p><p><strong>${escapeHtml(item.duration_text || `${item.days} Days / ${item.days - 1} Nights`)}</strong> · NPR ${Number(item.price).toLocaleString()}</p><button type="button" class="package-book" data-booking-package="${escapeHtml(title)}" data-booking-image="${escapeHtml(item.image)}" data-booking-price="${item.price}" data-booking-destination="${escapeHtml(item.destination)}" data-booking-category="${escapeHtml(item.category)}" data-booking-duration="${escapeHtml(item.duration_text || `${item.days} Days`)}" data-booking-package-id="${/^\d+$/.test(String(item.id ?? '')) ? escapeHtml(item.id) : ''}">Book this package</button></div></section>`;
        addImageFallback(modal.querySelector('img'));
        document.body.appendChild(modal);
        modal.addEventListener('click', closeEvent => { if (closeEvent.target === modal || closeEvent.target.closest('.package-details-backdrop, .package-details-close')) modal.remove(); });
    });
    render();
})();
