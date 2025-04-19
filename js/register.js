document
  .getElementById("registerForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();
    const messageDiv = document.getElementById("message");
    messageDiv.className = "message-hidden";

    // Client-side validation example
    const username = this.elements["username"].value.trim();
    const password = this.elements["password"].value;
    const ssgToken = this.elements["ssg_token"].value.trim();

    if (username.length < 4) {
      showMessage("نام کاربری باید حداقل 4 کارکتر باشد", "error");
      return;
    }

    if (password.length < 8) {
      showMessage("گذرواژه باید حداقل 8 کارکتر باشد", "error");
      return;
    }

    if (!ssgToken) {
      showMessage("SSG Token را وارد کنید", "error");
      return;
    }

    // If validation passes, submit the form
    this.submit();
  });

function showMessage(text, type) {
  const messageDiv = document.getElementById("message");
  messageDiv.textContent = text;
  messageDiv.className = type === "error" ? "error-message" : "success-message";
}

// For server-side errors passed via URL parameters
window.onload = function () {
  const urlParams = new URLSearchParams(window.location.search);
  const error = urlParams.get("error");
  const success = urlParams.get("success");

  if (error) {
    showMessage(decodeURIComponent(error), "error");
  }

  if (success) {
    showMessage(decodeURIComponent(success), "success");
  }
};
