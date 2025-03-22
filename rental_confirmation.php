<?php
require_once "db_connection.php";
include "navbar.php"; 
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = $_POST['item_id'];
    $rental_hours = $_POST['rental_hours'];

    // Fetch item details from database
    $sql = "SELECT rental_price FROM items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $row = $result->fetch_assoc();
        $rental_price_per_hour = $row['rental_price'];

        // Calculate total cost
        $total_cost = $rental_hours * $rental_price_per_hour;

        echo "<h2>Rental Confirmation</h2>";
        echo "<p>Item ID: " . htmlspecialchars($item_id) . "</p>";
        echo "<p>Rental Hours: " . htmlspecialchars($rental_hours) . "</p>";
        echo "<p>Total Cost: $" . number_format($total_cost, 2) . "</p>";

        // Payment form (replace with your actual payment integration)
        echo "<form method='POST' action='payment_processing.php'>";
        echo "<input type='hidden' name='item_id' value='" . htmlspecialchars($item_id) . "'/>";
        echo "<input type='hidden' name='rental_hours' value='" . htmlspecialchars($rental_hours) . "'/>";
        echo "<input type='hidden' name='total_cost' value='" . number_format($total_cost, 2) . "'/>";
        echo "<button type='submit'>Proceed to Payment</button>";
        echo "</form>";

    } else {
        echo "Error fetching item details.";
    }

    $stmt->close();
    $result->free();
} else {
    
    header("Location: index.php"); 
    exit;
}

$conn->close();
?>
<link rel="stylesheet" href="css/styles.css">
