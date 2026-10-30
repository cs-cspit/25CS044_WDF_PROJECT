# Practical 15: Security & Accessibility Auditing Checklist

**Project:** StudentHub Portal &bull; Enterprise Event & Academic Management System  
**Enrollment:** 25CS044 &bull; CHARUSAT, CSPIT  
**Date:** 2026-10-30  
**Compliance Standard:** OWASP Top 10 (Web Security) & WCAG 2.1 Level AA (Accessibility)  

---

## 1. Web Security Audit Checklist (OWASP Alignment)

| # | Security Domain | Control Implemented | Verification Method | Status |
|:---:|:---|:---|:---|:---:|
| **S1** | **SQL Injection (SQLi)** | Prepared Statements with parameterized queries (`bind_param`) across all DB queries (`db.php`, `login.php`, `events.php`). | Code inspection & Automated test queries | **PASS** |
| **S2** | **Cross-Site Scripting (XSS)** | Context-aware HTML entity sanitization (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`) on all user-controlled outputs. | Injected `<script>alert(1)</script>` into search & forms; escaped safely | **PASS** |
| **S3** | **Password Security** | Bcrypt hashing via `password_hash($pwd, PASSWORD_DEFAULT)` and verification using timing-safe `password_verify()`. | Inspected `users` table; all passwords stored as `$2y$10$...` hashes | **PASS** |
| **S4** | **Session Fixation** | Immediate invocation of `session_regenerate_id(true)` upon successful authentication in `authenticate.php`. | Inspected `PHPSESSID` cookie change before vs. after login | **PASS** |
| **S5** | **Session Inactivity Timeout** | 15-minute sliding session expiration implemented in `timeout.php` destroying expired sessions. | Simulated 900+ seconds idle time; redirected to `login.php?msg=timeout` | **PASS** |
| **S6** | **Role-Based Access Control (RBAC)** | Strict server-side role check (`$_SESSION['role'] === 'admin'`). Non-admin requests receive HTTP `403 Forbidden`. | Tested student session accessing `admin_dashboard.php` | **PASS** |
| **S7** | **Audit Logging & Traceability** | Security events (`LOGIN_SUCCESS`, `UNAUTHORIZED_ACCESS_BLOCKED`, `USER_STATUS_TOGGLE`) logged with actor ID and Client IP. | Verified records in `audit_logs` table | **PASS** |
| **S8** | **File Upload Restrictions** | Whitelisted MIME types (`image/jpeg`, `image/png`, `image/webp`), 2 MB file size cap, and randomized filename generation. | Uploaded oversized files & `.php` files; rejected immediately | **PASS** |

---

## 2. Accessibility Audit Checklist (WCAG 2.1 Level AA Alignment)

| # | Accessibility Criterion | Implementation Details | Verification Tool / Method | Status |
|:---:|:---|:---|:---|:---:|
| **A1** | **Semantic HTML5 Landmarks** | Comprehensive use of `<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, and `<footer>` instead of unsemantic `<div>` soup. | W3C Nu HTML Validator & Screen Reader | **PASS** |
| **A2** | **Heading Hierarchy** | Exactly one `<h1>` per page serving as the primary topic, followed sequentially by `<h2>` and `<h3>` without skipping levels. | HeadingsMap Extension | **PASS** |
| **A3** | **Explicit Form Labeling** | All `<input>`, `<select>`, and `<textarea>` elements possess matching `<label for="...">` attributes or explicit `aria-label`. | Wave Accessibility Evaluation Tool | **PASS** |
| **A4** | **Image Alt Text** | Meaningful descriptions on informative images (e.g., posters, logos) and empty `alt=""` or hidden status on decorative icons. | Screen Reader voice verification | **PASS** |
| **A5** | **Keyboard Operability** | All modals, dropdowns, table actions, and form inputs are fully focusable and navigable via `Tab`, `Enter`, and `Escape`. | 100% mouse-free keyboard navigation test | **PASS** |
| **A6** | **Visible Focus Indicators** | Distinctive high-contrast 2px focus outlines on buttons, search bars, and links (no `outline: none` without replacement). | Visual keyboard focus cycling | **PASS** |
| **A7** | **Color Contrast Ratio** | Minimum contrast ratio exceeding 4.5:1 for normal text and 3:1 for large text across light and dark color schemes. | WebAIM Contrast Checker | **PASS** |
| **A8** | **ARIA State Attributes** | Dynamic states use `aria-expanded="true/false"` on collapsibles and `aria-current="page"` on active navigation items. | Chrome DevTools Accessibility Tree | **PASS** |

---

## 3. Lighthouse Audit Scores Summary

- **Performance:** `98 / 100`
- **Accessibility:** `100 / 100`
- **Best Practices:** `100 / 100`
- **SEO:** `100 / 100`
