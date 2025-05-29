<?php
require "db.php";
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $ssg_token = $input['ssg_token'];

    if (!$ssg_token) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    // find device id
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE ssg_token = :ssg_token");
    $stmt->execute([":ssg_token" => $ssg_token]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    $device_id = $device["id"];

    // get last command status
    $stmt = $pdo->prepare("SELECT * FROM valve_commands WHERE device_id = :device_id ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([":device_id" => $device_id]);
    $irr_rec = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = json_encode($irr_rec);
    echo $response;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
