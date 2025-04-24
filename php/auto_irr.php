<?php
require 'db.php';

$status = "";

// get auto_irr data
$sql = "SELECT * FROM auto_irr WHERE enable = 1";
$stmt = $pdo->query($sql);
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nowDate = new DateTime();


foreach ($devices as $device) {

    $ssg_token = $device['ssg_token'];
    $duration = (int)$device['duration'];
    $stmt = $pdo->prepare("SELECT * FROM `irr_rec` WHERE `ssg_token` = :ssg_token ORDER BY start_datetime DESC LIMIT 1");
    $stmt->execute(["ssg_token" => $ssg_token]);
    if ($last_irr_info = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $last_irr_datetime = new DateTime($last_irr_info['start_datetime']);
        $next_irr_datetime = $last_irr_datetime->add(new DateInterval("P{$device['how_often']}D"));
        $next_irr_datetime->setTime((int)$device['hour'], (int)$device['minute']);

        $diff_days = $nowDate->diff($next_irr_datetime)->format("%r%a");
        if ($diff_days <= 0) {
            $diff_hour = $nowDate->format("H") - $next_irr_datetime->format("H");
            $diff_minute = $nowDate->format("H") - $next_irr_datetime->format("H");
            if ($diff_hour == 0 && $diff_minute >= 0) {
                // insert cmd in db
                $stmt = $pdo->prepare("INSERT INTO commands (ssg_token,cmd,duration) VALUES (:ssg_token, :cmd, :duration)");
                if ($stmt->execute(["ssg_token" => $ssg_token, "cmd" => "open", "duration" => $duration])) {
                    echo "cmd inserted successfully";
                    $status .= "device - " . $ssg_token . " - auto irr happened at " . $nowDate->format("Y-m-d H:i:s") . "\n";
                } else {
                    echo "cmd insertion failed";
                }
            } else {
                echo "irr must happen today but not now\n";
                $status .= "device - " . $ssg_token . " - auto irr will happen today at " . $next_irr_datetime->format("H:i") . "\n";
            }
        } else {
            echo "irr will happen " . $next_irr_datetime->format("Y-m-d H:i") . "\n";
            $status .= "device -> " . $ssg_token . " - auto irr will happen on" . $next_irr_datetime->format("Y-m-d H:i") . "\n";
        }
    }
}

file_put_contents("../auto_irr_status/status.txt", $status);
