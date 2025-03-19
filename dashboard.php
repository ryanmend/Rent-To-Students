<?php
/* Ryan Mendoza 100409153 */

session_start();

// check if the user is logged in ie. session data exist
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
        
</head>
<body>

<h1>Welcome, <?php echo $_SESSION["username"]; ?>!</h1>

<form method="post" action="logout.php">
    <button  class="red-btn" type="submit">Logout</button>
</form>

</body>
</html>