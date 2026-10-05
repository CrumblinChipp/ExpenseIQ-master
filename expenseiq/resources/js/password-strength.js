document.addEventListener('DOMContentLoaded', () => {

    // ============================
    // Elements
    // ============================

    const password = document.querySelector('[data-password]');
    const confirmPassword = document.querySelector('[data-confirm-password]');

    if (!password) return;

    const bar = document.getElementById('strength-bar');
    const text = document.getElementById('strength-text');
    const rules = document.getElementById('password-rules');

    const lengthRule = document.getElementById('rule-length');
    const upperRule = document.getElementById('rule-upper');
    const numberRule = document.getElementById('rule-number');
    const symbolRule = document.getElementById('rule-symbol');

    const confirmMessage = document.getElementById('confirm-message');

    const togglePassword = document.querySelector('[data-toggle-password]');
    const passwordIcon = document.getElementById('password-icon');

    const toggleConfirmPassword = document.querySelector('[data-toggle-confirm-password]');
    const confirmPasswordIcon = document.getElementById('confirm-password-icon');

    // ============================
    // Password Strength
    // ============================

    password.addEventListener('input', () => {

        const value = password.value;

        let score = 0;

        const hasLength = value.length >= 8;
        const hasUpper = /[A-Z]/.test(value);
        const hasNumber = /\d/.test(value);
        const hasSymbol = /[^A-Za-z0-9]/.test(value);

        if (lengthRule) {
            lengthRule.style.display = hasLength ? 'none' : 'list-item';
        }

        if (upperRule) {
            upperRule.style.display = hasUpper ? 'none' : 'list-item';
        }

        if (numberRule) {
            numberRule.style.display = hasNumber ? 'none' : 'list-item';
        }

        if (symbolRule) {
            symbolRule.style.display = hasSymbol ? 'none' : 'list-item';
        }

        if (hasLength) score++;
        if (hasUpper) score++;
        if (hasNumber) score++;
        if (hasSymbol) score++;

        if (bar && text) {

            if (score <= 1) {

                text.textContent = "Weak";
                text.className = "text-red-600";

                bar.style.width = "25%";
                bar.className = "bg-red-500 h-2 rounded-full transition-all duration-300";

            }
            else if (score <= 3) {

                text.textContent = "Medium";
                text.className = "text-yellow-600";

                bar.style.width = "65%";
                bar.className = "bg-yellow-500 h-2 rounded-full transition-all duration-300";

            }
            else {

                text.textContent = "Strong";
                text.className = "text-green-600";

                bar.style.width = "100%";
                bar.className = "bg-green-500 h-2 rounded-full transition-all duration-300";

            }

        }

        if (rules) {

            rules.style.display = score === 4 ? "none" : "block";

        }

        updateConfirmMessage();

    });

    // ============================
    // Confirm Password
    // ============================

    function updateConfirmMessage() {

        if (!confirmPassword || !confirmMessage) return;

        if (confirmPassword.value.length === 0) {

            confirmMessage.classList.add('hidden');
            return;

        }

        confirmMessage.classList.remove('hidden');

        if (password.value === confirmPassword.value) {

            confirmMessage.textContent = "✔ Passwords match";
            confirmMessage.className =
                "mt-2 text-sm font-medium text-green-600";

        } else {

            confirmMessage.textContent = "✖ Passwords do not match";
            confirmMessage.className =
                "mt-2 text-sm font-medium text-red-600";

        }

    }

    if (confirmPassword) {

        confirmPassword.addEventListener('input', updateConfirmMessage);

    }

    // ============================
    // Show / Hide Password
    // ============================

    if (togglePassword) {

        togglePassword.addEventListener('click', () => {

            const hidden = password.type === "password";

            password.type = hidden ? "text" : "password";

            if (passwordIcon) {

                passwordIcon.className = hidden
                    ? "fa-solid fa-eye-slash"
                    : "fa-solid fa-eye";

            }

        });

    }

    if (toggleConfirmPassword && confirmPassword) {

        toggleConfirmPassword.addEventListener('click', () => {

            const hidden = confirmPassword.type === "password";

            confirmPassword.type = hidden ? "text" : "password";

            if (confirmPasswordIcon) {

                confirmPasswordIcon.className = hidden
                    ? "fa-solid fa-eye-slash"
                    : "fa-solid fa-eye";

            }

        });

    }

});