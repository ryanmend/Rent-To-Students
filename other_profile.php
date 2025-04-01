<?php
require_once "db_connection.php";
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Check if lessor_id is provided in the URL
if (!isset($_GET['lessor_id'])) {
    header("Location: index.php");
    exit;
}
$lessor_id = $_GET['lessor_id'];
// Get lessor information
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $lessor_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "User not found.";
    exit;
}
$lessor = $result->fetch_assoc();
$stmt->close();

// Get reviews for this lessor with DISTINCT to prevent duplicates
$stmt = $conn->prepare("SELECT DISTINCT r.review_id, r.review, r.stars, r.renter_id, u.username 
                        FROM `reviews` r 
                        JOIN users u ON r.renter_id = u.user_id 
                        JOIN items i ON r.renter_id = i.renter_id 
                        WHERE i.lessor_id = ? 
                        ORDER BY r.review_id DESC");
$stmt->bind_param("i", $lessor_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$stmt->close();

// Calculate average rating with DISTINCT to prevent duplicates
$avg_rating = 0;
$total_reviews = $reviews_result->num_rows;
if ($total_reviews > 0) {
    $stmt = $conn->prepare("SELECT AVG(DISTINCT r.stars) as avg_rating 
                           FROM `reviews` r 
                           JOIN items i ON r.renter_id = i.renter_id 
                           WHERE i.lessor_id = ?");
    $stmt->bind_param("i", $lessor_id);
    $stmt->execute();
    $avg_result = $stmt->get_result();
    $avg_data = $avg_result->fetch_assoc();
    $avg_rating = round($avg_data['avg_rating'], 1);
    $stmt->close();
}

// Get lessor's items with availability set to 0
$stmt = $conn->prepare("SELECT * FROM items WHERE lessor_id = ? AND availability = 0");
$stmt->bind_param("i", $lessor_id);
$stmt->execute();
$items_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($lessor['username']); ?>'s Profile</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  
    <?php include "navbar.php"; ?>
    
    <div class="main">
        <div class="profile-container">
            <h1><?php echo htmlspecialchars($lessor['username']); ?>'s Profile</h1>
            
            <!-- Reviews Section -->
            <div class="reviews-section">
                <h2>Reviews</h2>
                
                <?php if ($total_reviews > 0): ?>
                    <div class="avg-rating">
                        <strong>Average Rating:</strong> <?php echo $avg_rating; ?> / 5
                        (<?php echo $total_reviews; ?> <?php echo $total_reviews == 1 ? 'review' : 'reviews'; ?>)
                    </div>
                    
                    <?php 
                    // Reset result pointer
                    $reviews_result->data_seek(0);
                    while ($review = $reviews_result->fetch_assoc()): 
                    ?>
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
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No reviews yet.</p>
                <?php endif; ?>
            </div>
            
            <div class="lessor-items">
                <h2>Available Items for Rent</h2>
                
                <?php if ($items_result->num_rows > 0): ?>
                    <div class="items-grid">
                        <?php while ($item = $items_result->fetch_assoc()): ?>
                            <div class="item-card">
                                <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?></p>
                                <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
                                <p><strong>Price:</strong> $<?php echo htmlspecialchars($item['rental_price']); ?>/hr</p>
                                
                                <form method="POST" action="item_page.php">
                                    <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                    <button type="submit" class="blue-btn">View Details</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>This user has no available items for rent at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>