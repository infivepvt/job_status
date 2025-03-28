<?php
include 'db.php';
session_start();

// Set timezone to Sri Lanka
date_default_timezone_set('Asia/Colombo');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

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