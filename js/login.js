document
  .getElementById("loginForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const formdata = new FormData(this);
    const res = await fetch("php/login.php", {
      method: "POST",
      body: formdata,
    });

    const data = await res.json();

    if (data.success) {
      window.location.href = "index.html";
    } else {
      const errorMsg = document.getElementById("errorMsg");
      errorMsg.innerText = data.message || "login failed";
      errorMsg.style.display = "block";
    }
  });
