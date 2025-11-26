// validation.js – Clean, professional form validation
document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const nameInput = document.querySelector('input[type="text"]');
  const emailInput = document.querySelector('input[type="email"]');
  const messageInput = document.querySelector("textarea");

  // Helper: Show error message
  function showError(input, message) {
    // Remove any previous error
    const existingError = input.parentElement.querySelector(".error-msg");
    if (existingError) existingError.remove();

    const error = document.createElement("small");
    error.className = "error-msg";
    error.style.color = "#ef4444";
    error.style.display = "block";
    error.style.marginTop = "6px";
    error.style.fontSize = "14px";
    error.textContent = message;

    input.parentElement.appendChild(error);
    input.style.borderColor = "#ef4444";
  }

  // Helper: Remove error
  function clearError(input) {
    const error = input.parentElement.querySelector(".error-msg");
    if (error) error.remove();
    input.style.borderColor = "#e2e8f0";
  }

  // Validate name
  function validateName() {
    if (nameInput.value.trim().length < 4) {
      showError(nameInput, "Please enter your full name");
      return false;
    }
    clearError(nameInput);
    return true;
  }

  // Validate email
  function validateEmail() {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailInput.value.trim()) {
      showError(emailInput, "Email is required");
      return false;
    }
    if (!emailRegex.test(emailInput.value.trim())) {
      showError(emailInput, "Please enter a valid email address");
      return false;
    }
    clearError(emailInput);
    return true;
  }

  // Validate message
  function validateMessage() {
    if (messageInput.value.trim().length < 10) {
      showError(messageInput, "Message must be at least 10 characters");
      return false;
    }
    clearError(messageInput);
    return true;
  }

  // Real-time validation
  nameInput.addEventListener("blur", validateName);
  emailInput.addEventListener("blur", validateEmail);
  messageInput.addEventListener("blur", validateMessage);

  // Form submit
  form.addEventListener("submit", function (e) {
    e.preventDefault(); // Prevent actual submission (for demo)

    const isNameValid = validateName();
    const isEmailValid = validateEmail();
    const isMessageValid = validateMessage();

    if (isNameValid && isEmailValid && isMessageValid) {
  // Disable button and show loading state
  const submitBtn = form.querySelector(".primary-btn");
  submitBtn.disabled = true;
  submitBtn.textContent = "Sending...";
  submitBtn.style.opacity = "0.7";

  // Create success message
  const successMsg = document.createElement("div");
  successMsg.innerHTML = `
    <div style="
      margin-top: 32px;
      padding: 20px;
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: 16px;
      text-align: center;
      color: #166534;
      font-size: 17px;
      font-weight: 600;
    ">
      <svg style="width:28px;height:28px;vertical-align:middle;margin-right:8px;color:#22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
      </svg>
      Thank you! Your message has been sent successfully.
      <br><span style="font-weight:400;font-size:15px;color:#22c55e;">Redirecting you to the homepage...</span>
    </div>
  `;

  // Add success message after the form
  form.insertAdjacentElement("afterend", successMsg);

  // Reset form
  form.reset();
  [nameInput, emailInput, messageInput].forEach(input => {
    input.style.borderColor = "#e2e8f0";
    clearError(input);
  });

  // Redirect after 2 seconds (feels smooth & professional)
  setTimeout(() => {
    window.location.href = "home.html";  // or "Home.html" if you prefer
  }, 2200);
}
  });
});
