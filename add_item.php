<!-- Ryan Mendoza 100409153 -->

<?php
//db_connection.php
require_once "db_connection.php";

$servername = "localhost";
$username = "root";
$password = "2025Spring";
$dbname = "item_rentlist";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//$result = $conn->query("SELECT id, description, priority, deadline FROM items");
?>

<?php
// Initialize variables
$itemId = $description = $priority = $deadline = "";
$isEdit = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_item"])) {
    $description = $_POST["description"];
    $priority = $_POST["priority"];
    $deadline = $_POST["deadline"];
    $itemId = $_POST["item_id"]; // Fix: Fetch the correct item ID

    if (!empty($itemId)) {
        // Update existing item
        $stmt = $conn->prepare(
            "UPDATE items SET description = ?, priority = ?, deadline = ? WHERE id = ?"
        );
        $stmt->bind_param("sssi", $description, $priority, $deadline, $itemId);
        $stmt->execute();

        echo "<p>Task updated successfully!</p>";
        header("Location: index.php"); // Redirect back after update
        exit();
    } else {
        // Add new item
        $stmt = $conn->prepare(
            "INSERT INTO items (description, priority, deadline) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $description, $priority, $deadline);
        $stmt->execute();

        echo "<p>Task added successfully!</p>";
        header("Location: index.php");
        exit();
    }
}

// Editing a item
if (
    $_SERVER["REQUEST_METHOD"] === "GET" &&
    isset($_GET["edit_item"]) &&
    isset($_GET["item_id"])
) {
    $itemId = $_GET["item_id"];
    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($item = $result->fetch_assoc()) {
        $description = $item["description"];
        $priority = $item["priority"];
        $deadline = $item["deadline"];
        $isEdit = true;
    }
}

// Deleting a item
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_item"])) {
    $itemId = (int) $_POST["item_id"];

    // Check if item id is valid
    if (empty($itemId)) {
        die("<p>Error: Invalid Task ID received.</p>");
    }

    // Delete the item
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    if ($stmt->error) {
        die("<p>SQL Error in item deletion: " . $stmt->error . "</p>");
    }

    if ($stmt->affected_rows > 0) {
        echo "<p>Task deleted successfully!</p>";
        header("Location: index.php");
        exit();
    } else {
        die("<p>Error: Task not found.</p>");
    }
}

// Complete a item
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["complete_item"])) {
    $itemId = (int) $_POST["item_id"];

    // Check if item id is valid
    if (empty($itemId)) {
        die("<p>Error: Invalid Task ID received.</p>");
    }

    // Complete the item
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    if ($stmt->error) {
        die("<p>SQL Error in item completion: " . $stmt->error . "</p>");
    }

    if ($stmt->affected_rows > 0) {
        echo "<p>Task completed successfully!</p>";
        header("Location: index.php");
        exit();
    } else {
        die("<p>Error: Task not found.</p>");
    }
}

// Validated user input
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form values
    $description = trim($_POST["description"]);
    $priority = $_POST["priority"];
    $deadline = $_POST["deadline"];

    // Validation for description
    if (empty($description)) {
        $errorMessage = "Task description is required";
    } elseif (!preg_match("/^[a-zA-Z0-9\s]{1,200}$/", $description)) {
        $errorMessage =
            "Invalid characters in item description. Only letters, numbers and spaces are allowed.";
    }

    // If deadline is empty or invalid format
    if (!isset($_POST["deadline"]) || $_POST["deadline"] < date("Y-m-d\TH:i")) {
        $errorMessage = "Please select a valid deadline";
    }

    // If there are no errors, process the form and insert/update the item
    if ($errorMessage == "") {
        try {
            // Your database insertion code here

            echo "<div class='success-message'>Task successfully saved!</div>";
        } catch (Exception $e) {
            $errorMessage = "Error saving item: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Adding New Task</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #8395a7;
                margin: 0;
                padding: 0;
                color: #333;
                align-content: center;
            }

            h1, h2{
                text-align: center;
                color: #444;
            }

            table {
                width: 80%;
                margin: 20px auto;
                border-collapse: collapse;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            table th, table td {
                padding: 12px;
                border: 1px solid #ddd;
                text-align: center;
            }

            table th {
                background-color: #f8f8f8;
                color: #555;
                font-weight: bold;
            }

            table tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            table tr:hover {
                background-color: #f1f1f1;
            }

            form {
                width: 60%;
                margin: 20px auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #fff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            form label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
            }

            form input[type="textarea"],
            form input[type="number"],
            form input[type="file"] {
                width: 100%;
                padding: 10px;
                margin-bottom: 10px;
                border: 1px solid #ddd;
                border-radius: 3px;
            }

            form button {
                background-color: #28a745;
                text-align: center;
                text-align-all: center;
                align-content: center;
                width: 100%;
                padding: 10px;
                margin-bottom: 10px;
                border: 1px solid #ddd;
                border-radius: 3px;
                cursor: pointer;
            }

            form button:hover {
                background-color: #218838;
            }

            small {
                display: block;
                margin-top: 5px;
                color: #888;
            }

            form button[type="submit"][name="delete_item"] {
                background-color: #dc3545;
                text-align: center;
            }

            form button[type="submit"][name="save_item"] {
                text-align: center;
                align-content: center;
                padding: 10px 15px;
                border: none;
                border-radius: 3px;
                cursor: pointer;
            }


            .error {
                color: red;
                padding: 10px;
                margin-bottom: 20px;
            }

            .success-message {
                color: green;
                padding: 10px;
                background-color: #e8f5ff;
                border: 1px solid #b2d7ff;
                margin-bottom: 20px;
            }

            p {
                color: red;
            }

        </style>
    </head>
    <body>
        <?php
        require_once "db_connection.php";

        $itemId = $description = $priority = $deadline = "";
        $isEdit = false;

        if ($isEdit) {
            echo "<h2>Edit Task</h2>";
        } 
        ?>

        <form action="add_item.php" method="POST">
            <input type="hidden" name="item_id" value="<?php echo htmlspecialchars(
                $itemId
            ); ?>">
            <h1>Add Task</h1><br>

            <label for="description">Task Description:</label><br>
            <input type="textarea" id="description" name="description" 
                   value="<?php echo htmlspecialchars(
                       $description
                   ); ?>" required><br>

            <label for="priority">Priority:</label><br>
            <select name="priority">
                <option value="Low" <?php if ($priority == "Low") {
                    echo "selected";
                } ?>>Low</option>
                <option value="Medium" <?php if ($priority == "Medium") {
                    echo "selected";
                } ?>>Medium</option>
                <option value="High" <?php if ($priority == "High") {
                    echo "selected";
                } ?>>High</option>
            </select><br>
            <br>
            <label for="deadline">Deadline:</label><br>
            <input type="datetime-local" id="deadline" name="deadline"
                   value="<?php echo htmlspecialchars($deadline); ?>"><br>
            <br>

            <br><button type="submit" name="save_item" 
                        <?php if (!empty($errorMessage)) { ?>disabled<?php } ?>>
                        <?php if ($isEdit) {
                            echo "Update Task";
                        } else {
                            echo "Add Task";
                        } ?>
            </button>

            <a href="index.php" style="text-align-last: center; display: block;">Cancel</a>
        </form>

    </body>
</html>