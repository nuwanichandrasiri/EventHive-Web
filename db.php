<?php
$host = "localhost";
$dbname = "eventhive";
$username = "root";
$password = "";

// Connect to MySQL database
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>