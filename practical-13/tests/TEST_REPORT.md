# Practical 13: Test & Evaluation Report
## PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload

**Course / Module:** Web Development Frameworks (WDF)  
**Practical No:** 13  
**Enrollment:** 25CS044  
**Date:** 2026-10-30  
**CO Mapping:** CO3, CO4, CO5  

---

## 1. Objective & Problem Definition
To implement a robust, RESTful PHP JSON API endpoint (`api/events.php`) and build a single-page interactive client dashboard (`index.html` + `assets/js/app.js`) utilizing the JavaScript **Fetch API**. All CRUD operations—**Create (POST)**, **Read/Search/Filter (GET)**, **Update (PUT)**, and **Delete (DELETE)**—are performed asynchronously without triggering any full browser page reloads.

---

## 2. Answers to Key Questions & Analysis

### Q1: Do PHP endpoints return valid JSON responses?
- **Analysis & Implementation:**
  - In `api/events.php`, the header `Content-Type: application/json; charset=UTF-8` is explicitly transmitted before any output.
  - A centralized helper function `send_json_response($status_code, $success, $message, $data = null, $errors = null)` enforces standard schema formatting:
    ```json
    {
      "success": true,
      "message": "Events fetched successfully.",
      "data": [...],
      "errors": []
    }
    ```
  - `json_encode()` is called with error detection, ensuring valid JSON parsing on the frontend via `response.json()`.
  - HTTP status codes are strictly returned via `http_response_code($status_code)` (e.g. `200 OK`, `201 Created`, `400 Bad Request`, `404 Not Found`, `405 Method Not Allowed`, `500 Internal Server Error`).

### Q2: Are GET and POST requests handled properly?
- **Analysis & Implementation:**
  - **GET Requests:** Handled to list all active events, filter by category (`?category=Tech`), search with debounce (`?search=hackathon`), or retrieve a single record by ID (`?id=3`). Prepared statements prevent SQL injection.
  - **POST Requests:** Handled by reading JSON input payloads via `file_get_contents('php://input')` (or `$_POST`). Performs server-side validation and executes `INSERT INTO events (...)` prepared statements. On success, responds with HTTP status `201 Created` and the inserted event object including its generated `event_id`.
  - **PUT & DELETE Requests:** Handled seamlessly by parsing HTTP methods and extracting IDs and payload data dynamically.

### Q3: Are loading, success, and error states shown on the frontend?
- **Analysis & Implementation:**
  - **Loading State:** An animated spinner overlay `#loadingSpinner` and table skeleton placeholder `#tableLoading` are toggled (`loadingSpinner.classList.remove('hidden')`) before every `fetch()` dispatch and hidden in the `.finally()` block.
  - **Success State:** Centralized toast notifications (`showToast(message, 'success')`) pop up dynamically with green accents and checkmark icons. Created/Updated rows flash a subtle green highlight in the table; deleted rows animate with a fade-and-slide transition before removal.
  - **Error State:** Network failures, server errors (500), and validation failures (400) trigger red alert toasts (`showToast(message, 'error')`). Inline field validation errors appear in red beneath the respective modal inputs.

### Q4: Is input validation performed before database operations?
- **Analysis & Implementation:**
  - Both client-side and server-side validation are enforced.
  - Server-side validation in `api/events.php`:
    - `title`: Required, 3–150 characters, sanitized with `htmlspecialchars()`.
    - `category`: Must match predefined whitelist (`Technical`, `Cultural`, `Sports`, `Workshop`, `Seminar`).
    - `event_date`: Required, verified via `strtotime()` to ensure valid YYYY-MM-DD date.
    - `max_participants`: Must be an integer greater than 0.
    - `venue`: Required, 2–100 characters.
  - If any check fails, the API immediately halts execution and returns HTTP `400 Bad Request` with an associative array of descriptive field errors without touching the database.

---

## 3. Advanced & Intermediate Extensions

| Extension | Requirement | Implementation Detail | Status |
|:---|:---|:---|:---:|
| **Intermediate** | **AJAX Search with Debounce** | Implemented a 350ms custom JavaScript debounce timer on the `#searchInput` event listener. Halts premature network calls until typing finishes, preventing server throttling. | **Completed** |
| **Advanced** | **Status Codes & Error Handling** | Implemented strict HTTP status codes (`200`, `201`, `400`, `404`, `405`, `500`) and a centralized error handling mechanism in both PHP (`send_json_response`) and JavaScript (`handleApiError`). | **Completed** |

---

## 4. Test Cases & Execution Matrix

| Test ID | Method | Endpoint / Action | Input Payload / Params | Expected HTTP Code | Expected UI Behavior | Result |
|:---:|:---:|:---|:---|:---:|:---|:---:|
| **TC-01** | `GET` | `/api/events.php` | None | `200 OK` | Loads 6 default seeded events into dynamic table rows without reload. | **PASS** |
| **TC-02** | `GET` | `/api/events.php?search=hack` | `search=hack` (Debounced 350ms) | `200 OK` | Instantly filters to "CodeStorm Hackathon 2026" without clearing the table structure. | **PASS** |
| **TC-03** | `GET` | `/api/events.php?category=Sports` | `category=Sports` | `200 OK` | Filters list to show only Sports category events. | **PASS** |
| **TC-04** | `POST` | `/api/events.php` | Valid Event JSON (`title`, `category`, `date`, `venue`, `max`) | `201 Created` | Modal closes, success toast displayed, new row appended dynamically. | **PASS** |
| **TC-05** | `POST` | `/api/events.php` | Missing `title` and negative `max_participants` | `400 Bad Request` | Modal remains open, validation error toast displayed, invalid fields highlighted. | **PASS** |
| **TC-06** | `PUT` | `/api/events.php` | `id=1`, Updated `venue: "Lab 302"` | `200 OK` | Row updates in-place with a green highlight; no page refresh. | **PASS** |
| **TC-07** | `DELETE`| `/api/events.php?id=5` | `id=5` | `200 OK` | Delete confirmation modal confirms, row animates out and event count updates. | **PASS** |
| **TC-08** | `PATCH`| `/api/events.php` | Unsupported method | `405 Method Not Allowed` | Returns JSON error message specifying allowed methods (GET, POST, PUT, DELETE). | **PASS** |

---

## 5. Network Tab Inspection Summary

Inspected via Chrome / Edge Developer Tools Network Tab:
- **Request URL:** `http://localhost/practical-13/api/events.php`
- **Request Method:** `GET`, `POST`, `PUT`, `DELETE`
- **Status Code:** `200 OK` / `201 Created`
- **Type:** `fetch` / `xhr`
- **Headers:**
  - `Accept: application/json`
  - `Content-Type: application/json`
- **Response Payload Preview:** Valid JSON containing `"success": true`.
- **Page Reload Count:** `0` across all CRUD cycles.

---

## 6. Conclusion
Practical 13 successfully satisfies all laboratory learning outcomes (CO3, CO4, CO5). The StudentHub Event Management portal demonstrates complete decoupling of backend REST API logic and frontend presentation via asynchronous Fetch calls, delivering a fast, responsive, and seamless user experience.
