<?php
session_start();

require 'jdatetime.class.php';
require 'db.php';

header('Content-Type: application/json');

try {
    // Validate session
    if (!isset($_SESSION['ssg_token'])) {
        throw new Exception('Session token missing');
    }

    $ssg_token = $_SESSION['ssg_token'];

    // Get last irrigation record
    $stmt = $pdo->prepare("SELECT * FROM irr_rec WHERE ssg_token = :ssg_token ORDER BY start_datetime DESC LIMIT 1");
    $stmt->execute(['ssg_token' => $ssg_token]);
    $irr_rec = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$irr_rec) {
        throw new Exception('No irrigation records found');
    }

    // Get auto irrigation info
    $stmt = $pdo->prepare('SELECT * FROM auto_irr WHERE ssg_token = :ssg_token');
    $stmt->execute(['ssg_token' => $ssg_token]);
    $auto_irr_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$auto_irr_info) {
        throw new Exception('Auto irrigation settings not found');
    }

    $how_often = (int)$auto_irr_info['how_often'];
    if ($how_often <= 0) {
        throw new Exception('Invalid irrigation frequency');
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
