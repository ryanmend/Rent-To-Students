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
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // collect user input for login
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($db_password);
    $stmt->fetch();
    $stmt->close();
    
    //compare data from user input to the database data
    if ($db_password && password_verify($password, $db_password)) {
        $_SESSION["username"] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
   <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Login</h2>
    
    <?php if (!empty($error_message)): ?>
        <p style="color: red; text-align: center;"><?php echo htmlspecialchars(
            $error_message
        ); ?></p>
    <?php endif; ?>

    <form method="POST" action="" autocomplete="off">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button class="blue-btn" "type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="step1.php">Register here</a>.</p>
</body>
</html>