document.addEventListener('DOMContentLoaded', function () {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('h3');
        const answer = item.querySelector('p');

        if (!question || !answer) return;

        answer.style.maxHeight = '0';
        answer.style.overflow = 'hidden';
        answer.style.transition = 'max-height 0.3s ease';

        question.style.cursor = 'pointer';
        question.setAttribute('aria-expanded', 'false');

        question.addEventListener('click', function () {
            const isOpen = item.classList.toggle('open');

            if (isOpen) {
                answer.style.maxHeight = answer.scrollHeight + 'px';
                question.setAttribute('aria-expanded', 'true');
            } else {
                answer.style.maxHeight = '0';
                question.setAttribute('aria-expanded', 'false');
            }
        });
    });
});
