(() => {
    const category = document.body.dataset.bookingCategory || 'Tour';
    const today = new Date().toISOString().split('T')[0];

    const modal = document.createElement('div');
    modal.className = 'booking-modal';
    modal.id = 'bookingModal';
    modal.hidden = true;
    modal.innerHTML = `
        <div class="booking-modal__backdrop" data-booking-close></div>
        <section class="booking-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="bookingTitle">
            <button class="booking-modal__close" type="button" aria-label="Close booking form" data-booking-close>&times;</button>
            <p class="booking-modal__eyebrow">${category} booking</p>
            <h2 id="bookingTitle">Plan your experience</h2>
            <p class="booking-modal__intro">Share your details and our travel team will confirm availability with you.</p>
            <div class="booking-package-preview" id="bookingPackagePreview" hidden>
                <img id="bookingPackageImage" src="" alt="Selected package">
                <strong id="bookingPackageTitle"></strong>
            </div>
            <form class="booking-form" action="../Backend/book_service.php" method="post">
                <input type="hidden" name="service_category" value="${escapeHtml(category)}">
                <label>Package or experience
                    <input id="bookingServiceName" name="service_name" required maxlength="190" placeholder="Choose a package">
                </label>
                <div class="booking-form__row">
                    <label>Full name
                        <input name="full_name" required maxlength="120" autocomplete="name">
                    </label>
                    <label>Email address
                        <input name="email" type="email" required maxlength="190" autocomplete="email">
                    </label>
                </div>
                <div class="booking-form__row">
                    <label>Phone number
                        <input name="phone" type="tel" required maxlength="40" autocomplete="tel">
                    </label>
                    <label>Preferred date
                        <input name="travel_date" type="date" required min="${today}">
                    </label>
                </div>
                <label>Number of travelers
                    <input name="travelers" type="number" min="1" max="50" value="1" required>
                </label>
                <label>Notes (optional)
                    <textarea name="message" rows="3" maxlength="2000" placeholder="Questions, pickup location, dietary needs, or anything else"></textarea>
                </label>
                <button class="booking-form__submit" type="submit">Send booking request</button>
            </form>
        </section>`;
    document.body.appendChild(modal);

    const serviceInput = modal.querySelector('#bookingServiceName');
    const packagePreview = modal.querySelector('#bookingPackagePreview');
    const packageImage = modal.querySelector('#bookingPackageImage');
    const packageTitle = modal.querySelector('#bookingPackageTitle');
    const openModal = (serviceName = '', imageUrl = '') => {
        const currentTitle = document.querySelector('.details-title')?.textContent?.trim();
        serviceInput.value = serviceName || currentTitle || '';
        const selectedImage = imageUrl || document.querySelector('.details-hero')?.src || '';
        packagePreview.hidden = !selectedImage;
        packageImage.src = selectedImage;
        packageTitle.textContent = serviceInput.value;
        modal.hidden = false;
        document.body.classList.add('booking-modal-open');
        window.setTimeout(() => serviceInput.focus(), 0);
    };
    const closeModal = () => {
        modal.hidden = true;
        document.body.classList.remove('booking-modal-open');
    };

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-booking-close]')) {
            closeModal();
            return;
        }
        const button = event.target.closest('.btn-book, .btn-book-large, .booking-trigger, .booking-card-action, [data-booking-package]');
        if (!button) return;
        event.preventDefault();
        openModal(button.dataset.bookingPackage || '', button.dataset.bookingImage || '');
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
    });

    if (new URLSearchParams(window.location.search).get('booking') === 'success') {
        const notice = document.createElement('p');
        notice.className = 'booking-success';
        notice.textContent = 'Thanks — your booking request has been received. We will contact you shortly.';
        document.body.prepend(notice);
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[char]);
    }
})();
