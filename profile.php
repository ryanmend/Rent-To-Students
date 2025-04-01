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
$successMessage = "";

// Check for success message from return confirmation
if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']); // Clear the message after displaying
}

$items_for_rental = [];
$rented_items = [];

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
        // Retrieve the item details from the database using the item_name and lessor_id
        $stmt = $conn->prepare("SELECT item_id, availability FROM items WHERE item_name = ? AND lessor_id = ?");
        $stmt->bind_param("si", $item_name, $lessor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $item_id = $row['item_id'];
            $availability = $row['availability'];

            // Check if the item is available (availability = 0) before deleting
            if ($availability == 0) {
                // Delete the item
                $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
                $stmt->bind_param("i", $item_id);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $successMessage = "Item successfully deleted.";
                } else {
                    $errorMessage = "Error: Unable to delete the item.";
                }
            } else {
                $errorMessage = "Error: Cannot delete an item that is currently being rented.";
            }
        } else {
            $errorMessage = "Error: Item not found or you do not have permission to delete it.";
        }
    } else {
        $errorMessage = "Error: Invalid Item Name received.";
    }
}

// Fetch items for rental (items where the current user is the lessor)
$stmt = $conn->prepare("SELECT * FROM items WHERE lessor_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Store items for rental in an array
while ($row = $result->fetch_assoc()) {
    $items_for_rental[] = $row;
}

// Fetch rented items (items where the current user is the renter)
$stmt = $conn->prepare("SELECT * FROM items WHERE renter_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Store rented items in an array
while ($row = $result->fetch_assoc()) {
    $rented_items[] = $row;
}

// Fetch reviews for the current user (where they are the lessor)
$reviews = [];
$avg_rating = 0;
$total_reviews = 0;

// Updated query using DISTINCT to prevent duplicate reviews
$stmt = $conn->prepare("SELECT DISTINCT r.review_id, r.review, r.stars, r.renter_id, u.username 
                        FROM reviews r 
                        JOIN users u ON r.renter_id = u.user_id 
                        JOIN items i ON r.renter_id = i.renter_id 
                        WHERE i.lessor_id = ? 
                        ORDER BY r.review_id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reviews_result = $stmt->get_result();

// Store reviews in an array
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
$total_reviews = count($reviews);

// Calculate average rating if there are reviews
if ($total_reviews > 0) {
    // Also updated this query to use DISTINCT
    $stmt = $conn->prepare("SELECT AVG(DISTINCT r.stars) as avg_rating 
                           FROM reviews r 
                           JOIN items i ON r.renter_id = i.renter_id 
                           WHERE i.lessor_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $avg_result = $stmt->get_result();
    $avg_data = $avg_result->fetch_assoc();
    $avg_rating = round($avg_data['avg_rating'], 1);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="css/styles.css">
   
</head>
<body>

<h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>

<?php if ($errorMessage != ""): ?>
    <div style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></div>
<?php endif; ?>

<?php if ($successMessage != ""): ?>
    <div style="color: green;"><?php echo htmlspecialchars($successMessage); ?></div>
<?php endif; ?>

<h2>Items for Rental</h2>
<?php if (count($items_for_rental) > 0): ?>
    <div>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
             
                <th>Rental Price</th>
                <th>Availability</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items_for_rental as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                
                    <td>$<?php echo htmlspecialchars($item['rental_price']); ?>/hr</td>
                    <td><?php echo ($item['availability'] == 0) ? 'Available' : 'Not Available'; ?></td>
                    <td>
                        <?php if ($item['availability'] == 0): ?>
                            <form method="post">
                                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>">
                                <button type="submit" class="del-prof-btn" name="delete_item">Delete</button>
                            </form>
                            <form method="get" action="edit_item.php">
                                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>">
                                <button type="submit" class="edit-prof-btn">Edit</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($item['availability'] == 1): ?>
                            <form method="get" action="return_confirmation.php">
                                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>">
                                <button type="submit" class="return-btn">Confirm Returned</button>
                            </form>
                        <?php endif; ?>
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

<h2>Rented Items</h2>
<?php if (count($rented_items) > 0): ?>
    <div>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
              
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rented_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                 
                    <td>$<?php echo htmlspecialchars($item['rental_price']); ?>/hr</td>
                    <td>
                        <?php if ($item['availability'] == 1): ?>
                            <form method="get" action="rental_confirmation.php">
                                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>">
                                <button type="submit" class="confirm-btn">Confirm Received</button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($user_id != $item['lessor_id']): ?>
                            <form method="get" action="leave_review.php">
                                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>">
                                <input type="hidden" name="lessor_id" value="<?php echo htmlspecialchars($item['lessor_id']); ?>">
                                <button type="submit" class="review-btn">Leave Review to Lessor</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php else: ?>
    <p>You have no rented items.</p>
<?php endif; ?>

<!-- Reviews Section - Added at the bottom -->
<div class="reviews-section">
    <h2>Your Reviews</h2>
    
    <?php if ($total_reviews > 0): ?>
        <div class="avg-rating">
            <strong>Average Rating:</strong> <?php echo $avg_rating; ?> / 5
            (<?php echo $total_reviews; ?> <?php echo $total_reviews == 1 ? 'review' : 'reviews'; ?>)
        </div>
        
        <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="stars">
                    <?php 
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $review['stars']) {
                            echo "★";
                        } else {
                            echo "☆";
                        }
                    }
                    ?>
                </div>
                <div class="reviewer-name">
                    By: <?php echo htmlspecialchars($review['username']); ?>
                </div>
                <div class="review-content">
                    <?php echo htmlspecialchars($review['review']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You have not received any reviews yet.</p>
    <?php endif; ?>
</div>

<p>Back to <a href="index.php">Home</a>.</p>

</body>
</html>