<?php
require_once "db_connection.php";

// Start or resume the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errorMessage = "";
$successMessage = "";

// Check if item_name is provided
if (!isset($_GET['item_name']) || empty($_GET['item_name'])) {
    $errorMessage = "No item specified.";
}

// Process rental confirmation (now without updating availability)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirm_rental"])) {
    $item_name = $_POST['item_name'];
    
    // Verify the item belongs to the current user
    $stmt = $conn->prepare("SELECT item_id FROM items WHERE item_name = ? AND renter_id = ?");
    $stmt->bind_param("si", $item_name, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Confirmation successful, but no changes to the database
        $successMessage = "Item receive confirmed.";
    } else {
        $errorMessage = "You are not authorized to confirm this rental.";
    }
}

// Fetch item details if item_name is provided
if (isset($_GET['item_name']) && empty($errorMessage)) {
    $item_name = $_GET['item_name'];
    
    $stmt = $conn->prepare("SELECT i.item_name, i.description, u.username AS lessor_name 
                            FROM items i
                            JOIN users u ON i.lessor_id = u.user_id
                            WHERE i.item_name = ? AND i.renter_id = ?");
    $stmt->bind_param("si", $item_name, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $errorMessage = "Invalid item or unauthorized access.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rental Confirmation</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Rental Confirmation</h1>

    <?php if (!empty($errorMessage)): ?>
        <div style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></div>
        <p><a href="profile.php">Back to Profile</a></p>
    <?php elseif (!empty($successMessage)): ?>
        <div style="color: green;"><?php echo htmlspecialchars($successMessage); ?></div>
        <p><a href="profile.php">Back to Profile</a></p>
    <?php else: ?>
        <div>
            <h2>Rental Details</h2>
            <?php 
            $item_details = $result->fetch_assoc();
            ?>
            <p>Item: <?php echo htmlspecialchars($item_details['item_name']); ?></p>
            <p>Description: <?php echo htmlspecialchars($item_details['description']); ?></p>
            <p>Lessor: <?php echo htmlspecialchars($item_details['lessor_name']); ?></p>

            <form method="post">
                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($_GET['item_name']); ?>">
                <p>Have you received this item?</p>
                <button type="submit" name="confirm_rental">Yes, I Have Received The Item</button>
            </form>
            <p><a href="profile.php">Cancel</a></p>
        </div>
    <?php endif; ?>
</body>
</html>