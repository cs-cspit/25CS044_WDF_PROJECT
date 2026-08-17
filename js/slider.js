document.addEventListener('DOMContentLoaded', function () {
    // Support multiple sliders on the page
    const sliderContainers = document.querySelectorAll('.campus-slider');

    sliderContainers.forEach(container => {
        const slides = container.querySelectorAll('.slider-slide');
        const prevBtn = container.querySelector('.slider-prev');
        const nextBtn = container.querySelector('.slider-next');
        const dots = container.querySelectorAll('.slider-dot');
        let current = 0;
        let interval = null;

        function showSlide(index) {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === current);
            });
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === current);
            });
        }

        function nextSlide() {
            showSlide(current + 1);
        }

        function prevSlide() {
            showSlide(current - 1);
        }

        function startAutoPlay() {
            stopAutoPlay();
            interval = setInterval(nextSlide, 5000);
        }

        function stopAutoPlay() {
            if (interval) { clearInterval(interval); interval = null; }
        }

        nextBtn?.addEventListener('click', function () {
            nextSlide();
            stopAutoPlay();
            startAutoPlay();
        });

        prevBtn?.addEventListener('click', function () {
            prevSlide();
            stopAutoPlay();
            startAutoPlay();
        });

        dots.forEach((dot, index) => {
            dot.addEventListener('click', function () {
                showSlide(index);
                stopAutoPlay();
                startAutoPlay();
            });
        });

        // pause autoplay on hover/focus
        container.addEventListener('mouseenter', stopAutoPlay);
        container.addEventListener('mouseleave', startAutoPlay);
        container.addEventListener('focusin', stopAutoPlay);
        container.addEventListener('focusout', startAutoPlay);

        showSlide(0);
        startAutoPlay();
    });
});
