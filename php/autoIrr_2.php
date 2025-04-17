<?php
// date_default_timezone_set('Asia/Tehran');

//addresses for cron job
$cmdAd = "public_html/dayi_hossein/infoFiles/cmd.txt";
$autoIrrInfoAd = "public_html/dayi_hossein/infoFiles/autoIrrInfo.txt";
$autoIrrRecAd = "public_html/dayi_hossein/server/autoIrrRec.txt";
$autoIrrMessage = "public_html/dayi_hossein/server/autoIrrMessage.txt";

//addresses if i wanted to run myself
// $cmdAd = "../infoFiles/cmd.txt";
// $autoIrrInfoAd = "../infoFiles/autoIrrInfo.txt";

// db connection info
$hostname = 'localhost:3306';
$username = 'jjqioyps_dayihossein';
$password = 'Sed1508libero';
$database = 'jjqioyps_dayi_hossein_ssg';

//get last irr datetime
$autoIrrInfoJson = file_get_contents($autoIrrInfoAd);
$autoIrrInfo = json_decode($autoIrrInfoJson);

//check if auto irr is on

if ($autoIrrInfo->autoIrrEn) {

    $conn = new mysqli($hostname, $username, $password, $database);

    //check error
    if ($conn->connect_error) {
        die("connect error: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM irr_rec ORDER BY irr_start_datetime DESC LIMIT 1";

    $result = $conn->query($sql);

    $rows = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }

    $lastIrrDatetime = new DateTime($rows[0]['irr_start_datetime']);

    //cal next irr date
    $nextIrrDateTime = $lastIrrDatetime->add(new DateInterval("P{$autoIrrInfo->howOften}D"));
    $nowDatetime = new DateTime();

    //compare
    if ($nowDatetime->format('Y-m-d') >= $nextIrrDateTime->format('Y-m-d')) {

        if ($nowDatetime->format('H') == $autoIrrInfo->hour && $nowDatetime->format('i') >= $autoIrrInfo->minute) {
            //send command
            $cmdInfoJson = file_get_contents($cmdAd);
            $cmdInfo = json_decode($cmdInfoJson);
            $cmdInfo->valveCmd = "open";
            $cmdInfo->durationCmd = $autoIrrInfo->duration;
            $cmdInfoJson = json_encode($cmdInfo);
            file_put_contents($cmdAd, $cmdInfoJson);

            echo "command for auto irr is send";
            file_put_contents($autoIrrMessage, "command for auto irr is send");
            file_put_contents($autoIrrRecAd, "auto irr happened at " . $nowDatetime->format('Y-m-d H:i:s'));
        } else {
            echo "it's irrigation day, irrigation will happen on: {$autoIrrInfo->hour}:{$autoIrrInfo->minute}";
            file_put_contents($autoIrrMessage, "it's irrigation day, irrigation will happen on: {$autoIrrInfo->hour}:{$autoIrrInfo->minute}");
        }
    } else {
        echo "it's not irrigation day";
        echo "irrigation day is " . $nextIrrDateTime->format('Y-m-d');
        file_put_contents($autoIrrMessage, "it's not irrigation day\nirrigation day is " . $nextIrrDateTime->format('Y-m-d'));
    }

    //if it's the day compare the hour and minute
} else {
    echo "auto irr is disable";
    file_put_contents($autoIrrMessage, "auto irr is disable");
}
