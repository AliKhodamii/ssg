<?php
require 'db.php';

// get auto_irr data
$sql = "SELECT * FROM auto_irr WHERE enable = 1";
$stmt = $pdo->query($sql);
$auto_irrs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($auto_irrs);
echo "\n";

foreach ($auto_irrs as $auto_irr) {
    echo $auto_irr['id'] . '\n';
}
