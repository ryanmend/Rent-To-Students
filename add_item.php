<?php

require_once "db_connection.php";
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$servername = ""; // Remove these, they are not needed as db_connection.php handles it
$username = "";
$password = "";
$dbname = "";

// Initialize variables
$item_name = $description = $category = $rental_price = "";
$isEdit = false;
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_item"])) {
    $item_name = $_POST["item_name"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $rental_price = $_POST["rental_price"];

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


    if ($errorMessage == "") {
        try {
            // Get the lessor_id from the session
            $lessor_id = $_SESSION['user_id'];

            // Determine if it's an update or insert
            if (isset($_POST["item_id"]) && !empty($_POST["item_id"])) {
                $itemId = (int)$_POST["item_id"]; // Get item ID for update

                // Update existing item
                $stmt = $conn->prepare(
                    "UPDATE items SET item_name = ?, description = ?, category = ?, rental_price = ?, lessor_id = ? WHERE id = ?"
                );
                $stmt->bind_param("ssssii", $item_name, $description, $category, $rental_price, $lessor_id, $itemId);

                if ($stmt->execute()) {
                    echo "<p>Item updated successfully!</p>";
                } else {
                    $errorMessage = "Error updating item: " . $stmt->error;
                }


            } else {
                // Add new item
                $stmt = $conn->prepare(
                    "INSERT INTO items (item_name, description, category, rental_price, lessor_id) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("ssssi", $item_name, $description, $category, $rental_price, $lessor_id);

                if ($stmt->execute()) {
                    echo "<p>Item added successfully!</p>";
                } else {
                    $errorMessage = "Error adding item: " . $stmt->error;
                }
            }


            header("Location: profile.php"); // Redirect after success
            exit();

        } catch (Exception $e) {
            $errorMessage = "Error saving item: " . $e->getMessage();
        }
    }
}

// Editing a item
if (
    $_SERVER["REQUEST_METHOD"] === "GET" &&
    isset($_GET["edit_item"]) &&
    isset($_GET["item_id"])
) {
    $itemId = (int)$_GET["item_id"]; // Cast to integer for safety

    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($item = $result->fetch_assoc()) {
        $item_name = $item["item_name"];
        $description = $item["description"];
        $category = $item["category"];
        $rental_price = $item["rental_price"];
        $isEdit = true;
    } else {
        // Handle case where item is not found.  Important!
        echo "<p>Error: Item not found.</p>";
    }
}

// Deleting a item (No changes needed here)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_item"])) {
    $itemId = (int) $_POST["item_id"];

    if (empty($itemId)) {
        die("<p>Error: Invalid Item ID received.</p>");
    }

    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    if ($stmt->error) {
        die("<p>SQL Error in item deletion: " . $stmt->error . "</p>");
    }

    if ($stmt->affected_rows > 0) {
        echo "<p>Item deleted successfully!</p>";
        header("Location: profile.php");
        exit();
    } else {
        die("<p>Error: Item not found.</p>");
    }
}

// Complete a item (No changes needed here)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["complete_item"])) {
    $itemId = (int) $_POST["item_id"];

    if (empty($itemId)) {
        die("<p>Error: Invalid Item ID received.</p>");
    }

    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    if ($stmt->error) {
        die("<p>SQL Error in item completion: " . $stmt->error . "</p>");
    }

    if ($stmt->affected_rows > 0) {
        echo "<p>Item completed successfully!</p>";
        header("Location: profile.php");
        exit();
    } else {
        die("<p>Error: Item not found.</p>");
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Item</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<h1>Item Management</h1>

<?php if ($errorMessage != ""): ?>
    <div style="color: red;"><?php echo $errorMessage; ?></div>
<?php endif; ?>


<form method="post" action="" <?php if($isEdit) echo "name='edit_item_form'" ?>>
    <label for="item_name">Item Name:</label>
    <input type="text" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item_name); ?>"><br><br>

    <label for="description">Description:</label>
    <textarea id="description" name="description"><?php echo htmlspecialchars($description); ?></textarea><br><br>

    <label for="category">Category:</label>
    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($category); ?>"><br><br>

    <label for="rental_price">Rental Price:</label>
    <input type="number" id="rental_price" name="rental_price" step="0.01" min="0" value="<?php echo htmlspecialchars($rental_price); ?>"><br><br>

    <?php if ($isEdit): ?>
        <input type="hidden" name="item_id" value="<?php echo $itemId; ?>">
    <?php endif; ?>


    <button type="submit" name="save_item"><?php echo $isEdit ? 'Update Item' : 'Add Item'; ?></button>
</form>
    <p>Back to <a href="index.php">Home.</a></p>

</body>
</html>
