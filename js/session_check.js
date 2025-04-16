async function check_session() {
  try {
    const res = await fetch("./php/session_check.php");
    const data = await res.json();
    return data;
  } catch (error) {
    console.log("error" + error);
  }
}

async function main() {
  const session = await check_session();

  // check session
  if (session.logged_in) {
    const logout = document.getElementById("logout");
    logout.style.display = "block";

    window.sessionData = {
      username: session.username,
      ssg_token: session.ssg_token,
    };

    const appScript = document.createElement("script");
    appScript.src = "js/app.js?v=" + Date.now();
    const irrRecScript = document.createElement("script");
    irrRecScript.src = "js/irrRec.js?v=" + Date.now();

    document.body.appendChild(appScript);
    document.body.appendChild(irrRecScript);

    document.getElementById("mainApp").style.display = "block";
  } else {
    const login = document.getElementById("login");
    login.style.display = "block";
    // window.location.href = "./login.html";
  }
}

main();
