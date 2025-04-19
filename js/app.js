console.log("app.js");
console.log(window.sessionData.ssg_token);
const ssg_token = window.sessionData.ssg_token;

// get data

// variables
var sysInfoJson = "";
var cmdInfoJson = "";
var autoIrrInfoJson = "";
var postCmdUrl = "";
var updateAutoIrrSec = true;
var updateDurationEn = true;
var waitingForResponse = false;
var unsuccessfulTries = 0;
var sysInfo;
var cmdInfo = {};
var autoIrrInfo;
var client;
var nextIrrDate;

// URLs
var sysInfoUrl = "php/get_info.php";
var cmdUrl = "php/insert_cmd.php";
var autoIrrUrl = "php/get_auto_irr_info.php";

// getAutoIrrInfo(autoIrrUrl);
// getCmdInfo(cmdUrl);
// getInfo(sysUrl);
get_all_data();

var t = setInterval(get_sys_info, 3000);

//assign functions to buttons
document.getElementById("valveButton").onclick = vlvBtnClick;
document.getElementById("autoIrrButton").onclick = autoIrrBtnClick;
document.getElementById("autoIrrSave").onclick = saveBtnClick;

// welcome user

// get device data every 2 sec

// functions
function getAutoIrrInfo(url) {
  fetch(url)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok: " + response.statusText);
      }
      return response.text(); // or response.json() depending on the server's response
    })
    .catch((error) => {
      // Handle any errors
      console.error("Fetch error:", error);
    })
    .then((data) => {
      // Handle the response data
      autoIrrInfoJson = data;
      console.log("autoIrrInfo:\n" + autoIrrInfoJson);
      autoIrrInfo = JSON.parse(autoIrrInfoJson);
    });
}

function getCmdInfo(url) {
  fetch(url)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok: " + response.statusText);
      }
      return response.text(); // or response.json() depending on the server's response
    })
    .catch((error) => {
      // Handle any errors
      console.error("Fetch error:", error);
    })
    .then((data) => {
      // Handle the response data
      cmdInfoJson = data;
      console.log("cmdInfo:\n" + cmdInfoJson);
      cmdInfo = JSON.parse(cmdInfoJson);
    });
}

function getInfo(url) {
  fetch(url)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok: " + response.statusText);
      }
      return response.text(); // or response.json() depending on the server's response
    })
    .catch((error) => {
      // Handle any errors
      console.error("Fetch error:", error);
    })

    .then((data) => {
      // Handle the response data
      sysInfoJson = data;
      sysInfo = JSON.parse(sysInfoJson);
      console.log("sysInfo:\n", sysInfoJson);
      if (!waitingForResponse) updateUI();
      if (sysInfo.copy && waitingForResponse) {
        waitingForResponse = false;
        updateUI();
      }
    });
}

function updateUI() {
  //hide disConPic
  // document.getElementById("disConDiv").classList.add("displayNone");
  // document.getElementById("disConDiv").style.display = "none";

  // sysInfo = JSON.parse(sysInfoJson);

  //update connection status
  // updateConStat();

  //update humidity

  // show username
  showUsername();

  updateHumidity();

  // update duration
  if (updateDurationEn) updateDuration();

  //update valve status
  updateVlvStat();

  //update duration

  //update autoIrrEn
  if (updateAutoIrrSec) updateAutoIrr();
}

function showUsername() {
  document.getElementById("username").textContent =
    "خوش اومدی " + sysInfo.username;
}

function updateHumidity() {
  if (sysInfo.humidity > 70) {
    document.getElementById("humQuality").textContent = "خوب";
    document.getElementById("checkedPic").classList.remove("displayNone");
    document.getElementById("warningPic").classList.add("displayNone");
    document.getElementById("errorPic").classList.add("displayNone");
  }
  if (sysInfo.humidity < 70 && sysInfo.humidity > 30) {
    document.getElementById("humQuality").textContent = "متوسط";
    document.getElementById("checkedPic").classList.add("displayNone");
    document.getElementById("warningPic").classList.remove("displayNone");
    document.getElementById("errorPic").classList.add("displayNone");
  }
  if (sysInfo.humidity < 30) {
    document.getElementById("humQuality").textContent = "کم";
    document.getElementById("checkedPic").classList.add("displayNone");
    document.getElementById("warningPic").classList.add("displayNone");
    document.getElementById("errorPic").classList.remove("displayNone");
  }

  document.getElementById("humidityPercent").textContent =
    sysInfo.humidity + "%";
}
function updateDuration() {
  updateDurationEn = false;
  var m = 0;
  var h = 0;
  h = Math.floor(sysInfo.duration / 60);
  if (h.toString().length < 2) h = "0" + h;
  m = sysInfo.duration % 60;
  if (m.toString().length < 2) m = "0" + m;
  document.getElementById("durationMin").value = m;
  document.getElementById("durationHour").value = h;
}

