# Student Portal

A static, multi-page front-end for a university **Student Portal** — an academic information system where students log in and access their dashboard, attendance, timetable, fees, subjects, leave applications, events, calendar, campus address, and account settings.

## Pages

| Page | File | Description |
|---|---|---|
| Landing / Login | `index.html` | Hero banner + Student ID/password login form |
| Dashboard | `pages/dashboard.html` | Profile card, stats (attendance, CGPA, credits, fees due), today's timetable, announcements, quick-access cards |
| Attendance | `pages/attendance.html` | Per-subject attendance table (conducted/attended/%/status) |
| Timetable | `pages/timetable.html` | Weekly Mon–Fri class schedule |
| Fees | `pages/fees.html` | Total/paid/pending summary cards + semester-wise payment table |
| Subjects | `pages/subject-information.html` | Subject code, name, faculty, credits table |
| Leave Application | `pages/leave-application.html` | Form to request leave (type, dates, reason) |
| Events | `pages/event-entry.html` | Campus events table (date, venue, status) |
| Calendar | `pages/calendar.html` | Academic calendar / key dates table |
| Address | `pages/address.html` | Campus contact info + embedded Google Map |
| Settings | `pages/settings.html` | Update name, email, mobile, password |
| Logout | `pages/logout.html` | JS `confirm()` on load → redirects to `../index.html`, or goes back if cancelled |

All dashboard pages share a fixed left **sidebar** (logo + full nav list) and a **topbar** with the page title; `dashboard.html` additionally shows a profile block in its topbar.

## Project Structure

```
.
├── README.md                       # Project documentation (this file)
├── index.html
├── css/
│   ├── style.css                # Global variables, reset, header/footer, base tables/forms
│   ├── login.css                 # Hero, login card, about/services/announcements (index page)
│   ├── responsive.css            # Breakpoints (shared across most pages)
│   ├── dashboard.css              # Sidebar, topbar, profile, stats, dashboard grid + logout-box
│   ├── attendance.css             # Self-contained sidebar/topbar/table styles + attendance table
│   ├── timetable.css              # Weekly timetable table box
│   ├── fees.css                    # Fee summary cards (paid/pending)
│   ├── leave.css                   # Leave application form
│   ├── calendar.css                # Academic calendar table
│   ├── event.css                    # Events table
│   ├── address.css                  # Address card + map iframe
│   ├── subject.css                   # Subjects page (currently empty)
│   └── settings.css                  # Account settings form
├── images/
│   ├── logo.png
│   ├── campus.png                 # Hero background (index.html)
│   ├── profile.png                 # Dashboard profile avatar
│   └── icons/
│       ├── address.png
│       ├── attendance.png
│       ├── calendar.png
│       ├── event.png
│       ├── fees.png
│       ├── leave.png
│       ├── logout.png
│       ├── settings.png
│       ├── subject.png
│       └── timetable.png
└── pages/
    ├── dashboard.html
    ├── attendance.html
    ├── timetable.html
    ├── fees.html
    ├── subject-information.html
    ├── leave-application.html
    ├── event-entry.html
    ├── calendar.html
    ├── address.html
    ├── settings.html
    └── logout.html
```

## Getting Started

1. Clone or download this repository, keeping the `css/`, `images/`, and `pages/` folders alongside `index.html`.
2. Open `index.html` in your browser — static site, no build step required.
3. Log in with any values (the form currently posts to `pages/dashboard.html` with no real authentication) to browse the dashboard pages.

```bash
# Optional: serve locally
npx serve .
# or
python3 -m http.server
```

## Design System

CSS custom properties defined in `style.css`:

| Variable | Value | Use |
|---|---|---|
| `--primary` | `#003366` | Sidebar, headings, buttons |
| `--secondary` | `#00509d` | Accents, focus states |
| `--accent` | `#f5b400` | Highlights, active nav links, hover states |
| `--background` | `#f5f7fb` | Page background |
| `--white` | `#ffffff` | Cards, panels |
| `--text` | `#222` | Body text |
| `--gray` | `#666` | Secondary text |
| `--border` | `#d9e2ec` | Input borders |
| `--shadow` | `0 8px 25px rgba(0,0,0,.08)` | Card elevation |

## Known Issues

- **Broken nav link:** `pages/leave-application.html` links to `attendence.html` (typo) instead of `attendance.html`.
- **`logout.html` has no stylesheet links**, so the `.logout-box` styles defined in `dashboard.css` never apply to it — the page currently just fires a JS `confirm()` on load with a blank body.
- **Inconsistent sidebar markup:** some pages include the logo `<img>` in the sidebar (`address.html`, `dashboard.html`, `fees.html`, etc.) while others (`calendar.html`, `event-entry.html`) only render the `<h2>` title, so the sidebar logo is missing on those pages.
- **Inconsistent "active" nav highlighting:** `dashboard.html` and `address.html` don't mark any sidebar link as `active`, unlike `attendance.html`, `calendar.html`, `event-entry.html`, `leave-application.html`, and `settings.html`.
- **Duplicated styles:** `attendance.css` and `calendar.css` redefine their own sidebar/topbar/table rules (with a slightly different palette, `#0b3b70` vs `#003366`) instead of relying on the shared `dashboard.css`, causing minor visual inconsistency between pages.
- **No real backend:** login, leave application, settings, and fee data are all static/hardcoded — no form actually submits or persists data.

## Tech Stack

- **HTML5**
- **CSS3** (custom stylesheets, CSS Grid/Flexbox, CSS variables, no framework)
- **Vanilla JavaScript** (inline `confirm()`/redirect on the logout page)
- **Google Fonts** — [Poppins](https://fonts.google.com/specimen/Poppins)

## Roadmap / TODO

- [ ] Fix the `attendence.html` → `attendance.html` typo in `leave-application.html`
- [ ] Add stylesheet links to `logout.html` so the logout confirmation card renders correctly
- [ ] Standardize sidebar markup (logo present, active-state) across all pages
- [ ] Finish `subject.css` styling for the Subjects page
- [ ] Consolidate `attendance.css`/`calendar.css` sidebar/topbar/table rules into shared `dashboard.css` styles
- [ ] Wire up login, leave application, and settings forms to a real backend/auth system
- [ ] Implement "Forgot Password?" flow on `index.html`

## License
25cs044


---
© 2026 Student Portal — Empowering Students Through Technology
