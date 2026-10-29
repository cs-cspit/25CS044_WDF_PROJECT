# Practical 9: Secure User Registration with Database Insert, Duplicate Email Check and Password Hashing

StudentHub College Event Portal — User Account Registration Module built with **plain PHP**, **MySQLi (Object-Oriented)**, **HTML5**, **CSS3**, and **JavaScript** (no third-party frameworks).

---

## 📂 Project Structure

```
practical-9/
├── schema.sql              # users table, audit_logs table, constraints & initial test seeds
├── db.php                  # MySQLi connection with strict error reporting & secure try-catch
├── register.php            # Registration form with CSRF, sticky fields & prepared statements
├── audit.php               # log_action() function for recording audit trails
├── style.css               # Clean responsive stylesheet with Bootstrap-inspired design
├── assets/
│   └── js/
│       └── validate.js     # Frontend validation & dynamic password strength meter
├── tests/
│   └── TEST_REPORT.md      # Systematic 14-point test execution matrix (All PASS)
└── README.md               # Setup steps, phpMyAdmin guide & screenshot manual
```

---

## 🏛️ Database Schema Architecture

The module creates and utilizes two core tables in the existing `studenthub` database:

### 1. `users` Table
| Column Name | Data Type | Constraints | Description |
|:---|:---|:---|:---|
| `user_id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier for each registered account |
| `full_name` | VARCHAR(100) | NOT NULL | Student's full legal name |
| `username` | VARCHAR(50) | NOT NULL, UNIQUE | Unique portal login handle (3–30 characters) |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | College email address (RFC validated) |
| `password_hash` | VARCHAR(255) | NOT NULL | 60-character BCRYPT salted hash (`$2y$...`) |
| `role` | ENUM | 'admin','organizer','student' DEFAULT 'student' | Enforced on server side; defaults to student |
| `status` | ENUM | 'active','inactive' DEFAULT 'active' | Account state |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Account registration timestamp |

### 2. `audit_logs` Table
| Column Name | Data Type | Constraints | Description |
|:---|:---|:---|:---|
| `log_id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique audit entry identifier |
| `user_id` | INT | NULL, FK $\to$ `users(user_id)` | Linked user account (if available) |
| `action` | VARCHAR(100) | NOT NULL | Action type (`REGISTRATION_SUCCESS`, `REGISTRATION_FAILED_DUPLICATE`, `CSRF_FAILED`) |
| `details` | TEXT | NULL | Contextual information (e.g. attempted duplicate email) |
| `ip_address` | VARCHAR(45) | NOT NULL | Client IPv4 / IPv6 address |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Event log timestamp |

---

## ⚙️ How to Setup and Run in XAMPP

1. **Start Apache & MySQL:**
   - Launch the **XAMPP Control Panel**.
   - Click **Start** for **Apache** and **MySQL**.

2. **Import Database Schema in phpMyAdmin:**
   - Open your browser to: `http://localhost/phpmyadmin/`
   - Select the `studenthub` database (created in Practical 8). If it does not exist yet, create database `studenthub` with collation `utf8mb4_unicode_ci`.
   - Click the **Import** tab.
   - Click **Choose File**, select `practical-9/schema.sql`, and click **Import**.
   - Tables `users` and `audit_logs` will be created with primary/unique constraints and initial seed users.

3. **Deploy to `htdocs`:**
   - Copy or symlink the folder `practical-9` to:
     ```
     C:\xampp\htdocs\studenthub\register
     ```
     *(Or directly place `practical-9` contents inside `C:\xampp\htdocs\studenthub`)*

4. **Access the Registration Portal:**
   - Open your browser and navigate to:
     ```
     http://localhost/studenthub/register.php
     ```

---

## 📸 Step-by-Step Screenshot Checklist

| Screenshot | Target Page / Location | What to Capture |
|:---|:---|:---|
| **1. Successful Registration** | `http://localhost/studenthub/register.php` | The green alert banner: `🎉 Registration successful! Your StudentHub account is now active.` with form inputs reset. |
| **2. Empty-Field Validation** | `register.php` (Submit blank) | The red alert summary at top: `Please correct the following errors: All fields are required` along with red inline error messages under Full Name, Username, Email, and Password. |
| **3. Invalid Email Format** | `register.php` (Input: `shubham@com`) | Inline error: `Please enter a valid email address` and summary alert box. |
| **4. Password Mismatch** | `register.php` (Different passwords) | Red inline message under Confirm Password: `Passwords do not match.` with the rest of the entered form data remaining sticky. |
| **5. Duplicate Email/Username** | `register.php` (Input existing email/user) | Explicit message: `Email already registered.` or `Username already taken.` or composite duplicate alert. |
| **6. phpMyAdmin `users` Table** | phpMyAdmin $\to$ `studenthub` $\to$ `users` | The Browse tab showing the inserted user row with `role = 'student'` and the secure `password_hash` column starting with `$2y$10$...` (confirming plain text is never stored). |
| **7. phpMyAdmin `audit_logs` Table** | phpMyAdmin $\to$ `studenthub` $\to$ `audit_logs` | The Browse tab showing entries for `REGISTRATION_SUCCESS`, `REGISTRATION_FAILED_DUPLICATE`, and client IP address `127.0.0.1`. |
