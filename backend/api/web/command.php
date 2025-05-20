<?php
require_once '../db/helper.php'; // includes $pdo and getDeviceByToken
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $command_info = $input['command_info'][0];

    $ssg_token     = $input['ssg_token']              ?? null;
    $valve_name    = $command_info['valve_name']      ?? null;
    $command       = $command_info['command']         ?? null;
    $duration      = $command_info['duration']        ?? null;

    // print_r($input);


    if (!$ssg_token || !$valve_name || !$command || $duration === null) {
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

    // Check if there is a pending command
    $stmt = $pdo->prepare("SELECT * FROM valve_commands WHERE valve_id = ? AND (status = ? OR status = ?)");
    $stmt->execute([$valve_id, 'pending', 'sent']);
    $pending_command = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($pending_command) {
        http_response_code(401);
        echo json_encode(['error' => 'There is already a pending or sent command']);
        exit;
    }

    // Update valve duration
    $stmt = $pdo->prepare("UPDATE valves SET duration = :duration WHERE id = :valve_id");
    $stmt->execute([":duration" => $duration, ":valve_id" => $valve_id]);


    // Insert command into queue
    $stmt = $pdo->prepare("
        INSERT INTO valve_commands (device_id,valve_id, command, status)
        VALUES (?, ?, ?, 'pending')
    ");
    $stmt->execute([$device_id, $valve_id, $command]);

    echo json_encode(['success' => true, 'message' => 'Command queued']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
