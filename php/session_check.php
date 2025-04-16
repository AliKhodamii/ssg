<?php
session_start();

if (isset($_SESSION['username']) || $_SESSION['ssg_token']) {
    echo json_encode([
        'logged_in' => true,
        'username' => $_SESSION['username'],
        'ssg_token' => $_SESSION['ssg_token']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
