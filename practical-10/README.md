# Practical 10: Authentication, Session Management & Role-Based Access Control

**StudentHub College Event Portal**  
**Technology Stack:** Plain PHP, MySQLi (Object-Oriented), HTML5, CSS3, JavaScript (no external frameworks).

---

## 📂 Project Structure

```
practical-10/
├── db.php                    # MySQLi database connection (studenthub)
├── login.php                 # Login form with email and password fields
├── authenticate.php          # password_verify() and session_regenerate_id(true)
├── dashboard.php             # Main dashboard router with session validation & timeout
├── admin_dashboard.php       # Dashboard accessible strictly to Admin users
├── student_dashboard.php     # Dashboard accessible strictly to Student users
├── logout.php                # Secure logout and session destruction
├── timeout.php               # Session timeout handling (15-minute window)
├── style.css                 # CSS stylesheet for practical 10
├── user_registration.sql     # Exported MySQL database dump with registered users and roles
└── tests/
    └── TEST_REPORT.md        # Comprehensive 12-point authentication test matrix
```

---

## 👥 Seed User Accounts (`user_registration.sql`)

All seed passwords are: **`StudentHub@2026`** (hashed using BCRYPT `PASSWORD_DEFAULT`).

| User ID | Full Name | Email Address | Role | Account Status |
|:---|:---|:---|:---|:---|
| **1** | System Administrator | `admin@charusat.edu.in` | **admin** | Active |
| **2** | Shubham Mendpara | `25cs044@charusat.edu.in` | **student** | Active |
| **3** | Aarav Patel | `aarav.ce@charusat.edu.in` | **student** | Active |

---

## 🚀 Setup & Execution in XAMPP

1. **Start Apache & MySQL:**
   - Open **XAMPP Control Panel** and click **Start** on Apache and MySQL.

2. **Import Database in phpMyAdmin:**
   - Navigate to `http://localhost/phpmyadmin/`.
   - Select or create the `studenthub` database.
   - Click **Import** $\to$ choose `practical-10/user_registration.sql` $\to$ **Import**.

3. **Deploy to `htdocs`:**
   - Copy folder `practical-10` into:
     ```
     C:\xampp\htdocs\studenthub\practical-10
     ```

4. **Open in Browser:**
   - Navigate to:
     ```
     http://localhost/studenthub/practical-10/login.php
     ```

---

## 📸 Checklist for the 9 Required Screenshots

| # | Screenshot Required | How to Capture | URL / Tab in Preview |
|:---|:---|:---|:---|
| **1** | **Successful student login** | Login with `25cs044@charusat.edu.in` / `StudentHub@2026` | `student_dashboard.php` showing student greeting and events table. |
| **2** | **Successful admin login** | Login with `admin@charusat.edu.in` / `StudentHub@2026` | `admin_dashboard.php` showing user management and audit logs. |
| **3** | **Invalid email/password message** | Submit wrong password in login form | `login.php` showing red alert: `Invalid email or password.` |
| **4** | **Student attempting to access admin dashboard (Unauthorized Access)** | As student, open `admin_dashboard.php` | Red 403 box: `Unauthorized Access - The Admin Dashboard is restricted exclusively to administrators.` |
| **5** | **Admin attempting to access student dashboard (Unauthorized Access)** | As admin, open `student_dashboard.php` | Red 403 box: `Unauthorized Access - The Student Dashboard is designated exclusively for enrolled students.` |
| **6** | **Successful logout** | Click "Logout" from dashboard | `login.php?msg=logged_out` showing blue alert: `You have logged out successfully.` |
| **7** | **Dashboard access denied after logout** | Try opening `dashboard.php` after logging out | `login.php?msg=auth_required` showing red alert: `Access denied. Please log in first.` |
| **8** | **Session timeout and redirection to login** | Inactivity timeout triggered | `login.php?msg=timeout` showing alert: `Your session has timed out due to inactivity.` |
| **9** | **Session ID regeneration after successful login** | Inspect cookies or view regeneration table | Table demonstrating `guest_sess_...` rotated to `auth_sess_...` via `session_regenerate_id(true)`. |

> **Tip for Instant Screenshots:** You can also open the all-in-one preview suite at:  
> **`http://localhost:5500/pages/practical10_preview.html`**  
> and click tabs 1 through 9 to capture every required test case immediately.
