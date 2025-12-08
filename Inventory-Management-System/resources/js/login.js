// resources/js/login-validation.js  ← Save it here

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    
    // Laravel uses name="email", not "username"!
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.querySelector('input[name="password"]');
    
    if (!form || !emailInput || !passwordInput) {
        console.warn("Login validation: Form or inputs not found");
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('input[type="submit"]');

    // Create error spans
    const emailError = document.createElement("span");
    emailError.className = "error-message";
    emailInput.parentElement.appendChild(emailError);

    const passwordError = document.createElement("span");
    passwordError.className = "error-message";
    passwordInput.parentElement.appendChild(passwordError);

    // Inject styles
    const style = document.createElement("style");
    style.textContent = `
        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 6px;
            display: block;
            min-height: 22px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .error-message.show {
            opacity: 1;
        }
        .relative.error input,
        .input-group.error input,
        div.error input {
            border-color: #e74c3c !important;
            box-shadow: 0 0 8px rgba(231, 76, 60, 0.4);
        }
    `;
    document.head.appendChild(style);

    function clearErrors() {
        [emailError, passwordError].forEach(el => {
            el.textContent = "";
            el.classList.remove("show");
        });
        [emailInput, passwordInput].forEach(input => {
            input.closest('div, .relative, .input-group')?.classList.remove("error");
        });
    }

    function showError(input, errorEl, message) {
        errorEl.textContent = message;
        errorEl.classList.add("show");
        input.closest('div, .relative, .input-group')?.classList.add("error");
        input.focus();
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        clearErrors();

        let isValid = true;
        const email = emailInput.value.trim();
        const password = passwordInput.value;

        // Email validation
        if (email === "") {
            showError(emailInput, emailError, "Please enter your email");
            isValid = false;
        } else if (!/^\S+@\S+\.\S+$/.test(email)) {
            showError(emailInput, emailError, "Please enter a valid email address");
            isValid = false;
        }

        // Password validation
        if (password === "") {
            showError(passwordInput, passwordError, "Please enter your password");
            isValid = false;
        } else if (password.length < 8) {
            showError(passwordInput, passwordError, "Password must be at least 8 characters");
            isValid = false;
        }

        if (isValid) {
            form.submit(); // Real submit to Laravel
        }
    });

    // Real-time clear errors
    emailInput.addEventListener("input", function () {
        if (this.value.trim() !== "" && /^\S+@\S+\.\S+$/.test(this.value)) {
            emailError.textContent = "";
            emailError.classList.remove("show");
            this.closest('div, .relative, .input-group')?.classList.remove("error");
        }
    });

    passwordInput.addEventListener("input", function () {
        if (this.value.length >= 8) {
            passwordError.textContent = "";
            passwordError.classList.remove("show");
            this.closest('div, .relative, .input-group')?.classList.remove("error");
        }
    });
});
