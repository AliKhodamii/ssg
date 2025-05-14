<?php
session_start();
$ssg_token = $_SESSION['ssg_token'];

require 'db.php';

if ($_POST['cmd'] != null) {
    $cmd = json_decode($_POST['cmd'], true);
    $valve = $cmd['valveCmd'];
    $duration = (int) $cmd['durationCmd'];

    if ($valve == 'open') {
        $stmt = $pdo->prepare("INSERT INTO commands (ssg_token,cmd,duration) VALUES (:ssg_token, :cmd , :duration)");
        if ($stmt = $stmt->execute(["ssg_token" => $ssg_token, "cmd" => $valve, "duration" => $duration])) {
            echo "cmd inserted successfully";
        } else {
            echo "cmd insertion failed.";
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO commands (ssg_token,valve) VALUES (:ssg_token,:valve)");
        if ($stmt = $stmt->execute(["ssg_token" => $ssg_token, "valve" => $valve])) {
            echo "cmd inserted successfully";
        } else {
            echo "cmd insertion failed";
        }
    }
}
