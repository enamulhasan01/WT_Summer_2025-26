<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Portal - AutoMart</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #0a192f;
            color: white;
        }
        
        .sidebar {
            width: 20%;
            float: left;
            height: 100vh;
            background-color: #000000;
            padding: 20px;
        }
        .sidebar h2 {
            color: #ffffff;
            margin-bottom: 40px;
            text-align: center;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            margin-bottom: 10px;
            border-radius: 8px;
            background-color: #1a1a1a;
        }
        .sidebar a.active, .sidebar a:hover {
            background-color: #0a66c2;
        }
        .sidebar a.logout {
            background-color: #cc0000;
            margin-top: 50px;
        }

        .main-content {
            width: 80%;
            float: left;
            padding: 40px;
            height: 100vh;
            overflow-y: auto;
        }
        
        .search-area {
            margin-bottom: 30px;
        }
        .search-area input[type="text"] {
            width: 70%;
            padding: 15px;
            border-radius: 20px;
            border: none;
            font-size: 16px;
        }
        .search-area button {
            width: 20%;
            padding: 15px;
            border-radius: 20px;
            border: none;
            background-color: #cccccc;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-left: 10px;
        }

        .car-card {
            background-color: white;
            color: black;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .car-card h3 {
            margin-top: 0;
            font-size: 22px;
        }
        .car-card p {
            color: #666666;
            margin-bottom: 15px;
        }
        .status-badge {
            background-color: #4CAF50;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        .price {
            font-weight: bold;
            font-size: 16px;
            margin-left: 10px;
        }
        .view-details {
            display: block;
            background-color: #a3d9a5;
            color: #004d00;
            text-align: center;
            padding: 10px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
        }
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
        
        <div class="search-area">
            <input type="text" placeholder="Search Cars...">
            <button>Filter</button>
        </div>

        <?php
        $sql = "SELECT * FROM VEHICLE";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $badgeColor = ($row["Availability"] == 'Available') ? '#4CAF50' : '#cc0000';
                
                if($row["Condition_Status"] == 'Brand New') {
                    $details = "Brand New | " . $row["Color"] . " | " . $row["Body_Type"];
                } else {
                    $details = "Mileage: " . number_format($row["Mileage"]) . " km | " . $row["Color"] . " | " . $row["Body_Type"];
                }

                echo '
                <div class="car-card">
                    <h3>' . $row["Year"] . ' ' . $row["Make"] . ' ' . $row["Model"] . '</h3>
                    <p>' . $details . '</p>
                    <span class="status-badge" style="background-color: ' . $badgeColor . ';">' . $row["Availability"] . '</span>
                    <span class="price">Starts at $' . number_format($row["Listed_Price"]) . '</span>
                    <a href="#" class="view-details">View Details</a>
                </div>';
            }
        } else {
            echo "<p>No cars currently available in inventory.</p>";
        }
        ?>

    </div>

</body>
</html>