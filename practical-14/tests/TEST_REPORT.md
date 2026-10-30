# Practical 14: Test & Evaluation Report
## Admin Dashboard with Role-Based Access Control, Dynamic Menu, and Audit Log

**Course / Module:** Web Development Frameworks (WDF)  
**Practical No:** 14  
**Enrollment:** 25CS044  
**Date:** 2026-10-30  
**CO Mapping:** CO1, CO4, CO5  

---

## 1. Objective & Problem Definition
To build an executive administration dashboard that computes live database statistics (Total Users, Enrolled Students, Campus Events, and Total Registrations), displays latest event registrations using relational SQL `JOIN`, enforces strict Role-Based Access Control (RBAC) restricting access exclusively to Administrators (HTTP 403 otherwise), renders dynamic navigation menus according to the user's role, and transparently logs all critical administrative actions in the `audit_logs` database table.

---

## 2. Answers to Key Questions & Analysis

### Q1: Are dashboard values loaded dynamically from the database?
- **Analysis & Verification:**  
  Yes. In `admin_dashboard.php`, all numeric metric cards are computed using real-time SQL aggregation queries (`COUNT(*)`) executed against the `studenthub` database:
  ```sql
  SELECT COUNT(*) AS cnt FROM `users`;
  SELECT COUNT(*) AS cnt FROM `students`;
  SELECT COUNT(*) AS cnt FROM `events`;
  SELECT COUNT(*) AS cnt FROM `registrations`;
  ```
  Values dynamically update as users register or new events are scheduled without hardcoding numbers.

### Q2: Is access restricted to admin users?
- **Analysis & Verification:**  
  Yes. Strict session and RBAC guards are enforced at the top of `admin_dashboard.php`:
  ```php
  if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
      http_response_code(403);
      log_audit_action($conn, $_SESSION['user_id'] ?? null, 'UNAUTHORIZED_ACCESS_BLOCKED', ...);
      // Renders 403 Forbidden Access Restricted screen
      exit;
  }
  ```
  If an enrolled student attempts direct URL access, execution halts immediately with HTTP status `403 Forbidden`, and an unauthorized violation is written to `audit_logs`.

### Q3: Are role-based menus working?
- **Analysis & Verification:**  
  Yes. The modular `menu.php` script dynamically filters navigation links based on `$_SESSION['role']`:
  - **Admin:** Has full access (Admin Dashboard, User Management, Student Roster, Campus Events, Event Registrations, Analytics, Security Audit Trail).
  - **Organizer:** Scoped to event management (Organizer Hub, Campus Events, Attendee Registrations, Event Analytics).
  - **Student:** Scoped to self-service student activities (Student Portal, Available Events, My Registrations, Profile).

### Q4: Are important admin actions recorded in `audit_logs`?
- **Analysis & Verification:**  
  Yes. Every critical action invokes `log_audit_action($conn, $user_id, $action, $details)` in `audit.php`, capturing the actor's ID, action type, descriptive details, IP address (`$_SERVER['REMOTE_ADDR']`), and a MySQL timestamp:
  - `LOGIN_SUCCESS`: Admin login authentication.
  - `USER_STATUS_TOGGLE`: Deactivating or activating accounts.
  - `EVENT_CREATE` / `EVENT_UPDATE`: Publishing or editing events.
  - `UNAUTHORIZED_ACCESS_BLOCKED`: Non-admin users attempting privileged access.

---

## 3. Advanced & Intermediate Extensions

| Extension | Requirement | Implementation Detail | Status |
|:---|:---|:---|:---:|
| **Intermediate** | **Active/Inactive User Status Toggle** | AJAX-driven status toggle in `toggle_user.php` with self-protection guard (admin cannot deactivate themselves) and audit log record. | **Completed** |
| **Advanced** | **Monthly Registration Analytics Chart** | Dynamic SQL grouping by `DATE_FORMAT(registered_at, '%M %Y')` rendered via modern **Chart.js** bar chart. | **Completed** |

---

## 4. Test Execution Matrix

| Test ID | Scenario | Input / Action | Expected Result | Actual Result |
|:---:|:---|:---|:---|:---:|
| **TC-01** | Dynamic Stat Queries | Load `admin_dashboard.php` | 4 stat counters match database row counts exactly | **PASS** |
| **TC-02** | Latest Registrations | Inspect registration table | Displays top 6 rows with student names & event titles via SQL JOIN | **PASS** |
| **TC-03** | Admin Role Access | Login as `admin@charusat.edu.in` | Admin dashboard loads with 7-item admin navigation menu | **PASS** |
| **TC-04** | Student Unauthorized Access | Login as `25cs044@charusat.edu.in` and open `admin_dashboard.php` | Returns HTTP 403 Forbidden error page; writes to `audit_logs` | **PASS** |
| **TC-05** | Intermediate Status Toggle | Click "Deactivate" on User #5 | AJAX updates status to "inactive" without reload; logs `USER_STATUS_TOGGLE` | **PASS** |
| **TC-06** | Self-Deactivation Guard | Admin clicks status button for User #1 | Button disabled; safety constraint prevents self-lockout | **PASS** |
| **TC-07** | Advanced Analytics Chart | Load Analytics Section | Chart.js renders monthly registration bars dynamically | **PASS** |
| **TC-08** | Audit Trail Verification | Inspect `audit_logs` table | Displays recorded logs with actor, action, IP, and timestamp | **PASS** |

---

## 5. Conclusion
Practical 14 meets all criteria outlined in the laboratory syllabus (CO1, CO4, CO5). The system provides a unified administrative control panel featuring data aggregation, role segregation, account moderation, and full security accountability.
