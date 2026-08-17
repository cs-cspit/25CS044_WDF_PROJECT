document.addEventListener('DOMContentLoaded', function () {
    const banner = document.getElementById('notification-banner');
    const closeButton = document.getElementById('notification-close');

    if (!banner || !closeButton) return;

    closeButton.addEventListener('click', function () {
        banner.classList.add('hidden');
        banner.addEventListener('transitionend', function () {
            banner.style.display = 'none';
        }, { once: true });
    });
});
