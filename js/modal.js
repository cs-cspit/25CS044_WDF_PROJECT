document.addEventListener('DOMContentLoaded', function () {
    const openAddress = document.getElementById('open-address-modal');
    const closeAddress = document.getElementById('close-address-modal');
    const addressModal = document.getElementById('address-modal');

    if (!openAddress || !closeAddress || !addressModal) return;

    openAddress.addEventListener('click', function (event) {
        event.preventDefault();
        addressModal.classList.add('active');
    });

    closeAddress.addEventListener('click', function () {
        addressModal.classList.remove('active');
    });

    addressModal.addEventListener('click', function (event) {
        if (event.target === addressModal) {
            addressModal.classList.remove('active');
        }
    });
});
