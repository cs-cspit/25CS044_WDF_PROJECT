# PHP Form Handling — College Assignment Mini Project

A lightweight, robust student registration mini-project built strictly with **pure PHP, HTML5, and modern CSS** (no external frameworks, no database required). Form entries are sanitized, validated server-side, and persisted to both **CSV** and **JSON** files with concurrency locking and Post/Redirect/Get (PRG) duplicate submission prevention.

---

## 📂 Project Structure

```
php-form-handling/
├── index.php                 # Form markup, POST handling, validation, file storage, records table
├── style.css                 # Clean, modern, responsive CSS stylesheet
├── README.md                 # Setup guide, test cases, and screenshot manual
└── data/
    ├── registrations.csv     # Comma-separated values store (auto-created if missing)
    └── registrations.json    # JSON array store (auto-created if missing)
```

---

## 🚀 How to Run on XAMPP (localhost)

### Option 1: Using XAMPP
1. Copy the folder `php-form-handling` into your XAMPP web root directory:
   - Default Windows Path: `C:\xampp\htdocs\php-form-handling`
2. Open the **XAMPP Control Panel** and click **Start** next to **Apache**.
3. Open your browser and navigate to:
   ```
   http://localhost/php-form-handling/index.php
   ```

### Option 2: Using PHP Built-In Server (CLI)
If PHP is added to your terminal PATH:
```bash
cd php-form-handling
php -S localhost:8000
```
Then visit: `http://localhost:8000/index.php`

---

## 🧪 Validation & Test Cases

| Test Case | Inputs | Expected Output |
|---|---|---|
| **Test 1: Empty Submission** | Leave all fields blank & submit | Red summary box at top + individual field error messages under each field. |
| **Test 2: Invalid Name** | Name: `John123!` | Error: *"Name must be 3 to 50 characters long and contain only letters and spaces."* |
| **Test 3: Invalid Email** | Email: `student@notvalid` | Error: *"Please enter a valid email address."* |
| **Test 4: Invalid Phone** | Phone: `98765` or `98765abcde` | Error: *"Phone number must be exactly 10 digits."* |
| **Test 5: Invalid Age** | Age: `14` or `75` | Error: *"Age must be a number between 16 and 60."* |
| **Test 6: Valid Submission** | Name: `Shubham Mendpara`<br>Email: `shubham@charusat.edu.in`<br>Phone: `9876543210`<br>Age: `20`<br>Gender: `Male`<br>Course: `Computer Engineering` | Green banner: *"Registration submitted successfully! Your ID is REG-XXXXXX."* Form clears, record appends to CSV and JSON, and table updates. |

---

## 📸 Steps to Capture the 4 Required Screenshots

1. **Screenshot 1 — Form UI:**
   - Open `http://localhost/php-form-handling/index.php`.
   - Take a full-screen or browser window capture of the clean form showing all inputs (Name, Email, Phone, Age, Gender radios, Course select, Message textarea, and Submit button).

2. **Screenshot 2 — Validation / Error Message:**
   - Enter invalid inputs (e.g., leave Name empty, type an invalid email `bademail`, enter phone `123`, age `99`).
   - Click **Submit Registration (POST)**.
   - Capture the red error summary box at the top and the specific red error notices below each invalid field. Notice sticky values remain in valid fields!

3. **Screenshot 3 — Successful Submission:**
   - Enter valid data across all fields and click **Submit Registration (POST)**.
   - Capture the bright green success banner displaying:  
     `✅ Success! Registration submitted successfully! Your ID is REG-XXXXXX.`
   - Point out that the form resets and the new record appears immediately in the "Submitted Records" table at the bottom.

4. **Screenshot 4 — Stored CSV & JSON Data:**
   - Open `data/registrations.csv` in VS Code or Microsoft Excel to show the CSV header row and comma-separated records.
   - Open `data/registrations.json` side-by-side in VS Code showing formatted JSON objects with timestamps and unique IDs.
