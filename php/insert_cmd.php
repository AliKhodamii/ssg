<?php
session_start();

require 'db.php';

if ($_POST) {
    $cmd = json_decode($_POST['cmd']);
    $valve = $cmd['valveCmd'];
    $duration = $cmd['durationCmd'];

    if ($valve == 'open') {
        $stmt = $pdo->prepare("INSERT INTO commands (ssg_token,cmd,duration) VALUES (:ssg_token,:cmd , :duration)");
        $stmt = $stmt->execute(["ssg_token" => $_SESSION['ssg_token'], "cmd" => $valve, "duration" => $duration]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO commands (ssg_token,valve) VALUES (:ssg_token,:valve)");
        $stmt = $stmt->execute(["ssg_token" => $_SESSION['ssg_token'], "valve" => $valve]);
    }
}
