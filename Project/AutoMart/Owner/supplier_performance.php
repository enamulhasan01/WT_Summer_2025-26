<?php
session_start();
if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Owner") {
    header("Location: ../login.php");
    exit();
}
include '../db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supplier Performance - AutoMart</title>
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

        .supplier-card { 
            background-color: #dbe4f0; 
            color: black; 
            padding: 25px 40px; 
            border-radius: 20px; 
            margin-bottom: 25px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        
        .supplier-info h3 { margin: 0 0 15px 0; font-size: 18px; color: #111;}
        .supplier-info p { margin: 0; font-size: 14px; font-weight: bold; color: #333;}
        .supplier-info span { font-size: 16px; font-weight: normal; display: block; margin-top: 5px; color: #000;}
        
        .approval-box { text-align: center; margin-right: 50px;}
        .approval-box p { margin: 0 0 8px 0; font-size: 14px; font-weight: bold; color: #333;}
        .approval-box .rate { font-size: 20px; font-weight: bold; color: #28a745; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard Overview</h2>
        <a href="owner_dashboard.php">DASHBOARD</a>
        <a href="pending_list.php">PENDING LIST</a>
        <a href="manage_inventory.php">MANAGE INVENTORY</a>
        <a href="supplier_performance.php" class="active">SUPPLIER PERFORMANCE</a>
        <a href="active_requests.php">ACTIVE CUSTOMER WISHLIST</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Supplier Performance</h1>

        <!-- Supplier Card 1 -->
        <div class="supplier-card">
            <div class="supplier-info">
                <h3>Premium Auto Wholesale</h3>
                <p>Vehicles Supplied <span>15</span></p>
            </div>
            <div class="approval-box">
                <p>Approval Rate</p>
                <div class="rate">92%</div>
            </div>
        </div>

        <!-- Supplier Card 2 -->
        <div class="supplier-card">
            <div class="supplier-info">
                <h3>City Auto Distributors</h3>
                <p>Vehicles Supplied <span>22</span></p>
            </div>
            <div class="approval-box">
                <p>Approval Rate</p>
                <div class="rate">88%</div>
            </div>
        </div>

    </div>
</body>
</html>