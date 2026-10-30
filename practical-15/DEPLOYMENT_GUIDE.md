# Practical 15: Deployment Guide & Hosting Architecture

**Project:** StudentHub Portal  
**Enrollment:** 25CS044 &bull; CHARUSAT, CSPIT  
**Date:** 2026-10-30  

---

## 1. Local Deployment with XAMPP

### Prerequisites
- XAMPP with PHP 8.1+ and MariaDB / MySQL 8.0+.
- Modern Web Browser (Chrome, Edge, Firefox).

### Step-by-Step Installation
1. **Clone / Place Project:**
   ```
   C:\xampp\htdocs\25CS044_WDF_PROJECT-main\
   ```
2. **Start Services:**
   Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
3. **Import Database:**
   - Open phpMyAdmin: `http://localhost/phpmyadmin/`.
   - Create database `studenthub`.
   - Import `practical-15/studenthub_final.sql`.
4. **Access the Application:**
   - Public Landing Page: `http://localhost/25CS044_WDF_PROJECT-main/index.html`
   - User Registration: `http://localhost/25CS044_WDF_PROJECT-main/practical-9/register.php`
   - Secure Login: `http://localhost/25CS044_WDF_PROJECT-main/practical-10/login.php`
   - Student Dashboard: `http://localhost/25CS044_WDF_PROJECT-main/practical-10/student_dashboard.php`
   - Student Roster & CSV: `http://localhost/25CS044_WDF_PROJECT-main/practical-11/index.php`
   - Event Posters & Moderation: `http://localhost/25CS044_WDF_PROJECT-main/practical-12/admin_dashboard.php`
   - AJAX & Fetch Console: `http://localhost/25CS044_WDF_PROJECT-main/practical-13/index.html`
   - Admin RBAC & Analytics: `http://localhost/25CS044_WDF_PROJECT-main/practical-14/admin_dashboard.php`

---

## 2. Intermediate Extension: Apache Virtual Hosts (`http://studenthub.local`)

To run the application under a custom local domain without the subfolder prefix:
1. Open `C:\Windows\System32\drivers\etc\hosts` (as Administrator) and add:
   ```
   127.0.0.1    studenthub.local
   ```
2. In `C:\xampp\apache\conf\extra\httpd-vhosts.conf`, append:
   ```apache
   Include "C:/xampp/htdocs/25CS044_WDF_PROJECT-main/practical-15/apache_vhost.conf"
   ```
3. Restart Apache. You can now access the portal directly at `http://studenthub.local/`.

---

## 3. Advanced Extension: Online Deployment & Hosting Architecture

### Architecture Breakdown
The StudentHub architecture is divided into two operational tiers:

```
+-----------------------------------------------------------+
|                     Client Tier (Browser)                 |
+-----------------------------+-----------------------------+
                              |
       +----------------------+----------------------+
       | Static Assets                               | Dynamic REST & SSR
       v                                             v
+-------------------------------+             +-------------------------------+
| Tier 1: Static Edge Hosting   |             | Tier 2: PHP & MySQL Backend   |
| (GitHub Pages / Vercel /      |             | (InfinityFree / Railway /     |
| Netlify / Cloudflare Pages)   |             | Render / AWS Lightsail)       |
+-------------------------------+             +-------------------------------+
| * index.html                  |             | * PHP 8.1+ Runtime Environment |
| * css/ & js/ assets           |             | * Managed MySQL / MariaDB     |
| * pages/ (Public & Info)      |             | * Server Sessions & Cookies   |
| * Client-side Fetch modules   |             | * File Upload Directory       |
+-------------------------------+             +-------------------------------+
```

### Static vs. Dynamic Hosting Limitations
| Aspect | Static Hosting (e.g. GitHub Pages) | Dynamic PHP/MySQL Hosting |
|:---|:---|:---|
| **Supported Files** | HTML5, CSS3, Client JavaScript, Images | Full PHP scripts, `.htaccess`, Cron jobs |
| **Database Operations** | Not supported (read-only mock data) | Full transactional MySQL queries |
| **Authentication** | Client-side simulation only | Secure timing-safe `password_verify()` & HTTP cookies |
| **File Uploads** | No persistent writable filesystem | Permanent disk storage for posters |

### Cloud Deployment Strategy
1. **Frontend Hosting (GitHub Pages):**
   - The static marketing and UI modules (`index.html`, `pages/*.html`, CSS, JS) can be deployed seamlessly via GitHub Pages branch deployment (`gh-pages` or `main` root).
2. **Backend API & Dynamic Hosting (Render / Railway / InfinityFree):**
   - Deploy PHP code and connect via environment variables (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`).
   - CORS headers in `practical-13/api/events.php` enable seamless cross-origin communication between the static front-end and the dynamic PHP backend.
