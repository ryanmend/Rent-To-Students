<!-- Ryan Mendoza 100409153 -->

<?php

//Connect to DB
$servername = "localhost";
$username = "root";
$password = "2025Spring";
$dbname = "item_rentlist";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$result = $conn->query("SELECT id, description, priority, deadline FROM items");
?>


<style>

    body {
        align-content: center;
    }

</style>
