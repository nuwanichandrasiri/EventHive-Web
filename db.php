<?php
$host = "sqlXXX.infinityfree.com";
$dbname = "epiz_XXXXXXX_eventhive";
$username = "epiz_XXXXXXX";
$password = "your_generated_password";

// Connect to MySQL database
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
