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
// Modified query to only select items with availability = 0
$result = $conn->query("SELECT * FROM items WHERE availability = 0");
echo "<h1>Rent-To-Students</h1>";
if ($result->num_rows > 0) {
    echo "<div class='item-list'>";
    while ($item = $result->fetch_assoc()) {
        // Create a item row
        $item_name = htmlspecialchars($item["item_name"]); 
        $description = htmlspecialchars($item["description"]);
        $category = htmlspecialchars($item["category"]); 
        $rental_price = htmlspecialchars($item["rental_price"]); 
        echo "<div class='item-item'>";
        // Item content display
        echo "<p><strong>" . $item_name . "</strong></p>"; 
        echo "<p><strong>Rental Price:</strong> $" . $rental_price . "/hr</p>"; 
        
        // Action buttons
        echo "<div class='item-actions'>";
        
        // View Item button
        echo "<form method='POST' action='item_page.php'>";
        echo "<input type='hidden' name='item_id' value='" . htmlspecialchars($item["item_id"]) . "'/>";
        echo "<button type='submit' class='blue-btn'>View Item</button>";
        echo "</form>";
       
       
        echo "</div>"; // End item-actions
        echo "</div>"; // End item-item
    }
    echo "</div>"; // Close item-list div
} 
if ($result->num_rows < 1) {
    echo "<p>No items found.</p>";
    echo "<div class='add-item-container'>";
    echo "<form method='GET' action='add_item.php'>";
    echo "<button type='submit' class='blue-btn'>Add Item for Rent</button>";
    echo "</form>";
    echo "</div>";
}
$result->free();
$conn->close();
?>
</div>
</body>
</html>