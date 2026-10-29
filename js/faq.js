/**
 * faq.js (Upgraded to Modular Dynamic Fetch with Category Filter & Search)
 */
document.addEventListener('DOMContentLoaded', function () {
    const faqList = document.querySelector('.faq-list');
    const searchInput = document.getElementById('faq-search');
    const categorySelect = document.getElementById('faq-category');
    const statusMsg = document.getElementById('faq-status');

    if (!faqList) return;

    let allFaqs = [];

    function showLoading() {
        if (statusMsg) {
            statusMsg.style.display = 'block';
            statusMsg.innerHTML = '⏳ Loading FAQs from faqs.json...';
            statusMsg.className = 'status-msg loading';
        }
    }

    function showError(err) {
        if (statusMsg) {
            statusMsg.style.display = 'block';
            statusMsg.innerHTML = `⚠️ Failed to fetch FAQs: ${err}. Ensure a local web server is used.`;
            statusMsg.className = 'status-msg error';
        }
    }

    function hideStatus() {
        if (statusMsg) statusMsg.style.display = 'none';
    }

    // Dynamic fetch from JSON
    async function loadFaqs() {
        showLoading();
        try {
            const res = await fetch('../data/faqs.json');
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            allFaqs = await res.json();
            hideStatus();
            renderFaqs(allFaqs);
        } catch (e) {
            showError(e.message);
            // Fallback: If fetch fails (e.g. file:// protocol restriction), keep static items interactive
            initAccordion();
        }
    }

    // Render FAQ Elements dynamically into DOM
    function renderFaqs(items) {
        if (items.length === 0) {
            faqList.innerHTML = `<p style="text-align:center; color:#64748b; padding:20px;">No questions found matching your query.</p>`;
            return;
        }

        faqList.innerHTML = items.map(item => `
            <article class="faq-item" data-category="${item.category}">
                <h3 role="button" tabindex="0" aria-expanded="false">
                    <span>${item.question}</span>
                    <span class="faq-tag">${item.category}</span>
                </h3>
                <p>${item.answer}</p>
            </article>
        `).join('');

        initAccordion();
    }

    // Attach Collapsible Accordion logic
    function initAccordion() {
        const faqItems = faqList.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const question = item.querySelector('h3');
            const answer = item.querySelector('p');
            if (!question || !answer) return;

            answer.style.maxHeight = '0';
            answer.style.overflow = 'hidden';
            answer.style.transition = 'max-height 0.3s ease';

            function toggleFAQ() {
                const isOpen = item.classList.toggle('open');
                if (isOpen) {
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                    question.setAttribute('aria-expanded', 'true');
                } else {
                    answer.style.maxHeight = '0';
                    question.setAttribute('aria-expanded', 'false');
                }
            }

            question.addEventListener('click', toggleFAQ);
            question.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleFAQ();
                }
            });
        });
    }

    // Search and Category Filter Handler
    function filterFaqs() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const cat = categorySelect ? categorySelect.value : 'all';

        const filtered = allFaqs.filter(faq => {
            const matchesQuery = faq.question.toLowerCase().includes(query) ||
                                 faq.answer.toLowerCase().includes(query);
            const matchesCat = (cat === 'all') || (faq.category === cat);
            return matchesQuery && matchesCat;
        });

        renderFaqs(filtered);
    }

    if (searchInput) searchInput.addEventListener('input', filterFaqs);
    if (categorySelect) categorySelect.addEventListener('change', filterFaqs);

    loadFaqs();
});
