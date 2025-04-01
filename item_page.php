<?php
require_once "db_connection.php";
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Check if item_id is passed via POST
if (!isset($_POST['item_id'])) {
    // If not in POST, check if it's in the session
    if (!isset($_SESSION['selected_item_id'])) {
        // If no item_id is found, redirect back to index
        header("Location: index.php");
        exit;
    }
    $item_id = $_SESSION['selected_item_id'];
} else {
    // Store the item_id in session for future access
    $_SESSION['selected_item_id'] = $_POST['item_id'];
    $item_id = $_POST['item_id'];
}
// Prepare SQL to prevent SQL injection
$stmt = $conn->prepare("SELECT i.*, u.username FROM items i INNER JOIN users u ON i.lessor_id = u.user_id WHERE i.item_id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Item not found.";
    exit;
}
$item = $result->fetch_assoc();
// Check if the current user is the item's lessor
$is_lessor = ($_SESSION['user_id'] == $item['lessor_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Item Details</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <form method='GET' action='index.php' class="back-button-form">
        <button type='submit' class='back-button'>&lt;</button>
    </form>
    <?php include "navbar.php"; ?>
    
    <div class="main">
        <div class="item-details-container">
            <h1>Item Details</h1>
            <div class="item-details">
                <p><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
                <p><strong>Rental Price:</strong> $<?php echo htmlspecialchars($item['rental_price']); ?>/hr</p>
                
                <?php if (!$is_lessor): ?>
                <p>
                    <strong>Lessor:</strong> 
                    <a href="other_profile.php?lessor_id=<?php echo htmlspecialchars($item['lessor_id']); ?>">
                        <?php echo htmlspecialchars($item['username']); ?>
                    </a>
                </p>
                <?php endif; ?>
                
                <div class="item-actions">
                    <?php if ($is_lessor): ?>
                        <!-- If the user is the lessor, show Edit Item button instead of Rent -->
                        <form method='POST' action='edit_item.php' class="main-form">
                            <input type='hidden' name='item_id' value='<?php echo htmlspecialchars($item['item_id']); ?>'/>
                            <button type='submit' class='blue-btn'>Edit Item</button>
                        </form>
                    <?php else: ?>
                        <!-- Regular rental form for non-lessor users -->
                        <form method='POST' action='payment_confirmation.php' class="main-form">
                            <input type='hidden' name='item_id' value='<?php echo htmlspecialchars($item['item_id']); ?>'/>
                            <label for='hours'>Rental Hours:</label>
                            <select id='hours' name='rental_hours'>
                                <?php for ($i = 1; $i <= 24; $i++) { ?>
                                    <option value='<?php echo $i; ?>'><?php echo $i; ?></option>
                                <?php } ?>
                            </select>
                            <button type='submit' class='green-btn'>Rent</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>