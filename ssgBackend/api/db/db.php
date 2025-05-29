<?
$dbname = "jjqioyps_ssg";
$host = "localhost:3306";
$user = "jjqioyps_ssg";
$pass = "123456";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_STRINGIFY_FETCHES => false
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, $options);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
