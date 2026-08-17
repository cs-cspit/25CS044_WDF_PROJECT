# StudentHub — Wireframe Notes (Low-Fidelity)

Use Draw.io or Figma to recreate these layouts. Screenshots of your diagrams should be submitted with Practical 1.

## Home Page (`index.html`)

```
+--------------------------------------------------+
| [Logo] StudentHub          [Nav links] [Theme]   |
+--------------------------------------------------+
|  HERO: Welcome text          |  LOGIN CARD      |
|  + Explore button             |  ID + Password  |
+--------------------------------------------------+
| ABOUT | NOTIFICATION BANNER                      |
+--------------------------------------------------+
| SERVICES (4-card grid)                           |
+--------------------------------------------------+
| ANNOUNCEMENTS list                               |
+--------------------------------------------------+
| CAMPUS SLIDER                                    |
+--------------------------------------------------+
| FAQ (collapsible items)                          |
+--------------------------------------------------+
| CONTACT CTA                                      |
+--------------------------------------------------+
| FOOTER                                           |
+--------------------------------------------------+
```

## Dashboard Page (`pages/dashboard.html`)

```
+----------+---------------------------------------+
| SIDEBAR  | TOPBAR: Title + Profile mini-card     |
| - Logo   +---------------------------------------+
| - Nav    | Welcome section                       |
| - Links  | [Stats grid: 4 cards]                 |
|          | [Today's timetable | Announcements]   |
|          | Quick access icon cards               |
+----------+---------------------------------------+
```

## Registration Page (`pages/registration.html`)

```
+--------------------------------------------------+
| Header (logo)                                    |
+--------------------------------------------------+
| Registration Form (centered card)                |
|  Name | Email                                    |
|  Mobile                                          |
|  Password | Confirm + strength bar              |
|  Course | Year                                   |
|  Gender (radio)                                  |
|  Terms checkbox                                  |
|  [Register] [Cancel]                             |
+--------------------------------------------------+
```

## Responsive Breakpoints

| Viewport | Layout change |
|----------|---------------|
| Desktop (>992px) | Sidebar + multi-column grids |
| Tablet (768–992px) | Stacked hero, 2-column cards |
| Mobile (<768px) | Hamburger nav, single column, full-width forms |
