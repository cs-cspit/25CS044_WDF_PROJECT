# StudentHub — Accessibility Checklist

| Check | Status | Notes |
|-------|--------|-------|
| Semantic landmarks (`header`, `nav`, `main`, `footer`) | Yes | Used on public and dashboard pages |
| Single `<h1>` per page | Yes | Each page has one primary heading |
| Form labels linked with `for` / `id` | Yes | Registration, contact, feedback forms |
| Image `alt` text | Yes | Logo, icons, slider images |
| Skip-to-content link | Yes | Public pages include `.skip-link` |
| Keyboard navigation | Yes | FAQ, modal, hamburger, theme toggle |
| `aria-expanded` on collapsible FAQ | Yes | `js/faq.js` |
| `aria-label` on icon buttons | Yes | Hamburger, slider, modal close |
| Color contrast (text on background) | Yes | Primary `#003366` on white |
| Focus visible on interactive elements | Yes | CSS `:focus` styles in `style.css` |
| Error messages near fields | Yes | Registration validation |
| `aria-current="page"` on active nav | Yes | Sidebar and public nav |

## Browser Testing

1. Open DevTools → Lighthouse → Accessibility audit
2. Tab through interactive elements on each page
3. Test with screen reader (NVDA/VoiceOver) on login and registration forms
