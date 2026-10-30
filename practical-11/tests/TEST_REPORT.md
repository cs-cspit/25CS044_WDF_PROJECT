# Practical 11: Student Records Management Test Report

**Project:** StudentHub College Event Portal  
**Module:** Practical 11 - Student Information System (CRUD, Soft Delete, Course Filter & Excel Export)  
**Database Driver:** PHP MySQLi (Object-Oriented with Prepared Statements)  
**Architecture:** RESTful PRG Pattern, Soft Deletion Pattern (`status = 'inactive'`), Prepared Statement Filters, and UTF-8 BOM CSV Generation.

---

## 🧪 Systematic Test Execution Matrix

| ID | Test Scenario | Steps / Inputs | Expected Output | Actual Output | Status |
|:---|:---|:---|:---|:---|:---:|
| **TC-01** | **Student Records Displayed in Table** | Navigate to `index.php` | Display all student rows with Roll No, Name, Email, Phone, Course, Year, Status, and Action buttons. | Clean HTML table populated with 6 test student records; active badges displayed. | **PASS** |
| **TC-02** | **Successful Addition of a New Student** | Click "Add New Student" $\to$ Roll: `"25CS099"`, Name: `"Yash Patel"`, Course: `"B.Tech CSE"` $\to$ Submit | Form submits; row inserted via prepared statement; green flash banner: *"Student 'Yash Patel' (25CS099) added successfully!"*; record visible in table. | Student successfully inserted; green alert displayed; inputs sanitized. | **PASS** |
| **TC-03** | **Successful Update of Student Details** | Click "Edit" on student #1 $\to$ Change phone to `"9998887770"` $\to$ Save Changes | Prepared statement executes `UPDATE ... WHERE student_id = ?`; green banner: *"Student details for 'Shubham Mendpara' updated successfully!"*. | Record updated in database; table immediately reflects new phone number. | **PASS** |
| **TC-04** | **Search Functionality** | Enter `"Shubham"` into search input $\to$ Click "Apply Filter" | SQL: `WHERE (full_name LIKE ? OR roll_no LIKE ? OR email LIKE ?)`; returns matching student row. | Table filters dynamically to show 1 matching record; non-matching rows hidden. | **PASS** |
| **TC-05** | **Course-wise Filtering** | Select `"B.Tech CSE"` from course dropdown $\to$ Submit | SQL: `WHERE course = ?`; returns only CSE students. | Table displays exclusively students enrolled in B.Tech CSE; student counter updates. | **PASS** |
| **TC-06** | **Delete Confirmation Message** | Click "Delete" button on an active student | Browser confirmation prompt displayed: *"Are you sure you want to soft delete student '...'? Their status will be set to inactive."* | Modal confirmation dialog prompts user before executing deletion request. | **PASS** |
| **TC-07** | **Successful Soft Delete** | Confirm deletion on student #6 (`Rohan Raval`) | `UPDATE students SET status = 'inactive' WHERE student_id = ?`; record retained in database; badge changed to `Inactive`. | Record not deleted from database; status changed to `Inactive (Soft-Deleted)`; restore button enabled. | **PASS** |
| **TC-08** | **Success / Error Messages Display** | Trigger successful action or duplicate roll number | Success actions display green banner; duplicate inserts display red error: *"Error: Roll number or email already exists in records."* | Both success and error banners render prominently at top with clear messages. | **PASS** |
| **TC-09** | **Exported CSV File Opened in Excel** | Click "Export to Excel (CSV)" button | Downloads `StudentHub_Students_Export_...csv` with UTF-8 BOM; opens in Microsoft Excel with properly formatted columns. | CSV downloads automatically; columns align into individual spreadsheet cells in Excel. | **PASS** |

---

## 🔒 Security & Data Integrity Compliance
- **Zero Permanent Data Loss (Soft Deletion):** Student historical records and foreign key associations in registrations remain intact.
- **SQL Injection Prevention:** 100% of queries use MySQLi parameterized statements (`$stmt->bind_param()`).
- **XSS Sanitization:** All values output using `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
- **CSRF Protection:** Form submissions protected with cryptographic token verification using `hash_equals()`.
