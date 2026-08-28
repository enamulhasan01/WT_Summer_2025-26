<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';
$email = $_SESSION["email"];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Status - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        
        
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; }
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; }
        .sidebar a { display: block; color: white; padding: 15px; text-decoration: none; margin-bottom: 10px; border-radius: 8px; background-color: #1a1a1a; }
        .sidebar a.active, .sidebar a:hover { background-color: #0a66c2; }
        .sidebar a.logout { background-color: #cc0000; margin-top: 50px; }

        
        .main-content { width: 80%; float: left; padding: 40px; height: 100vh; overflow-y: auto; }
        
        .search-area { margin-bottom: 30px; }
        .search-area input[type="text"] { width: 70%; padding: 15px; border-radius: 20px; border: none; font-size: 16px; }
        .search-area button { width: 20%; padding: 15px; border-radius: 20px; border: none; background-color: #cccccc; font-size: 16px; font-weight: bold; cursor: pointer; margin-left: 10px; }

        h2.section-title { margin-bottom: 20px; }

        
        .car-card { background-color: white; color: black; padding: 20px; border-radius: 15px; margin-bottom: 20px; overflow: hidden; }
        
        
        .car-info { width: 65%; float: left; }
        .car-info h3 { margin-top: 0; font-size: 22px; margin-bottom: 5px; }
        .car-info p { color: #666666; margin-top: 0; margin-bottom: 15px; }
        
        .status-badge { color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .transaction-info { font-weight: bold; font-size: 16px; margin-left: 10px; }
        .date-text { color: #888888; font-weight: normal; }

        
        .car-image-container { width: 35%; float: right; text-align: right; }
        .car-image-container img { width: 100%; max-width: 200px; height: auto; object-fit: contain; }

        
        .action-btn { display: block; width: 100%; clear: both; text-align: center; padding: 10px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AutoMart</h2>
        <a href="customer_dashboard.php">Explore your next car</a>
        <a href="make_request.php">Make a Request</a>
        <a href="trade_in.php">Trade-in/ Sell</a>
        <a href="order_status.php" class="active">Order Status</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        
        <div class="search-area">
            <input type="text" placeholder="Search Orders...">
            <button>Filter</button>
        </div>

        <h2 class="section-title">Recent Activity</h2>

        <?php
        
       $stmt = $conn->prepare("
            SELECT s.Sale_Id, s.Vehicle_Id, s.Status, s.Sale_Price, s.Order_Date, 
                   v.Year, v.Make, v.Model, v.Condition_Status, v.Mileage, v.Color, v.Body_Type, v.Image 
            FROM SALE s 
            JOIN VEHICLE v ON s.Vehicle_Id = v.Vehicle_Id 
            WHERE s.Customer_Email = ? 
            ORDER BY s.Order_Date DESC
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                
                
                
                if ($row["Status"] == 'Approved') {
                    $badgeBg = '#4CAF50'; 
                    $btnBg = '#c8e6c9'; 
                    $btnColor = '#256029';
                    $btnText = "View Details";
                    $actionLink = "#";
                } elseif ($row["Status"] == 'Pending') {
                    $badgeBg = '#f1c40f'; 
                    $btnBg = '#e3d596'; 
                    $btnColor = '#000000';
                    $btnText = "Cancel Request";
                    
                    $actionLink = "cancel_request.php?sale_id=" . $row["Sale_Id"] . "&vehicle_id=" . $row["Vehicle_Id"];
                } else {
                    $badgeBg = '#d9534f'; 
                    $btnBg = '#ffcdd2'; 
                    $btnColor = '#c62828';
                   $btnText = "Order Cancelled";
                    $actionLink = "#";
                }
                
                $imagePath = !empty($row["Image"]) ? '../Assets/' . $row["Image"] : '../Assets/default_car.png';
                $orderDate = date("F j, Y", strtotime($row["Order_Date"])); 
                
                if($row["Condition_Status"] == 'Brand New') {
                    $details = "Brand New | " . $row["Color"] . " | " . $row["Body_Type"];
                } else {
                    $details = "Mileage: " . number_format($row["Mileage"]) . " km | " . $row["Color"] . " | " . $row["Body_Type"];
                }

                echo '
                <div class="car-card">
                    <div class="car-info">
                        <h3>' . $row["Year"] . ' ' . $row["Make"] . ' ' . $row["Model"] . '</h3>
                        <p>' . $details . '</p>
                        <span class="status-badge" style="background-color: ' . $badgeBg . ';">' . $row["Status"] . '</span>
                        <span class="transaction-info">at $' . number_format($row["Sale_Price"]) . ' <span class="date-text">on ' . $orderDate . '</span></span>
                    </div>
                    <div class="car-image-container">
                        <img src="' . $imagePath . '" alt="' . $row["Make"] . '">
                    </div>
                   <a href="' . $actionLink . '" class="action-btn" style="background-color: ' . $btnBg . '; color: ' . $btnColor . ';">' . $btnText . '</a>
                </div>';
            }
        } else {
            echo "<p>You have no recent activity.</p>";
        }
        $stmt->close();
        ?>
        
        <h2 class="section-title" style="margin-top: 40px;">Custom Car Requests</h2>
        
        <?php
        
        $reqStmt = $conn->prepare("SELECT * FROM CAR_REQUEST WHERE Customer_Email = ? ORDER BY Request_Date DESC");
        $reqStmt->bind_param("s", $email);
        $reqStmt->execute();
        $reqResult = $reqStmt->get_result();

        if ($reqResult && $reqResult->num_rows > 0) {
            while($req = $reqResult->fetch_assoc()) {
                
                $status = $req["Status"];
                
                
                if ($status == 'Pending') {
                    $badgeBg = '#f1c40f'; 
                    $btnBg = '#e3d596'; 
                    $btnColor = '#000000';
                    $btnText = "Cancel Request"; 
                } elseif ($status == 'Approved' || $status == 'Found') {
                    $badgeBg = '#4CAF50'; 
                    $btnBg = '#c8e6c9'; 
                    $btnColor = '#256029';
                    $btnText = "Supplier Found a Match!";
                } else {
                    $badgeBg = '#d9534f'; 
                    $btnBg = '#ffcdd2'; 
                    $btnColor = '#c62828';
                    $btnText = "Request Closed";
                }

                $reqDate = date("F j, Y", strtotime($req["Request_Date"]));
                $notes = !empty($req["Additional_Notes"]) ? $req["Additional_Notes"] : "No additional notes.";
                
                echo '
                <div class="car-card">
                    <div class="car-info">
                        <h3>Requested: ' . $req["Car_Make"] . ' ' . $req["Car_Model"] . '</h3>
                        <p>Preferred Years: ' . $req["Year_Range"] . ' | Notes: ' . $notes . '</p>
                        <span class="status-badge" style="background-color: ' . $badgeBg . ';">' . $status . '</span>
                        <span class="transaction-info">Max Budget: $' . number_format($req["Max_Budget"]) . ' <span class="date-text">on ' . $reqDate . '</span></span>
                    </div>
                    <div class="car-image-container">
                        
                        <img src="../Assets/default_car.png" alt="Custom Request">
                    </div>
                    <a href="#" class="action-btn" style="background-color: ' . $btnBg . '; color: ' . $btnColor . ';">' . $btnText . '</a>
                </div>';
            }
        } else {
            echo "<p>You have no pending custom requests.</p>";
        }
        $reqStmt->close();
        ?>

    </div>

</body>
</html>