<?php
ini_set('session.gc_maxlifetime', 604800); // 7 days
session_set_cookie_params(604800);         // 7 days for the cookie
session_start();

require 'db.php';

if ($_POST) {
    // get valve and duration
    $stmt = $pdo->prepare('SELECT * FROM valves WHERE ssg_token = :ssg_token');
    $stmt->execute(['ssg_token' => $_POST['ssg_token']]);
    $valve = $stmt->fetch(PDO::FETCH_ASSOC);

    // get humidity
    $stmt = $pdo->prepare('SELECT * FROM humidity_sensors WHERE ssg_token = :ssg_token');
    $stmt->execute(['ssg_token' => $_POST['ssg_token']]);
    $humidity = $stmt->fetch(PDO::FETCH_ASSOC);

    $data = [];

    $data['valve'] = (bool)$valve['status'];
    $data['duration'] = $valve['duration'];
    $data['humidity'] = $humidity['humidity_value'];
    $data['username'] = ucfirst(strtolower($_SESSION['username']));

    echo json_encode($data);
}
