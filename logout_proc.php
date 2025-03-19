<?php
/* Ryan Mendoza 100409153 */

session_start();

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["confirm_logout"]) &&
    $_POST["confirm_logout"] === "yes"
) { // process logout
    session_unset();
    session_destroy();
    setcookie(session_name(), "", 0, "/"); // clear session cookie
    header("Location: login.php");
    exit();
} else {
    // if the form wasn't submitted correctly or confirmation was not 'yes', redirect back to dashboard
    header("Location: dashboard.php");
    exit();
}
?>
