<?php
include 'db.php';
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
    
    $stmt = $conn->prepare("UPDATE job_status SET hidden = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
}
?>