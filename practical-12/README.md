# Practical 12: Event Management with File Upload (Event Poster) & Admin Controls

**StudentHub College Event Portal**  
**Module:** Practical 12 - Event Management, Secure Poster Image File Uploads, Admin Controls & Unauthorized Access Restrictions.

---

## 📂 Project Structure

```
practical-12/
├── schema.sql              # Database schema for events table with poster_path, category, max_seats
├── db.php                  # Secure MySQLi connection with utf8mb4 charset & strict exception handling
├── auth_admin.php          # Admin authentication & role-based access restriction middleware
├── login.php               # Admin login portal with one-click role demo switches
├── admin_dashboard.php     # Full event management dashboard (Create with Poster, Read, Update, Delete)
├── uploads/                # Directory storing uploaded event poster images (.jpg, .png, .webp)
├── create_sample_posters.py # Script generating placeholder poster image assets
├── README.md               # Setup steps, XAMPP instructions & screenshot checklist
└── tests/
    └── TEST_REPORT.md      # Systematic 10-point test matrix covering all required scenarios
```

---

## 🚀 Setup & Execution in XAMPP

1. **Start Services:** Start **Apache** and **MySQL** in XAMPP Control Panel.
2. **Import Database:** In phpMyAdmin (`http://localhost/phpmyadmin/`), select database `studenthub` and import `practical-12/schema.sql`.
3. **Deploy:** Copy folder `practical-12` to `C:\xampp\htdocs\studenthub\practical-12`.
4. **Access in Browser:** Open:
   ```
   http://localhost/studenthub/practical-12/login.php
   ```

---

## 📸 Checklist for the 10 Required Screenshots

| # | Screenshot Required | How to Capture | URL / Tab in Preview |
|:---|:---|:---|:---|
| **1** | **Admin login and dashboard** | Log in with admin credentials and view overview | Tab **"1. Admin Login & Dashboard"** |
| **2** | **Successful event creation** | Create new event with valid details and poster | Tab **"2. Successful Event Creation"** showing green banner: `Successful event creation: 'AI & Deep Learning Symposium' scheduled...` |
| **3** | **Event list with uploaded posters** | View events table with thumbnail images | Tab **"3. Event List with Posters"** showing poster thumbnails alongside event rows |
| **4** | **Successful event update** | Edit event venue/capacity and save | Tab **"4. Successful Event Update"** showing green banner: `Successful event update: 'Git & GitHub Hands-on Workshop' details refreshed...` |
| **5** | **Successful event deletion** | Delete an event and confirm removal | Tab **"5. Successful Event Deletion"** showing green banner confirming permanent deletion |
| **6** | **Valid poster upload** | Upload valid JPG/PNG file $\le 2$ MB | Tab **"6. Valid Poster Upload"** showing MIME verification and hash storage |
| **7** | **Invalid file type validation** | Attempt to upload `.pdf` or non-image file | Tab **"7. Invalid File Type Validation"** showing red alert: `Invalid file type validation: Only JPG, PNG, and WebP image files are allowed.` |
| **8** | **File size limit validation** | Attempt to upload image $> 2$ MB | Tab **"8. File Size Limit Validation"** showing red alert: `File size limit exceeded: The poster must not exceed 2 MB. Uploaded file was 4.82 MB.` |
| **9** | **Success and error messages** | Display active alert banners | Tab **"9. Success & Error Messages"** showing both green success and red error alert boxes |
| **10** | **Unauthorized user access restriction** | Access `admin_dashboard.php` as a student user | Tab **"10. Unauthorized User Restriction"** showing 403 Forbidden: `Unauthorized User Access Restriction - Access Denied!` |

> **Interactive All-in-One Screenshot Suite:**  
> 👉 **`http://localhost:5500/pages/practical12_preview.html`**  
> (Allows instant capture of all 10 test cases directly from your browser).
