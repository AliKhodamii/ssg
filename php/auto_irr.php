<?php
require 'db.php';

$status = "";

// get auto_irr data
$sql = "SELECT * FROM valves WHERE auto_irr_en = 1";
$stmt = $pdo->query($sql);
$valves = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nowDate = new DateTime();


foreach ($valves as $valve) {

    $device_id = $valve['device_id'];
    $valve_id = $valve['id'];
    $duration = (int)$valve['auto_irr_duration'];
    $stmt = $pdo->prepare("SELECT * FROM `irrigation_record` WHERE `valve_id` = :valve_id ORDER BY started_at DESC LIMIT 1");
    $stmt->execute([":valve_id" => $valve_id]);
    if ($last_irr_info = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $last_irr_datetime = new DateTime($last_irr_info['started_at']);
        $next_irr_datetime = $last_irr_datetime->add(new DateInterval("P{$valve['auto_irr_often']}D"));
        $next_irr_datetime->setTime((int)$valve['auto_irr_hour'], (int)$valve['auto_irr_minute']);

        $diff_days = $nowDate->diff($next_irr_datetime)->format("%r%a");
        if ($diff_days <= 0) {
            $diff_hour = $nowDate->format("H") - $next_irr_datetime->format("H");
            $diff_minute = $nowDate->format("i") - $next_irr_datetime->format("i");
            if ($diff_hour == 0 && $diff_minute >= 0) {

                // update duration to auto_irr_duration
                $stmt = $pdo->prepare("UPDATE valves SET duration = :duration WHERE id = :valve_id");
                $stmt->execute([":duration" => $duration, ":valve_id" => $valve_id]);

                // insert cmd in db
                $stmt = $pdo->prepare("INSERT INTO valve_commands (device_id,valve_id,command) VALUES (:device_id,:valve_id, :cmd)");
                if ($stmt->execute([":valve_id" => $valve_id, ":cmd" => "open", ":device_id" => $device_id])) {

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
