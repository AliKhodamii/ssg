<?php
require_once '../db/helper.php'; // This includes DB connection and helper functions
header('Content-Type: application/json');

try {
    // 1. Parse JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['ssg_token'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ssg_token is required']);
        exit;
    }

    // 2. Get device by token
    $device = getDeviceByToken($pdo, $input['ssg_token']);
    if (!$device) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }
    $device_id = $device['id'];

    // 3. Get all valves of this device
    $stmt = $pdo->prepare("SELECT id, name , duration FROM valves WHERE device_id = ?");
    $stmt->execute([$device_id]);
    $valves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$valves) {
        echo json_encode(['commands' => []]); // No valves = no commands
        exit;
    }

    $valveIdToName = [];
    $valveDuation = [];
    $valveIds = [];

    foreach ($valves as $valve) {
        $valveIdToName[$valve['id']] = $valve['name'];
        $valveDuration[$valve['id']] = $valve['duration'];
        $valveIds[] = $valve['id'];
    }

    // 4. Get all pending commands for these valves
    // $inQuery = implode(',', array_fill(0, count($valveIds), '?'));
    $stmt = $pdo->prepare("SELECT id, valve_id, command FROM valve_commands WHERE device_id = ? AND status = 'pending'");
    $stmt->execute([$device_id]);
    $commands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Prepare response
    $response = [];

    foreach ($commands as $command) {
        $response[] = [
            'valve_name' => $valveIdToName[$command['valve_id']],
            'command' => $command['command'],
            'duration' => $valveDuration[$command['valve_id']]
        ];
    }

    // 6. Optionally mark commands as sent (you can change this to 'done' if ESP confirms execution later)
    if (!empty($commands)) {
        $commandIds = array_column($commands, 'id');
        $inCommandQuery = implode(',', array_fill(0, count($commandIds), '?'));
        $updateStmt = $pdo->prepare("UPDATE valve_commands SET status = 'sent' WHERE id IN ($inCommandQuery)");
        $updateStmt->execute($commandIds);
    }

    if (!empty($response)) {
        echo json_encode(['commands' => $response]);
    } else {
        echo json_encode(["commands" => "no commands"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
