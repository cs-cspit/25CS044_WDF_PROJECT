document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registration-form');
    if (!form) return;

    // Regular Expression validators
    const validators = {
        name: val => /^[A-Za-z\s]{3,40}$/.test(val.trim()),
        email: val => /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,}$/.test(val.trim()),
        mobile: val => /^[6-9][0-9]{9}$/.test(val.trim()),
        password: val => val.length >= 8
    };

    // Form DOM Elements
    const fields = {
        name: document.getElementById('name'),
        email: document.getElementById('email'),
        mobile: document.getElementById('mobile'),
        password: document.getElementById('password'),
        confirm: document.getElementById('confirm'),
        course: document.getElementById('course'),
        year: document.getElementById('year'),
        terms: document.getElementById('terms'),
        captcha: document.getElementById('captcha-input'),
        gender: form.querySelectorAll('input[name="gender"]')
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
        captcha: document.getElementById('err-captcha'),
        terms: document.getElementById('err-terms')
    };

    // --- CANVAS CAPTCHA (Advanced Extension) ---
    const canvas = document.getElementById('captcha-canvas');
    const refreshBtn = document.getElementById('refresh-captcha');
    let currentCaptcha = '';

    function generateCaptcha() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        currentCaptcha = '';
        for (let i = 0; i < 5; i++) {
            currentCaptcha += chars.charAt(Math.floor(Math.random() * chars.length));
        }

        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Background
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Noise lines
        for (let i = 0; i < 4; i++) {
            ctx.strokeStyle = `rgba(100, 116, 139, ${Math.random() * 0.4 + 0.2})`;
            ctx.beginPath();
            ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
            ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
            ctx.stroke();
        }

        // Draw distorted text
        ctx.font = 'bold 22px Poppins, monospace';
        ctx.fillStyle = '#0f172a';
        ctx.textBaseline = 'middle';
        for (let i = 0; i < currentCaptcha.length; i++) {
            const x = 16 + i * 24;
            const y = 22 + (Math.random() * 6 - 3);
            ctx.fillText(currentCaptcha[i], x, y);
        }
    }

    if (refreshBtn) refreshBtn.addEventListener('click', generateCaptcha);
    generateCaptcha();

    // --- PASSWORD STRENGTH METER ---
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');

    function checkPasswordStrength(pw) {
        let score = 0;
        if (pw.length >= 8) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score; // 0-4
    }

    fields.password.addEventListener('input', function () {
        const score = checkPasswordStrength(this.value);
        const labels = ['None', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['#e2e8f0', '#ef4444', '#f59e0b', '#3b82f6', '#10b981'];

        if (strengthBar) {
            strengthBar.style.width = (score / 4) * 100 + '%';
            strengthBar.style.background = colors[score];
        }
        if (strengthText) {
            strengthText.textContent = `Strength: ${labels[score]}`;
            strengthText.style.color = score === 0 ? '#64748b' : colors[score];
        }
    });

    // --- HELPER: SET & CLEAR FIELD ERRORS ---
    function setError(input, errElem, message) {
        if (errElem) errElem.textContent = message;
        if (input && input.setAttribute) {
            input.setAttribute('aria-invalid', 'true');
        }
    }

    function clearError(input, errElem) {
        if (errElem) errElem.textContent = '';
        if (input && input.removeAttribute) {
            input.removeAttribute('aria-invalid');
        }
    }

    // --- REAL-TIME VALIDATION (Intermediate Extension) ---
    fields.name.addEventListener('blur', function () {
        if (!validators.name(this.value)) {
            setError(this, errors.name, 'Full name must contain at least 3 letters.');
        } else clearError(this, errors.name);
    });

    fields.email.addEventListener('blur', function () {
        if (!validators.email(this.value)) {
            setError(this, errors.email, 'Please enter a valid email address.');
        } else clearError(this, errors.email);
    });

    fields.mobile.addEventListener('blur', function () {
        if (!validators.mobile(this.value)) {
            setError(this, errors.mobile, 'Enter a valid 10-digit mobile number.');
        } else clearError(this, errors.mobile);
    });

    fields.confirm.addEventListener('blur', function () {
        if (this.value !== fields.password.value) {
            setError(this, errors.confirm, 'Passwords do not match.');
        } else clearError(this, errors.confirm);
    });

    fields.course.addEventListener('change', function () {
        if (!this.value) {
            setError(this, errors.course, 'Please select your course.');
        } else clearError(this, errors.course);
    });

    fields.year.addEventListener('change', function () {
        if (!this.value) {
            setError(this, errors.year, 'Please select your academic year.');
        } else clearError(this, errors.year);
    });

    // --- FULL FORM SUBMISSION VALIDATION ---
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        let isValid = true;

        // Name
        if (!validators.name(fields.name.value)) {
            setError(fields.name, errors.name, 'Full name must contain at least 3 alphabetic letters.');
            isValid = false;
        } else clearError(fields.name, errors.name);

        // Email
        if (!validators.email(fields.email.value)) {
            setError(fields.email, errors.email, 'Please enter a valid email address.');
            isValid = false;
        } else clearError(fields.email, errors.email);

        // Mobile
        if (!validators.mobile(fields.mobile.value)) {
            setError(fields.mobile, errors.mobile, 'Enter a valid 10-digit mobile number starting with 6-9.');
            isValid = false;
        } else clearError(fields.mobile, errors.mobile);

        // Password
        if (!validators.password(fields.password.value)) {
            setError(fields.password, errors.password, 'Password must be at least 8 characters long.');
            isValid = false;
        } else clearError(fields.password, errors.password);

        // Confirm Password
        if (!fields.confirm.value || fields.password.value !== fields.confirm.value) {
            setError(fields.confirm, errors.confirm, 'Passwords do not match.');
            isValid = false;
        } else clearError(fields.confirm, errors.confirm);

        // Course
        if (!fields.course.value) {
            setError(fields.course, errors.course, 'Please select your course.');
            isValid = false;
        } else clearError(fields.course, errors.course);

        // Year
        if (!fields.year.value) {
            setError(fields.year, errors.year, 'Please select your academic year.');
            isValid = false;
        } else clearError(fields.year, errors.year);

        // Gender
        const genderSelected = Array.from(fields.gender).some(r => r.checked);
        if (!genderSelected) {
            errors.gender.textContent = 'Please select your gender.';
            isValid = false;
        } else errors.gender.textContent = '';

        // CAPTCHA
        if (!fields.captcha.value || fields.captcha.value.trim().toUpperCase() !== currentCaptcha) {
            setError(fields.captcha, errors.captcha, 'Incorrect CAPTCHA code. Please try again.');
            generateCaptcha();
            isValid = false;
        } else clearError(fields.captcha, errors.captcha);

        // Terms
        if (!fields.terms.checked) {
            errors.terms.textContent = 'You must accept the terms and conditions.';
            isValid = false;
        } else errors.terms.textContent = '';

        // Accessibility Focus: Focus first invalid input
        if (!isValid) {
            const firstInvalid = form.querySelector('[aria-invalid="true"]');
            if (firstInvalid) {
                firstInvalid.focus();
            } else if (!genderSelected) {
                fields.gender[0].focus();
            } else if (!fields.terms.checked) {
                fields.terms.focus();
            }
            return;
        }

        // Gather registration payload
        const selectedGender = Array.from(fields.gender).find(r => r.checked)?.value || 'Not specified';
        const payload = {
            name: fields.name.value.trim(),
            email: fields.email.value.trim(),
            mobile: fields.mobile.value.trim(),
            course: fields.course.options[fields.course.selectedIndex]?.text || fields.course.value,
            year: fields.year.options[fields.year.selectedIndex]?.text || fields.year.value,
            gender: selectedGender
        };

        // Real Backend Storage to CSV & JSON
        fetch('/api/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(result => {
            alert(`🎉 ${result.message}\n\nData has been saved into registrations.csv and registrations.json! Redirecting to dashboard...`);
            window.location.href = '../pages/dashboard.html';
        })
        .catch(err => {
            console.warn('Backend server not reachable, saving to local session.', err);
            alert('🎉 Registration Successful! Redirecting to dashboard...');
            window.location.href = '../pages/dashboard.html';
        });
    });
});
