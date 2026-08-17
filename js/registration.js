document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registration-form');
    if (!form) return;

    const validators = {
        name: value => /^[A-Za-z\s]{3,}$/.test(value.trim()),
        email: value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        mobile: value => /^[0-9]{10}$/.test(value),
        password: value => value.length >= 8,
    };

    const elems = {
        name: document.getElementById('name'),
        email: document.getElementById('email'),
        mobile: document.getElementById('mobile'),
        password: document.getElementById('password'),
        confirm: document.getElementById('confirm'),
        course: document.getElementById('course'),
        year: document.getElementById('year'),
        terms: document.getElementById('terms'),
        gender: form.querySelectorAll('input[name="gender"]'),
    };

    const errors = {
        name: document.getElementById('err-name'),
        email: document.getElementById('err-email'),
        mobile: document.getElementById('err-mobile'),
        password: document.getElementById('err-password'),
        confirm: document.getElementById('err-confirm'),
        course: document.getElementById('err-course'),
        year: document.getElementById('err-year'),
        gender: document.getElementById('err-gender'),
        terms: document.getElementById('err-terms'),
    };

    function clearErrors() {
        Object.values(errors).forEach(e => { if (e) e.textContent = ''; });
    }

    function getSelectedGender() {
        for (const g of elems.gender) {
            if (g.checked) return g.value;
        }
        return null;
    }

    function checkPasswordStrength(pw) {
        let score = 0;
        if (pw.length >= 8) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score; // 0-4
    }

    const strengthBar = document.getElementById('strength-bar');
    elems.password.addEventListener('input', function () {
        const s = checkPasswordStrength(this.value);
        const pct = (s / 4) * 100;
        if (strengthBar) strengthBar.style.width = pct + '%';
        if (s <= 1) strengthBar.style.background = 'red';
        else if (s === 2) strengthBar.style.background = 'orange';
        else if (s === 3) strengthBar.style.background = '#2d9f3b';
        else strengthBar.style.background = '#0b8f4a';
    });

    // realtime validation
    elems.email.addEventListener('blur', () => {
        if (!validators.email(elems.email.value)) errors.email.textContent = 'Enter a valid email.';
        else errors.email.textContent = '';
    });

    elems.mobile.addEventListener('blur', () => {
        if (!validators.mobile(elems.mobile.value)) errors.mobile.textContent = 'Enter a 10-digit mobile number.';
        else errors.mobile.textContent = '';
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        let valid = true;

        if (!validators.name(elems.name.value)) {
            errors.name.textContent = 'Please enter your full name (letters only).';
            valid = false;
        }
        if (!validators.email(elems.email.value)) {
            errors.email.textContent = 'Please enter a valid email address.';
            valid = false;
        }
        if (!validators.mobile(elems.mobile.value)) {
            errors.mobile.textContent = 'Please enter a 10-digit mobile number.';
            valid = false;
        }
        if (!validators.password(elems.password.value)) {
            errors.password.textContent = 'Password must be at least 8 characters.';
            valid = false;
        }
        if (elems.password.value !== elems.confirm.value) {
            errors.confirm.textContent = 'Passwords do not match.';
            valid = false;
        }
        if (!elems.course.value) {
            errors.course.textContent = 'Select a course.';
            valid = false;
        }
        if (!elems.year.value) {
            errors.year.textContent = 'Select year.';
            valid = false;
        }
        if (!getSelectedGender()) {
            errors.gender.textContent = 'Select gender.';
            valid = false;
        }
        if (!elems.terms.checked) {
            errors.terms.textContent = 'You must accept the terms.';
            valid = false;
        }

        if (!valid) {
            const firstError = form.querySelector('.error:not(:empty)');
            firstError?.previousElementSibling?.focus();
            return;
        }

        // On successful validation - show a simple success and redirect to dashboard
        alert('Registration successful (demo). Redirecting to dashboard...');
        window.location.href = '../pages/dashboard.html';
    });
});
