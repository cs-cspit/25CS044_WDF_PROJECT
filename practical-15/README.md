# Practical 15: Final Integration, Testing, Documentation, GitHub Submission, and Local Deployment

## Project Overview
Practical 15 marks the final capstone integration of the semester-long **StudentHub** academic portal. It unites all frontend and backend modules into a single production-ready web application with verified cross-module navigation, responsive layout, session security, prepared statement database operations, RESTful JSON APIs, executive dashboard analytics, security/accessibility audits, and Apache Virtual Host deployment.

---

## Semester Practical Roadmap & Architecture

| Module | Practical Topic | Key Implemented Features |
|:---:|:---|:---|
| **P1–P3** | Frontend Foundation & Responsive Design | Semantic HTML5, CSS Grid/Flexbox, mobile-first breakpoints. |
| **P4–P5** | JavaScript Interactivity & Form Validation | FAQ accordion, campus slider, client regex validation, theme switcher. |
| **P8** | Database Modeling & 3NF Schema | Relational design, M:N junction table `registrations`, cascade rules. |
| **P9** | User Registration & Password Security | Duplicate email checks, Bcrypt password hashing, initial audit logging. |
| **P10** | Session Authentication & RBAC | `password_verify()`, `session_regenerate_id(true)`, sliding 15-min timeout. |
| **P11** | Student Roster CRUD & CSV Export | Search, course filtering, soft deletion, Excel-compatible CSV stream. |
| **P12** | Event Poster Uploads & Moderation | 2 MB file cap, MIME whitelisting, randomized file storage, error handling. |
| **P13** | RESTful JSON API & Fetch CRUD | Zero full-page reloads, 350ms input debounce, standard HTTP status codes. |
| **P14** | Admin Dashboard & Traceability | Live SQL aggregations, status toggle, Chart.js analytics, audit trail. |
| **P15** | Final Integration & Deployment | Consolidated SQL dump, virtual host config, security/a11y audit suite. |

---

## Deliverables in `practical-15/`

```
practical-15/
├── studenthub_final.sql            # Master consolidated database dump with all tables & seeds
├── apache_vhost.conf               # Intermediate Extension: Apache Virtual Host (studenthub.local)
├── DEPLOYMENT_GUIDE.md             # Complete XAMPP, vhost, and cloud hosting architecture guide
├── SECURITY_AND_A11Y_CHECKLIST.md  # Formal OWASP Top 10 & WCAG 2.1 AA auditing checklists
├── tests/
│   └── TEST_REPORT.md              # Final integration test report answering all 5 key questions
└── README.md                       # Milestone overview & quick start instructions
```

---

## Quick Start & Verification

### 1. Database Setup
- Open phpMyAdmin: `http://localhost/phpmyadmin/`.
- Import the master database dump: [`practical-15/studenthub_final.sql`](file:///d:/25CS044_WDF_PROJECT-main/practical-15/studenthub_final.sql).

### 2. Launch Local Test Suites
- **Interactive Master Preview & Test Inspector (Active on port 5500):**
  [http://localhost:5500/pages/practical15_preview.html](http://localhost:5500/pages/practical15_preview.html)
- **Main Portal Landing Page:**
  [http://localhost:5500/index.html](http://localhost:5500/index.html)
- **Executive Admin Dashboard:**
  `http://localhost/25CS044_WDF_PROJECT-main/practical-14/admin_dashboard.php`
- **AJAX & Fetch Event Console:**
  `http://localhost/25CS044_WDF_PROJECT-main/practical-13/index.html`

### 3. Apache Virtual Host (Intermediate Extension)
Access the application under the dedicated local hostname:
```
http://studenthub.local/
```
*(Refer to [`apache_vhost.conf`](file:///d:/25CS044_WDF_PROJECT-main/practical-15/apache_vhost.conf) for setup instructions)*
