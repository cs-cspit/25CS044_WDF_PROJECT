# Practical 11: Student Records Management (CRUD, Soft Delete & Excel CSV Export)

**StudentHub College Event Portal**  
**Module:** Practical 11 - Student Information System with Prepared Statements, Course-wise Filtering, Search, Soft Deletion & CSV Export.

---

## 📂 Project Structure

```
practical-11/
├── schema.sql              # Database schema for students table with soft delete status & course index
├── db.php                  # Secure MySQLi connection with utf8mb4 charset & strict exception handling
├── index.php               # Complete CRUD interface (Create, Read, Update, Soft Delete, Restore)
├── export_csv.php          # Excel-compatible CSV export engine with UTF-8 BOM
├── README.md               # Setup steps, XAMPP instructions & screenshot checklist
└── tests/
    └── TEST_REPORT.md      # Systematic testing report for all 9 required verification scenarios
```

---

## 🚀 Setup & Execution in XAMPP

1. **Start Services:** Start **Apache** and **MySQL** in XAMPP Control Panel.
2. **Import Database:** In phpMyAdmin (`http://localhost/phpmyadmin/`), select database `studenthub` and import `practical-11/schema.sql`.
3. **Deploy:** Copy folder `practical-11` to `C:\xampp\htdocs\studenthub\practical-11`.
4. **Access in Browser:** Open:
   ```
   http://localhost/studenthub/practical-11/index.php
   ```

---

## 📸 Checklist for the 9 Required Screenshots

| # | Screenshot Required | How to Capture | URL / Tab in Preview |
|:---|:---|:---|:---|
| **1** | **Student records displayed in a table** | Open `index.php` showing populated students table | Tab **"1. Student Records Table"** |
| **2** | **Successful addition of a new student** | Click "Add New Student", fill form and submit | Tab **"2. Successful Add Student"** showing green banner: `Student 'Yash Patel' (25CS099) added successfully!` |
| **3** | **Successful update of student details** | Click "Edit" on a student, modify phone/course, submit | Tab **"3. Successful Update Student"** showing green banner: `Student details for 'Shubham Mendpara' updated successfully!` |
| **4** | **Search functionality** | Type "Shubham" in the search box and click "Apply Filter" | Tab **"4. Search Functionality"** displaying filtered matching row |
| **5** | **Course-wise filtering** | Select "B.Tech CSE" in course dropdown and filter | Tab **"5. Course-wise Filtering"** displaying only CSE students |
| **6** | **Delete confirmation message** | Click "Delete" button on an active student | Tab **"6. Delete Confirmation Message"** showing modal confirmation dialog asking before deletion |
| **7** | **Successful soft delete (student status changed to inactive)** | Confirm deletion of a record | Tab **"7. Soft Delete (Inactive Status)"** showing record status badge changed to `Inactive (Soft-Deleted)` |
| **8** | **Success/error messages** | Display active alert banners | Tab **"8. Success/Error Messages"** showing both green success and red error alert boxes |
| **9** | **Exported CSV file opened in Excel** | Click "Export to Excel (CSV)" button and open file in Excel | Tab **"9. Exported CSV in Excel"** showing table grid opened in Microsoft Excel with UTF-8 BOM |

> **Interactive All-in-One Screenshot Suite:**  
> 👉 **`http://localhost:5500/pages/practical11_preview.html`**  
> (Allows instant capture of all 9 test cases without needing a running MySQL instance).
