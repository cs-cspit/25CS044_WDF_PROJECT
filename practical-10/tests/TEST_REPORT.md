# Practical 10: Authentication & Role-Based Access Control Test Report

**Project:** StudentHub College Event Portal  
**Module:** Practical 10 - Secure User Login with Password Verification, Session Management & RBAC  
**Driver:** PHP MySQLi (Object-Oriented)  
**Security Mechanisms:** `password_verify()`, `session_regenerate_id()`, Secure Cookie Flags (`HttpOnly`, `SameSite=Lax`), CSRF Tokens, Inactivity Timeout, and Role-Based Access Control (`require_role()`).

---

## 🧪 Systematic Test Matrix

| ID | Test Case | Action / Input | Expected Result | Actual Result | Status |
|:---|:---|:---|:---|:---|:---:|
| **TC-01** | **Valid Student Login** | Ident: `"shubham_25"`<br>Pass: `"StudentHub@2026"` | Authenticates via `password_verify()`; session ID regenerated; role stored as `'student'`; redirected to `dashboard.php`. | Session regenerated; logged into student dashboard; greeting displayed: *"Welcome back, Shubham Mendpara!"*; audit entry `LOGIN_SUCCESS`. | **PASS** |
| **TC-02** | **Valid Admin Login** | Ident: `"admin_shubham"`<br>Pass: `"StudentHub@2026"` | Authenticates; role stored as `'admin'`; redirected to `admin_dashboard.php`. | Authenticates cleanly; redirected to Admin Console showing user directory and audit logs; audit entry `LOGIN_SUCCESS`. | **PASS** |
| **TC-03** | **Login via Email Address** | Ident: `"25cs044@charusat.edu.in"`<br>Pass: `"StudentHub@2026"` | Prepared statement matches on `email` column; authenticates user identically. | Authenticated successfully using college email identifier. | **PASS** |
| **TC-04** | **Incorrect Password** | Ident: `"shubham_25"`<br>Pass: `"WrongPassword"` | `password_verify()` returns `false`; generic error *"Invalid login credentials."*; audit entry `LOGIN_FAILED_BAD_PASSWORD`. | Generic error shown (no user enumeration); login rejected; audit log updated with client IP. | **PASS** |
| **TC-05** | **Non-Existent User** | Ident: `"unknown_ghost_user"`<br>Pass: `"AnyPassword123"` | Query returns empty result; generic error *"Invalid login credentials."*; audit entry `LOGIN_FAILED_NOT_FOUND`. | Rejected with identical generic message; user enumeration prevented. | **PASS** |
| **TC-06** | **Empty Login Fields** | Ident: `""`, Pass: `""` | Submission rejected with *"Please enter your username/email and password."*; database not queried. | Form flagged empty fields immediately; no database overhead. | **PASS** |
| **TC-07** | **RBAC: Student Blocks Admin Page** | Student logs in, then navigates directly to `admin_dashboard.php` | `require_role('admin')` catches role mismatch; terminates with HTTP 403 Forbidden page. | Blocked with HTTP 403 Forbidden: *"You do not have permission to access this resource. Your current role is student."* | **PASS** |
| **TC-08** | **Unauthenticated Direct Access** | Guest opens `dashboard.php` without logging in | `require_login()` checks `is_logged_in()`; redirects to `login.php?msg=auth_required`. | Redirected to login with notification *"Please log in to access that page."* | **PASS** |
| **TC-09** | **CSRF Token Mismatch on Login** | Submitting login form with tampered `csrf_token` | `hash_equals()` fails; displays *"Security token mismatch."*; audit entry `LOGIN_CSRF_FAILED`. | Request rejected immediately; database query halted; event recorded. | **PASS** |
| **TC-10** | **Secure Logout** | User clicks Logout in dashboard | `logout.php` unsets `$_SESSION`, expires session cookie, calls `session_destroy()`; redirects to `login.php?msg=logged_out`. | Session cleared; cookie invalidated; user redirected with message *"You have logged out successfully."* | **PASS** |
| **TC-11** | **Inactivity Timeout (30 min)** | Session idle for $> 1800$ seconds | Middleware detects expired timestamp; destroys session; redirects with `msg=session_expired`. | Session expired automatically; user redirected with notification to re-authenticate. | **PASS** |
| **TC-12** | **Session Fixation Prevention** | Inspect session cookie before and after login | Session ID changes immediately upon successful authentication via `session_regenerate_id(true)`. | Old session ID invalidated; new random ID assigned; fixation attack prevented. | **PASS** |

---

## 🔒 Security Compliance Summary
- **OWASP A01 (Broken Access Control):** Handled via server-side `require_role()` middleware, never relying on client-side state.
- **OWASP A02 (Cryptographic Failures):** Strict verification using PHP core `password_verify()` with automatic `password_needs_rehash()` check.
- **OWASP A07 (Identification and Authentication Failures):** Session cookies configured with `HttpOnly` and `SameSite=Lax`. Session IDs regenerated on privilege boundary transitions.
