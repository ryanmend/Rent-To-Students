<?php

//db_connection.php
require_once "db_connection.php";
session_start();

// check if the user is logged in ie. session data exist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
  


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Rent-To-Students</title>
        <link rel="stylesheet" href="css/styles.css">
    </head>
<body>

<?php
require_once "db_connection.php";
include "navbar.php";

$itemId = $name = $priority = $imagePath = "";
$isEdit = false;

if ($isEdit) {
    echo "<h2>Edit Item</h2>";
}
?>


<div class ="main">
<?php
$result = $conn->query("SELECT * FROM items");
echo "<h1>Rent-To-Students</h1>";

if ($result->num_rows > 0) {
    echo "<div class='item-list'>";

    while ($item = $result->fetch_assoc()) {

        // Create a item row

        $item_name = htmlspecialchars($item["item_name"]); // Added this line
        $description = htmlspecialchars($item["description"]);
        $category = htmlspecialchars($item["category"]);  //Added this line
        $rental_price = htmlspecialchars($item["rental_price"]); //Added this line

        echo "<div class='item-item'>";

        // Item content display
        echo "<p><strong>Item Name:</strong> " . $item_name . "</p>"; // Display item name
        echo "<p><strong>Description:</strong> " . $description . "</p>";
        echo "<p><strong>Category:</strong> " . $category . "</p>";  //Display category
        echo "<p><strong>Rental Price:</strong> $" . $rental_price . "/hr</p>"; // Display rental price


        // Action buttons
        echo "<div class='item-actions'>";
        
         // Rent button
        echo "<form method='POST' action='rental_confirmation.php'>";
        echo "<input type='hidden' name='item_id' value='" . htmlspecialchars($item["item_id"]) . "'/>";
        echo "<label for='hours'>Rental Hours:</label>";
        echo "<select id='hours' name='rental_hours'>";
        for ($i = 1; $i <= 24; $i++) { // Allow up to 24 hours
            echo "<option value='" . $i . "'>" . $i . "</option>";
        }
        echo "</select>";
        echo "<button type='submit' class='green-btn'>Rent</button>";
        echo "</form>";

      

        // Mark as returned button
        echo "<form method='GET' action='add_item.php'>";
        echo "<input type='hidden' name='item_id' value='" . htmlspecialchars($item["item_id"]) . "'>"; // Corrected: Use $item['item_id']
        echo "<button type='submit' class='blue-btn' name='completed_item'>Mark as Returned</button>";
        echo "</form>";

       

        echo "</div>"; // End item-actions

        echo "</div>"; // End item-item

    }

    if ($result->num_rows < 1) {
        echo "<p>No items found.</p>";
    }
}

$result->free();
$conn->close();
?>
</div>
</body>
</html>
