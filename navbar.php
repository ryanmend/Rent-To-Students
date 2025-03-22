<!-- Ryan Mendoza 100409153 -->
<?php
//session_start();

// check if the user is logged in ie. session data exist
/*if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}*/
?>

<html>
<nav>
    <link rel="stylesheet" href="css/styles.css">
      <a href ="login.php" style ="text-align: center; text-align-all: center; color: #2c3e50">Sign In / Sign Up</a><br>
      <a href ="profile.php" style ="text-align: center; text-align-all: center; color: #2c3e50">Profile</a>
    <a href ="index.php" style ="text-align: center; text-align-all: center; color: #2c3e50">Home</a>
    <a href ="add_item.php" style ="text-align: center; text-align-all: center; color: #2c3e50">Add Item</a>
     
     </nav>
    
</html>