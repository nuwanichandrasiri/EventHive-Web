<?php
$host = "sql212.infinityfree.com";
$dbname = "if0_42413935_epiz_eventhive";
$username = "if0_42413935";
$password = "Ma9B6Qim1S";

// Connect to MySQL database
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
