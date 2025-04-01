<?php
include 'db.php';

// Set timezone to Sri Lanka
date_default_timezone_set('Asia/Colombo');

// Configure 10-hour session settings (36000 seconds)
ini_set('session.gc_maxlifetime', 36000);
ini_set('session.cookie_lifetime', 36000);
session_set_cookie_params([
    'lifetime' => 36000,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']), // Auto-detect HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

// Check for inactivity timeout (10 hours)
if (time() - $_SESSION['LAST_ACTIVITY'] > 36000) {
    session_unset();
    session_destroy();
    header('Location: login.php?reason=timeout');
    exit();
}

// Update last activity time stamp
$_SESSION['LAST_ACTIVITY'] = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    
    // If status is being set to Finished, update completion_date with current date
    if ($status === 'Finished') {
        $currentDate = date('Y-m-d H:i:s'); // This will now use Colombo time
        $sql = "UPDATE job_status SET status = ?, completion_date = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $currentDate, $id);
    } else {
        $sql = "UPDATE job_status SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);
    }
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    $stmt->close();
}
?>