<?php
session_start();


if(!isset($_SESSION["email"])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION["email"]; ?>!</h2>
    <p>This is your Customer dashboard. Only a logged-in user can see this.</p>
    
    
    <a href="../logout.php"><button>Logout</button></a>
</body>
</html>