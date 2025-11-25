// login-validation.js
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    const usernameInput = document.querySelector('input[name="username"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('input[type="submit"]');

    // Create error spans
    const usernameError = document.createElement("span");
    usernameError.className = "error-message";
    usernameInput.parentElement.appendChild(usernameError);

    const passwordError = document.createElement("span");
    passwordError.className = "error-message";
    passwordInput.parentElement.appendChild(passwordError);

    // Add stylish error styling
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
        .input-group.error input {
            border-color: #e74c3c !important;
            box-shadow: 0 0 8px rgba(231, 76, 60, 0.4);
        }
        .input-group.focused.error input {
            box-shadow: 0 0 10px rgba(231, 76, 60, 0.5);
        }
    `;
    document.head.appendChild(style);

    // Clear all errors
    function clearErrors() {
        [usernameError, passwordError].forEach(el => {
            el.textContent = "";
            el.classList.remove("show");
        });
        [usernameInput, passwordInput].forEach(input => {
            input.parentElement.classList.remove("error");
        });
    }

    // Show error function
    function showError(input, errorEl, message) {
        errorEl.textContent = message;
        errorEl.classList.add("show");
        input.parentElement.classList.add("error");
        input.focus(); // Optional: focus the first invalid field
    }

    // Form Submit Validation
    form.addEventListener("submit", function (e) {
        e.preventDefault(); // Always prevent first
        clearErrors();

        let isValid = true;
        const username = usernameInput.value.trim();
        const password = passwordInput.value;

        // Username validation
        if (username === "") {
            showError(usernameInput, usernameError, "Please enter your email or username");
            isValid = false;
        } else if (username.length < 3) {
            showError(usernameInput, usernameError, "Username must be at least 3 characters");
            isValid = false;
        }

        // Password validation - at least 8 characters
        if (password === "") {
            showError(passwordInput, passwordError, "Please enter your password");
            isValid = false;
        } else if (password.length < 8) {
            showError(passwordInput, passwordError, "Password must be at least 8 characters");
            isValid = false;
        }

        // If everything is valid → allow submission
        if (isValid) {
            // Remove e.preventDefault() above and uncomment below if real submit
            form.submit(); // Or use AJAX here
            // alert("Login successful!"); // For testing
        }
    });

    // Real-time validation (clear error when user types correctly)
    usernameInput.addEventListener("input", function () {
        if (this.value.trim().length >= 3) {
            usernameError.textContent = "";
            usernameError.classList.remove("show");
            this.parentElement.classList.remove("error");
        }
    });

    passwordInput.addEventListener("input", function () {
        if (this.value.length >= 8) {
            passwordError.textContent = "";
            passwordError.classList.remove("show");
            this.parentElement.classList.remove("error");
        }
    });

    // Optional: Add visual feedback when clicking submit button with empty fields
    if (submitBtn) {
        submitBtn.addEventListener("click", function () {
            // Trigger validation instantly when button is clicked
            form.dispatchEvent(new Event("submit"));
        });
    }
});
