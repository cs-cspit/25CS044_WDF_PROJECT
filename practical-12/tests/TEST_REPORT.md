# Practical 12: Event Management with File Upload (Event Poster) & Admin Controls Test Report

**Project:** StudentHub College Event Portal  
**Module:** Practical 12 - Event Creation, Secure Poster File Upload, CRUD Operations & Access Restriction  
**Database Driver:** PHP MySQLi (Object-Oriented with Prepared Statements)  
**Security Mechanisms:** Strict MIME type checking (`finfo_open`), file extension whitelisting, file size boundary validation ($\le 2$ MB), cryptographically randomized filenames (`bin2hex(random_bytes(6))`), and Role-Based Access Control (`check_admin_access()`).

---

## 🧪 Systematic Test Execution Matrix (All 10 Cases)

| ID | Test Scenario | Steps / Inputs | Expected Output | Actual Output | Status |
|:---|:---|:---|:---|:---|:---:|
| **TC-01** | **Admin Login and Dashboard** | Login with `admin@charusat.edu.in` | Authenticates; session sets `role = 'admin'`; redirects to `admin_dashboard.php` showing overview counters and event controls. | Logged in as Admin; greeting banner displayed; full admin console accessible. | **PASS** |
| **TC-02** | **Successful Event Creation** | Click "Create New Event" $\to$ Fill event name, venue, date $\to$ Attach valid PNG poster $\to$ Submit | Event inserted into MySQL `events` table; poster stored in `practical-12/uploads/`; green flash banner displayed. | Green banner: *"Successful event creation: 'AI & Deep Learning Symposium' scheduled with poster uploaded."* | **PASS** |
| **TC-03** | **Event List with Uploaded Posters** | View table on `admin_dashboard.php` | Display table rendering event name, category, venue, date, max seats, and poster image thumbnails. | Clean HTML table displaying thumbnail images (`poster-thumb`) alongside event details. | **PASS** |
| **TC-04** | **Successful Event Update** | Click "Edit" on event #1 $\to$ Change venue to *"Auditorium 1"* $\to$ Save Changes | Prepared statement executes `UPDATE events ... WHERE event_id = ?`; green banner confirms update. | Event record updated in database; table immediately reflects new venue and capacity. | **PASS** |
| **TC-05** | **Successful Event Deletion** | Click "Delete" on an event $\to$ Confirm prompt | `DELETE FROM events WHERE event_id = ?` executes; associated image file unlinked; green confirmation banner. | Event permanently deleted; row removed from table; green success banner displayed. | **PASS** |
| **TC-06** | **Valid Poster Upload** | Upload image `codeverse_banner.png` (480 KB, MIME `image/png`) | Validation passes; unique filename generated (`poster_timestamp_hash.png`); file moved to `uploads/`. | File successfully stored and linked to the new event in database. | **PASS** |
| **TC-07** | **Invalid File Type Validation** | Attempt to upload `agenda.pdf` or `script.php` as poster | Rejected by extension whitelist (`['jpg', 'jpeg', 'png', 'webp']`) and MIME check; red error banner. | Red alert: *"Invalid file type validation: Only JPG, PNG, and WebP image files are allowed. Detected: 'pdf'."* | **PASS** |
| **TC-08** | **File Size Limit Validation** | Attempt to upload high-res image `huge_poster.jpg` ($4.82$ MB) | Upload blocked by `$file['size'] > 2 * 1024 * 1024`; file not moved; red error banner. | Red alert: *"File size limit exceeded: The poster must not exceed 2 MB. Uploaded file was 4.82 MB."* | **PASS** |
| **TC-09** | **Success and Error Messages** | Test valid upload vs. invalid upload | System displays green alert on success and red alert on validation failure via PRG flash session. | Clear contextual alerts rendered at top of the dashboard. | **PASS** |
| **TC-10** | **Unauthorized User Access Restriction** | Authenticate as student, then attempt to access `admin_dashboard.php` | Middleware `check_admin_access()` catches non-admin role; terminates with HTTP 403 Forbidden. | Blocked with 403 error page: *"Unauthorized User Access Restriction - Access Denied! You do not have administrative privileges."* | **PASS** |

---

## 🔒 Security Compliance Summary
- **OWASP File Upload Protection:** Server verifies MIME type using `finfo_file()` rather than trusting client-sent `$_FILES['type']`.
- **RCE Prevention:** Files are renamed to cryptographically random alphanumeric hashes (`poster_time_hash.ext`), preventing directory traversal or script execution.
- **Strict Role-Based Access Control:** Unauthorized users (students/guests) are strictly blocked from admin creation and deletion endpoints.
