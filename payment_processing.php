<?php
session_start();
require_once "db_connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to process a payment.";
    header("Location: login.php");
    exit;
}

// Check if all required POST data is present
if ($_SERVER["REQUEST_METHOD"] == "POST" && 
    isset($_POST['item_id']) && 
    isset($_POST['rental_hours']) && 
    isset($_POST['total_cost'])) {
    
    try {
        // Start a database transaction for data integrity
        $conn->begin_transaction();

        // Sanitize inputs
        $item_id = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user_id'];

        // Validate inputs
        if ($item_id === false) {
            throw new Exception("Invalid input data.");
        }

        // Update item - set only renter_id and availability
        $update_item_sql = "UPDATE items 
                            SET renter_id = ?, 
                                availability = 1 
                            WHERE item_id = ?";
        $update_stmt = $conn->prepare($update_item_sql);
        $update_stmt->bind_param("ii", $user_id, $item_id);
        $update_stmt->execute();

        // Commit the transaction
        $conn->commit();

        // Set success message
        $_SESSION['success_message'] = "Item rented successfully!";
        header("Location: profile.php");
        exit;
    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        $conn->rollback();

        // Log the error (in a production environment, use proper logging)
        error_log("Rental Processing Error: " . $e->getMessage());

        // Set error message
        $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
        header("Location: payment_confirmation.php");
        exit;
    }
} else {
    // Redirect if accessed without proper form submission
    $_SESSION['error_message'] = "Invalid access.";
    header("Location: index.php");
    exit;
}

// Close connection
$conn->close();
?>