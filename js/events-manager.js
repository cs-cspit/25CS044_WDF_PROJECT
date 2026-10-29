/**
 * events-manager.js
 * Modular ES6 Fetch, Filter, Search, Sort & Pagination for Campus Events
 */

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('events-tbody');
    const searchInput = document.getElementById('event-search');
    const categoryFilter = document.getElementById('event-category');
    const sortSelect = document.getElementById('event-sort');
    const paginationContainer = document.getElementById('event-pagination');
    const statusMsg = document.getElementById('event-status');

    if (!tableBody) return;

    let allEvents = [];
    let filteredEvents = [];
    let currentPage = 1;
    const itemsPerPage = 5;

    // Show loading state
    function showLoading() {
        if (statusMsg) {
            statusMsg.style.display = 'block';
            statusMsg.innerHTML = '<span class="spinner">⏳</span> Loading events data...';
            statusMsg.className = 'status-msg loading';
        }
    }

    // Show error state
    function showError(err) {
        if (statusMsg) {
            statusMsg.style.display = 'block';
            statusMsg.innerHTML = `⚠️ Failed to load events: ${err}. Please ensure a local server is running.`;
            statusMsg.className = 'status-msg error';
        }
        tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:24px; color:#ef4444;">Could not load events data.</td></tr>`;
    }

    // Hide status message
    function hideStatus() {
        if (statusMsg) statusMsg.style.display = 'none';
    }

    // Fetch JSON data via Fetch API
    async function fetchEvents() {
        showLoading();
        try {
            const response = await fetch('../data/events.json');
            if (!response.ok) throw new Error(`HTTP ${response.status} ${response.statusText}`);
            const data = await response.json();
            allEvents = data;
            filteredEvents = [...allEvents];
            hideStatus();
            applyFiltersAndRender();
        } catch (error) {
            showError(error.message);
        }
    }

    // Filter, Search, and Sort Pipeline
    function applyFiltersAndRender() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const selectedCategory = categoryFilter ? categoryFilter.value : 'all';
        const sortMode = sortSelect ? sortSelect.value : 'date-asc';

        // 1. Search & Category Filter
        filteredEvents = allEvents.filter(evt => {
            const matchesQuery = evt.name.toLowerCase().includes(query) ||
                                 evt.venue.toLowerCase().includes(query) ||
                                 evt.organizer.toLowerCase().includes(query);
            const matchesCat = (selectedCategory === 'all') || (evt.category === selectedCategory);
            return matchesQuery && matchesCat;
        });

        // 2. Sort Records
        filteredEvents.sort((a, b) => {
            if (sortMode === 'date-asc') return new Date(a.date) - new Date(b.date);
            if (sortMode === 'date-desc') return new Date(b.date) - new Date(a.date);
            if (sortMode === 'name-asc') return a.name.localeCompare(b.name);
            if (sortMode === 'name-desc') return b.name.localeCompare(b.name);
            if (sortMode === 'seats-desc') return b.seats - a.seats;
            return 0;
        });

        currentPage = 1;
        renderPage();
    }

    // Render Paginated Data
    function renderPage() {
        const totalItems = filteredEvents.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const pageItems = filteredEvents.slice(startIndex, startIndex + itemsPerPage);

        if (pageItems.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:24px; color:#64748b;">No events match your criteria.</td></tr>`;
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        tableBody.innerHTML = pageItems.map(item => `
            <tr>
                <td><strong>${item.name}</strong><br><small style="color:#64748b;">${item.id}</small></td>
                <td><span class="badge badge-${item.category.toLowerCase()}">${item.category}</span></td>
                <td>📅 ${item.date}</td>
                <td>📍 ${item.venue}</td>
                <td>🪑 ${item.seats} seats</td>
                <td>
                    <span class="status-pill status-${item.status.toLowerCase()}">${item.status}</span>
                </td>
            </tr>
        `).join('');

        renderPagination(totalPages);
    }

    // Render Pagination Controls
    function renderPagination(totalPages) {
        if (!paginationContainer) return;
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = `<button type="button" class="page-btn" ${currentPage === 1 ? 'disabled' : ''} id="prev-page">« Prev</button>`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<button type="button" class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        html += `<button type="button" class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} id="next-page">Next »</button>`;

        paginationContainer.innerHTML = html;

        // Pagination Click Handlers
        paginationContainer.querySelector('#prev-page')?.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderPage();
            }
        });

        paginationContainer.querySelector('#next-page')?.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderPage();
            }
        });

        paginationContainer.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', () => {
                currentPage = parseInt(btn.getAttribute('data-page'), 10);
                renderPage();
            });
        });
    }

    // Event Listeners for Filter / Search / Sort
    if (searchInput) searchInput.addEventListener('input', applyFiltersAndRender);
    if (categoryFilter) categoryFilter.addEventListener('change', applyFiltersAndRender);
    if (sortSelect) sortSelect.addEventListener('change', applyFiltersAndRender);

    // Initial Fetch
    fetchEvents();
});
