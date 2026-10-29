/**
 * events-manager.js
 * Modular ES6 Fetch, Filter, Search, Sort & Pagination for Campus Events
 * Includes automatic offline/file:// CORS fallback for local double-click execution.
 */

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('events-tbody');
    const searchInput = document.getElementById('event-search');
    const categoryFilter = document.getElementById('event-category');
    const sortSelect = document.getElementById('event-sort');
    const paginationContainer = document.getElementById('event-pagination');
    const statusMsg = document.getElementById('event-status');

    if (!tableBody) return;

    // Embedded fallback dataset in case browser blocks file:/// fetch due to CORS
    const fallbackEvents = [
        { "id": "EVT-101", "name": "Hackathon 2026 - CodeVerse", "category": "Technical", "date": "2026-07-20", "venue": "Main Auditorium, Block 3", "organizer": "Computer Engineering Dept", "seats": 120, "status": "Open" },
        { "id": "EVT-102", "name": "AI & Deep Learning Symposium", "category": "Technical", "date": "2026-08-05", "venue": "Seminar Hall A", "organizer": "AIML Research Group", "seats": 80, "status": "Open" },
        { "id": "EVT-103", "name": "Annual Sports Meet - Spardha", "category": "Sports", "date": "2026-08-18", "venue": "University Sports Ground", "organizer": "Sports Council", "seats": 300, "status": "Upcoming" },
        { "id": "EVT-104", "name": "National Cyber Security Conclave", "category": "Technical", "date": "2026-08-25", "venue": "Auditorium 2", "organizer": "IT Department", "seats": 150, "status": "Open" },
        { "id": "EVT-105", "name": "Cultural Fest - Vrundavan 2026", "category": "Cultural", "date": "2026-09-10", "venue": "Open Air Amphitheatre", "organizer": "Student Council", "seats": 500, "status": "Upcoming" },
        { "id": "EVT-106", "name": "Robotics Maze Solver Challenge", "category": "Technical", "date": "2026-09-18", "venue": "Robotics Lab, Tech Park", "organizer": "Electronics Club", "seats": 60, "status": "Open" },
        { "id": "EVT-107", "name": "Inter-College Cricket Tournament", "category": "Sports", "date": "2026-09-24", "venue": "Cricket Pavilion", "organizer": "Sports Council", "seats": 160, "status": "Open" },
        { "id": "EVT-108", "name": "Web3 & Blockchain Workshop", "category": "Workshop", "date": "2026-10-02", "venue": "Lab 402, IT Block", "organizer": "Developer Student Club", "seats": 50, "status": "Closed" },
        { "id": "EVT-109", "name": "Industry Career & Placement Expo", "category": "Career", "date": "2026-10-12", "venue": "Central Convention Center", "organizer": "Training & Placement Cell", "seats": 400, "status": "Upcoming" },
        { "id": "EVT-110", "name": "Battle of the Bands - Music Night", "category": "Cultural", "date": "2026-10-20", "venue": "University Amphitheatre", "organizer": "Music Society", "seats": 350, "status": "Open" },
        { "id": "EVT-111", "name": "Cloud Computing with AWS Bootcamp", "category": "Workshop", "date": "2026-10-28", "venue": "Virtual / Lab 201", "organizer": "Cloud Innovation Club", "seats": 75, "status": "Open" },
        { "id": "EVT-112", "name": "Design Thinking & UI/UX Sprint", "category": "Workshop", "date": "2026-11-04", "venue": "Design Studio 1", "organizer": "Creative Arts Club", "seats": 45, "status": "Upcoming" },
        { "id": "EVT-113", "name": "Badminton Singles & Doubles Trophy", "category": "Sports", "date": "2026-11-12", "venue": "Indoor Sports Complex", "organizer": "Sports Council", "seats": 64, "status": "Open" },
        { "id": "EVT-114", "name": "Startup Pitch & Angel Pitchfest", "category": "Career", "date": "2026-11-20", "venue": "Incubation Center Boardroom", "organizer": "E-Cell CHARUSAT", "seats": 70, "status": "Open" },
        { "id": "EVT-115", "name": "Alumni Homecoming Dinner & Gala", "category": "Cultural", "date": "2026-12-05", "venue": "Grand Lawn, University Campus", "organizer": "Alumni Relations Office", "seats": 250, "status": "Upcoming" },
        { "id": "EVT-116", "name": "Photography & Film-Making Exhibit", "category": "Cultural", "date": "2026-12-14", "venue": "Art Gallery, Block 1", "organizer": "Shutterbug Society", "seats": 100, "status": "Open" }
    ];

    let allEvents = [];
    let filteredEvents = [];
    let currentPage = 1;
    const itemsPerPage = 5;

    function showLoading() {
        if (statusMsg) {
            statusMsg.style.display = 'block';
            statusMsg.innerHTML = '<span class="spinner">⏳</span> Loading events data...';
            statusMsg.className = 'status-msg loading';
        }
    }

    function hideStatus() {
        if (statusMsg) statusMsg.style.display = 'none';
    }

    // Fetch JSON data via Fetch API with transparent fallback
    async function fetchEvents() {
        showLoading();
        try {
            const response = await fetch('../data/events.json');
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            allEvents = await response.json();
            hideStatus();
        } catch (error) {
            // Browser blocked local fetch (CORS file:// protocol), load embedded fallback
            console.warn('Fetch failed (likely file:// protocol CORS). Loading local events dataset.', error);
            allEvents = fallbackEvents;
            hideStatus();
        }
        filteredEvents = [...allEvents];
        applyFiltersAndRender();
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
            if (sortMode === 'name-desc') return b.name.localeCompare(a.name);
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
                <td>
                    ${item.status.toLowerCase() === 'closed'
                        ? `<button type="button" class="btn-register disabled" disabled>Closed</button>`
                        : `<a href="../php-form-handling/register.php?event_id=${encodeURIComponent(item.id)}&event_name=${encodeURIComponent(item.name)}" class="btn-register">Register</a>`
                    }
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
        html += `<button type="button" class="page-btn ${currentPage === totalPages ? 'disabled' : ''} id="next-page">Next »</button>`;

        paginationContainer.innerHTML = html;

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

    if (searchInput) searchInput.addEventListener('input', applyFiltersAndRender);
    if (categoryFilter) categoryFilter.addEventListener('change', applyFiltersAndRender);
    if (sortSelect) sortSelect.addEventListener('change', applyFiltersAndRender);

    fetchEvents();
});
