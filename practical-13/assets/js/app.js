/**
 * Practical 13: Asynchronous AJAX/Fetch CRUD & Debounced Live Search
 * File: practical-13/assets/js/app.js
 *
 * Implements:
 * - Fetch API requests for List (GET), Add (POST), Update (PUT), and Delete (DELETE).
 * - Real-time loading spinners and state transitions without page reload.
 * - Dynamic modal form manipulation.
 * - Intermediate Extension: Debounced live search (350ms delay).
 * - Advanced Extension: HTTP status code inspection and centralized toast error messaging.
 */

const API_URL = 'api/events.php';

// State management
let debounceTimer = null;
let currentEvents = [];

document.addEventListener('DOMContentLoaded', () => {
    // Initial fetch of event records
    loadEvents();

    // Attach search input with Debounce (Intermediate Requirement)
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                loadEvents(e.target.value, document.getElementById('category-filter').value);
            }, 350); // 350ms debounce
        });
    }

    // Attach category filter change
    const categoryFilter = document.getElementById('category-filter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', (e) => {
            loadEvents(searchInput.value, e.target.value);
        });
    }

    // Attach Add Event Form Submit
    const addForm = document.getElementById('add-event-form');
    if (addForm) {
        addForm.addEventListener('submit', handleAddEvent);
    }

    // Attach Edit Event Form Submit
    const editForm = document.getElementById('edit-event-form');
    if (editForm) {
        editForm.addEventListener('submit', handleEditEvent);
    }
});

/**
 * 1. GET: Fetch events asynchronously from JSON API
 */
