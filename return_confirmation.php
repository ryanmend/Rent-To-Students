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

// Check if item name is provided in the URL
if (!isset($_GET['item_name']) || empty($_GET['item_name'])) {
    $errorMessage = "No item specified for return.";
} else {
    $item_name = $_GET['item_name'];

    // Process return confirmation when form is submitted
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm_return'])) {
        // Prepare statement to update item availability and reset renter_id
        $stmt = $conn->prepare("UPDATE items SET availability = 0, renter_id = NULL WHERE item_name = ? AND lessor_id = ?");
        $stmt->bind_param("si", $item_name, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Redirect to profile with success message
            $_SESSION['successMessage'] = "Item successfully returned.";
            header("Location: profile.php");
            exit;
        } else {
            $errorMessage = "Error: Unable to process return.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirm Item Return</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Confirm Item Return</h1>

    <?php if ($errorMessage != ""): ?>
        <div style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <p>Are you sure you want to confirm that the item "<?php echo htmlspecialchars($item_name); ?>" has been returned?</p>

    <form method="post">
        <button type="submit" name="confirm_return">Yes, Confirm Return</button>
        <button type="button" onclick="window.location.href='profile.php'">Cancel</button>
    </form>

    <p>Back to <a href="profile.php">Profile</a>.</p>
</body>
</html>