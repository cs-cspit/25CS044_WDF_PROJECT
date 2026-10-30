# Practical 13: PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload

## Overview
Practical 13 implements a high-performance, asynchronous RESTful JSON API using **PHP** and **MySQLi**, consumed by an interactive frontend dashboard built with vanilla **HTML5**, **CSS3**, and the **Fetch API**. All data queries and mutations (List, Search, Filter, Add, Edit, and Delete) execute seamlessly in the background without requiring a full browser page refresh.

---

## Key Features

1. **RESTful PHP JSON API (`api/events.php`)**:
   - Explicit `Content-Type: application/json; charset=UTF-8` response headers.
   - Standardized JSON envelope: `{ success: bool, message: string, data: any, errors: array }`.
   - Strict HTTP Status Codes:
     - `200 OK` (Fetch success / update / delete)
     - `201 Created` (New event inserted)
     - `400 Bad Request` (Server-side validation failure)
     - `404 Not Found` (Event record not found)
     - `405 Method Not Allowed` (Unsupported HTTP verb)
     - `500 Internal Server Error` (Database query/connection failure)
   - Prepared SQL statements for complete SQL injection protection.

2. **Frontend Asynchronous Architecture (`assets/js/app.js` & `index.html`)**:
   - Zero full-page reloads across all CRUD operations.
   - **Intermediate Extension:** Real-time search with a **350ms Debounce** to limit network traffic.
   - **Category Filter Dropdown:** Live asynchronous filtering by event category.
   - **Loading Indicators:** Global backdrop spinner and skeleton loading table states.
   - **Notification System:** Animated toast notification stack for instant success/error feedback.
   - **In-Place DOM Mutation:** Smooth addition, green highlight on update, and slide-out removal animation on delete.

3. **Visual Test & Network Simulation Inspector (`pages/practical13_preview.html`)**:
   - Interactive 8-tab test report and DevTools Network tab simulator showing real HTTP request/response payloads, headers, status codes, and timing.

---

## Directory Structure
```
practical-13/
├── api/
│   └── events.php        # Centralized RESTful JSON API endpoint (GET, POST, PUT, DELETE)
├── assets/
│   └── js/
│       └── app.js        # JavaScript Fetch client, debounced search, modal handlers, toasts
├── db.php                # Database connection helper with JSON error handling
├── index.html            # Main SPA dashboard interface
├── schema.sql            # MySQL schema for `events` table with sample seed data
├── tests/
│   └── TEST_REPORT.md    # Detailed test execution matrix & key question analysis
└── README.md             # Setup guide and submission documentation
```

---

## Setup & Running with XAMPP

1. **Move Project Folder:**
   Ensure the project is inside your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\25CS044_WDF_PROJECT-main\practical-13\
   ```

2. **Start Services:**
   - Launch the **XAMPP Control Panel**.
   - Start **Apache** and **MySQL**.

3. **Import Database Schema:**
   - Open phpMyAdmin at `http://localhost/phpmyadmin/`.
   - Select or create the database `studenthub`.
   - Click the **Import** tab and select `practical-13/schema.sql`, or execute its SQL queries directly in the SQL tab.

4. **Launch the Dashboard:**
   - Access the application in your browser:
     ```
     http://localhost/25CS044_WDF_PROJECT-main/practical-13/index.html
     ```

5. **View the Interactive Test Report & Network Simulator:**
   - Open in your browser:
     ```
     http://localhost:5500/pages/practical13_preview.html
     ```
     *(or through your local server setup)*

---

## API Endpoints Reference

| Method | Endpoint | Description | Request Body / Parameters | Success Status |
|:---|:---|:---|:---|:---:|
| `GET` | `/api/events.php` | List all events / filter | `?search=term&category=Tech` | `200 OK` |
| `GET` | `/api/events.php?id={id}` | Get event details | Query param `id` | `200 OK` |
| `POST` | `/api/events.php` | Create new event | JSON: `{ title, category, event_date, venue, max_participants, description }` | `201 Created` |
| `PUT` | `/api/events.php` | Update existing event | JSON: `{ event_id, title, category, event_date, venue, max_participants, description }` | `200 OK` |
| `DELETE` | `/api/events.php?id={id}` | Delete event | Query param `id` or JSON `{ id }` | `200 OK` |

---

## Submission Checklist
- [x] PHP JSON API returning valid JSON response with `application/json` header.
- [x] GET, POST, PUT, DELETE handling implemented.
- [x] Input validation before database operations.
- [x] Visual loading spinners, success toasts, and error feedback.
- [x] AJAX search with 350ms input debounce.
- [x] HTTP status codes (`200`, `201`, `400`, `404`, `405`, `500`).
- [x] Detailed `TEST_REPORT.md` answering all laboratory analysis questions.
- [x] Network Tab preview simulation (`pages/practical13_preview.html`).
