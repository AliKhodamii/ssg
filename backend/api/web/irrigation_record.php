<?php
require_once '../db/helper.php'; // includes $pdo and getDeviceByToken
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $ssg_token            = $input['ssg_token'];


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

    // Get irrigation record

    $stmt = $pdo->prepare("
        WITH ranked_records AS (
            SELECT 
            *,
            ROW_NUMBER() OVER (PARTITION BY valve_id ORDER BY started_at DESC) AS row_num
            FROM irrigation_record
            WHERE device_id = :device_id
            )
            SELECT * 
            FROM ranked_records
            WHERE row_num <= 5;");
    $stmt->execute([":device_id" => $device_id]);
    $irrigation_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    print_r($irrigation_records);

    $response = [];
    $valves = [];
    foreach ($irrigation_records as $record) {
        $valves["valve" . $record['valve_id']][] = [
            "date" => $record['started_at'],
            "day_of_week" => "sun",
            "time" => "00:18",
            "duration" => $record['duration']
        ];
    }
    print_r($valves);


    echo json_encode(['success' => true, 'message' => 'Config updated']);

    // echo json_encode(['success' => false, 'message' => 'Config update failed']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
