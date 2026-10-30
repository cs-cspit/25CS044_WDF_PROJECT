# StudentHub Portal

A semester-long **StudentHub** web portal — a static, multi-page front-end for a university academic information system. Built for WDFW lab Practicals 1–5 using HTML5, CSS3, and JavaScript.

## Lab Practical Coverage

| Practical | Topic | Status | Key Files |
|-----------|-------|--------|-----------|
| **P1** | Requirements, sitemap, wireframe, GitHub setup | Done | `docs/REQUIREMENTS.md`, `docs/SITEMAP.md`, `docs/WIREFRAME-NOTES.md` |
| **P2** | Semantic HTML5 + accessibility (10+ pages) | Done | All `pages/*.html`, `docs/ACCESSIBILITY-CHECKLIST.md` |
| **P3** | Responsive CSS Grid/Flexbox | Done | `css/responsive.css`, page-specific CSS |
| **P4** | JS DOM, events, interactivity | Done | `js/faq.js`, `js/modal.js`, `js/slider.js`, `js/ui.js`, `js/notification.js` |
| **P5** | Registration form + validation | Done | `pages/registration.html`, `js/registration.js` |

## Required Pages (Syllabus)

| Page | File | Role |
|------|------|------|
| Home | `index.html` | Guest |
| About | `pages/about.html` | Guest |
| Register | `pages/registration.html` | Guest |
| Login | `index.html#login` | Guest |
| Dashboard | `pages/dashboard.html` | Student |
| Events | `pages/event-entry.html` | Student |
| Profile | `pages/profile.html` | Student |
| Contact | `pages/contact.html` | Guest |
| Admin | `pages/admin.html` | Admin |
| FAQ | `pages/faq.html` | Guest |
| Feedback | `pages/feedback.html` | Student |

**Additional pages:** Attendance, Timetable, Fees, Subjects, Leave, Calendar, Address, Settings, Logout.

## User Roles

- **Guest** — Home, About, FAQ, Contact, Register, Login
- **Student** — Dashboard, Profile, academic modules, Feedback, Settings
- **Admin** — Admin panel for registrations and event approval

## Project Structure

```
.
├── README.md
├── index.html                      # Home + Login
├── docs/
│   ├── REQUIREMENTS.md             # Functional requirements (P1)
│   ├── SITEMAP.md                  # Site hierarchy (P1)
│   ├── WIREFRAME-NOTES.md          # Low-fidelity wireframe guide (P1)
│   └── ACCESSIBILITY-CHECKLIST.md  # A11y checklist (P2)
├── css/
│   ├── style.css                   # Global variables, reset, components
│   ├── login.css                   # Hero, login card (home page)
│   ├── responsive.css              # Mobile-first breakpoints (P3)
│   ├── public-pages.css            # About, FAQ, Contact, Profile, Admin
│   ├── dashboard.css               # Sidebar, topbar, dashboard grid
│   └── [page-specific CSS files]
├── js/
│   ├── faq.js                      # Collapsible FAQ (P4)
│   ├── modal.js                    # Address modal popup (P4)
│   ├── slider.js                   # Campus image slider (P4)
│   ├── notification.js             # Dismissible banner (P4)
│   ├── ui.js                       # Hamburger + theme toggle + localStorage (P4)
│   └── registration.js             # Form validation + regex (P5)
├── images/
└── pages/
    ├── about.html, contact.html, faq.html
    ├── registration.html, profile.html, feedback.html, admin.html
    └── [dashboard module pages]
```

## Getting Started

1. Clone or download this repository.
2. Open `index.html` in a browser — no build step required.
3. Login with any credentials (demo) → redirects to dashboard.

```bash
# Optional local server
npx serve .
```

## Key Features

### Practical 4 — JavaScript Interactivity
- Collapsible FAQ accordion
- Modal popup for address details
- Image/content slider with dots and arrows
- Dismissible notification banner
- Hamburger menu (mobile)
- Light/dark theme switcher with `localStorage`

### Practical 5 — Registration Validation
- Fields: name, email, mobile, password, confirm password, course, year, gender, terms
- Regex validation for name, email, mobile
- Password strength meter
- Real-time validation on blur
- Accessible error messages near each field

## Design System

CSS custom properties in `style.css`:

| Variable | Use |
|----------|-----|
| `--primary` | Sidebar, headings, buttons |
| `--secondary` | Accents, focus states |
| `--accent` | Highlights, active nav |
| `--background` | Page background |

## Git Commit Strategy (Per Practical)

```bash
git commit -m "P1: Add requirements, sitemap, wireframe notes, README"
git commit -m "P2: Add semantic HTML pages with accessibility structure"
git commit -m "P3: Add responsive CSS Grid and Flexbox layouts"
git commit -m "P4: Add JS interactivity (FAQ, modal, slider, theme)"
git commit -m "P5: Add registration form with regex validation"
```

## Key Questions — Quick Answers (Viva Prep)

1. **URL parts:** Protocol + domain + path + optional fragment/query (`https://site.edu/pages/dashboard.html#stats`)
2. **HTML in browser:** Parse → build DOM → fetch CSS/JS/images → render → execute scripts
3. **Page navigation:** Relative links between HTML files; sidebar for dashboard, top nav for public pages
4. **GitHub commits:** One meaningful commit per practical with descriptive messages

## Tech Stack

- HTML5 (semantic elements)
- CSS3 (Grid, Flexbox, media queries, CSS variables)
- JavaScript ES6+ (DOM, events, localStorage, regex)
## Practical Coverage (Summary)

- **Practical 6:** Events Management with JSON Fetch & Dynamic UI (`pages/event-entry.html`, `js/events-manager.js`)
- **Practical 7:** PHP Form Handling & Data Persistence (`php-form-handling/`)
- **Practical 8:** StudentHub Database Design (3NF), MySQL PDO & Prepared Statements (`studenthub-db/`, `database/`)
- **Practical 9:** Secure User Registration with MySQLi, BCRYPT Password Hashing, CSRF & Audit Logging (`practical-9/`)

## Author

**25CS044** — Charotar University of Science and Technology (CHARUSAT)

---
© 2026 StudentHub — Empowering Students Through Technology
