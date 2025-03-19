<?php
/* Ryan Mendoza 100409153 */

session_start();

// check if the user is logged in ie. session data exist
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Logout Confirmation</title>
        <link rel="stylesheet" href="css/styles.css">
    </head>
    <body>

        <h2>Are you sure you want to logout?</h2>

        <form method="post" action="logout_proc.php">
            <button class="red-btn" type="submit" name="confirm_logout" value="yes">Confirm logout</button>

        </form>
        <br><br>
        <a href="dashboard.php">Cancel</a>
    </body>
</html>
