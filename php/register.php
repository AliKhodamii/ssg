<?php
require_once 'db.php';
ini_set('session.gc_maxlifetime', 604800); // 7 days
session_set_cookie_params(604800);         // 7 days for the cookie
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $username = strtolower(trim($_POST['username']));
    $password = $_POST['password'];
    $ssgToken = trim($_POST['ssg_token']);

    // Validation
    $errors = [];

    if (strlen($username) < 4) {
        $errors[] = "نام کاربری باید حداقل 4 کارکتر داشته باشد";
    }

    if (strlen($password) < 8) {
        $errors[] = "گذرواژه باید حداقل 8 کارکتر داشته باشد";
    }

    if (empty($ssgToken)) {
        $errors[] = "SSG Token را وارد کنید";
    }

    // Check if username already exists
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "این نام کاربری قبلا استفاده شده است";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }

    // Verify SSG token exists in tokens table
    try {
        $stmt = $pdo->prepare("SELECT id FROM tokens WHERE ssg_token = ?");
        $stmt->execute([$ssgToken]);
        if ($stmt->rowCount() === 0) {
            $errors[] = "SSG Token نامعتبر است";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }

    // If there are errors, redirect back with messages
    if (!empty($errors)) {
        header("Location: ../register.html?error=" . urlencode(implode(", ", $errors)));
        exit();
    }

    // If validation passes, proceed with registration
    try {
        $pdo->beginTransaction();


        // 2. Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 3. Create the user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, ssg_token) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, $ssgToken]);

        $pdo->commit();

        // Registration successful
        header("Location: ../register.html?success=" . urlencode("Registration successful! You can now login"));
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../register.html?error=" . urlencode("Registration failed: " . $e->getMessage()));
        exit();
    }
}

// If not a POST request, redirect to register page
header("Location: ../register.html");
exit();
