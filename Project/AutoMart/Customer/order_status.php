<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

$email = $_SESSION["email"];


$searchQuery = "";
if(isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchQuery = trim($_GET['search']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Status - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; position: fixed; }
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; }
        .sidebar a { display: block; color: white; padding: 15px; text-decoration: none; margin-bottom: 10px; border-radius: 8px; background-color: #1a1a1a; }
        .sidebar a.active, .sidebar a:hover { background-color: #0a66c2; }
        .sidebar a.logout { background-color: #cc0000; margin-top: 50px; }

        .main-content { width: 80%; float: right; padding: 40px; height: 100vh; overflow-y: auto; }
        
        
        .search-area { margin-bottom: 30px; width: 100%; overflow: hidden; }
        .search-area input[type="text"] { width: 70%; padding: 15px; border-radius: 20px; border: none; font-size: 16px; float: left; }
        .search-area button { width: 20%; padding: 15px; border-radius: 20px; border: none; background-color: #cccccc; font-size: 16px; font-weight: bold; cursor: pointer; float: left; margin-left: 2%; }
        .search-area button:hover { background-color: #b3b3b3; }

        
        .row-container { width: 100%; overflow: hidden; clear: both; }
        .col-half { width: 48%; float: left; }
        .col-half.right { float: right; }

        .section-title { margin-top: 0; font-size: 20px; margin-bottom: 20px; }

        
        .car-card {
            background-color: white;
            color: black;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 20px;
            width: 100%;
            overflow: hidden;
            box-sizing: border-box;
        }
        
        .car-info { width: 60%; float: left; }
        .car-info h3 { margin-top: 0; font-size: 16px; margin-bottom: 8px; }
        .car-info p { color: #666666; margin-bottom: 10px; font-size: 13px; margin-top: 0; }
        
        .status-badge { color: white; padding: 4px 10px; border-radius: 20px; font-weight: bold; font-size: 11px; }
        .transaction-info { font-weight: bold; font-size: 13px; margin-left: 8px; }
        .date-text { font-size: 11px; color: #666666; font-weight: normal; }

        .car-image-container { width: 40%; float: right; text-align: right; }
        .car-image-container img { width: 100%; max-width: 140px; height: 90px; object-fit: fill; border-radius: 5px; }

        .action-btn { display: block; width: 100%; clear: both; text-align: center; padding: 10px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-top: 15px; font-size: 13px; box-sizing: border-box; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AutoMart</h2>
        <a href="customer_dashboard.php">Explore your next car</a>
        <a href="make_request.php">Make a Request</a>
        <a href="trade_in.php">Trade-in</a>
        <a href="order_status.php" class="active">Order Status</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        
        
        <form class="search-area" action="order_status.php" method="GET">
            <input type="text" name="search" placeholder="Search by Car Make or Model..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search Order</button>
        </form>

        <div class="row-container">
            
            
            <div class="col-half">
                <h2 class="section-title">Recent Ordered Activity</h2>
                
                <?php
                if(!empty($searchQuery)) {
                    $searchTerm = "%" . $searchQuery . "%";
                    $stmt = $conn->prepare("SELECT s.Sale_Id, s.Vehicle_Id, s.Status, s.Sale_Price, s.Order_Date, v.Year, v.Make, v.Model, v.Condition_Status, v.Mileage, v.Color, v.Body_Type, v.Image FROM SALE s JOIN VEHICLE v ON s.Vehicle_Id = v.Vehicle_Id WHERE s.Customer_Email = ? AND (v.Make LIKE ? OR v.Model LIKE ?) ORDER BY s.Order_Date DESC");
                    $stmt->bind_param("sss", $email, $searchTerm, $searchTerm);
                } else {
                    $stmt = $conn->prepare("SELECT s.Sale_Id, s.Vehicle_Id, s.Status, s.Sale_Price, s.Order_Date, v.Year, v.Make, v.Model, v.Condition_Status, v.Mileage, v.Color, v.Body_Type, v.Image FROM SALE s JOIN VEHICLE v ON s.Vehicle_Id = v.Vehicle_Id WHERE s.Customer_Email = ? ORDER BY s.Order_Date DESC");
                    $stmt->bind_param("s", $email);
                }
                
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        
                        
                        if ($row["Status"] == 'Approved') {
                            $badgeBg = '#4CAF50'; $btnBg = '#c8e6c9'; $btnColor = '#256029'; $btnText = "View Details"; 
                            $actionLink = "order_details.php?sale_id=" . $row["Sale_Id"]; 
                        } elseif ($row["Status"] == 'Pending') {
                            $badgeBg = '#f1c40f'; $btnBg = '#e3d596'; $btnColor = '#000000'; $btnText = "Cancel Request";
                            $actionLink = "cancel_request.php?sale_id=" . $row["Sale_Id"] . "&vehicle_id=" . $row["Vehicle_Id"];
                        } else {
                            $badgeBg = '#d9534f'; $btnBg = '#ffcdd2'; $btnColor = '#c62828'; $btnText = "Order Cancelled"; $actionLink = "#";
                        }

                        $imagePath = !empty($row["Image"]) ? '../Assets/' . $row["Image"] : '../Assets/default_car.png';
                        $orderDate = date("F j, Y", strtotime($row["Order_Date"]));
                        $details = ($row["Condition_Status"] == 'Brand New') ? "Brand New | " . $row["Color"] . " | " . $row["Body_Type"] : "Mileage: " . number_format($row["Mileage"]) . " km | " . $row["Color"] . " | " . $row["Body_Type"];

                        echo '
                        <div class="car-card">
                            <div class="car-info">
                                <h3>' . $row["Year"] . ' ' . $row["Make"] . ' ' . $row["Model"] . '</h3>
                                <p>' . $details . '</p>
                                <span class="status-badge" style="background-color: ' . $badgeBg . ';">' . $row["Status"] . '</span>
                                <span class="transaction-info">at $' . number_format($row["Sale_Price"]) . ' <br><span class="date-text">on ' . $orderDate . '</span></span>
                            </div>
                            <div class="car-image-container">
                                <img src="' . $imagePath . '" alt="Car">
                            </div>
                            <a href="' . $actionLink . '" class="action-btn" style="background-color: ' . $btnBg . '; color: ' . $btnColor . ';">' . $btnText . '</a>
                        </div>';
                    }
                } else {
                    echo "<p>No ordered activity found.</p>";
                }
                $stmt->close();
                ?>
            </div>

            
            <div class="col-half right">
                <h2 class="section-title">Recent Custom Car Requests</h2>
                
                <?php
                if(!empty($searchQuery)) {
                    $searchTerm = "%" . $searchQuery . "%";
                    $reqStmt = $conn->prepare("SELECT * FROM CAR_REQUEST WHERE Customer_Email = ? AND (Car_Make LIKE ? OR Car_Model LIKE ?) ORDER BY Request_Date DESC");
                    $reqStmt->bind_param("sss", $email, $searchTerm, $searchTerm);
                } else {
                    $reqStmt = $conn->prepare("SELECT * FROM CAR_REQUEST WHERE Customer_Email = ? ORDER BY Request_Date DESC");
                    $reqStmt->bind_param("s", $email);
                }
                
                $reqStmt->execute();
                $reqResult = $reqStmt->get_result();

                if ($reqResult && $reqResult->num_rows > 0) {
                    while($req = $reqResult->fetch_assoc()) {
                        $status = $req["Status"];
                        
                        if ($status == 'Pending') {
                            $badgeBg = '#f1c40f'; $btnBg = '#e3d596'; $btnColor = '#000000'; $btnText = "Cancel Request";
                            $actionLink = "cancel_custom_request.php?req_id=" . $req["Request_Id"];
                        } elseif ($status == 'Approved' || $status == 'Found') {
                            $badgeBg = '#4CAF50'; $btnBg = '#c8e6c9'; $btnColor = '#256029'; $btnText = "Supplier Found a Match!"; $actionLink = "#";
                        } else {
                            $badgeBg = '#d9534f'; $btnBg = '#ffcdd2'; $btnColor = '#c62828'; $btnText = "Request Cancelled"; $actionLink = "#";
                        }

                        $reqDate = date("F j, Y", strtotime($req["Request_Date"]));
                        $notes = !empty($req["Additional_Notes"]) ? $req["Additional_Notes"] : "No additional notes.";
                        
                        echo '
                        <div class="car-card">
                            <div class="car-info" style="width: 100%;">
                                <h3>Requested: ' . $req["Car_Make"] . ' ' . $req["Car_Model"] . '</h3>
                                <p>Preferred Years: ' . $req["Year_Range"] . '<br>Notes: ' . $notes . '</p>
                                <span class="status-badge" style="background-color: ' . $badgeBg . ';">' . $status . '</span>
                                <span class="transaction-info">Max Budget: $' . number_format($req["Max_Budget"]) . ' <br><span class="date-text">on ' . $reqDate . '</span></span>
                            </div>
                            <a href="' . $actionLink . '" class="action-btn" style="background-color: ' . $btnBg . '; color: ' . $btnColor . ';">' . $btnText . '</a>
                        </div>';
                    }
                } else {
                    echo "<p>No custom requests found.</p>";
                }
                $reqStmt->close();
                ?>
            </div>

        </div> 
    </div>

</body>
</html>