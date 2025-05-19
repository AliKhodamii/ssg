<?php
require_once '../db/helper.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['ssg_token']) || !isset($input['executed'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing ssg_token or executed']);
        exit;
    }

    // Get device
    $device = getDeviceByToken($pdo, $input['ssg_token']);
    if (!$device) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }

    $device_id = $device['id'];

    foreach ($input['executed'] as $item) {
        $valveName = $item['valve_name'];
        $command = $item['command'];
        if (!empty($item['duration'])) {
            $duration = $item['duration'];
        }


        // Find the valve
        $stmt = $pdo->prepare("SELECT id FROM valves WHERE device_id = ? AND name = ?");
        $stmt->execute([$device_id, $valveName]);
        $valve = $stmt->fetch();

        if (!$valve) continue; // skip if valve not found

        $valve_id = $valve['id'];

        // Find the matching command
        $stmt = $pdo->prepare("SELECT id FROM valve_commands WHERE valve_id = ? AND command = ? AND status = 'sent' ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$valve_id, $command]);
        $db_command = $stmt->fetch();

        if ($db_command) {
            // Mark command as done
            $stmt = $pdo->prepare("UPDATE valve_commands SET status = 'executed', executed_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$command['id']]);
        }

        // Log irrigation only if command is turn_on (or similar)
        if ($command === 'open') {
            $stmt = $pdo->prepare("INSERT INTO irrigation_record (device_id,valve_id,duration) VALUES (?, ?, ?)");
            if ($stmt->execute([$device_id, $valve_id, $duration])) {
                $e = "no error";
            } else {
                $e = "error";
            }
        }

        //set irr ended time
        if ($command === 'close') {
            // get last irrigation record id
            $stmt = $pdo->prepare("SELECT id FROM irrigation_record WHERE device_id = ? AND valve_id = ? AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1");
            $stmt->execute([$device_id, $valve_id]);
            $irr_rec = $stmt->fetch();

            $lastIrrigationId = $irr_rec["id"];
            $stmt = $pdo->prepare("UPDATE irrigation_record SET ended_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$lastIrrigationId]);
        }
    }

    echo json_encode(['status' => 'acknowledged']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
