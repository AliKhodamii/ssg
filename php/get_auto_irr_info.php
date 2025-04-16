<?php
require 'db.php';

if ($_POST) {
    // get valve and duration
    $stmt = $pdo->prepare('SELECT * FROM auto_irr WHERE ssg_token = :ssg_token');
    $stmt->execute(['ssg_token' => $_POST['ssg_token']]);
    $autoIrr = $stmt->fetch(PDO::FETCH_ASSOC);

    $autoIrr['hour'] = (int)$autoIrr['hour'];
    $autoIrr['minute'] = (int)$autoIrr['minute'];
    $autoIrr['howOften'] = (int)$autoIrr['how_often'];
    $autoIrr['autoIrrEn'] = (bool)$autoIrr['enable'];

    echo json_encode($autoIrr);
}
