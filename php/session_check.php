<?php
ini_set('session.gc_maxlifetime', 604800); // 7 days
session_set_cookie_params(604800);         // 7 days for the cookie
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
