<?php
/*
    data format : json
    example:
    {
        "ssg_token" : 
        "valves" : [
            "id" : 
            "status" :
            "duration" :
            "auto_irr_en" :
            "auto_irr_how_often" :
            "auto_irr_hour" :
            "auto_irr_minute" :
            "auto_irr_duration" :
        ]
        "humiditySensors" : [
            "id" :
            "value" :
            "status" :
        ]
        "millis" :
    }

*/
include '../../db.php';

if (!empty($_REQUEST["ssg_token"]) && !empty($_REQUEST["data"])) {
    $ssg_token = $_REQUEST['ssg_token'];
    $sysInfoJson = $_REQUEST['data'];

    $sysInfo = json_decode($sysInfoJson);

    if ($sysInfo === null) {
        echo "Invalid JSON";
        exit;
    }
    $millis = (int)$sysInfo->millis;
    $errorOccurred = false;

    // update valves status
    foreach ($sysInfo->valves as $valve) {
        $v = json_decode($valve);
        $id = $v->id;
        $status = (bool)$v->status;
        $duration = (int)$v->duration;
        $name = $v->name;

        $stmt = $pdo->prepare("UPDATE valves SET status = :status, duration = :duration, millis = :millis WHERE ssg_token = :ssg_token AND name = :name");

        if (!$stmt->execute([
            ":status" => $status,
            ":duration" => $duration,
            ":ssg_token" => $ssg_token,
            ":name" => $name,
            ":millis" => $millis
        ])) {
            $errorOccurred = true;
            error_log("Query failed for valve: $name");
        } elseif ($stmt->rowCount() === 0) {
            $errorOccurred = true;
            error_log("No row updated for valve: $name");
        }
    }

    // update humidity sensor
    foreach ($sysInfo->humiditySensors as $humiditySensor) {
        $s = json_decode($humiditySensor);
        $name = $s->name;
        $value = (int)$s->value;

        $stmt = $pdo->prepare("UPDATE humidity_sensors SET value = :value, millis = :millis WHERE ssg_token = :ssg_token AND name = :name");

        if (!$stmt->execute([
            "value" => $value,
            ":ssg_token" => $ssg_token,
            ":name" => $name,
            ":millis" => $millis
        ])) {
            $errorOccurred = true;
            error_log("Query failed for valve: $name");
        } elseif ($stmt->rowCount() === 0) {
            $errorOccurred = true;
            error_log("No row updated for valve: $name");
        }
    }

    if ($errorOccurred) {
        echo "Error: Some updates failed.";
    } else {
        echo "Update successful";
    }
} else {
    echo "Missing ssg_token or data";
}
