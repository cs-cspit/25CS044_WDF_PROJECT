# Practical 15: Final Integration Test & Verification Report

**Course / Module:** Web Development Frameworks (WDF)  
**Practical No:** 15  
**Enrollment:** 25CS044  
**Date:** 2026-10-30  
**CO Mapping:** CO2, CO4, CO5  

---

## 1. Executive Summary & Problem Definition
Practical 15 represents the final end-to-end integration and verification of the semester-long **StudentHub** academic event portal. All individual laboratory modules—from semantic front-ends (P1–P5) to 3NF database design (P8), Bcrypt security (P9), session-based RBAC (P10), student roster CRUD with CSV export (P11), poster moderation (P12), RESTful JSON API with debounced search (P13), and executive admin monitoring with audit logging (P14)—have been unified into a single, fully deployable web application.

---

## 2. Answers to Key Questions & Analysis

### Q1: Are all pages properly linked and navigable?
- **Analysis & Verification:**  
  All cross-module hyperlinks, breadcrumb trails, and navigation bars have been audited and verified. The central landing page (`index.html`), public information pages (`pages/*.html`), student dashboards, and administrative panels feature consistent header and footer links. Broken or dead links (`404`) are zero.

### Q2: Are sessions, roles, validations, and database operations working end-to-end?
- **Analysis & Verification:**  
  Full end-to-end integration testing confirmed:
  - **Registration (`P9`):** Input sanitized, duplicate email check performed, password hashed with Bcrypt.
  - **Authentication (`P10`):** `password_verify()` succeeds, session ID regenerated (`session_regenerate_id(true)`), and role stored in `$_SESSION['role']`.
  - **Inactivity Timeout (`P10`):** Sliding 15-minute expiration handled automatically by `timeout.php`.
  - **Student Roster (`P11`):** Dynamic search, course filter, soft-delete, and CSV export function seamlessly.
  - **Event CRUD (`P12` & `P13`):** Image uploads with file validation, and REST API asynchronous CRUD operations with 350ms input debounce.
  - **RBAC Dashboard (`P14`):** Aggregates live database counts and prevents non-admin access with HTTP 403.

### Q3: Are security and accessibility checklists completed?
- **Analysis & Verification:**  
  A formal audit was performed against OWASP Top 10 web vulnerabilities and WCAG 2.1 Level AA accessibility criteria:
  - **Security Checklist:** Prepared statements (SQLi defense), output encoding (XSS defense), Bcrypt hashing, session fixation prevention, and security audit logging.
  - **Accessibility Checklist:** Semantic HTML5 landmarks, 1:1 `<label for>` mappings, visible focus outlines, high color contrast, screen reader compatibility, and single `<h1>` hierarchy.
  - Refer to [`practical-15/SECURITY_AND_A11Y_CHECKLIST.md`](file:///d:/25CS044_WDF_PROJECT-main/practical-15/SECURITY_AND_A11Y_CHECKLIST.md) for full details.

### Q4: Is project documentation sufficient for installation and testing?
- **Analysis & Verification:**  
  Yes. The repository includes:
  - Root [`README.md`](file:///d:/25CS044_WDF_PROJECT-main/README.md) with comprehensive semester overview and run commands.
  - Per-practical READMEs with isolated instructions and test reports.
  - Consolidated SQL dump [`practical-15/studenthub_final.sql`](file:///d:/25CS044_WDF_PROJECT-main/practical-15/studenthub_final.sql).
  - Virtual host configuration [`practical-15/apache_vhost.conf`](file:///d:/25CS044_WDF_PROJECT-main/practical-15/apache_vhost.conf).
  - Deployment and hosting guide [`practical-15/DEPLOYMENT_GUIDE.md`](file:///d:/25CS044_WDF_PROJECT-main/practical-15/DEPLOYMENT_GUIDE.md).

### Q5: Is the project repository clean and properly committed?
- **Analysis & Verification:**  
  The Git repository on branch `main` is clean, tracked, and synchronized with remote `origin main`. All temporary scratch files, duplicate folders, and transient artifacts are excluded via `.gitignore`. Commits follow semantic, descriptive commit messages documenting each milestone.

---

## 3. End-to-End System Test Matrix

| Test Suite | Module | Test Description | Expected Result | Actual Result |
|:---:|:---:|:---|:---|:---:|
| **E2E-01** | P1–P5 | Public Navigation & Accessibility | All pages render with valid landmarks, responsive grid, and zero console errors | **PASS** |
| **E2E-02** | P9 | User Registration & Duplicate Email | Rejects duplicate emails; inserts new user with Bcrypt hash; logs action | **PASS** |
| **E2E-03** | P10 | Login, Session Auth & Timeout | Validates hash, regenerates session ID, logs audit trail, expires after 15m idle | **PASS** |
| **E2E-04** | P10 & P14 | RBAC & Unauthorized Restriction | Student access to `admin_dashboard.php` blocked with HTTP 403 Forbidden | **PASS** |
| **E2E-05** | P11 | Student CRUD, Soft Delete & CSV | Records filter by course/search; soft delete marks status; CSV downloads | **PASS** |
| **E2E-06** | P12 | Event Moderation & File Upload | Uploads JPG/PNG/WebP under 2 MB; rejects invalid MIME types and files | **PASS** |
| **E2E-07** | P13 | REST API Asynchronous CRUD | GET, POST, PUT, DELETE work via Fetch API with 0 page reloads; 350ms debounce | **PASS** |
| **E2E-08** | P14 | Dashboard SQL Aggregation & Audit | Live counts match DB; Chart.js displays monthly trends; audit log records actions | **PASS** |
| **E2E-09** | P15 | Apache Virtual Host Deployment | Accessible via `http://studenthub.local` with clean DocumentRoot mapping | **PASS** |

---

## 4. Conclusion
Practical 15 successfully delivers an integrated, enterprise-grade academic event management application. All course outcomes (CO2, CO4, CO5) are fulfilled with verified security, accessibility compliance, clean source versioning, and deployment readiness.
