<?php
/* Ryan Mendoza 100409153 */

session_start();
session_unset();
session_destroy();
setcookie(session_name(), "", 0, "/"); // clear session cookie
session_start();

require_once "db_connection.php"; //db connector

$error_message = ""; //initialize variable for error message

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    //initialize variables
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // collect user input for login
    $stmt = $conn->prepare("SELECT password, user_id FROM users WHERE email = ?");
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($db_password, $user_id);
    $stmt->fetch();
    $stmt->close();

    //compare data from user input to the database data
    if ($db_password && password_verify($password, $db_password)) {
        $_SESSION["email"] = $email;
        $_SESSION["user_id"] = $user_id; // Store user_id in session
        header("Location: profile.php"); // Changed to redirect to profile.php
        exit();
    } else {
        $error_message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome To Rent-To-Students !!</title>
    
   <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Welcome to Rent-To-Students !!</h2>

    <?php if (!empty($error_message)): ?>
        <p style="color: red; text-align: center;"><?php echo htmlspecialchars(
            $error_message
        ); ?></p>
    <?php endif; ?>

    <form method="POST" action="" autocomplete="off" class="main-form">
        <label for="email">Enter School Email</label>
        <input type="text" id="email" name="email" required>

        <label for="password">Enter Password</label>
        <input type="password" id="password" name="password" required>
<div class="btn-container">
        <button class="hollow-btn" type="submit">Sign In</button>
        <button class="fill-btn" type="submit"><a class="signup" href="register.php">Sign Up</a></button>
</div>
    </form>

    <!--<p>Don't have an account? <a href="register.php">Register here</a>.</p>-->
</body>
</html>