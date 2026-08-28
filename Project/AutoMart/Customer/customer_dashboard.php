<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';


$searchQuery = "";
if(isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchQuery = trim($_GET['search']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Portal - AutoMart</title>
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
        .search-area button { width: 18%; padding: 15px; border-radius: 20px; border: none; background-color: #cccccc; font-size: 16px; font-weight: bold; cursor: pointer; float: left; margin-left: 2%; }

        
        .cars-container { width: 100%; overflow: hidden; clear: both; }

        
        .car-card {
            background-color: white;
            color: black;
            padding: 15px; 
            border-radius: 15px;
            margin-bottom: 20px;
            width: 48%; 
            float: left; 
            margin-right: 2%; 
            box-sizing: border-box;
            
            
            height: 165px; 
            position: relative; 
        }
        
        
        .car-card:nth-child(2n) { margin-right: 0; }
        .car-card:nth-child(2n+1) { clear: both; }
        
        
        .car-info { width: 55%; float: left; }
        .car-info h3 { 
            margin-top: 0; 
            font-size: 16px; 
            margin-bottom: 4px; 
            height: 38px; 
            overflow: hidden; 
        }
        .car-info p { color: #666666; margin-bottom: 8px; font-size: 12px; margin-top: 0; }
        
        .status-badge { color: white; padding: 3px 8px; border-radius: 20px; font-weight: bold; font-size: 11px; }
        .price { font-weight: bold; font-size: 14px; margin-left: 8px; }

        
        .car-image-container { width: 45%; float: right; text-align: right; }
        .car-image-container img { 
            width: 120px; 
            height: 80px; 
            object-fit: fill; 
            border-radius: 5px; 
        }

        
        .view-details { 
            position: absolute;
            bottom: 12px; 
            left: 15px;
            width: calc(100% - 30px); 
            display: block; 
            text-align: center; 
            padding: 8px; 
            border-radius: 10px; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 13px; 
            box-sizing: border-box;
        }
        
        
        .not-found-msg { text-align: center; margin-top: 50px; background-color: #1a2a42; padding: 30px; border-radius: 15px; }
        .not-found-msg h3 { font-size: 24px; margin-bottom: 10px; }
        .not-found-msg p { font-size: 16px; margin-bottom: 20px; color: #cccccc; }
        .request-btn { background-color: #f1c40f; color: black; padding: 12px 25px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 16px; display: inline-block; }
        .request-btn:hover { background-color: #d4ac0d; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AutoMart</h2>
        <a href="customer_dashboard.php" class="active">Explore your next car</a>
        <a href="make_request.php">Make a Request</a>
        <a href="trade_in.php">Trade-in/ Sell</a>
        <a href="order_status.php">Order Status</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        
        
        <form class="search-area" action="customer_dashboard.php" method="GET">
            <input type="text" name="search" placeholder="Search by Make or Model..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
        </form>

        <div class="cars-container">
            <?php
            
            if(!empty($searchQuery)) {
                $searchTerm = "%" . $searchQuery . "%";
                $stmt = $conn->prepare("SELECT * FROM VEHICLE WHERE Make LIKE ? OR Model LIKE ?");
                $stmt->bind_param("ss", $searchTerm, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $sql = "SELECT * FROM VEHICLE";
                $result = $conn->query($sql);
            }

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    
                    $availability = $row["Availability"];
                    
                    if ($availability == 'Available') {
                        $badgeBg = '#4CAF50'; $btnBg = '#c8e6c9'; $btnColor = '#256029';
                    } elseif ($availability == 'Pending') {
                        $badgeBg = '#f1c40f'; $btnBg = '#e3d596'; $btnColor = '#000000';
                    } else {
                        $badgeBg = '#d9534f'; $btnBg = '#ffcdd2'; $btnColor = '#c62828';
                    }

                    $imagePath = !empty($row["Image"]) ? '../Assets/' . $row["Image"] : '../Assets/default_car.png';
                    
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
                            <span class="status-badge" style="background-color: ' . $badgeBg . ';">' . $row["Availability"] . '</span>
                            <span class="price">$' . number_format($row["Listed_Price"]) . '</span>
                        </div>
                        <div class="car-image-container">
                            <img src="' . $imagePath . '" alt="' . $row["Make"] . '">
                        </div>
                        <a href="car_details.php?id=' . $row["Vehicle_Id"] . '" class="view-details" style="background-color: ' . $btnBg . '; color: ' . $btnColor . ';">View Details</a>
                    </div>';
                }
            } else {
                echo '
                <div class="not-found-msg">
                    <h3>Car Not Found</h3>
                    <p>We couldn\'t find any cars matching "<strong>' . htmlspecialchars($searchQuery) . '</strong>" in our current inventory.</p>
                    <a href="make_request.php" class="request-btn">Request this car from our Suppliers</a>
                </div>';
            }
            
            if(isset($stmt)) { $stmt->close(); }
            ?>
        </div>
    </div>

</body>
</html>