# Practical 14: Admin Dashboard with RBAC, Dynamic Menu, and Audit Log

## Overview
Practical 14 implements a feature-rich, role-protected Administration Dashboard for the **StudentHub** portal. It aggregates dynamic statistics from MySQL, displays latest registrations using multi-table relational `JOIN`, enforces strict Role-Based Access Control (RBAC), provides role-segregated navigation menus, and logs administrative operations into the `audit_logs` table.

---

## Directory Structure
```
practical-14/
├── admin_dashboard.php   # Main Admin Dashboard with SQL aggregations & Bootstrap UI
├── audit.php             # Centralized audit logging helper (log_audit_action)
├── db.php                # Database connection helper
├── menu.php              # Dynamic role-based navigation menu component
├── schema.sql            # Database schema with users, students, events, registrations & audit seeds
├── toggle_user.php       # Intermediate Extension: AJAX active/inactive status toggle
├── tests/
│   └── TEST_REPORT.md    # Lab evaluation report answering key questions & test matrix
└── README.md             # Setup guide and submission overview
```

---

## Key Features

1. **Dynamic Database Aggregations (SQL `COUNT(*)`)**:
   - Total Users & Active Accounts
   - Enrolled Students count
   - Campus Events scheduled
   - Total Student Event Registrations

2. **Relational Data Display (SQL `JOIN`)**:
   - Displays latest registrations with student names, email addresses, event titles, and categories.

3. **Strict Role-Based Access Control (RBAC)**:
   - Validates `$_SESSION['role'] === 'admin'`.
   - Returns **HTTP 403 Forbidden** screen to unauthorized users and logs an `UNAUTHORIZED_ACCESS_BLOCKED` security event with the client IP address.

4. **Dynamic Role-Based Menu (`menu.php`)**:
   - Renders tailored navigation items for **Admin**, **Organizer**, and **Student** roles.

5. **Intermediate Extension: User Status Toggle (`toggle_user.php`)**:
   - AJAX-powered switch between `active` and `inactive` states without page reload.
   - Includes self-lockout prevention (admin cannot deactivate their own account).

6. **Advanced Extension: Monthly Registration Analytics**:
   - Visualized with **Chart.js**, grouping registrations by month (`DATE_FORMAT(registered_at, '%M %Y')`).

---

## Setup & Running with XAMPP

1. **Deploy to XAMPP:**
   Ensure project resides in your XAMPP root:
   ```
   C:\xampp\htdocs\25CS044_WDF_PROJECT-main\practical-14\
   ```

2. **Import Database:**
   - Open phpMyAdmin at `http://localhost/phpmyadmin/`.
   - Select database `studenthub`.
   - Import `practical-14/schema.sql`.

3. **Launch Dashboard:**
   - Open in your browser:
     ```
     http://localhost/25CS044_WDF_PROJECT-main/practical-14/admin_dashboard.php
     ```

4. **View Interactive Test Suite & Preview:**
   - Open:
     ```
     http://localhost:5500/pages/practical14_preview.html
     ```

---

## Submission Checklist
- [x] PHP Code (Dashboard queries, RBAC check, Audit logs).
- [x] Bootstrap UI (`admin_dashboard.php`).
- [x] Database schema (`schema.sql`).
- [x] Dynamic stats & latest registrations screenshots.
- [x] Dynamic menu by role & 403 restricted access screenshots.
- [x] Audit log proof table screenshots showing recorded admin actions.
- [x] Intermediate extension: Active/inactive user status toggle.
- [x] Advanced extension: Monthly analytics chart using Chart.js.
