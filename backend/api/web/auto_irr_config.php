<?php
require_once '../db/helper.php'; // includes $pdo and getDeviceByToken
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $ssg_token            = $input['ssg_token'];
    $valve_info           = $input['valve_info'][0];
    $valve_name           = $valve_info['valve_name'];
    $auto_irr_en          = $valve_info['auto_irr_en'];
    $auto_irr_hour        = $valve_info['auto_irr_hour'];
    $auto_irr_min         = $valve_info['auto_irr_min'];
    $auto_irr_often       = $valve_info['auto_irr_often'];
    $auto_irr_duration    = $valve_info['auto_irr_duration'];

    if (!$ssg_token) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    // Authenticate device
    $device = getDeviceByToken($pdo, $ssg_token);
    if (!$device) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }

    $device_id = $device['id'];


    // Find valve by name and device
    $stmt = $pdo->prepare("SELECT id FROM valves WHERE device_id = ? AND name = ?");
    $stmt->execute([$device_id, $valve_name]);
    $valve = $stmt->fetch();

    if (!$valve) {
        http_response_code(404);
        echo json_encode(['error' => 'Valve not found']);
        exit;
    }

    $valve_id = $valve['id'];

    // Update valve auto irr config
    $stmt = $pdo->prepare("
    UPDATE valves SET auto_irr_en = :auto_irr_en, auto_irr_hour = :auto_irr_hour,
    auto_irr_min = :auto_irr_min, auto_irr_often = :auto_irr_often, auto_irr_duration = :auto_irr_duration
    WHERE id = :valve_id");
    if ($stmt->execute([
        ":auto_irr_en" => $auto_irr_en,
        ":auto_irr_hour" => $auto_irr_hour,
        "auto_irr_min" => $auto_irr_min,
        "auto_irr_often" => $auto_irr_often,
        "auto_irr_duration" => $auto_irr_duration,
        ":valve_id" => $valve_id
    ])) {
        echo json_encode(['success' => true, 'message' => 'Config updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Config update failed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
