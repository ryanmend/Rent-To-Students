<!-- Ryan Mendoza 100409153 -->

<?php
// 
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

//$result = $conn->query("SELECT id, description, priority, created_at, deadline FROM items");
?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Task Manager</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #8395a7;
                margin: 0;
                padding: 0;
                color: #333;

            }

            .main {
                width: 60%;
                margin: 20px auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #fff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            h1, h2 {
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

            form label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
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
                background-color: #ff6b6b;

            }

            form button[type="submit"][name="edit_item"] {
                background-color: #feca57;
                color: black;

            }

            form button[type="submit"][name="completed_item"] {
                background-color: #1dd1a1;

            }

            .item-list {
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
            }

            .item-item {
                border-bottom: 1px solid #ddd;
                margin: 15px 0;
                padding: 15px 0;
            }

            .action-button {

                padding: 5px 10px;
                text-decoration: none;
                border-radius: 3px;
                margin-right: 10px;
            }

            .view-more {
                display: block;
                margin-top: 20px;
                color: #2196F3;
                text-align: center;
            }
        </style>
    </head>
    <body>

        <?php
        require_once "db_connection.php";
        include "navbar.php";

        $itemId = $name = $priority = $imagePath = "";
        $isEdit = false;

        if ($isEdit) {
            echo "<h2>Edit Product</h2>";
        }
        ?>


        <div class ="main">
            <?php
            $result = $conn->query("SELECT * FROM items");
            echo "<h1>Manage Tasks</h1>";

            if ($result->num_rows > 0) {
                echo "<div class='item-list'>";

                while ($item = $result->fetch_assoc()) {

                    // Create a item row
                    $itemId = htmlspecialchars($item["id"]);

                    // Description and Priority
                    $description = htmlspecialchars($item["description"]);
                    $priority = htmlspecialchars($item["priority"]);
                    $created_at = htmlspecialchars($item["created_at"]);

                    $deadline = htmlspecialchars($item["deadline"]);

                    echo "<div class='item-item'>";

                    // Task content display
                    echo "<p><strong>Description:</strong> " .
                        $description .
                        "</p>";
                    echo "<p><strong>Priority:</strong> " . $priority . "</p>";
                    echo "<p><strong>Created at:</strong> " .
                        $created_at .
                        "</p>";
                    echo "<p><strong>Deadline:</strong> " . $deadline . "</p>";

                    // Action buttons
                    echo "<div class='item-actions'>";

                    // Delete button
                    echo "<form method='POST' action='add_item.php'>";
                    echo "<input type='hidden' name='item_id' value='" .
                        htmlspecialchars($itemId) .
                        "'/>";
                    echo "<button type='submit' name='delete_item' class='action-button' onclick=\"showDeleteConfirm('item_id', '$itemId')\"> Delete </button>";

                    // Dialog box with confirmation
                    echo "<div id='deleteDialog' class='dialog' style='display:none;'>Please confirm if you want to delete item #$itemId#<p>Click Yes to continue or Cancel to discard.</p></div>";
                    ?>

                    <script>
                        function hideDialog() {
                            document.getElementById('deleteDialog').style.display = 'none';
                        }

                        function showDeleteConfirm(item_id) {
                            const itemId = <?php echo json_encode($itemId); ?>;

                            let isConfirmed = confirm(`Are you sure you want to delete this item?`);

                            if (isConfirmed) {
                                // Proceed with deletion
                                document.getElementById('deleteDialog').style.display = 'none';
                            } else {
                                header("Location: index.php");
                            }
                        }
                    </script>
                    <?php
                    echo "</form>";

                    // Edit button inside the while loop (inside item-item div)
                    echo "<form method='GET' action='add_item.php'>";
                    echo "<input type='hidden' name='edit_item' value='true'>";
                    echo "<input type='hidden' name='item_id' value='" .
                        $itemId .
                        "'>";
                    echo "<button type='submit' name='edit_item' class='action-button'>Edit</button>";
                    echo "</form>";

                    // Mark as completed button
                    echo "<form method='GET' action='add_item.php'>";
                    echo "<input type='hidden' name='item_id' value='" .
                        $itemId .
                        "'>";
                    echo "<button type='submit' name='completed_item' class='action-button'>Mark as Completed</button>";
                    echo "</form>";

                    echo "</div>"; // End item-actions

                    echo "</div>"; // End item-item

                }

                if ($result->num_rows < 1) {
                    echo "<p>No items found.</p>";
                }
            }

            $result->free();
            $conn->close();
            ?>
        </div>
    </body>
</html>