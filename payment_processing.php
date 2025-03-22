<html>
<link rel="stylesheet" href="css/styles.css">z
<?php
require_once "db_connection.php";
include "navbar.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = $_POST['item_id'];
    $rental_hours = $_POST['rental_hours'];
    $total_cost = $_POST['total_cost'];

    echo "<h2>Payment Processing</h2>";
    echo "<p>Simulating payment for Item ID: " . htmlspecialchars($item_id) . "</p>";
    echo "<p>Rental Hours: " . htmlspecialchars($rental_hours) . "</p>";
    echo "<p>Total Cost: $" . number_format($total_cost, 2) . "</p>";


    echo "<p>Payment successful! Thank you for renting.</p>";

    // Update item status in database (example - mark as rented)
    $sql = "UPDATE items SET status = 'rented' WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    if ($stmt->execute()) {
        echo "<p>Item marked as rented in the database.</p>";
    } else {
        echo "<p>Error updating item status: " . $conn->error . "</p>";
    }

    $stmt->close();
} else {
    header("Location: index.php");
    exit;
}

$conn->close();
?>

</html>
