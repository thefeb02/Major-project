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
                <input id="bookingServiceCategory" type="hidden" name="service_category" value="${escapeHtml(category)}">
                <input id="bookingPackageId" type="hidden" name="package_id" value="">
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
                        <input id="bookingPhone" name="phone" type="tel" required maxlength="13" autocomplete="tel" inputmode="numeric" pattern="(?:\\+977)?(?:98|97)[0-9]{8}" placeholder="98XXXXXXX or +97798XXXXXXX">
                    </label>
                    <label>Preferred date
                        <input name="travel_date" type="date" required min="${today}">
                    </label>
                </div>
                <label>Number of travelers
                    <input name="travelers" type="number" min="1" max="50" value="1" required>
                </label>
                <label>Notes (optional)
                    <textarea name="message" rows="3" maxlength="2000" placeholder="Questions, dietary needs, or anything else. All tours start from Butwal."></textarea>
                </label>
                <div class="booking-form__row">
                    <label>Payment option
                        <select name="payment_method" required>
                            <option value="esewa">Pay by eSewa</option>
                            <option value="khalti">Pay by Khalti</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="pay_later">Pay later / on arrival</option>
                        </select>
                    </label>
                    <label>Deposit amount (NPR)
                        <input id="bookingAmount" name="amount" type="number" min="0" step="0.01" value="100" required>
                    </label>
                </div>
                <div id="bankDetailsBox" class="booking-bank-details" hidden>
                    <h3>Bank transfer details</h3>
                    <ul>
                        <li><strong>MSA Bank</strong> — A/C Holder: Nepal Tour and Travel — A/C No: 012-345-6789</li>
                        <li><strong>Prabhu Bank</strong> — A/C Holder: Nepal Tour and Travel — A/C No: 987-654-3210</li>
                        <li><strong>Rastriya Banijya Bank</strong> — A/C Holder: Nepal Tour and Travel — A/C No: 456-789-0123</li>
                        <li><strong>Himalaya Bank</strong> — A/C Holder: Nepal Tour and Travel — A/C No: 321-098-7654</li>
                        <li><strong>Everest Bank</strong> — A/C Holder: Nepal Tour and Travel — A/C No: 654-321-0987</li>
                    </ul>
                    <p>Share the transaction reference in the field below after making the transfer.</p>
                </div>
                <label>Payment reference / transaction ID
                    <input name="payment_reference" maxlength="100" placeholder="Optional if you already paid">
                </label>
                <p class="booking-modal__intro">Choose a real payment method for your booking and share the transaction reference so our team can confirm it quickly.</p>
                <button class="booking-form__submit" type="submit">Book now & confirm</button>
            </form>
        </section>`;
    document.body.appendChild(modal);

    const serviceInput = modal.querySelector('#bookingServiceName');
    const serviceCategoryInput = modal.querySelector('#bookingServiceCategory');
    const packageIdInput = modal.querySelector('#bookingPackageId');
    const fullNameInput = modal.querySelector('input[name="full_name"]');
    const emailInput = modal.querySelector('input[name="email"]');
    const phoneInput = modal.querySelector('#bookingPhone');
    const amountInput = modal.querySelector('#bookingAmount');
    const paymentMethodSelect = modal.querySelector('select[name="payment_method"]');
    const bankDetailsBox = modal.querySelector('#bankDetailsBox');
    const submitButton = modal.querySelector('.booking-form__submit');
    const packagePreview = modal.querySelector('#bookingPackagePreview');
    const packageImage = modal.querySelector('#bookingPackageImage');
    const packageTitle = modal.querySelector('#bookingPackageTitle');
    const getPendingBooking = () => {
        try {
            return JSON.parse(window.sessionStorage.getItem('pendingBooking') || 'null');
        } catch (error) {
            return null;
        }
    };
    const savePendingBooking = (payload) => {
        try {
            window.sessionStorage.setItem('pendingBooking', JSON.stringify(payload));
        } catch (error) {
            // Ignore storage failures so the flow still continues.
        }
    };
    const clearPendingBooking = () => {
        try {
            window.sessionStorage.removeItem('pendingBooking');
        } catch (error) {
            // Ignore storage failures so the flow still continues.
        }
    };
    const openModal = (serviceName = '', imageUrl = '', packagePrice = '', destination = '', packageCategory = '', duration = '', packageId = '') => {
        const currentTitle = document.querySelector('.details-title')?.textContent?.trim();
        serviceInput.value = serviceName || currentTitle || '';
        serviceCategoryInput.value = packageCategory || category;
        packageIdInput.value = /^\d+$/.test(String(packageId)) ? String(packageId) : '';
        const selectedImage = imageUrl || document.querySelector('.details-hero')?.src || '';
        const userName = document.body.dataset.userName || '';
        const userEmail = document.body.dataset.userEmail || '';
        if (fullNameInput && !fullNameInput.value && userName) {
            fullNameInput.value = userName;
        }
        if (emailInput && !emailInput.value && userEmail) {
            emailInput.value = userEmail;
        }
        const priceValue = packagePrice && Number(packagePrice) > 0 ? String(packagePrice) : '100';
        if (amountInput) amountInput.value = priceValue;
        const bookingSummary = [destination, packageCategory || category, duration].filter(Boolean).join(' • ');
        if (bookingSummary) {
            const noteField = modal.querySelector('textarea[name="message"]');
            if (noteField && !noteField.value) {
                noteField.value = `Package details: ${bookingSummary}`;
            }
        }
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
        if (!isUserLoggedIn()) {
            savePendingBooking({
                serviceName: button.dataset.bookingPackage || '',
                imageUrl: button.dataset.bookingImage || '',
                packagePrice: button.dataset.bookingPrice || '',
                destination: button.dataset.bookingDestination || '',
                category: button.dataset.bookingCategory || '',
                duration: button.dataset.bookingDuration || '',
                packageId: button.dataset.bookingPackageId || ''
            });
            const currentPage = `${window.location.pathname.split('/').pop() || 'index.php'}${window.location.search}${window.location.hash}`;
            const loginUrl = new URL('login.php', window.location.href);
            loginUrl.searchParams.set('redirect', currentPage);
            loginUrl.searchParams.set('message', 'Please log in first to book this tour.');
            window.location.href = loginUrl.pathname + loginUrl.search;
            return;
        }
        openModal(
            button.dataset.bookingPackage || '',
            button.dataset.bookingImage || '',
            button.dataset.bookingPrice || '',
            button.dataset.bookingDestination || '',
            button.dataset.bookingCategory || '',
            button.dataset.bookingDuration || '',
            button.dataset.bookingPackageId || ''
        );
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
    });

    const form = modal.querySelector('.booking-form');
    const isUserLoggedIn = () => document.body.dataset.loggedIn === '1' || document.body.dataset.loggedIn === 'true' || document.body.dataset.loggedIn === 'yes';
    const restorePendingBooking = () => {
        const pendingBooking = getPendingBooking();
        if (!pendingBooking) return;
        clearPendingBooking();
        openModal(
            pendingBooking.serviceName || '',
            pendingBooking.imageUrl || '',
            pendingBooking.packagePrice || '',
            pendingBooking.destination || '',
            pendingBooking.category || '',
            pendingBooking.duration || '',
            pendingBooking.packageId || ''
        );
    };
    const normalizePhone = (value) => {
        const trimmed = String(value ?? '').trim();
        if (!trimmed) return '';

        const digitsOnly = trimmed.replace(/\D/g, '');
        if (!digitsOnly) return '';

        if (digitsOnly.startsWith('977')) {
            return `+${digitsOnly}`;
        }

        if (/^(98|97)\d{8}$/.test(digitsOnly)) {
            return `+977${digitsOnly}`;
        }

        return trimmed;
    };

    const updatePaymentButtonLabel = () => {
        if (!submitButton || !paymentMethodSelect) return;
        const method = paymentMethodSelect.value;
        if (method === 'esewa') {
            submitButton.textContent = 'Book & pay with eSewa';
        } else if (method === 'khalti') {
            submitButton.textContent = 'Book & pay with Khalti';
        } else if (method === 'bank_transfer') {
            submitButton.textContent = 'Book & pay by bank transfer';
        } else {
            submitButton.textContent = 'Book now & confirm';
        }
    };
    const updateBankDetailsVisibility = () => {
        if (!bankDetailsBox || !paymentMethodSelect) return;
        bankDetailsBox.hidden = paymentMethodSelect.value !== 'bank_transfer';
    };
    paymentMethodSelect?.addEventListener('change', () => {
        updatePaymentButtonLabel();
        updateBankDetailsVisibility();
    });
    updatePaymentButtonLabel();
    updateBankDetailsVisibility();

    form?.addEventListener('submit', (event) => {
        if (!phoneInput || !phoneInput.value) {
            event.preventDefault();
            phoneInput.focus();
            phoneInput.reportValidity();
            return;
        }

        const normalizedPhone = normalizePhone(phoneInput.value);
        if (!/^\+977(98|97)\d{8}$/.test(normalizedPhone)) {
            event.preventDefault();
            phoneInput.setCustomValidity('Please enter a valid Nepal mobile number like 98XXXXXXXX or +97798XXXXXXXX.');
            phoneInput.reportValidity();
            phoneInput.focus();
            return;
        }

        phoneInput.value = normalizedPhone;
        phoneInput.setCustomValidity('');

        const paymentMethod = paymentMethodSelect?.value || 'pay_later';
        if (paymentMethod === 'khalti') {
            window.open('https://khalti.com/', '_blank', 'noopener,noreferrer');
        }
    });

    if (isUserLoggedIn()) {
        restorePendingBooking();
    }

    if (new URLSearchParams(window.location.search).get('booking') === 'success') {
        const notice = document.createElement('p');
        notice.className = 'booking-success';
        const params = new URLSearchParams(window.location.search);
        const paymentMode = params.get('payment');
        const paymentMethod = params.get('method') || 'pay_later';
        const amount = params.get('amount');
        let message = 'Thanks — your booking request has been received. We will contact you shortly.';

        if (paymentMode === 'online') {
            if (paymentMethod === 'esewa') {
                message = `Thanks — your booking request has been received. Please pay NPR ${amount || '100'} through eSewa and share the transaction ID with us.`;
            } else if (paymentMethod === 'khalti') {
                message = `Thanks — your booking request has been received. Please pay NPR ${amount || '100'} through Khalti and share the transaction ID with us.`;
            } else if (paymentMethod === 'bank_transfer') {
                message = `Thanks — your booking request has been received. Please transfer NPR ${amount || '100'} to our bank account and share the transaction reference with us.`;
            } else {
                message = `Thanks — your booking request has been received. Please complete your payment and share the transaction reference with us.`;
            }
        }

        const paymentLink = paymentMethod === 'esewa'
            ? '<a href="https://esewa.com.np/#/home" target="_blank" rel="noopener noreferrer">Pay with eSewa</a>'
            : paymentMethod === 'khalti'
                ? '<a href="https://khalti.com/" target="_blank" rel="noopener noreferrer">Pay with Khalti</a>'
                : '';
        notice.innerHTML = `${message}<br><strong>Payment options:</strong> eSewa, Khalti, or bank transfer for confirmed bookings.${paymentLink ? `<br>${paymentLink}` : ''}`;
        document.body.prepend(notice);
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[char]);
    }
})();
