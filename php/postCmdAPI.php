<?php
include 'db.php';

if (!empty($_REQUEST["ssg_token"])) {
    $ssg_token = $_REQUEST['ssg_token'];

    // Optional: Find the most recent row to update
    $selectStmt = $pdo->prepare("SELECT id FROM commands WHERE ssg_token = :ssg_token ORDER BY created_at DESC LIMIT 1");
    $selectStmt->execute([':ssg_token' => $ssg_token]);
    $row = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $updateStmt = $pdo->prepare("UPDATE commands SET status = :status WHERE id = :id");
        $updateStmt->execute([
            ':status' => 'executed',
            ':id' => $row['id']
        ]);
        echo "Update successful";
    } else {
        echo "No matching record found";
    }

    // insert req to db
    if ($_REQUEST["data"] != null) {
        $cmdJson = $_REQUEST["data"];
        $cmd = json_decode($cmdJson, true);
        if ($cmd["cmd"]) {
            $stmt = $pdo->prepare("INSERT INTO irr_rec (ssg_token , duration) values (:ssg_token , :duration)");
            if ($stmt->execute(["ssg_token" => $ssg_token, "duration" => $duration])) {
                echo "inserting irr_rec was successful";
            } else {
                echo "insertion irr_rec failed.";
            }
        }
    }
} else {
    echo "No ssg_token provided";
}
