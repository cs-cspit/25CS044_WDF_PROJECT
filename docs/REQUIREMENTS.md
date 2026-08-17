# StudentHub — Functional Requirements

## 1. Problem Definition

Universities need a single web portal where students and administrators can access academic information, register for events, submit feedback, and manage profiles without visiting multiple offices or paper forms.

**StudentHub** is a semester-long static front-end project that models this portal using HTML5, CSS3, and JavaScript.

## 2. User Roles

| Role | Description | Primary Access |
|------|-------------|----------------|
| **Guest** | Visitor not logged in | Home, About, FAQ, Contact, Register, Login |
| **Student** | Enrolled student | Dashboard, Profile, Attendance, Timetable, Fees, Events, Feedback, Settings |
| **Admin** | University staff | Admin panel, event approval, registration overview, announcements |

## 3. Key Modules

1. **Authentication** — Login and registration (client-side validation only)
2. **Dashboard** — Academic overview, stats, quick access
3. **Academic Records** — Attendance, timetable, subjects, fees, calendar
4. **Student Services** — Leave application, events, address
5. **Communication** — FAQ, contact, feedback, announcements
6. **Administration** — Admin dashboard for staff actions

## 4. Minimum Pages (11+)

| # | Page | File | Role |
|---|------|------|------|
| 1 | Home | `index.html` | Guest |
| 2 | About | `pages/about.html` | Guest |
| 3 | Register | `pages/registration.html` | Guest |
| 4 | Login | `index.html#login` | Guest |
| 5 | Dashboard | `pages/dashboard.html` | Student |
| 6 | Events | `pages/event-entry.html` | Student |
| 7 | Profile | `pages/profile.html` | Student |
| 8 | Contact | `pages/contact.html` | Guest |
| 9 | Admin | `pages/admin.html` | Admin |
| 10 | FAQ | `pages/faq.html` | Guest |
| 11 | Feedback | `pages/feedback.html` | Student |

**Additional pages:** Attendance, Timetable, Fees, Subjects, Leave, Calendar, Address, Settings, Logout.

## 5. Navigation Flow

```
Guest Flow:
  Home → About / FAQ / Contact → Register → Login → Dashboard

Student Flow:
  Dashboard → Profile / Attendance / Timetable / Fees / Events / Feedback / Settings → Logout → Home

Admin Flow:
  Login → Admin Panel → Manage events & registrations → Logout
```

## 6. Non-Functional Requirements

- Semantic HTML5 structure (`header`, `nav`, `main`, `section`, `article`, `footer`)
- Accessibility-ready labels, alt text, ARIA attributes, skip-to-content link
- Responsive layout (mobile-first) using CSS Grid, Flexbox, and media queries
- JavaScript interactivity: FAQ accordion, modal, slider, theme toggle, hamburger menu, form validation
- Version control via Git/GitHub with meaningful commits per practical

## 7. Out of Scope (Current Phase)

- Backend database and real authentication
- REST API integration
- Payment gateway for fees
