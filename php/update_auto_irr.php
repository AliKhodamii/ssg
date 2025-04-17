<?php
session_start();
$ssg_token = $_SESSION['ssg_token'];

require 'db.php';

if ($_POST['autoIrrInfo'] != null) {
    $auto_irr_info = json_decode($_POST['autoIrrInfo'], true);
    $enable = $auto_irr_info['autoIrrEn'];
    $how_often = (int) $auto_irr_info['howOften'];
    $hour = (int) $auto_irr_info['hour'];
    $minute = (int) $auto_irr_info['minute'];
    $duration = (int) $auto_irr_info['duration'];

    $stmt = $pdo->prepare("UPDATE auto_irr SET `enable` =  :enable , `how_often`= :how_often , `hour` = :hour , `minute` = :minute, `duration` = :duration WHERE ssg_token = :ssg_token");
    if ($stmt = $stmt->execute(["ssg_token" => $ssg_token, "enable" => $enable, "how_often" => $how_often, "duration" => $duration, "hour" => $hour, "minute" => $minute])) {
        echo "auto_irr_info inserted successfully";
    } else {
        echo "auto_irr_info insertion failed.";
    }
}
