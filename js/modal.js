document.addEventListener('DOMContentLoaded', function () {
    const openAddress = document.getElementById('open-address-modal');
    const closeAddress = document.getElementById('close-address-modal');
    const addressModal = document.getElementById('address-modal');

    if (!openAddress || !closeAddress || !addressModal) return;

    function openModal() {
        addressModal.classList.add('active');
        addressModal.setAttribute('aria-hidden', 'false');
        closeAddress.focus();
    }

    function closeModal() {
        addressModal.classList.remove('active');
        addressModal.setAttribute('aria-hidden', 'true');
        openAddress.focus();
    }

    openAddress.addEventListener('click', function (event) {
        event.preventDefault();
        openModal();
    });

    closeAddress.addEventListener('click', function () {
        closeModal();
    });

    // Close on click outside
    addressModal.addEventListener('click', function (event) {
        if (event.target === addressModal) {
            closeModal();
        }
    });

    // Close on Escape key (Keyboard accessibility)
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && addressModal.classList.contains('active')) {
            closeModal();
        }
    });
});

