<?php
header('Content-Type: application/json');
require_once '../db/helper.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ssg_token'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ssg_token']);
    exit;
}

$device = getDeviceByToken($pdo, $input['ssg_token']);
if (!$device) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

$device_id = $device['id'];

// Update millis
if (!empty($input['millis'])) {
    $stmt =  $pdo->prepare("UPDATE devices SET millis = :millis WHERE id = :device_id");
    $stmt->execute([":millis" => $input['millis'], ":device_id" => $device_id]);
}

//  Handle valves
if (!empty($input['valves'])) {
    foreach ($input['valves'] as $valve) {
        $stmt = $pdo->prepare("
            UPDATE valves
            SET status = :status
            WHERE name = :name AND device_id = :device_id
        ");
        $stmt->execute([
            ":status" => $valve['status'] ? 1 : 0,
            ":name" => $valve['name'],
            ":device_id" => $device_id
        ]);
    }
}

//  Handle humidity sensors
if (!empty($input['humiditySensors'])) {
    foreach ($input['humiditySensors'] as $sensor) {
        // Find sensor by name and device
        $stmt = $pdo->prepare("SELECT id FROM humidity_sensors WHERE name = ? AND device_id = ?");
        $stmt->execute([$sensor['name'], $device_id]);
        $row = $stmt->fetch();

        if (!$row) {
            // Optionally, auto-create sensor if not found
            $stmt = $pdo->prepare("INSERT INTO humidity_sensors (name, value, device_id) VALUES (?, ?, ?)");
            $stmt->execute([$sensor['name'], $sensor['value'], $device_id]);
            $sensor_id = $pdo->lastInsertId();
        } else {
            $sensor_id = $row['id'];

            // Update value
            $stmt = $pdo->prepare("UPDATE humidity_sensors SET value = ? WHERE id = ?");
            $stmt->execute([$sensor['value'], $sensor_id]);
        }

        // Insert into log table
        // $stmt = $pdo->prepare("INSERT INTO sensor_logs (sensor_id, value) VALUES (?, ?)");
        // $stmt->execute([$sensor_id, $sensor['value']]);
    }
}

echo json_encode(['status' => 'data_updated']);