function updateVlvStat() {
  if (sysInfo.valve) valveIsOpen();
  else valveIsClose();
}

function valveIsOpen() {
  // update H3
  document.getElementById("valveStatus").textContent = "شیر باز است";
  document.getElementById("valveStatus").classList.add("greenH3");
  document.getElementById("valveStatus").classList.remove("redH3");

  // update button
  document.getElementById("valveButton").textContent = "بستن";
  document.getElementById("valveButton").classList.remove("loadingButton");
  document.getElementById("valveButton").classList.add("redButton");
  document.getElementById("valveButton").classList.remove("greenButton");
  document.getElementById("valveButton").removeAttribute("disabled");

  // update duration section
  document.getElementById("irrTimeTd").classList.add("displayNone");
  document.getElementById("irrigating").classList.remove("displayNone");
  document.getElementById("durationTimeDiv").classList.add("displayNone");
}

function valveIsClose() {
  // update H3
  document.getElementById("valveStatus").textContent = "شیر بسته است";
  document.getElementById("valveStatus").classList.remove("greenH3");
  document.getElementById("valveStatus").classList.add("redH3");

  // update button
  document.getElementById("valveButton").textContent = "باز کردن";
  document.getElementById("valveButton").classList.remove("loadingButton");
  document.getElementById("valveButton").classList.remove("redButton");
  document.getElementById("valveButton").classList.add("greenButton");
  document.getElementById("valveButton").removeAttribute("disabled");

  //update duration section
  document.getElementById("irrTimeTd").classList.remove("displayNone");
  document.getElementById("irrigating").classList.add("displayNone");
  document.getElementById("durationTimeDiv").classList.remove("displayNone");
}

