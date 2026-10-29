/**
 * students-manager.js
 * Modular ES6 Fetch, Filter, Search, Sort & Pagination for Admin Panel Student Records
 */

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('students-tbody');
    const searchInput = document.getElementById('student-search');
    const courseFilter = document.getElementById('student-course');
    const sortSelect = document.getElementById('student-sort');
    const paginationContainer = document.getElementById('student-pagination');
    const statusMsg = document.getElementById('student-status');

    if (!tableBody) return;

    let allStudents = [];
    let filteredStudents = [];
    let currentPage = 1;
    const itemsPerPage = 6;

    function showLoading() {
        if (statusMsg) {
            statusMsg.style.display = 'block';
            statusMsg.innerHTML = '⏳ Loading student records from students.json...';
            statusMsg.className = 'status-msg loading';
        }
    }

    function showError(err) {
        if (statusMsg) {
            statusMsg.style.display = 'block';
            statusMsg.innerHTML = `⚠️ Failed to load students: ${err}`;
            statusMsg.className = 'status-msg error';
        }
    }

    function hideStatus() {
        if (statusMsg) statusMsg.style.display = 'none';
    }

    // Fetch JSON data via Fetch API
    async function fetchStudents() {
        showLoading();
        try {
            const res = await fetch('../data/students.json');
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            allStudents = await res.json();
            filteredStudents = [...allStudents];
            hideStatus();
            applyFiltersAndRender();
        } catch (e) {
            showError(e.message);
        }
    }

    // Search, Filter, Sort Pipeline
    function applyFiltersAndRender() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const course = courseFilter ? courseFilter.value : 'all';
        const sortMode = sortSelect ? sortSelect.value : 'id-asc';

        // 1. Filter
        filteredStudents = allStudents.filter(stu => {
            const matchesQuery = stu.name.toLowerCase().includes(query) ||
                                 stu.studentId.toLowerCase().includes(query) ||
                                 stu.email.toLowerCase().includes(query);
            const matchesCourse = (course === 'all') || (stu.course === course);
            return matchesQuery && matchesCourse;
        });

        // 2. Sort
        filteredStudents.sort((a, b) => {
            if (sortMode === 'id-asc') return a.studentId.localeCompare(b.studentId);
            if (sortMode === 'name-asc') return a.name.localeCompare(b.name);
            if (sortMode === 'name-desc') return b.name.localeCompare(a.name);
            if (sortMode === 'gpa-desc') return b.gpa - a.gpa;
            if (sortMode === 'gpa-asc') return a.gpa - b.gpa;
            return 0;
        });

        currentPage = 1;
        renderPage();
    }

    // Paginated Table Rendering
    function renderPage() {
        const totalItems = filteredStudents.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const pageItems = filteredStudents.slice(startIndex, startIndex + itemsPerPage);

        if (pageItems.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:24px; color:#64748b;">No student records found.</td></tr>`;
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        tableBody.innerHTML = pageItems.map(s => `
            <tr>
                <td><strong>${s.studentId}</strong></td>
                <td>${s.name}</td>
                <td>${s.course}</td>
                <td>${s.year}</td>
                <td><span style="font-weight:600; color:#0a66ff;">${s.gpa.toFixed(2)}</span></td>
                <td>
                    <span class="status-pill status-${s.status.toLowerCase()}">${s.status}</span>
                </td>
                <td>
                    <button type="button" class="btn-action ${s.status === 'Active' ? 'btn-view' : 'btn-approve'}" onclick="alert('Action processed for ${s.name} (${s.studentId})')">
                        ${s.status === 'Active' ? 'View' : 'Approve'}
                    </button>
                </td>
            </tr>
        `).join('');

        renderPagination(totalPages);
    }

    // Render Pagination Buttons
    function renderPagination(totalPages) {
        if (!paginationContainer) return;
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = `<button type="button" class="page-btn" ${currentPage === 1 ? 'disabled' : ''} id="stu-prev">« Prev</button>`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<button type="button" class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        html += `<button type="button" class="page-btn ${currentPage === totalPages ? 'disabled' : ''} id="stu-next">Next »</button>`;

        paginationContainer.innerHTML = html;

        paginationContainer.querySelector('#stu-prev')?.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderPage();
            }
        });

        paginationContainer.querySelector('#stu-next')?.addEventListener('click', () => {
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
    if (courseFilter) courseFilter.addEventListener('change', applyFiltersAndRender);
    if (sortSelect) sortSelect.addEventListener('change', applyFiltersAndRender);

    fetchStudents();
});
