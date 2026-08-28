<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}



include '../db.php';




if(!isset($_POST['vehicle_id']) || !isset($_POST['price'])) {
    header("Location: customer_dashboard.php");
    exit();
}



$vehicle_id = $_POST['vehicle_id'];
$listed_price = $_POST['price'];
$email = $_SESSION["email"];


$carStmt = $conn->prepare("SELECT * FROM VEHICLE WHERE Vehicle_Id = ?");
$carStmt->bind_param("i", $vehicle_id);
$carStmt->execute();
$carResult = $carStmt->get_result();
$car = $carResult->fetch_assoc();
$carStmt->close();


$creditStmt = $conn->prepare("SELECT SUM(Expected_Price) AS Total_Credits FROM TRADE_IN WHERE Customer_Email = ? AND Status = 'Approved'");
$creditStmt->bind_param("s", $email);
$creditStmt->execute();
$creditResult = $creditStmt->get_result();
$creditRow = $creditResult->fetch_assoc();
$availableCredits = $creditRow['Total_Credits'] ? $creditRow['Total_Credits'] : 0;
$creditStmt->close();


$finalPrice = max(0, $listed_price - $availableCredits);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; position: fixed; }
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; }
        .sidebar a { display: block; color: white; padding: 15px; text-decoration: none; margin-bottom: 10px; border-radius: 8px; background-color: #1a1a1a; }
        .sidebar a.active, .sidebar a:hover { background-color: #0a66c2; }
        .sidebar a.logout { background-color: #cc0000; margin-top: 50px; }

        .main-content { width: 80%; float: right; padding: 40px; height: 100vh; overflow-y: auto; }
        
        .checkout-container { width: 70%; background-color: white; color: black; padding: 30px; border-radius: 15px; margin-bottom: 40px; }
        .checkout-container h2 { margin-top: 0; color: #0a192f; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; font-size: 14px; margin-bottom: 5px; color: #333; }
        .form-group input[type="text"], 
        .form-group input[type="tel"], 
        .form-group select, 
        .form-group textarea {
            width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; background-color: #f9f9f9; font-size: 15px; color: #333;
        }
        .form-group textarea { resize: none; height: 80px; }
        
        .summary-box { background-color: #f1f1f1; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .summary-box p { margin: 8px 0; font-size: 15px; }
        .summary-box span { font-weight: bold; }
        
        .btn-submit { background-color: #4CAF50; color: white; width: 100%; padding: 15px; border-radius: 10px; border: none; font-size: 16px; font-weight: bold; cursor: pointer; }
        .btn-submit:hover { background-color: #45a049; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #4da6ff; text-decoration: none; font-size: 15px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AutoMart</h2>
        <a href="customer_dashboard.php" class="active">Explore your next car</a>
        <a href="make_request.php">Make a Request</a>
        <a href="trade_in.php">Trade-in</a>
        <a href="order_status.php">Order Status</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <a href="customer_dashboard.php" class="back-link">&larr; Back to Inventory</a>
        
        <div class="checkout-container">
            <h2>Complete Your Purchase</h2>
            
            <div class="summary-box">
                <p><span>Car:</span> <?php echo $car["Year"] . " " . $car["Make"] . " " . $car["Model"]; ?></p>
                <p><span>Listed Price:</span> $<?php echo number_format($listed_price); ?></p>
                <p><span>Available Trade-in Credits:</span> -$<?php echo number_format($availableCredits); ?></p>
                <hr style="border: 0; border-top: 1px solid #ccc;">
                <p style="font-size: 18px; color: #256029;"><span>Final Total:</span> <strong>$<?php echo number_format($finalPrice); ?></strong></p>
            </div>

            <form action="process_purchase.php" method="POST">
                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">
                <input type="hidden" name="final_price" value="<?php echo $finalPrice; ?>">
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="text" value="<?php echo htmlspecialchars($email); ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" placeholder="e.g. +1 555-0198" required>
                </div>

                <div class="form-group">
                    <label>Delivery Address</label>
                    <textarea name="address" placeholder="Enter your full street address, city, and zip code..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" required>
                        <option value="" disabled selected>Select payment method...</option>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="Credit Card">Credit / Debit Card</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Confirm and Place Order</button>
            </form>
        </div>
    </div>

</body>
</html>