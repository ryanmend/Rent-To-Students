<?php
/* Ryan Mendoza 100409153 */

session_start();
session_destroy();

require "autoload.php";
require "db_connection.php";
$error_message = "";
// start the session if not already started
if (!session_id()) {
    session_set_cookie_params([
        "secure" => true,
        "httponly" => true,
        "samesite" => "Lax",
    ]);
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // initialize session data
    $name = $_POST["name"];
    $email = $_POST["email"];

    // validate inputs
    if (empty($name) || empty($email)) {
        $error_message = "Please fill in all fields.";
    } else {
        // check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // email exists in db, show error message but stay in page
            $error_message = "Email already exist. Please login.";
        } else {
            // email does not exist, store form data in the session
            $_SESSION["name"] = $name;
            $_SESSION["email"] = $email;

            header("Location: step2.php"); // directs to step2
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration - Step 1</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Step 1: Personal Information</h2>
    
    <?php if (!empty($error_message)): ?>
        <p style="color: red; text-align: center;"><?php echo htmlspecialchars(
            $error_message
        ); ?></p>
    <?php endif; ?>
    
    <form method="POST" action="">
        <label for="name">Enter your name:</label>
        <input type="text" id="name" name="name" value="<?php echo isset($name)
            ? htmlspecialchars($name)
            : ""; ?>" required><br>
        
        <label for="email">Enter your email:</label>
        <input type="email" id="email" name="email" value="<?php echo isset(
            $email
        )
            ? htmlspecialchars($email)
            : ""; ?>" required><br>

        <button class="green-btn" type="submit">Next</button>
        <br>
    </form>
    
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</body>
</html>