<?php
require_once '../db/helper.php'; // Include PDO setup and helper functions
header('Content-Type: application/json');

try {
    // Read token from GET or POST
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['ssg_token'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ssg_token is required']);
        exit;
    }

    $ssg_token = $input['ssg_token'];

    // Authenticate device
    $device = getDeviceByToken($pdo, $ssg_token);
    if (!$device) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }

    $device_id = $device['id'];

    // Fetch all valves for this device
    $stmt = $pdo->prepare(" SELECT * FROM valves WHERE device_id = ?");
    $stmt->execute([$device_id]);
    $valves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all humidity sensors
    $stmt = $pdo->prepare(" SELECT * FROM humidity_sensors WHERE device_id = ?");
    $stmt->execute([$device_id]);
    $sensors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Optional: Include basic device info
    $deviceInfo = [
        'id' => $device['id'],
        'ssg_token' => $device['ssg_token']
    ];

    // Return as one combined object
    echo json_encode([
        'device' => $deviceInfo,
        'valves' => $valves,
        'humidity_sensors' => $sensors
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
