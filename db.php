<?php
$host = "localhost";
$user = "u263749830_root123456"; 
$pass = "ZcYk~Dnf+#5"; 
$dbname = "u263749830_jes_job";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
