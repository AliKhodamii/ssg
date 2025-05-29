<?php
session_start();

require 'jdatetime.class.php';
require 'db.php';

header('Content-Type: application/json');

try {
    // Validate session
    if (!isset($_SESSION['ssg_token'])) {
        echo json_encode([
            "message" => "Session token missing"
        ]);
        exit();
    }

    $ssg_token = $_SESSION['ssg_token'];

    $stmt = $pdo->prepare(("SELECT * FROM devices WHERE ssg_token = :ssg_token"));
    $stmt->execute(([':ssg_token' => $ssg_token]));
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    $device_id = $device["id"];

    // Get last irrigation record
    $stmt = $pdo->prepare("SELECT * FROM irrigation_record WHERE device_id = :device_id ORDER BY started_at DESC LIMIT 1");
    $stmt->execute([':device_id' => $device_id]);
    $irr_rec = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$irr_rec) {
        echo json_encode([
            "message" => "No irrigation records found"
        ]);
        exit();
    }

    // Get auto irrigation info
    $stmt = $pdo->prepare('SELECT * FROM valves WHERE device_id = :device_id');
    $stmt->execute([':device_id' => $device_id]);
    $auto_irr_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$auto_irr_info) {
        echo json_encode([
            "message" => "Auto irrigation settings not found"
        ]);
        exit();
    }

    $how_often = (int)$auto_irr_info['auto_irr_often'];
    if ($how_often <= 0) {
        echo json_encode([
            "message" => "Invalid irrigation frequency"
        ]);
        exit();
    }

    $last_irr_date = new DateTime($irr_rec['start_datetime']);
    $next_irr_date = (clone $last_irr_date)->add(new DateInterval("P{$how_often}D"));
    $next_irr_miladi = $next_irr_date->format('Y-m-d');

    $next_irr_shamsi = (new jDateTime(false, true))->convertFormatToFormat('d / m / Y', 'Y-m-d', $next_irr_miladi);

    echo json_encode([
        'success' => true,
        'next_irr_shamsi' => $next_irr_shamsi
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