function updateAutoIrr() {
  updateAutoIrrSec = false;
  if (autoIrrInfo.autoIrrEn) {
    // update H3
    document.getElementById("autoIrrEn").textContent = "آبیاری خودکار فعال است";
    document.getElementById("autoIrrEn").classList.remove("redH3");
    document.getElementById("autoIrrEn").classList.add("greenH3");

    // update button
    document.getElementById("autoIrrButton").removeAttribute("disabled");
    document.getElementById("autoIrrButton").textContent = "خاموش کردن";
    document.getElementById("autoIrrButton").classList.remove("loadingButton");
    document.getElementById("autoIrrButton").classList.remove("greenButton");
    document.getElementById("autoIrrButton").classList.add("redButton");

    //request next irr date
    const Http = new XMLHttpRequest();
    const url = "php/next_irr_date.php";
    Http.open("GET", url);
    Http.send();

    Http.onreadystatechange = (e) => {
      // console.log(Http.responseText);
      var d = JSON.parse(Http.responseText);
      nextIrrDate = d.next_irr_shamsi;

      //update next irr section
      document.getElementsByName("crossPic")[0].classList.add("displayNone");
      document.getElementById("nextIrr").classList.remove("displayNone");
      document.getElementById("nextIrr").textContent = nextIrrDate;
    };

    document.getElementsByName("crossPic")[0].classList.add("displayNone");
    document.getElementById("nextIrr").classList.remove("displayNone");
    document.getElementById("nextIrr").textContent = nextIrrDate;

    // update irrigation howOften
    document.getElementsByName("crossPic")[1].classList.add("displayNone");
    document.getElementById("howOftenDiv").classList.remove("displayNone");
    document.getElementById("howOften").value = autoIrrInfo.howOften;

    // update irrigation clock
    document.getElementsByName("crossPic")[2].classList.add("displayNone");
    document.getElementById("irrClockDiv").classList.remove("displayNone");
    h = autoIrrInfo.hour;
    if (h.toString().length < 2) h = "0" + h;
    m = autoIrrInfo.minute;
    if (m.toString().length < 2) m = "0" + m;
    document.getElementById("minute").value = m;
    document.getElementById("hour").value = h;

    //update irrigation duration
    document.getElementsByName("crossPic")[3].classList.add("displayNone");
    document.getElementById("irrTimeDiv").classList.remove("displayNone");
    var m = 0;
    var h = 0;
    h = Math.floor(autoIrrInfo.duration / 60);
    if (h.toString().length < 2) h = "0" + h;
    m = autoIrrInfo.duration % 60;
    if (m.toString().length < 2) m = "0" + m;
    document.getElementById("AIdurationMin").value = m;
    document.getElementById("AIdurationHour").value = h;

    // update save button
    document.getElementById("autoIrrSave").classList.remove("loadingButton");
    document.getElementById("autoIrrSave").classList.add("saveButton");
    document.getElementById("autoIrrSave").textContent = "ذخیره";
    document.getElementById("autoIrrSave").removeAttribute = "disabled";
  } else {
    // update H3
    document.getElementById("autoIrrEn").textContent =
      "آبیاری خودکار خاموش است";
    document.getElementById("autoIrrEn").classList.add("redH3");
    document.getElementById("autoIrrEn").classList.remove("greenH3");

    // update button
    document.getElementById("autoIrrButton").removeAttribute("disabled");
    document.getElementById("autoIrrButton").textContent = "روشن کردن";
    document.getElementById("autoIrrButton").classList.remove("loadingButton");
    document.getElementById("autoIrrButton").classList.add("greenButton");
    document.getElementById("autoIrrButton").classList.remove("redButton");

    // update next irr section
    document.getElementsByName("crossPic")[0].classList.remove("displayNone");
    document.getElementById("nextIrr").classList.add("displayNone");

    // update irrigation howOften
    document.getElementsByName("crossPic")[1].classList.remove("displayNone");
    document.getElementById("howOftenDiv").classList.add("displayNone");

    // update irrigation clock
    document.getElementsByName("crossPic")[2].classList.remove("displayNone");
    document.getElementById("irrClockDiv").classList.add("displayNone");

    //update irrigation duration
    document.getElementsByName("crossPic")[3].classList.remove("displayNone");
    document.getElementById("irrTimeDiv").classList.add("displayNone");

    // update save button
    document.getElementById("autoIrrSave").classList.add("loadingButton");
    document.getElementById("autoIrrSave").classList.remove("saveButton");
    document.getElementById("autoIrrSave").textContent = "ذخیره";
    document.getElementById("autoIrrSave").setAttribute = "disabled";
  }
}

function vlvBtnClick() {
  // release duration update En
  updateDurationEn = true;
  //wait for valve open response
  waitingForResponse = true;

  // check if valve is open
  if (sysInfo.valve) {
    console.log("Closing Valve");

    //prepare data to post
    cmdInfo.valveCmd = "close";
    cmdInfoJson = JSON.stringify(cmdInfo);

    //post new data to cmdInfo
    post();
  } else {
    //prepare data to send

    var hour = document.getElementById("durationHour").value;
    var min = document.getElementById("durationMin").value;
    var irrDuration = Number(hour) * 60 + Number(min);
    cmdInfo.durationCmd = irrDuration;
    cmdInfo.valveCmd = "open";
    cmdInfoJson = JSON.stringify(cmdInfo);

    //post new data to cdmInfo
    post();
  }

  // update button css
  document.getElementById("valveButton").classList.remove("greenButton");
  document.getElementById("valveButton").classList.remove("redButton");
  document.getElementById("valveButton").classList.add("loadingButton");
  document.getElementById("valveButton").textContent = "در حال ارسال...";
}

