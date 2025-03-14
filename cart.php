<?php
require_once 'db_connection.php';
include 'navbar.php'; // Include the navigation bar

// Fetch and display cart items
$stmt = $conn->prepare("
    SELECT c.id AS cart_item_id, p.name, p.price, p.image_path, c.quantity 
    FROM cart_items c
    INNER JOIN products p ON c.product_id = p.id");
$stmt->execute();
$result = $stmt->get_result();
$grandTotal = 0;

echo "<h1>Your Shopping Cart</h1>";
if ($result->num_rows > 0) {
    echo "<form method='POST' action='cart.php'>";
    echo "<table border='1'>";
    echo "<tr><th>Image</th><th>Name</th><th>Price</th><th>Quantity</th><th>Total</th><th>Actions</th></tr>";
    
    $grandTotal = 0;
    while ($row = $result->fetch_assoc()) {
        $total = $row['price'] * $row['quantity'];
        $grandTotal += $total;
        echo "<tr>";
        
        echo "<td><img src='" . htmlspecialchars($row['image_path']) . "' width='50'></td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>$" . number_format($row['price'], 2) . "</td>";
        
        // Input fields for updating quantity
        echo "<td>";
        echo "<input type='hidden' name='cart_item_ids[]' value='" . $row['cart_item_id'] . "'>";
        echo "<input type='number' name='quantity[" . $row['cart_item_id'] . "]' value='" . $row['quantity'] . "' min='1'>";
        echo "</td>";
        
        echo "<td>$" . number_format($total, 2) . "</td>";
        
        // Buttons
        echo "<td class='Action_Columns'>";
        echo "<button type='submit' name='delete' value='" . $row['cart_item_id'] . "' onclick=\"return confirm('Are you sure you want to delete this item?')\">Delete</button>";
        echo "</td>";
        
        echo "</tr>";
    }
    
    // Grand Total row
    echo "<tr><td colspan='4'><strong>Grand Total</strong></td><td colspan='2'>$" . number_format($grandTotal, 2) . "</td></tr>";
    
    echo "</table>";
    echo "<button type='submit' id='A1' name='update'>Update Cart</button>";
    echo "</form>";
} else {
    echo "<p>Your cart is empty.</p>";
}

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    if (!empty($_POST['cart_item_ids']) && !empty($_POST['quantity'])) {
        foreach ($_POST['cart_item_ids'] as $cartItemId) {
            $newQuantity = $_POST['quantity'][$cartItemId];
            if ($newQuantity > 0) {
                $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                $stmt->bind_param("ii", $newQuantity, $cartItemId);
                $stmt->execute();
            } else {
                // Remove the item if quantity is set to 0
                $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
                $stmt->bind_param("i", $cartItemId);
                $stmt->execute();
                header("Location: cart.php");
                exit;
            }
        }
    }
}

// Handle item deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $cartItemId = $_POST['delete'];
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
    $stmt->bind_param("i", $cartItemId);
    $stmt->execute();
    header("Location: cart.php");
    exit;
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    color: #333;
}
h1 {
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
form input[type="number"] {
    width: 50%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 3px;
    text-align: center;
}
button {
    padding: 10px 15px;
    background-color: #28a745;
    color: #fff;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}
button:hover {
    background-color: #218838;
}
form button[type="submit"][name="delete"] {
    background-color: #dc3545;
    margin-left: 10px;
}
.Action_Columns {
    width: 200px; /* Adjust the width as needed */
}
#A1 {
    text-align: center;
    display: block;
    margin: auto;
}
p {
    text-align: center;
}
</style>