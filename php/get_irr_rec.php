<?php
// Start session and set headers first
session_start();
header('Content-Type: application/json');

// Validate session first
if (!isset($_SESSION['ssg_token'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized - Session token missing']));
}

// Database configuration
$hostname = 'localhost:3306';
$username = 'jjqioyps_ssg';
$password = '123456';
$database = 'jjqioyps_ssg';

require_once('jdatetime.class.php');

try {
    // Create connection with error handling
    $conn = new mysqli($hostname, $username, $password, $database);

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Prepare statement with parameter binding
    $stmt = $conn->prepare("SELECT * FROM `irr_rec` WHERE ssg_token = ? ORDER BY `start_datetime` DESC LIMIT 5");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameter securely
    $stmt->bind_param("s", $_SESSION['ssg_token']);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            try {
                $jdate = new jDateTime(false, true);
                $row["date"] = $jdate->convertFormatToFormat('d / F', 'Y-m-d H:i:s', $row["start_datetime"]);
                $row["farsiDay"] = $jdate->convertFormatToFormat('l', 'Y-m-d H:i:s', $row["start_datetime"]);
                $row["time"] = $jdate->convertFormatToFormat('H:i', 'Y-m-d H:i:s', $row["start_datetime"]);
                $row['irr_duration'] = $row['duration'];
                $data[] = $row;
            } catch (Exception $e) {
                // Log date conversion errors but continue
                error_log("Date conversion error: " . $e->getMessage());
                $data[] = $row; // Add unconverted data
            }
        }
    }

    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // Close connections in finally block
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
