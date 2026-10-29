/**
 * Practical 9: Frontend Validation & Password Strength Checker
 * File: assets/js/validate.js
 *
 * Rules:
 * - Empty field check
 * - Email format validation
 * - Password minimum length 8
 * - Password must contain at least 1 letter and 1 number
 * - Password and Confirm Password match
 * - Username 3-30 characters (letters, numbers, underscores only)
 * - Full Name 2-100 characters
 * - Inline feedback & blocking submission on invalid inputs
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registration-form');
    if (!form) return;

    const fullNameInput = document.getElementById('full_name');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');

    // Utility: Show error
    function setError(input, message) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        const feedback = input.parentElement.querySelector('.invalid-feedback') || document.getElementById(`${input.id}-feedback`);
        if (feedback) {
            feedback.textContent = message;
            feedback.classList.add('active');
        }
        return false;
    }

    // Utility: Mark valid
    function setSuccess(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        const feedback = input.parentElement.querySelector('.invalid-feedback') || document.getElementById(`${input.id}-feedback`);
        if (feedback) {
            feedback.textContent = '';
            feedback.classList.remove('active');
        }
        return true;
    }

    // 1. Validate Full Name
    function validateFullName() {
        const val = fullNameInput.value.trim();
        if (val === '') {
            return setError(fullNameInput, 'Full Name is required.');
        }
        if (val.length < 2 || val.length > 100) {
            return setError(fullNameInput, 'Full Name must be between 2 and 100 characters.');
        }
        return setSuccess(fullNameInput);
    }

    // 2. Validate Username
    function validateUsername() {
        const val = usernameInput.value.trim();
        const usernameRegex = /^[a-zA-Z0-9_]{3,30}$/;
        if (val === '') {
            return setError(usernameInput, 'Username is required.');
        }
        if (!usernameRegex.test(val)) {
            return setError(usernameInput, 'Username must be 3-30 characters (letters, numbers, underscore only).');
        }
        return setSuccess(usernameInput);
    }

    // 3. Validate Email
    function validateEmail() {
        const val = emailInput.value.trim();
        // RFC 5322 standard compliant regex
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (val === '') {
            return setError(emailInput, 'Email address is required.');
        }
        if (!emailRegex.test(val)) {
            return setError(emailInput, 'Please enter a valid email address (e.g., student@charusat.edu.in).');
        }
        return setSuccess(emailInput);
    }

    // 4. Validate Password & Strength
    function validatePassword() {
        const val = passwordInput.value;
        if (val === '') {
            updateStrengthMeter('');
            return setError(passwordInput, 'Password is required.');
        }
        if (val.length < 8) {
            updateStrengthMeter(val);
            return setError(passwordInput, 'Password must be at least 8 characters long.');
        }
        const hasLetter = /[a-zA-Z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        if (!hasLetter || !hasNumber) {
            updateStrengthMeter(val);
            return setError(passwordInput, 'Password must contain at least one letter and one number.');
        }

        updateStrengthMeter(val);
        setSuccess(passwordInput);

        // Also re-validate confirm password if it already has text
        if (confirmPasswordInput.value !== '') {
            validateConfirmPassword();
        }
        return true;
    }

    // Dynamic Password Strength Meter
    function updateStrengthMeter(pwd) {
        if (!strengthBar || !strengthText) return;

        if (pwd.length === 0) {
            strengthBar.style.width = '0%';
            strengthBar.className = 'strength-bar';
            strengthText.textContent = '';
            return;
        }

        let score = 0;
        if (pwd.length >= 8) score++;
        if (/[a-zA-Z]/.test(pwd) && /[0-9]/.test(pwd)) score++;
        if (/[^a-zA-Z0-9]/.test(pwd) || pwd.length >= 12) score++;

        if (score === 1) {
            strengthBar.style.width = '33%';
            strengthBar.className = 'strength-bar strength-weak';
            strengthText.textContent = 'Weak (Needs letters & numbers)';
            strengthText.className = 'strength-text strength-weak';
        } else if (score === 2) {
            strengthBar.style.width = '66%';
            strengthBar.className = 'strength-bar strength-medium';
            strengthText.textContent = 'Medium (Good length & characters)';
            strengthText.className = 'strength-text strength-medium';
        } else {
            strengthBar.style.width = '100%';
            strengthBar.className = 'strength-bar strength-strong';
            strengthText.textContent = 'Strong password!';
            strengthText.className = 'strength-text strength-strong';
        }
    }

    // 5. Validate Confirm Password
    function validateConfirmPassword() {
        const val = confirmPasswordInput.value;
        const pwdVal = passwordInput.value;
        if (val === '') {
            return setError(confirmPasswordInput, 'Please confirm your password.');
        }
        if (val !== pwdVal) {
            return setError(confirmPasswordInput, 'Passwords do not match.');
        }
        return setSuccess(confirmPasswordInput);
    }

    // Real-time Event Listeners
    fullNameInput.addEventListener('blur', validateFullName);
    usernameInput.addEventListener('blur', validateUsername);
    emailInput.addEventListener('blur', validateEmail);
    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validateConfirmPassword);

    // Form Submit Interception
    form.addEventListener('submit', (e) => {
        const isFullNameValid = validateFullName();
        const isUsernameValid = validateUsername();
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        const isConfirmPasswordValid = validateConfirmPassword();

        if (!isFullNameValid || !isUsernameValid || !isEmailValid || !isPasswordValid || !isConfirmPasswordValid) {
            e.preventDefault();
            // Scroll to the first invalid input
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });
});
