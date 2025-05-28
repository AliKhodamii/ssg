<?php
require_once __DIR__ . '/db.php';

function getDeviceByToken($pdo, $ssg_token)
{
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE ssg_token = ?");
    $stmt->execute([$ssg_token]);
    return $stmt->fetch();
}
