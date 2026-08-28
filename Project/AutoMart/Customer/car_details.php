<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';


if(!isset($_GET['id']) || empty($_GET['id'])) {
    
    header("Location: customer_dashboard.php");
    exit();
}

$vehicle_id = $_GET['id'];


$stmt = $conn->prepare("SELECT * FROM VEHICLE WHERE Vehicle_Id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    echo "Car not found!";
    exit();
}

$car = $result->fetch_assoc();
$imagePath = !empty($car["Image"]) ? '../Assets/' . $car["Image"] : '../Assets/default_car.png';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Car Details - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        
        
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; }
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; }
        .sidebar a { display: block; color: white; padding: 15px; text-decoration: none; margin-bottom: 10px; border-radius: 8px; background-color: #1a1a1a; }
        .sidebar a.active, .sidebar a:hover { background-color: #0a66c2; }
        .sidebar a.logout { background-color: #cc0000; margin-top: 50px; }

        
        .main-content { width: 80%; float: left; padding: 40px; height: 100vh; overflow-y: auto; }
        
        .details-container { background-color: white; color: black; padding: 30px; border-radius: 15px; overflow: hidden; }
        .image-box { width: 50%; float: left; text-align: center; }
        .image-box img { width: 100%; max-width: 400px; border-radius: 10px; }
        
        .info-box { width: 50%; float: left; padding-left: 30px; }
        .info-box h1 { margin-top: 0; color: #0a192f; }
        .info-box h2 { color: #4CAF50; margin-bottom: 20px; }
        .specs { background-color: #f1f1f1; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .specs p { margin: 5px 0; font-size: 16px; }
        .specs span { font-weight: bold; }
        
        .purchase-btn {
            display: block; width: 100%; text-align: center; padding: 15px;
            background-color: #0a66c2; color: white; text-decoration: none;
            font-size: 18px; font-weight: bold; border-radius: 10px; border: none; cursor: pointer;
        }
        .purchase-btn:hover { background-color: #004182; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #4da6ff; text-decoration: none; font-size: 16px; }
        .back-link:hover { text-decoration: underline; }
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
        <a href="customer_dashboard.php" class="back-link">&larr; Back to Inventory</a>
        
        <div class="details-container">
            <div class="image-box">
                <img src="<?php echo $imagePath; ?>" alt="Car Image">
            </div>
            
            <div class="info-box">
                <h1><?php echo $car["Year"] . " " . $car["Make"] . " " . $car["Model"]; ?></h1>
                <h2>$<?php echo number_format($car["Listed_Price"]); ?></h2>
                
                <div class="specs">
                    <p><span>Condition:</span> <?php echo $car["Condition_Status"]; ?></p>
                    <p><span>Mileage:</span> <?php echo number_format($car["Mileage"]); ?> km</p>
                    <p><span>Color:</span> <?php echo $car["Color"]; ?></p>
                    <p><span>Body Type:</span> <?php echo $car["Body_Type"]; ?></p>
                    <p><span>Availability:</span> <?php echo $car["Availability"]; ?></p>
                </div>

                <?php if($car["Availability"] == 'Available'): ?>
                    
                    <form action="process_purchase.php" method="POST">
                        <input type="hidden" name="vehicle_id" value="<?php echo $car["Vehicle_Id"]; ?>">
                        <input type="hidden" name="price" value="<?php echo $car["Listed_Price"]; ?>">
                        <button type="submit" class="purchase-btn">Proceed to Purchase</button>
                    </form>
                <?php else: ?>
                    <button class="purchase-btn" style="background-color: #cccccc; cursor: not-allowed;" disabled>Currently Unavailable</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>