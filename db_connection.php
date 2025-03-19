<?php
// db_connection.php
$servername = "localhost"; // Replace with your server name
$username = "root"; // Replace with your database username
$password = "2025Spring"; // Replace with your database password
$dbname = "lab4"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
