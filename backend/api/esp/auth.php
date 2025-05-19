<?php
header('Content-Type: application/json');
require_once '../db/helpers.php';

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

echo json_encode([
    'status' => 'ok',
    'device_id' => $device['id']
]);
