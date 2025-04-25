<?php
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

    // Update valve table
    $stmt = $pdo->prepare("UPDATE valves SET status = :valve WHERE ssg_token = :ssg_token");
    $stmt->execute([
        ":valve" => $sysInfo["valve"],
        ":ssg_token" => $ssg_token
    ]);

    // Update humidity table
    $stmt = $pdo->prepare("UPDATE humidity_sensors SET humidity_value = :humidity WHERE ssg_token = :ssg_token");
    $stmt->execute([
        ":humidity" => $sysInfo["humidity"],
        ":ssg_token" => $ssg_token
    ]);

    echo "Update successful";
} else {
    echo "Missing ssg_token or data";
}
