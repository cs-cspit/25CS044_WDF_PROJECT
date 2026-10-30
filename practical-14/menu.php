<?php
/**
 * Practical 14: Dynamic Role-Based Menu Component
 * File: practical-14/menu.php
 *
 * Requirements:
 * - Dynamically renders navigation links based on user role:
 *     * admin     --> Full access (Admin Dashboard, User Directory, Student Roster, Events, Analytics, Audit Trail)
 *     * organizer --> Event management & Registrations
 *     * student   --> View campus events, My Registrations, Profile
 */

function render_dynamic_menu($current_role, $active_page = 'dashboard') {
    $menus = [
        'admin' => [
            ['title' => 'Admin Dashboard', 'url' => 'admin_dashboard.php', 'icon' => '📊', 'id' => 'dashboard'],
            ['title' => 'User Management', 'url' => '#users-section', 'icon' => '👥', 'id' => 'users'],
            ['title' => 'Student Roster', 'url' => '../practical-11/index.php', 'icon' => '🎓', 'id' => 'students'],
            ['title' => 'Campus Events', 'url' => '../practical-12/admin_dashboard.php', 'icon' => '📅', 'id' => 'events'],
            ['title' => 'Event Registrations', 'url' => '#registrations-section', 'icon' => '📝', 'id' => 'registrations'],
            ['title' => 'Registration Analytics', 'url' => '#analytics-section', 'icon' => '📈', 'id' => 'analytics'],
            ['title' => 'Security Audit Trail', 'url' => '#audit-section', 'icon' => '🛡️', 'id' => 'audit']
        ],
        'organizer' => [
            ['title' => 'Organizer Hub', 'url' => 'admin_dashboard.php', 'icon' => '🎪', 'id' => 'dashboard'],
            ['title' => 'Campus Events', 'url' => '../practical-12/admin_dashboard.php', 'icon' => '📅', 'id' => 'events'],
            ['title' => 'Attendee Registrations', 'url' => '#registrations-section', 'icon' => '📝', 'id' => 'registrations'],
            ['title' => 'Event Analytics', 'url' => '#analytics-section', 'icon' => '📈', 'id' => 'analytics']
        ],
        'student' => [
            ['title' => 'Student Portal', 'url' => '../practical-10/student_dashboard.php', 'icon' => '🎓', 'id' => 'dashboard'],
            ['title' => 'Available Events', 'url' => '../practical-13/index.html', 'icon' => '⚡', 'id' => 'events'],
            ['title' => 'My Registrations', 'url' => '#registrations-section', 'icon' => '📋', 'id' => 'registrations'],
            ['title' => 'Student Profile', 'url' => '../pages/profile.html', 'icon' => '👤', 'id' => 'profile']
        ]
    ];

    $role = strtolower($current_role ?? 'student');
    $items = $menus[$role] ?? $menus['student'];

    $html = '<nav class="nav flex-column">';
    foreach ($items as $item) {
        $active_class = ($item['id'] === $active_page) ? ' active bg-primary text-white shadow-sm' : ' text-secondary';
        $html .= sprintf(
            '<a class="nav-link d-flex align-items-center py-2 px-3 rounded mb-1%s" href="%s">
                <span class="me-2 fs-5">%s</span>
                <span class="fw-semibold">%s</span>
            </a>',
            $active_class,
            htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'),
            $item['icon'],
            htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8')
        );
    }
    $html .= '</nav>';

    return $html;
}
