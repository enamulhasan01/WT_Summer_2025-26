<?php
session_start();
if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Owner") {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

$invResult = $conn->query("SELECT COUNT(*) as total FROM VEHICLE WHERE Availability = 'Available' OR Availability = 'In Stock'");
$totalInventory = $invResult->fetch_assoc()['total'];


$pendResult = $conn->query("SELECT COUNT(*) as pending FROM TRADE_IN WHERE Status = 'Pending'");
$totalPending = $pendResult->fetch_assoc()['pending'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Owner Dashboard - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; position: fixed; }
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; font-size: 18px; text-transform: uppercase;}
        .sidebar a { display: block; color: white; padding: 12px 15px; text-decoration: none; margin-bottom: 10px; border-radius: 20px; background-color: #555555; text-align: center; font-weight: bold; font-size: 13px;}
        .sidebar a.active, .sidebar a:hover { background-color: #6b6bff; }
        .sidebar a.logout { background-color: #cc0000; position: absolute; bottom: 20px; width: calc(100% - 40px); }

        .main-content { width: 80%; float: right; padding: 40px; height: 100vh; overflow-y: auto; }
        h1 { margin-top: 0; font-size: 24px; font-weight: normal; text-transform: uppercase; margin-bottom: 30px;}

        .metrics-container { display: flex; gap: 20px; }
        .metric-card { background-color: #0d2342; border-radius: 15px; padding: 30px; width: 48%; text-align: center; border: 1px solid #1f3a5f;}
        .metric-card h3 { color: #ffffff; margin-top: 0; font-weight: normal; font-size: 18px;}
        .metric-card .number { font-size: 60px; font-weight: bold; margin: 10px 0; }
        .metric-card.pending { background-color: #162947; }
        .pending-badge { background-color: #f1c40f; color: black; padding: 5px 15px; border-radius: 20px; font-weight: bold; display: inline-block;}
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard Overview</h2>
        //add
        <a href="active_requests.php">ACTIVE CUSTOMER WISHLIST</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Dashboard Overview</h1>
        <div class="metrics-container">
            <div class="metric-card">
                <h3>Total Inventory</h3>
                <div class="number"><?php echo $totalInventory; ?></div>
            </div>
            <div class="metric-card pending">
                <br><br>
                <div class="pending-badge">⏳ Pending Approval - <?php echo $totalPending; ?></div>
            </div>
        </div>
    </div>
</body>
</html>