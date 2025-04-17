<?
$dbname = "jjqioyps_ssg";
$host = "localhost:3306";
$user = "jjqioyps_ssg";
$pass = "123456";

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
