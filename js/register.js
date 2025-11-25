// register-validation.js → WORKS 100% with your exact HTML
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    const fullname = document.querySelector('input[name="fullname"]');
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');
    const confirmPass = document.querySelector('input[name="confirm_password"]');

    // Create error messages inside each .input-group
    const createErrorSpan = (input) => {
        const span = document.createElement("span");
        span.className = "error-message";
        input.closest(".input-group").appendChild(span);
        return span;
    };

    const errors = {
        fullname: createErrorSpan(fullname),
        email: createErrorSpan(email),
        password: createErrorSpan(password),
        confirm_password: createErrorSpan(confirmPass)
    };

    // Add the same beautiful styling as login
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
        .error-message.show { opacity: 1; }
        .input-group.error input {
            border-color: #e74c3c !important;
            box-shadow: 0 0 8px rgba(231, 76, 60, 0.4);
        }
    `;
    document.head.appendChild(style);

    function clearErrors() {
        Object.values(errors).forEach(el => {
            el.textContent = "";
            el.classList.remove("show");
        });
        document.querySelectorAll(".input-group").forEach(g => g.classList.remove("error"));
    }

    function showError(field, message) {
        const errorEl = errors[field.name];
        errorEl.textContent = message;
        errorEl.classList.add("show");
        field.closest(".input-group").classList.add("error");
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
    }

    // FORM SUBMIT → SHOW ERRORS EVEN IF NOTHING TYPED
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        clearErrors();

        let valid = true;

        // Full Name
        if (!fullname.value.trim()) {
            showError(fullname, "Full name is required");
            valid = false;
        } else if (fullname.value.trim().length < 3) {
            showError(fullname, "Name must be at least 3 characters");
            valid = false;
        }

        // Email
        if (!email.value.trim()) {
            showError(email, "Email is required");
            valid = false;
        } else if (!isValidEmail(email.value)) {
            showError(email, "Enter a valid email address");
            valid = false;
        }

        // Password
        if (!password.value) {
            showError(password, "Password is required");
            valid = false;
        } else if (	password.value.length < 8) {
            showError(password, "Password must be at least 8 characters");
            valid = false;
        }

        // Confirm Password
        if (!confirmPass.value) {
            showError(confirmPass, "Please confirm your password");
            valid = false;
        } else if (confirmPass.value !== password.value) {
            showError(confirmPass, "Passwords do not match");
            valid = false;
        }

        if (valid) {
            alert("Registration successful!");
            form.submit();
        }
    });

    // Real-time clearing (same as login)
    fullname.addEventListener("input", () => {
        if (fullname.value.trim().length >= 3) {
            errors.fullname.textContent = "";
            errors.fullname.classList.remove("show");
            fullname.closest(".input-group").classList.remove("error");
        }
    });

    email.addEventListener("input", () => {
        if (isValidEmail(email.value.trim())) {
            errors.email.textContent = "";
            errors.email.classList.remove("show");
            email.closest(".input-group").classList.remove("error");
        }
    });

    password.addEventListener("input", () => {
        if (password.value.length >= 8) {
            errors.password.textContent = "";
            errors.password.classList.remove("show");
            password.closest(".input-group").classList.remove("error");
        }
        // Revalidate confirm password
        if (confirmPass.value && confirmPass.value !== password.value) {
            showError(confirmPass, "Passwords do not match");
        }
    });

    confirmPass.addEventListener("input", () => {
        if (confirmPass.value === password.value && confirmPass.value) {
            errors.confirm_password.textContent = "";
            errors.confirm_password.classList.remove("show");
            confirmPass.closest(".input-group").classList.remove("error");
        }
    });
});
