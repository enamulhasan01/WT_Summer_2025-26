<?php
session_start();


if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Evaluator") {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Evaluator Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION["email"]; ?>!</h2>
    <h3>Role: <?php echo $_SESSION["role"]; ?></h3>
    <p>This is the specialized Evaluator dashboard.</p>
    
    
    <a href="../logout.php"><button>Logout</button></a>
</body>
</html>