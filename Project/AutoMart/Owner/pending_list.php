<?php
session_start();
if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Owner") {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

// Handle Approve / Reject logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $trade_id = $_POST['trade_id'];
    
    if ($_POST['action'] == 'approve') {
        
        $updateStmt = $conn->prepare("UPDATE TRADE_IN SET Status = 'Approved' WHERE Trade_Id = ?");
        $updateStmt->bind_param("i", $trade_id);
        $updateStmt->execute();
        $updateStmt->close();
        
        
        $fetchTrade = $conn->query("SELECT * FROM TRADE_IN WHERE Trade_Id = $trade_id");
        if ($tradeData = $fetchTrade->fetch_assoc()) {
            $make = $tradeData['Car_Make'];
            $model = $tradeData['Car_Model'];
            $year = $tradeData['Year'];
            $price = $tradeData['Expected_Price']; 
            $color = 'Standard'; 
            $availability = 'Available';

            $insertVeh = $conn->prepare("INSERT INTO VEHICLE (Make, Model, Year, Listed_Price, Color, Availability) VALUES (?, ?, ?, ?, ?, ?)");
            $insertVeh->bind_param("ssiiss", $make, $model, $year, $price, $color, $availability);
            $insertVeh->execute();
            $insertVeh->close();
        }
        echo "<script>alert('Car Approved and added to Inventory!'); window.location.href='pending_list.php';</script>";
        
    } elseif ($_POST['action'] == 'reject') {
        $rejectStmt = $conn->prepare("UPDATE TRADE_IN SET Status = 'Rejected' WHERE Trade_Id = ?");
        $rejectStmt->bind_param("i", $trade_id);
        $rejectStmt->execute();
        $rejectStmt->close();
        echo "<script>alert('Car Trade-in Rejected.'); window.location.href='pending_list.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Cars - AutoMart</title>
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

        .top-bar { display: flex; gap: 15px; margin-bottom: 30px; }
        .search-bar { flex-grow: 1; padding: 15px 20px; border-radius: 8px; border: none; background-color: #cccccc; font-size: 16px; }
        .filter-btn { padding: 15px 30px; border-radius: 8px; border: none; background-color: #cccccc; font-weight: bold; cursor: pointer; font-size: 16px;}

        .car-card { background-color: #cccccc; color: black; padding: 20px; border-radius: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;}
        .car-info h3 { margin: 0 0 5px 0; font-size: 18px; }
        .car-info p { margin: 0 0 15px 0; font-size: 13px; font-weight: bold;}
        .price-text { color: #d4ac0d; }
        
        .action-buttons { display: flex; gap: 10px; }
        .btn { padding: 8px 25px; border-radius: 20px; border: none; font-weight: bold; cursor: pointer; color: white;}
        .btn-reject { background-color: #f28b82; }
        .btn-approve { background-color: #81c784; }
        
        .car-image { width: 220px; height: auto; border-radius: 10px;}
        
        .no-data { text-align: center; color: #a0aec0; margin-top: 50px; font-size: 18px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard Overview</h2>
        <a href="owner_dashboard.php">DASHBOARD</a>
        <a href="pending_list.php" class="active">PENDING LIST</a>
        <a href="manage_inventory.php">MANAGE INVENTORY</a>
        <a href="supplier_performance.php">SUPPLIER PERFORMANCE</a>
        <a href="active_requests.php">ACTIVE CUSTOMER WISHLIST</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Pending Cars For Approval</h1>
        
        <div class="top-bar">
            <input type="text" class="search-bar" placeholder="Search Cars...">
            <button class="filter-btn">Filter</button>
        </div>

        <?php
        
        $sql = "SELECT * FROM TRADE_IN WHERE Status = 'Pending' ORDER BY Request_Date DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '
                <div class="car-card">
                    <div class="car-info">
                        <h3>' . htmlspecialchars($row["Year"]) . ' ' . htmlspecialchars($row["Car_Make"]) . ' ' . htmlspecialchars($row["Car_Model"]) . ' - ' . htmlspecialchars($row["Customer_Email"]) . '</h3>
                        <p>Proposed Price: <span class="price-text">$' . number_format($row["Expected_Price"]) . '</span></p>
                        
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="trade_id" value="' . $row["Trade_Id"] . '">
                            <div class="action-buttons">
                                <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                                <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                            </div>
                        </form>
                    </div>
                    <img src="../Assets/default_car.png" class="car-image" alt="Car Image">
                </div>';
            }
        } else {
            echo '<div class="no-data">No cars are currently pending for approval.</div>';
        }
        ?>
    </div>
</body>
</html>