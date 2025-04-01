<?php
require_once "db_connection.php";

// Start or resume the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Get the renter_id (current user)
$errorMessage = "";
$successMessage = "";
$item_name = "";
$lessor_id = "";
$lessor_username = "";

// Check if item_name and lessor_id are provided in the URL
if (isset($_GET['item_name']) && isset($_GET['lessor_id'])) {
    $item_name = $_GET['item_name'];
    $lessor_id = $_GET['lessor_id'];
    
    // Verify that the user is not trying to review themselves
    if ($user_id == $lessor_id) {
        $errorMessage = "Error: You cannot leave a review for yourself.";
        header("Location: profile.php");
        exit;
    }
    
    // Get lessor's username for display
    $stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $lessor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lessor_username = $row['username'];
    } else {
        $errorMessage = "Error: Lessor not found.";
    }
} else {
    // Redirect if item_name or lessor_id is not provided
    header("Location: profile.php");
    exit;
}

// Process review submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_review"])) {
    $review_text = $_POST["review_text"];
    $stars = $_POST["stars"];
    
    // Validate stars input (1-5)
    if (!is_numeric($stars) || $stars < 1 || $stars > 5) {
        $errorMessage = "Error: Stars must be a number between 1 and 5.";
    } else {
        // Insert the review into the database
        $stmt = $conn->prepare("INSERT INTO reviews (review, stars, lessor_id, renter_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siii", $review_text, $stars, $lessor_id, $user_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $successMessage = "Review submitted successfully.";
            // Redirect back to profile page after successful submission
            $_SESSION['successMessage'] = $successMessage;
            header("Location: profile.php");
            exit;
        } else {
            $errorMessage = "Error: Unable to submit review.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leave a Review</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .star-rating {
            margin: 15px 0;
        }
        .review-form {
            max-width: 500px;
            margin: 0 auto;
        }
        .star-input {
            margin: 5px;
        }
        .review-textarea {
            width: 100%;
            min-height: 100px;
            margin-bottom: 15px;
            padding: 10px;
        }
    </style>
</head>
<body>

<h1>Leave a Review for <?php echo htmlspecialchars($lessor_username); ?></h1>
<h2>Item: <?php echo htmlspecialchars($item_name); ?></h2>

<?php if ($errorMessage != ""): ?>
    <div style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></div>
<?php endif; ?>

<?php if ($successMessage != ""): ?>
    <div style="color: green;"><?php echo htmlspecialchars($successMessage); ?></div>
<?php endif; ?>

<div class="review-form">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?item_name=" . urlencode($item_name) . "&lessor_id=" . urlencode($lessor_id)); ?>">
        <div class="star-rating">
            <label>Rating (1-5 stars):</label><br>
            <label class="star-input"><input type="radio" name="stars" value="1" required> 1</label>
            <label class="star-input"><input type="radio" name="stars" value="2"> 2</label>
            <label class="star-input"><input type="radio" name="stars" value="3"> 3</label>
            <label class="star-input"><input type="radio" name="stars" value="4"> 4</label>
            <label class="star-input"><input type="radio" name="stars" value="5"> 5</label>
        </div>
        
        <div>
            <label for="review_text">Your Review:</label><br>
            <textarea name="review_text" id="review_text" class="review-textarea" required></textarea>
        </div>
        
        <div>
            <button type="submit" name="submit_review">Submit Review</button>
            <a href="profile.php"><button type="button">Cancel</button></a>
        </div>
    </form>
</div>

</body>
</html>