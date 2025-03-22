<?php
require_once "db_connection.php";

// Start or resume the session (important for accessing session variables)
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Get the user ID from the session
$errorMessage = "";
$items = [];
$lessor_id = $_SESSION['user_id'];

// Obtain username from users table
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = $row['username'];
    $_SESSION['username'] = $username; // Store username in session
} else {
    $username = "Unknown User";
    $_SESSION['username'] = $username;
}

// Process item deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_item"])) {
    // Get the item name from the form submission
    $item_name = $_POST["item_name"];
    $lessor_id = $_SESSION['user_id']; // Ensure lessor_id is correct

    if (!empty($item_name)) {
        // Retrieve the item_id from the database using the item_name and lessor_id
        $stmt = $conn->prepare("SELECT item_id FROM items WHERE item_name = ? AND lessor_id = ?");
        $stmt->bind_param("si", $item_name, $lessor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $item_id = $row['item_id'];

            // Now delete the item with the retrieved item_id
            $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                header("Location: profile.php"); // Redirect to profile page after successful deletion
                exit;
            } else {
                $errorMessage = "Error: Item not found.";
            }
        } else {
            $errorMessage = "Error: Item not found or you do not have permission to delete it.";
        }
    } else {
        $errorMessage = "Error: Invalid Item Name received.";
    }
}

// Fetch all items for the current user
$stmt = $conn->prepare("SELECT * FROM items WHERE lessor_id = ?"); // Corrected query
$stmt->bind_param("i", $lessor_id);
$stmt->execute();
$result = $stmt->get_result();

// Store all items in an array
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<h1>Welcome, <?php echo $_SESSION["username"]; ?>!</h1>

<?php if ($errorMessage != ""): ?>
    <div style="color: red;"><?php echo $errorMessage; ?></div>
<?php endif; ?>

<?php if (count($items) > 0): ?>
    <div>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Description</th>
                <th>Category</th>
                <th>Rental Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <td>$<?php echo htmlspecialchars($item['rental_price']); ?>/hr</td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="item_name" style="del-prof-btn" value="<?php echo htmlspecialchars($item['item_name']); ?>">
                            <button type="submit" class="del-prof-btn" style="del-prof-btn" name="delete_item">Delete</button>
                        </form>
                        <form method="get" action="edit_item.php" style="edit-prof-btn">
                            <input type="hidden" class="edit-prof-btn" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>">
                            <button type="submit" class="edit-prof-btn" style="edit-prof-btn">Edit</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php else: ?>
    
    <form>
        <p>You have no items for rent.</p>
        <button type="submit" formaction="add_item.php">Add Item</button>
    </form>
<?php endif; ?>

<p>Back to <a href="index.php">Home.</a></p>

</body>
</html>