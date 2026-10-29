# Practical 9: Comprehensive Test Report

**Project:** StudentHub College Event Portal  
**Module:** Practical 9 - Secure User Registration with Database Insert, Duplicate Email Check and Password Hashing  
**Database Driver:** PHP MySQLi (Object-Oriented Prepared Statements)  
**Security Architecture:** BCRYPT Hash (`PASSWORD_DEFAULT`), Session CSRF Protection (`hash_equals()`), Server-side Prepared Statements (`bind_param`), Cascading Foreign Keys, and Automated Audit Trail.

---

## 🧪 Systematic Test Execution Matrix

| ID | Test Case | Input Data | Expected Result | Actual Result | Status |
|:---|:---|:---|:---|:---|:---:|
| **TC-01** | **Valid Registration** | Name: `"Meera Bhatt"`<br>User: `"meera_it"`<br>Email: `"meera.it@charusat.edu.in"`<br>Pass: `"Meera@2026"`<br>Confirm: `"Meera@2026"` | Form submits successfully; flash message `"Registration successful!"` appears; row inserted into `users` with `role = 'student'` and `status = 'active'`; form cleared via Post/Redirect/Get. | Green banner displayed: *"Registration successful! Your StudentHub account is now active."*; record inserted into `users` table; session rotated. | **PASS** |
| **TC-02** | **All Fields Empty** | All inputs left blank (`""`) | Submission blocked; top alert summary *"All fields are required."*; inline red error messages beneath each input field. | Form blocked both by frontend `validate.js` and server-side PHP; summary alert displayed; all fields highlighted red. | **PASS** |
| **TC-03** | **One Field Empty** | Test with each field omitted individually (e.g. Missing Full Name or Missing Username) | Specific field inline error (e.g. *"Full Name is required"* or *"Username is required"*); entered values preserved (sticky form). | Specific missing field flagged with inline message; non-empty values kept intact via `htmlspecialchars()`. | **PASS** |
| **TC-04** | **Invalid Email Format** | Email: `"abc@"`, `"shubham.com"`, `"test@@charusat"` | Validation error *"Invalid email address."* displayed; submit halted; database not touched. | Caught by `filter_var(..., FILTER_VALIDATE_EMAIL)` and RFC regex; inline feedback displayed. | **PASS** |
| **TC-05** | **Password Mismatch** | Password: `"SecurePass1"`<br>Confirm: `"SecurePass2"` | Error message *"Passwords do not match."* displayed; password fields emptied for security; other text fields remain sticky. | Both frontend and backend flagged mismatch; passwords cleared; username/email retained. | **PASS** |
| **TC-06** | **Weak / Short Password** | Password: `"short"`, `"12345678"`, `"abcdefgh"` | Password strength meter shows *"Weak"*; error message *"Password must be at least 8 characters long"* or *"Password must contain at least one letter and one number"*. | Weak password rejected with explicit rule violation notice; strength bar dynamically showed red/warning. | **PASS** |
| **TC-07** | **Duplicate Email** | Email: `"25cs044@charusat.edu.in"` (Already in database)<br>New username: `"unique_user99"` | Specific error *"Email already registered."* displayed; `audit_logs` records `REGISTRATION_FAILED_DUPLICATE` with client IP. | Query `SELECT user_id ... WHERE email = ?` matched existing record; specific message returned; logged in `audit_logs`. | **PASS** |
| **TC-08** | **Duplicate Username** | Username: `"shubham_25"` (Already in database)<br>New email: `"brandnew@charusat.edu.in"` | Specific error *"Username already taken."* displayed; `audit_logs` records `REGISTRATION_FAILED_DUPLICATE`. | Prepared statement caught duplicate username; user prompted with exact reason; logged in audit table. | **PASS** |
| **TC-09** | **Duplicate Email AND Username** | Email: `"25cs044@charusat.edu.in"`<br>Username: `"shubham_25"` | Differentiated error *"Both email and username are already registered. Please choose different ones."* displayed. | Accurately distinguished composite duplicate state; user notified clearly without generic ambiguity. | **PASS** |
| **TC-10** | **Password Stored as Secure Hash** | User: `"rohan_mech"`, Pass: `"Rohan@2026"` | Database column `password_hash` contains a 60-character BCRYPT string starting with `$2y$10$`; plain text is never stored or logged anywhere. | Inspected `users` row: `password_hash` = `$2y$10$e8V9...` (irreversible salted hash). Confirmed plain text is never persisted. | **PASS** |
| **TC-11** | **SQL Injection Attempt** | Email: `' OR '1'='1`<br>Username: `admin' --` | SQL injection characters safely parameterized by `bind_param("ss", ...)`. Input treated literally; triggers validation error or safe zero match. No database bypass possible. | Characters escaped at driver level; zero SQL syntax error; malicious query failed safely. | **PASS** |
| **TC-12** | **XSS (Cross-Site Scripting) Attempt** | Full Name: `<script>alert(1)</script>` | Payload safely escaped via `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`. Rendered as benign text entities; script does not execute in the browser DOM. | Rendered safely as `&lt;script&gt;alert(1)&lt;/script&gt;`; no JavaScript execution triggered. | **PASS** |
| **TC-13** | **CSRF Token Missing / Tampered** | POST request submitted without `csrf_token` or with modified token `"tampered_token_xyz"` | Request rejected immediately; error *"Security token mismatch. Please refresh the page and try again."*; `audit_logs` records `CSRF_FAILED`. | `hash_equals($_SESSION['csrf_token'], $token)` returned `false`; database insert halted; security event logged. | **PASS** |
| **TC-14** | **Audit Trail Verification** | Run TC-01 (Success), TC-07 (Duplicate), and TC-13 (CSRF tampered) | Table `audit_logs` contains entries for `REGISTRATION_SUCCESS`, `REGISTRATION_FAILED_DUPLICATE`, and `CSRF_FAILED` with real IP, timestamp, and details. | Verified rows in `audit_logs`: all three actions stored with exact timestamps, client IP (`127.0.0.1`), and context metadata. | **PASS** |

---

## 🔒 Verification of Security Standards
- **OWASP A01 (Broken Access Control):** Role is locked to `'student'` exclusively on the server side (`INSERT ... VALUES (..., 'student', 'active')`), rendering form parameter tampering impossible.
- **OWASP A02 (Cryptographic Failures):** Industry-standard `PASSWORD_DEFAULT` (BCRYPT with automatic salt) ensures robust resistance against rainbow tables.
- **OWASP A03 (Injection):** 100% of queries use MySQLi Prepared Statements (`$conn->prepare()` and `$stmt->bind_param()`), with zero raw SQL concatenation.
- **OWASP A07 (Identification and Authentication Failures):** Strict rate-ready CSRF tokens via `random_bytes(32)` validated with constant-time `hash_equals()`.
