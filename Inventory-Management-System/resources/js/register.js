document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    if (!form) return;

    const nameInput = document.querySelector('input[name="name"]');
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const passwordConfirmationInput = document.querySelector('input[name="password_confirmation"]');

    const lengthEl = document.getElementById("length");
    const uppercaseEl = document.getElementById("uppercase");
    const specialEl = document.getElementById("special");

    // Fix layout
    document.querySelectorAll(".password-condition").forEach(el => {
        el.style.display = "flex";
        el.style.gap = "6px";
        el.style.alignItems = "center";
    });

    // Prepare error spans
    const errorSpans = {};

    [nameInput, emailInput, passwordInput, passwordConfirmationInput].forEach(input => {
        if (!input) return;

        let errorSpan = document.createElement("span");
        errorSpan.className = "error-message";

        // INSERT CONFIRM-PASSWORD ERROR ABOVE CONDITIONS
        if (input.name === "password_confirmation") {
            const conditionsBox = document.getElementById("password-conditions");
            input.parentElement.insertBefore(errorSpan, conditionsBox);
        } else {
            input.parentElement.appendChild(errorSpan);
        }

        errorSpans[input.name] = errorSpan;
    });

    // Inject styles
    if (!document.getElementById("register-val-styles")) {
        const style = document.createElement("style");
        style.id = "register-val-styles";
        style.textContent = `
            .error-message {
                color: #e74c3c;
                font-size: 0.85rem;
                display: block;
                margin-top: 4px;
                opacity: 0;
                transition: opacity .2s;
            }
            .error-message.show { opacity: 1; }
            .input-group.error input { border-color: #e74c3c !important; }
        `;
        document.head.appendChild(style);
    }

    function showError(input, msg) {
        const span = errorSpans[input.name];
        span.textContent = msg;
        span.classList.add("show");
        input.closest(".input-group").classList.add("error");
    }

    function clearErrors() {
        Object.values(errorSpans).forEach(span => {
            span.textContent = "";
            span.classList.remove("show");
        });
        document.querySelectorAll(".input-group").forEach(el => el.classList.remove("error"));
    }

    function updatePasswordStrength(pwd) {
        const hasLength = pwd.length >= 8;
        const hasUpper = /[A-Z]/.test(pwd);
        const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>/?]/.test(pwd);

        lengthEl.textContent = hasLength ? "✔" : "✘";
        lengthEl.style.color = hasLength ? "green" : "red";

        uppercaseEl.textContent = hasUpper ? "✔" : "✘";
        uppercaseEl.style.color = hasUpper ? "green" : "red";

        specialEl.textContent = hasSpecial ? "✔" : "✘";
        specialEl.style.color = hasSpecial ? "green" : "red";

        return hasLength && hasUpper && hasSpecial;
    }

    passwordInput.addEventListener("input", () => {
        updatePasswordStrength(passwordInput.value);
    });

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        clearErrors();

        let valid = true;

        if (nameInput.value.trim() === "") {
            showError(nameInput, "Please enter your full name");
            valid = false;
        }

        if (emailInput.value.trim() === "") {
            showError(emailInput, "Please enter your email");
            valid = false;
        } else if (!/^\S+@\S+\.\S+$/.test(emailInput.value)) {
            showError(emailInput, "Please enter a valid email");
            valid = false;
        }

        const pwdOK = updatePasswordStrength(passwordInput.value);
        if (passwordInput.value === "") {
            showError(passwordInput, "Please enter a password");
            valid = false;
        } else if (!pwdOK) {
            showError(passwordInput, "Password must meet all 3 requirements");
            valid = false;
        }

        if (passwordConfirmationInput.value === "") {
            showError(passwordConfirmationInput, "Please confirm your password");
            valid = false;
        } else if (passwordInput.value !== passwordConfirmationInput.value) {
            showError(passwordConfirmationInput, "Passwords do not match");
            valid = false;
        }

        if (valid) form.submit();
    });
});

