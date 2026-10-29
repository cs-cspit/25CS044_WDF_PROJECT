/**
 * students-manager.js
 * Modular ES6 Fetch, Filter, Search, Sort & Pagination for Admin Panel Student Records
 * Includes automatic offline/file:// CORS fallback for local double-click execution.
 */

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('students-tbody');
    const searchInput = document.getElementById('student-search');
    const courseFilter = document.getElementById('student-course');
    const sortSelect = document.getElementById('student-sort');
    const paginationContainer = document.getElementById('student-pagination');
    const statusMsg = document.getElementById('student-status');

    if (!tableBody) return;

    const fallbackStudents = [
        { "studentId": "25CS001", "name": "Aarav Patel", "course": "Computer Engineering", "year": "2nd Year", "email": "aarav.ce@charusat.edu.in", "gpa": 8.92, "status": "Active" },
        { "studentId": "25CS014", "name": "Bhavya Shah", "course": "Information Technology", "year": "3rd Year", "email": "bhavya.it@charusat.edu.in", "gpa": 9.15, "status": "Active" },
        { "studentId": "25CS023", "name": "Chirag Trivedi", "course": "Computer Engineering", "year": "1st Year", "email": "chirag.ce@charusat.edu.in", "gpa": 7.84, "status": "Pending" },
        { "studentId": "25CS032", "name": "Diya Joshi", "course": "Electronics & Communication", "year": "4th Year", "email": "diya.ec@charusat.edu.in", "gpa": 8.76, "status": "Active" },
        { "studentId": "25CS044", "name": "Shubham Mendpara", "course": "Computer Engineering", "year": "2nd Year", "email": "shubham.ce@charusat.edu.in", "gpa": 9.40, "status": "Active" },
        { "studentId": "25CS051", "name": "Esha Parmar", "course": "Information Technology", "year": "2nd Year", "email": "esha.it@charusat.edu.in", "gpa": 8.20, "status": "Active" },
        { "studentId": "25CS067", "name": "Harshil Dave", "course": "Artificial Intelligence & ML", "year": "1st Year", "email": "harshil.ai@charusat.edu.in", "gpa": 8.65, "status": "Pending" },
        { "studentId": "25CS078", "name": "Ishita Mehta", "course": "Computer Engineering", "year": "3rd Year", "email": "ishita.ce@charusat.edu.in", "gpa": 9.02, "status": "Active" },
        { "studentId": "25CS089", "name": "Karan Vora", "course": "Electronics & Communication", "year": "2nd Year", "email": "karan.ec@charusat.edu.in", "gpa": 7.95, "status": "Inactive" },
        { "studentId": "25CS095", "name": "Meera Bhatt", "course": "Information Technology", "year": "4th Year", "email": "meera.it@charusat.edu.in", "gpa": 9.31, "status": "Active" },
        { "studentId": "25CS102", "name": "Nikhil Solanki", "course": "Artificial Intelligence & ML", "year": "3rd Year", "email": "nikhil.ai@charusat.edu.in", "gpa": 8.44, "status": "Active" },
        { "studentId": "25CS113", "name": "Pooja Desai", "course": "Computer Engineering", "year": "1st Year", "email": "pooja.ce@charusat.edu.in", "gpa": 8.10, "status": "Pending" },
        { "studentId": "25CS124", "name": "Rohan Raval", "course": "Information Technology", "year": "3rd Year", "email": "rohan.it@charusat.edu.in", "gpa": 7.75, "status": "Active" },
        { "studentId": "25CS135", "name": "Sneha Prajapati", "course": "Electronics & Communication", "year": "4th Year", "email": "sneha.ec@charusat.edu.in", "gpa": 8.88, "status": "Active" },
        { "studentId": "25CS146", "name": "Tirth Patel", "course": "Computer Engineering", "year": "2nd Year", "email": "tirth.ce@charusat.edu.in", "gpa": 9.18, "status": "Active" },
        { "studentId": "25CS158", "name": "Zalak Shah", "course": "Artificial Intelligence & ML", "year": "2nd Year", "email": "zalak.ai@charusat.edu.in", "gpa": 8.52, "status": "Active" }
    ];

    let allStudents = [];
    let filteredStudents = [];
    let currentPage = 1;
    const itemsPerPage = 6;

    function showLoading() {
        if (statusMsg) {
            statusMsg.style.display = 'block';
            statusMsg.innerHTML = '⏳ Loading student records...';
            statusMsg.className = 'status-msg loading';
        }
    }

    function hideStatus() {
        if (statusMsg) statusMsg.style.display = 'none';
    }

    async function fetchStudents() {
        showLoading();
        try {
            const res = await fetch('../data/students.json');
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            allStudents = await res.json();
            hideStatus();
        } catch (e) {
            console.warn('Fetch failed (likely file:// protocol CORS). Loading local students dataset.', e);
            allStudents = fallbackStudents;
            hideStatus();
        }
        filteredStudents = [...allStudents];
        applyFiltersAndRender();
    }

    function applyFiltersAndRender() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const course = courseFilter ? courseFilter.value : 'all';
        const sortMode = sortSelect ? sortSelect.value : 'id-asc';

        filteredStudents = allStudents.filter(stu => {
            const matchesQuery = stu.name.toLowerCase().includes(query) ||
                                 stu.studentId.toLowerCase().includes(query) ||
                                 stu.email.toLowerCase().includes(query);
            const matchesCourse = (course === 'all') || (stu.course === course);
            return matchesQuery && matchesCourse;
        });

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
