<?php
require_once "db_connection.php";
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check for item_id from different sources
$item_id = null;

// 1. Check if item_id is passed via POST (from item_page.php)
if (isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    $_SESSION['selected_item_id'] = $item_id; // Store in session for future use
}
// 2. Check if item_name is passed via GET (from profile.php)
else if (isset($_GET['item_name'])) {
    // Fetch the item_id using the item_name and user_id
    $item_name = $_GET['item_name'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT item_id FROM items WHERE item_name = ? AND lessor_id = ?");
    $stmt->bind_param("si", $item_name, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $item_id = $row['item_id'];
        $_SESSION['selected_item_id'] = $item_id; // Store in session
    } else {
        echo "Item not found.";
        exit;
    }
    $stmt->close();
}
// 3. Check if it's in the session (from previous requests)
else if (isset($_SESSION['selected_item_id'])) {
    $item_id = $_SESSION['selected_item_id'];
}
// If no item_id is found through any method, redirect
else {
    header("Location: index.php");
    exit;
}

// Now fetch the current item details using the retrieved item_id
$stmt = $conn->prepare("SELECT * FROM items WHERE item_id = ? AND lessor_id = ?");
$stmt->bind_param("ii", $item_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Ensure the item exists and belongs to the current user
if ($result->num_rows == 0) {
    echo "Item not found or you do not have permission to edit this item.";
    exit;
}

$item = $result->fetch_assoc();
$item_name = $item['item_name'];
$description = $item['description'];
$category = $item['category'];
$rental_price = $item['rental_price'];
$errorMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_item'])) {
    // Validate and sanitize input
    $item_name = trim($_POST['item_name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $rental_price = floatval($_POST['rental_price']);

    // Validate Input (Important!)
    if (empty($item_name)) {
        $errorMessage = "Item name is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9\s]{1,200}$/", $item_name)) {
        $errorMessage = "Invalid characters in item name. Only letters, numbers and spaces are allowed.";
    }

    if (empty($description)) {
        $errorMessage .= "<br>Description is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9\s]{1,200}$/", $description)) {
        $errorMessage .= "<br>Invalid characters in description. Only letters, numbers and spaces are allowed.";
    }

    if (empty($category)) {
        $errorMessage .= "<br>Category is required.";
    }

    if (!is_numeric($rental_price) || $rental_price <= 0) {
        $errorMessage .= "<br>Rental price must be a positive number.";
    }

    // If no errors, update the item
    if ($errorMessage == "") {
        $update_stmt = $conn->prepare("UPDATE items SET item_name = ?, description = ?, category = ?, rental_price = ? WHERE item_id = ?");
        $update_stmt->bind_param("sssdi", $item_name, $description, $category, $rental_price, $item_id);
        
        if ($update_stmt->execute()) {
            // Redirect to profile page with success message
            echo "<p>Item updated successfully!</p>";
            header("Location: profile.php");
            exit;
        } else {
            $errorMessage = "Error updating item: " . $update_stmt->error;
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Item</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include "navbar.php"; ?>

    <h1>Edit Item</h1>

    <?php if ($errorMessage != ""): ?>
        <div style="color: red;"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <form method="post" action="" class="main-form" name="edit_item_form">
        <label for="item_name">Item Name:</label>
        <input type="text" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item_name); ?>"><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description"><?php echo htmlspecialchars($description); ?></textarea><br><br>

        <label for="category">Category:</label>
        <select id="category" name="category">
            <option value="Electronics" <?php echo ($category == 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
            <option value="Tools" <?php echo ($category == 'Tools') ? 'selected' : ''; ?>>Tools</option>
            <option value="Sports Equipment" <?php echo ($category == 'Sports Equipment') ? 'selected' : ''; ?>>Sports Equipment</option>
            <option value="Musical Instruments" <?php echo ($category == 'Musical Instruments') ? 'selected' : ''; ?>>Musical Instruments</option>
            <option value="Other" <?php echo ($category == 'Other') ? 'selected' : ''; ?>>Other</option>
        </select><br><br>

        <label for="rental_price">Rental Price (per hour):</label>
        <input type="number" id="rental_price" name="rental_price" step="0.01" min="0" value="<?php echo htmlspecialchars($rental_price); ?>"><br><br>

        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">

        <button type="submit" name="save_item">Update Item</button>
        <a href="item_page.php" style="margin-left: 10px;">Cancel</a>
    </form>

    <p>Back to <a href="index.php">Home.</a></p>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>