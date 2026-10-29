# Practical 8: StudentHub Database (MySQL + PHP PDO)

A complete database and backend connection module built with **MySQL** and **PHP Data Objects (PDO)** following **Third Normal Form (3NF)** rules, secure parameterized queries, cascading foreign keys, and stored procedures.

---

## 📂 Project Structure

```
studenthub-db/
├── schema.sql           # Database schema in 3NF, constraints, seeds, view & stored procedure
├── db.php               # Secure PDO connection with try-catch and error_log()
├── test_connection.php  # Output page testing connection, 4 Prepared Statements & Stored Procedure
├── style.css            # Clean, modern, responsive stylesheet
└── README.md            # Setup guide, ER diagram, 3NF documentation & screenshot manual
```

---

## 📐 Database Architecture & Entity Relationship (ER) Diagram

```
+------------------+             +--------------------+             +------------------+
|     STUDENTS     |             |   REGISTRATIONS    |             |      EVENTS      |
+------------------+             +--------------------+             +------------------+
| PK student_id    |<---(1:M)--->| PK registration_id |<---(M:1)--->| PK event_id      |
|    full_name     |             | FK student_id      |             |    event_name    |
|    email (UQ)    |             | FK event_id        |             |    event_date    |
|    phone         |             |    registered_at   |             |    venue         |
|    department    |             | UQ (stu_id, evt_id)|             |    description   |
|    year_of_study |             +--------------------+             +------------------+
+------------------+
```

### Cardinality:
- **`students` to `registrations`:** One-to-Many ($1:M$) — One student can register for multiple events.
- **`events` to `registrations`:** One-to-Many ($1:M$) — One event can have multiple student registrations.
- **`students` to `events`:** Many-to-Many ($M:N$) modeled through the associative table `registrations`.

---

## 🏛️ 3NF Normalization Explanation

1. **First Normal Form (1NF):**
   - Each column contains atomic (indivisible) values.
   - There are no repeating groups or comma-separated arrays in any row.
   - Each table possesses an explicitly designated Primary Key (`student_id`, `event_id`, `registration_id`).

2. **Second Normal Form (2NF):**
   - Tables meet 1NF.
   - All tables use a single-attribute Primary Key. Therefore, **no partial dependency** can exist — all non-key attributes depend on the entire Primary Key.

3. **Third Normal Form (3NF):**
   - Tables meet 2NF.
   - **No transitive dependencies** exist ($X \to Y$ and $Y \to Z$). Every non-key column (e.g., student name, email, department, event date, venue) depends directly and solely on the primary key, and not on other non-key attributes.

---

## 🚀 How to Run on XAMPP (Localhost)

1. **Start Services:**
   - Open the **XAMPP Control Panel**.
   - Start **Apache** and **MySQL**.

2. **Import Database in phpMyAdmin:**
   - Open your browser and navigate to: `http://localhost/phpmyadmin/`
   - Click on the **Import** tab.
   - Click **Choose File** and select `studenthub-db/schema.sql`.
   - Click **Import** at the bottom.
   - The database `studenthub` along with tables `students`, `events`, `registrations`, view `view_registration_details`, and procedure `RegisterStudent` will be created automatically.

3. **Place Project in `htdocs`:**
   - Copy or symlink the folder `studenthub-db` into:
     ```
     C:\xampp\htdocs\studenthub
     ```

4. **Open in Browser:**
   - Navigate to:
     ```
     http://localhost/studenthub/test_connection.php
     ```

---

## 📸 Step-by-Step Screenshot Guide

### Screenshot 1: Successful Connection & Database Tables Output
- **URL:** `http://localhost/studenthub/test_connection.php`
- **What to show:**
  1. The top green alert banner: `✅ Database connected successfully! Connected to MySQL database studenthub`.
  2. The Stored Procedure execution info banner (`RegisterStudent`).
  3. The rendered HTML tables displaying:
     - Table 1: All Students
     - Table 2: All Events
     - Table 3: Joined Registrations
     - Table 4: Bound Parameter Filter Query (`WHERE department = ?`)

### Screenshot 2: phpMyAdmin Designer / ER Diagram View
- **URL:** `http://localhost/phpmyadmin/index.php?route=/database/designer&db=studenthub`
- **Steps:**
  1. In phpMyAdmin, click on database `studenthub` in the left sidebar.
  2. Click **More** in the top navigation bar and select **Designer**.
  3. Capture the relationship lines showing foreign keys linking `students` $\to$ `registrations` $\to$ `events`.

### Screenshot 3: Tables Structure & Constraints
- **Steps:**
  1. In phpMyAdmin, click table `registrations` $\to$ **Structure** tab.
  2. Click **Relation view** to display the `FOREIGN KEY` definitions with `ON DELETE CASCADE` and `ON UPDATE CASCADE`.
