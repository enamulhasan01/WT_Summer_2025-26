<?php
session_start();
if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Owner") {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $make = $_POST['car_make'];
    $model = $_POST['car_model'];
    $year = $_POST['car_year'];
    $price = $_POST['max_budget'];
    $color = $_POST['car_color'];
    $availability = 'Available';
    
    $stmt = $conn->prepare("INSERT INTO VEHICLE (Make, Model, Year, Listed_Price, Color, Availability) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiss", $make, $model, $year, $price, $color, $availability);
    
    if($stmt->execute()) {
        echo "<script>alert('Vehicle Added Successfully'); window.location.href='manage_inventory.php';</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Vehicle - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; position: fixed; }
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; font-size: 18px; text-transform: uppercase;}
        .sidebar a { display: block; color: white; padding: 12px 15px; text-decoration: none; margin-bottom: 10px; border-radius: 20px; background-color: #555555; text-align: center; font-weight: bold; font-size: 13px;}
        .sidebar a.active, .sidebar a:hover { background-color: #6b6bff; }
        .sidebar a.logout { background-color: #cc0000; position: absolute; bottom: 20px; width: calc(100% - 40px); }

        .main-content { width: 80%; float: right; padding: 40px; height: 100vh; overflow-y: auto; }
        
        .form-group { margin-bottom: 25px; }
        .form-group label {
            display: inline-block;
            background-color: #e3d596; 
            color: black;
            padding: 6px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .form-group input[type="text"], .form-group input[type="number"] {
            width: 100%;
            padding: 18px;
            border-radius: 25px;
            border: none;
            background-color: #cccccc; 
            font-size: 16px;
            color: #333333;
        }
        .btn-submit { background-color: #4CAF50; color: white; padding: 15px 30px; border-radius: 20px; border: none; font-size: 16px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard Overview</h2>
        <a href="owner_dashboard.php">DASHBOARD</a>
        <a href="pending_list.php">PENDING LIST</a>
        <a href="manage_inventory.php" class="active">MANAGE INVENTORY</a>
        <a href="supplier_performance.php">SUPPLIER PERFORMANCE</a>
        <a href="active_requests.php">ACTIVE CUSTOMER WISHLIST</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <form action="" method="POST">
            <div class="form-group">
                <label>Car Make</label>
                <input type="text" name="car_make" placeholder="e.g. BMW, Toyota, Honda..." required>
            </div>
            <div class="form-group">
                <label>Car Model</label>
                <input type="text" name="car_model" placeholder="e.g. M4 Competition, Civic..." required>
            </div>
            <div class="form-group">
                <label>Manufacture Year</label>
                <input type="number" name="car_year" placeholder="e.g. 2024" required>
            </div>
            <div class="form-group">
                <label>Listed Price</label>
                <input type="number" name="max_budget" placeholder="e.g. 90000" required>
            </div>
            <div class="form-group">
                <label>Color</label>
                <input type="text" name="car_color" placeholder="e.g. Black, Nardo Gray, Red..." required>
            </div>
            <button type="submit" class="btn-submit">Add Vehicle to Inventory</button>
        </form>
    </div>
</body>
</html>