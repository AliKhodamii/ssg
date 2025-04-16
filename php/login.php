<?
session_start();

require 'db.php';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // check if username exist
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['ssg_token'] = $user['ssg_token'];

        echo json_encode(['success' => true, 'username' => $user['username']]);
    } else {
        echo json_encode(['success' => false, 'message' => "Username or Password is wrong!"]);
    }
}
