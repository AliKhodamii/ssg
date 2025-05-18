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
        "humidity" : [
            "id" :
            "value" :
            "status" :
        ]
    }

*/

include 'db.php';

if (!empty($_REQUEST["ssg_token"]) && !empty($_REQUEST["data"])) {
    $ssg_token = $_REQUEST['ssg_token'];
    $sysInfoJson = $_REQUEST['data'];

    // Decode JSON as associative array
    $sysInfo = json_decode($sysInfoJson, true);

    if ($sysInfo === null) {
        echo "Invalid JSON";
        exit;
    }

    foreach ($sysInfo->valves as $valve) {
        $v = json_decode($valve);
        $id = $v->id;
        $status = $v->status;
        $duration = $v->duration;
        $name = $v->name;

        // Update valve table
        $stmt = $pdo->prepare("UPDATE valves SET status = :status AND humidity = :duration WHERE ssg_token = :ssg_token AND name = :name");
        $stmt->execute([
            ":status" => $status,
            ":duration" => $duration,
            ":ssg_token" => $ssg_token,
            ":name" => $name
        ]);
    }

    // // Update humidity table
    // $stmt = $pdo->prepare("UPDATE humidity_sensors SET humidity_value = :humidity WHERE ssg_token = :ssg_token");
    // $stmt->execute([
    //     ":humidity" => $sysInfo["humidity"],
    //     ":ssg_token" => $ssg_token
    // ]);

    echo "Update successful";
} else {
    echo "Missing ssg_token or data";
}