async function loadEvents(search = '', category = 'all') {
    const tbody = document.getElementById('events-tbody');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');

    // Show loading state, hide table rows
    if (loadingState) loadingState.style.display = 'block';
    if (tbody) tbody.style.display = 'none';
    if (emptyState) emptyState.style.display = 'none';

    try {
        const url = new URL(API_URL, window.location.href);
        if (search) url.searchParams.set('search', search);
        if (category && category !== 'all') url.searchParams.set('category', category);

        const response = await fetch(url.toString(), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        // Check HTTP Status Code
        if (!response.ok || !result.success) {
            throw new Error(result.message || `HTTP ${response.status} Error`);
        }

        currentEvents = result.data.events || [];
        renderEventsTable(currentEvents);

    } catch (error) {
        console.error('Fetch error:', error);
        showToast('error', `Failed to load events: ${error.message}`);
    } finally {
        if (loadingState) loadingState.style.display = 'none';
        if (tbody) tbody.style.display = 'table-row-group';
    }
}

/**
 * Render rows dynamically in the DOM
 */
function renderEventsTable(events) {
    const tbody = document.getElementById('events-tbody');
    const emptyState = document.getElementById('empty-state');
    const countBadge = document.getElementById('event-count');

    if (countBadge) countBadge.textContent = `${events.length} Events`;

    if (!events || events.length === 0) {
        tbody.innerHTML = '';
        if (emptyState) emptyState.style.display = 'block';
        return;
    }

    if (emptyState) emptyState.style.display = 'none';

    tbody.innerHTML = events.map(ev => `
        <tr id="row-event-${ev.event_id}" class="fade-in-row">
            <td>
                <img src="../${ev.poster_path}" alt="Poster" class="table-poster-thumb" onerror="this.src='../images/default_poster.png'">
            </td>
            <td>
                <strong>${escapeHtml(ev.event_name)}</strong>
                <div style="font-size:0.8rem; color:#64748b;">ID: #${ev.event_id}</div>
            </td>
            <td><span class="badge badge-purple">${escapeHtml(ev.category)}</span></td>
            <td>📅 ${escapeHtml(ev.event_date)}</td>
            <td>📍 ${escapeHtml(ev.venue)}</td>
            <td>${escapeHtml(ev.organizer_club)}</td>
            <td><strong>${ev.max_seats}</strong> seats</td>
            <td>
                <div style="display:flex; gap:6px;">
                    <button type="button" class="btn-action-sm btn-edit" onclick="openEditModal(${ev.event_id})">Edit</button>
                    <button type="button" class="btn-action-sm btn-del" onclick="deleteEvent(${ev.event_id}, '${escapeHtml(ev.event_name)}')">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * 2. POST: Create new event asynchronously without page reload
 */
async function handleAddEvent(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');

    const payload = {
        event_name: form.event_name.value.trim(),
        category: form.category.value,
        event_date: form.event_date.value,
        venue: form.venue.value.trim(),
        organizer_club: form.organizer_club.value.trim(),
        max_seats: parseInt(form.max_seats.value, 10),
        poster_path: form.poster_choice.value,
        description: form.description.value.trim()
    };

    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Creating...';

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (response.status === 201 && result.success) {
            showToast('success', result.message || 'Event added successfully!');
            closeModal('addModal');
            form.reset();
            // Refresh table asynchronously without reload
            await loadEvents();
        } else {
            const errDetails = result.errors ? result.errors.join(' ') : result.message;
            showToast('error', `Error (${result.status_code}): ${errDetails}`);
        }
    } catch (err) {
        showToast('error', `Network error: ${err.message}`);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Create Event via API';
    }
}

/**
 * 3. PUT: Update existing event asynchronously
 */
async function handleEditEvent(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');

    const payload = {
        event_id: parseInt(form.edit_event_id.value, 10),
        event_name: form.edit_event_name.value.trim(),
        category: form.edit_category.value,
        event_date: form.edit_event_date.value,
        venue: form.edit_venue.value.trim(),
        organizer_club: form.edit_organizer_club.value.trim(),
        max_seats: parseInt(form.edit_max_seats.value, 10),
        description: form.edit_description.value.trim()
    };

    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Updating...';

    try {
        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showToast('success', result.message || 'Event updated successfully!');
            closeModal('editModal');
            await loadEvents();
        } else {
            showToast('error', result.message || 'Update failed.');
        }
    } catch (err) {
        showToast('error', `Network error: ${err.message}`);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Update via API';
    }
}

/**
 * 4. DELETE: Remove event asynchronously
 */
async function deleteEvent(eventId, eventName) {
    if (!confirm(`Are you sure you want to delete event "${eventName}"?\nThis operation will be performed via AJAX without reloading.`)) {
        return;
    }

    try {
        const response = await fetch(API_URL, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ event_id: eventId })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showToast('success', `Event #${eventId} deleted successfully!`);
            // Smoothly remove row from DOM
            const row = document.getElementById(`row-event-${eventId}`);
            if (row) {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => row.remove(), 300);
            }
        } else {
            showToast('error', result.message || 'Deletion failed.');
        }
    } catch (err) {
        showToast('error', `Network error: ${err.message}`);
    }
}

/**
 * Helpers: Modal Controls
 */
function openAddModal() {
    const modal = document.getElementById('addModal');
    if (modal) modal.classList.add('active');
}

function openEditModal(eventId) {
    const ev = currentEvents.find(e => e.event_id == eventId);
    if (!ev) return;

    const form = document.getElementById('edit-event-form');
    form.edit_event_id.value = ev.event_id;
    form.edit_event_name.value = ev.event_name;
    form.edit_category.value = ev.category;
    form.edit_event_date.value = ev.event_date;
    form.edit_venue.value = ev.venue;
    form.edit_organizer_club.value = ev.organizer_club;
    form.edit_max_seats.value = ev.max_seats;
    form.edit_description.value = ev.description || '';

    const modal = document.getElementById('editModal');
    if (modal) modal.classList.add('active');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

/**
 * Centralized Toast Notification System
 */
function showToast(type, message) {
    const toast = document.getElementById('live-toast');
    if (!toast) return;

    toast.className = `toast toast-${type} active`;
    toast.innerHTML = `
        <span style="font-size:1.2rem;">${type === 'success' ? '✅' : '⚠️'}</span>
        <div><strong>${type === 'success' ? 'Success' : 'API Notice'}:</strong> ${escapeHtml(message)}</div>
    `;

    setTimeout(() => {
        toast.classList.remove('active');
    }, 4000);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
