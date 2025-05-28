<?php
require_once '../db/helper.php'; // includes $pdo and getDeviceByToken
require '../../lib/jdatetime.class.php';

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


    $response = [];
    $valves = [];
    foreach ($irrigation_records as $record) {
        $valves["valve" . $record['valve_id']][] = [
            "date" => farsiDate($record['started_at']),
            "day_of_week" => farsiDayOfWeek($record['started_at']),
            "time" => farsiTime($record['started_at']),
            "duration" => $record['duration']
        ];
    }
    // print_r($valves);
    $response[] = $valves;

    echo json_encode(['success' => true, "records" => $response]);

    // echo json_encode(['success' => false, 'message' => 'Config update failed']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}



function farsiDayOfWeek($date)
{
    $jdate = new jDateTime();
    $timestamp = strtotime($date);
    $day_of_week = $jdate->date("l", $timestamp);
    return $day_of_week;
}

function farsiTime($date)
{
    $timestamp = strtotime($date);
    $time = date("H:i", $timestamp);
    return $time;
}

function farsiDate($date)
{
    $jdate = new jDateTime();
    $timestamp = strtotime($date);
    $farsiDate = $jdate->date("d/F", $timestamp);
    return $farsiDate;
}