function autoIrrBtnClick() {
  // release auto irr section to be updated
  updateAutoIrrSec = true;
  //wait for valve open response
  waitingForResponse = true;
  //check if auto irr was enable or not
  if (autoIrrInfo.autoIrrEn) {
    //prepare data to post in cmdInfo
    autoIrrInfo.autoIrrEn = false;
    autoIrrInfoJson = JSON.stringify(autoIrrInfo);

    //post new data
    autoIrrPost();

    updateUI();
  } else {
    // prepare data to send to cmdInfo
    var AIhour = autoIrrInfo.hour;
    var AImin = autoIrrInfo.minute;
    var irrHowOften = autoIrrInfo.howOften;
    var irrDuration = autoIrrInfo.duration;

    // cmdInfo.autoIrrEnCmd = 1;
    // cmdInfo.durationCmd = irrDuration;
    // cmdInfo.minuteCmd = AImin;
    // cmdInfo.hourCmd = AIhour;
    // cmdInfo.howOftenCmd = irrHowOften;
    // cmdInfoJson = JSON.stringify(cmdInfo);

    // // post new data
    // post();
    autoIrrInfo.autoIrrEn = 1;
    autoIrrInfoJson = JSON.stringify(autoIrrInfo);

    autoIrrPost();

    updateUI();
  }

  //update button
  // document.getElementById("autoIrrButton").classList.remove("greenButton");
  // document.getElementById("autoIrrButton").classList.remove("redButton");
  // document.getElementById("autoIrrButton").classList.add("loadingButton");
  // document.getElementById("autoIrrButton").textContent = "در حال ارسال...";

  // waitForResponse();
}

function saveBtnClick() {
  // release auto irr section to be updated
  updateAutoIrrSec = true;
  //wait for valve open response
  waitingForResponse = true;

  // prepare data to post in autoIrrInfo
  var hour = document.getElementById("AIdurationHour").value;
  var min = document.getElementById("AIdurationMin").value;
  var irrHowOften = document.getElementById("howOften").value;
  var AImin = document.getElementById("minute").value;
  var AIhour = document.getElementById("hour").value;
  var irrDuration = Number(hour) * 60 + Number(min);

  autoIrrInfo.autoIrrEn = autoIrrInfo.autoIrrEn;
  autoIrrInfo.duration = irrDuration;
  autoIrrInfo.minute = AImin;
  autoIrrInfo.hour = AIhour;
  autoIrrInfo.howOften = irrHowOften;

  autoIrrInfoJson = JSON.stringify(autoIrrInfo);
  // post new data to autoIrrInfo
  autoIrrPost();
  //update button
  // document.getElementById("autoIrrSave").classList.remove("greenButton");
  // document.getElementById("autoIrrSave").classList.remove("redButton");
  // document.getElementById("autoIrrSave").classList.add("loadingButton");
  // document.getElementById("autoIrrSave").textContent = "در حال ارسال...";

  updateAutoIrrSec = true;
  updateAutoIrr();
}

function insertIntoDB() {
  var bracketIndex = sysInfoJson.indexOf("{");
  if (bracketIndex != -1) {
    sysInfoJson = sysInfoJson.substring(bracketIndex);
    sysInfo = JSON.parse(sysInfoJson);
  }
  var sendData = { duration: sysInfo.duration };
  var sendDataJson = JSON.stringify(sendData);
  console.log(sendDataJson);
  fetch("http://sed-smarthome.ir/karkevand/php/insertToDb.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "insertIntoDB=" + sendDataJson,
  }).then((res) => {
    console.log("Request complete! response:", res);
  });
}

function post() {
  fetch(cmdUrl, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "cmd=" + cmdInfoJson,
  })
    .then((res) => res.text())
    .then((d) => console.log("response : \n" + d));
}
function autoIrrPost() {
  fetch("php/update_auto_irr.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "autoIrrInfo=" + autoIrrInfoJson,
  })
    .then((res) => res.text())
    .then((d) => console.log("response: \n" + d));
}
async function fetch_data(url, ssg_token) {
  const formData = new FormData();
  formData.append("ssg_token", ssg_token);

  try {
    const res = await fetch(url, {
      method: "POST",
      body: formData,
    });
    const data = await res.json();
    return data;
  } catch (error) {
    console.log("fetch failed." + error);
  }
}
async function get_sys_info() {
  sysInfo = await fetch_data("php/get_info.php", ssg_token);
  console.log(sysInfo);
  if (!waitingForResponse) updateUI();
  if (sysInfo.copy && waitingForResponse) {
    waitingForResponse = false;
    updateUI();
  }
}
async function get_auto_irr_info() {
  autoIrrInfo = await fetch_data(autoIrrUrl, ssg_token);
  console.log(autoIrrInfo);
}

async function get_all_data() {
  await get_auto_irr_info();
  await get_sys_info();
}
