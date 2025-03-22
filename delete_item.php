<?php
require_once "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id'])) {
    $itemId = $_POST['item_id'];

    // Validate $itemId (important for security!)
    if (is_numeric($itemId)) {  // Ensure it's a number.  Add more validation as needed.
        $sql = "DELETE FROM items WHERE item_id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $itemId); // 'i' indicates integer type
            if ($stmt->execute()) {
                // Deletion successful. Redirect back to the page displaying items.
                header("Location: index.php");  // Replace with actual URL
                exit(); // Important: Stop further script execution after redirect
            } else {
                echo "Error deleting item.";
            }
            $stmt->close();
        } else {
            echo "Error preparing statement.";
        }
    } else {
        echo "Invalid Item ID.";  // Handle invalid input gracefully.
    }
} else {
    header("Location: your_item_listing_page.php"); // Redirect if not a POST request or item_id is missing
    exit();
}

$conn->close();
?>
