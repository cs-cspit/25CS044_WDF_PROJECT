# StudentHub — Sitemap

```
StudentHub Portal
│
├── Public (Guest)
│   ├── Home ........................ index.html
│   ├── About ....................... pages/about.html
│   ├── FAQ ......................... pages/faq.html
│   ├── Contact ..................... pages/contact.html
│   ├── Register .................... pages/registration.html
│   └── Login ....................... index.html#login
│
├── Student (Authenticated)
│   ├── Dashboard ................... pages/dashboard.html
│   ├── Profile ..................... pages/profile.html
│   ├── Attendance .................. pages/attendance.html
│   ├── Timetable ................... pages/timetable.html
│   ├── Fees ........................ pages/fees.html
│   ├── Subjects .................... pages/subject-information.html
│   ├── Leave Application ........... pages/leave-application.html
│   ├── Events ...................... pages/event-entry.html
│   ├── Calendar .................... pages/calendar.html
│   ├── Address ..................... pages/address.html
│   ├── Feedback .................... pages/feedback.html
│   ├── Settings .................... pages/settings.html
│   └── Logout ...................... pages/logout.html
│
└── Admin
    └── Admin Panel ................. pages/admin.html
```

## URL Structure

| Part | Example | Purpose |
|------|---------|---------|
| **Protocol** | `https://` | Secure transfer (production) |
| **Domain** | `studenthub.university.edu` | Server hostname |
| **Path** | `/pages/dashboard.html` | File/folder on server |
| **Fragment** | `#login` | In-page anchor (login section) |

**Local development example:**
`file:///E:/SEM-3/WDFW/html/HTML LAB PROJECT/index.html`

## Page Linking Strategy

- Public pages share a top navigation bar (`header > nav`)
- Dashboard pages share a left sidebar (`aside.sidebar`) and main content area
- Relative paths: `../index.html` from `pages/`, `pages/dashboard.html` from root
- Active nav item marked with `class="active"` and `aria-current="page"`
