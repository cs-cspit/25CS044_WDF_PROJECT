document.addEventListener('DOMContentLoaded', function () {
    const hamburger = document.getElementById('hamburger');
    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;
    const body = document.body;
    const mainNav = document.getElementById('main-nav');

    // Hamburger menu
    if (hamburger && mainNav) {
        hamburger.addEventListener('click', function () {
            const isOpen = body.classList.toggle('nav-open');
            hamburger.setAttribute('aria-expanded', String(isOpen));
        });

        // Close menu when clicking a link
        mainNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function () {
                if (body.classList.contains('nav-open')) {
                    body.classList.remove('nav-open');
                    hamburger.setAttribute('aria-expanded', 'false');
                }
            });
        });

        // Close on escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && body.classList.contains('nav-open')) {
                body.classList.remove('nav-open');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });

        // Close when clicking outside the nav on mobile
        document.addEventListener('click', function (e) {
            if (!body.classList.contains('nav-open')) return;
            if (!mainNav.contains(e.target) && e.target !== hamburger && !hamburger.contains(e.target)) {
                body.classList.remove('nav-open');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Theme toggle (light/dark)
    const THEME_KEY = 'site-theme';

    function applyTheme(theme) {
        if (theme === 'dark') {
            html.classList.add('dark');
            body.classList.add('dark');
            if (themeToggle) {
                themeToggle.textContent = '☀️';
                themeToggle.setAttribute('aria-pressed', 'true');
            }
        } else {
            html.classList.remove('dark');
            body.classList.remove('dark');
            if (themeToggle) {
                themeToggle.textContent = '🌙';
                themeToggle.setAttribute('aria-pressed', 'false');
            }
        }
    }

    // Initialize theme from localStorage or system preference
    const saved = localStorage.getItem(THEME_KEY);
    if (saved) {
        applyTheme(saved);
    } else {
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(prefersDark ? 'dark' : 'light');
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const isDark = html.classList.contains('dark') || body.classList.contains('dark');
            const next = isDark ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem(THEME_KEY, next);
        });
    }
});
