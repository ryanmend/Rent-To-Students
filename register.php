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
    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];

    // validate inputs
    if (empty($email) || empty($username) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        // check if email already exists
        $check_email_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_email_stmt->bind_param("s", $email);
        $check_email_stmt->execute();
        $check_email_stmt->store_result();

        if ($check_email_stmt->num_rows > 0) {
            // email exists in db, show error message but stay in page
            $error_message = "Email already exist. Please login.";
        } else {
            // check if username already exists
            $check_username_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $check_username_stmt->bind_param("s", $username);
            $check_username_stmt->execute();
            $check_username_stmt->store_result();

            if ($check_username_stmt->num_rows > 0) {
                // username exists in db, show error message but stay in page
                $error_message = "Username already exist. Please choose another.";
            } else {
                // email and username do not exist, insert into database
                $stmt = $conn->prepare(
                        "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
                );

                $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password

                $stmt->bind_param(
                        "sss",
                        $username,
                        $email,
                        $hashed_password
                );

                if ($stmt->execute()) {
                    // Insertion successful, redirect to login page
                    header("Location: login.php");
                    exit();
                } else {
                    // Handle insertion error (e.g., display an error message)
                    $error_message = "Error inserting data into the database.";
                }

                $stmt->close();
            }
        }
    }

    $check_email_stmt->close();
    $check_username_stmt->close();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Registration - Step 1</title>
        <link rel="stylesheet" href="css/styles.css">
        <?php include "navbar.php"; ?>
    </head>
    <body>
        <h2>Register</h2>

        <?php if (!empty($error_message)): ?>
            <p style="color: red; text-align: center;"><?php
                echo htmlspecialchars(
                        $error_message
                );
                ?></p>
        <?php endif; ?>

        <form method="POST" action="" class="main-form">

            <label for="username">Enter your username:</label>
            <input type="text" id="username" name="username" 
                   value="<?php
                   echo htmlspecialchars(
                           $username
                   );
                   ?>" required><br>
            <label for="email">Enter your email:</label>
            <input type="email" id="email" name="email" value="<?php
            echo isset(
                    $email
            ) ? htmlspecialchars($email) : "";
            ?>" required><br>


            <label for="password">Enter your password:</label>
            <input type="password" id="password" name="password" value="<?php
            echo isset(
                    $password
            ) ? htmlspecialchars($password) : "";
            ?>" required><br>

            <br>
            <button type="submit">Confirm Sign Up</button>
            <br>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </body>
</html>