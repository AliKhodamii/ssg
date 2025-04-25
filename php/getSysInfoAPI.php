<?php

include 'db.php';

if (!empty($_REQUEST["ssg_token"])) {
    $ssg_token = $_REQUEST["ssg_token"];

    $stmt = $pdo->prepare("SELECT * FROM valves WHERE ssg_token = :ssg_token LIMIT 1");

    if ($stmt->execute([":ssg_token" => $ssg_token])) {
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $res["duration"] = (int) $res["duration"];

        if ($res) {
            echo json_encode($res);
        } else {
            echo json_encode(["message" => "No valve found"]);
        }
    } else {
        echo json_encode([
            "error" => "Query execution failed",
            "details" => $stmt->errorInfo()
        ]);
    }
} else {
    echo json_encode(["error" => "No ssg_token provided"]);
}
