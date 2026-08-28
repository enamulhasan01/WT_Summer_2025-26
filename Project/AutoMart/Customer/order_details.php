<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

if(!isset($_GET['sale_id']) || empty($_GET['sale_id'])) {
    header("Location: order_status.php");
    exit();
}

$sale_id = $_GET['sale_id'];
$email = $_SESSION["email"];


$stmt = $conn->prepare("SELECT s.*, v.Year, v.Make, v.Model, v.Image, v.Color, v.Body_Type FROM SALE s JOIN VEHICLE v ON s.Vehicle_Id = v.Vehicle_Id WHERE s.Sale_Id = ? AND s.Customer_Email = ?");
$stmt->bind_param("is", $sale_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    echo "<script>alert('Order not found!'); window.location.href = 'order_status.php';</script>";
    exit();
}

$order = $result->fetch_assoc();
$imagePath = !empty($order["Image"]) ? '../Assets/' . $order["Image"] : '../Assets/default_car.png';
$orderDate = date("F j, Y, g:i a", strtotime($order["Order_Date"]));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Details - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; position: fixed;}
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; }
        .sidebar a { display: block; color: white; padding: 15px; text-decoration: none; margin-bottom: 10px; border-radius: 8px; background-color: #1a1a1a; }
        .sidebar a.active, .sidebar a:hover { background-color: #0a66c2; }
        .sidebar a.logout { background-color: #cc0000; margin-top: 50px; }
        
        .main-content { width: 80%; float: right; padding: 40px; height: 100vh; overflow-y: auto; }
        
        .details-container { background-color: white; color: black; padding: 30px; border-radius: 15px; overflow: hidden; margin-bottom: 20px;}
        
        
        .success-banner { background-color: #e8f5e9; border-left: 6px solid #4CAF50; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .success-banner h3 { margin-top: 0; color: #256029; font-size: 22px; margin-bottom: 8px;}
        .success-banner p { margin: 0; color: #333; font-size: 16px; }

        .image-box { width: 45%; float: left; text-align: center; }
        .image-box img { width: 100%; max-width: 350px; border-radius: 10px; }
        
        .info-box { width: 55%; float: left; padding-left: 30px; }
        .info-box h1 { margin-top: 0; color: #0a192f; font-size: 26px; margin-bottom: 5px;}
        .info-box p.car-sub { color: #666; margin-top: 0; margin-bottom: 20px; }
        
        .receipt-box { background-color: #f8f9fa; padding: 20px; border-radius: 10px; border: 1px solid #e0e0e0;}
        .receipt-box h4 { margin-top: 0; border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-bottom: 15px; color: #333;}
        .receipt-row { margin-bottom: 10px; font-size: 15px; overflow: hidden; }
        .receipt-label { float: left; font-weight: bold; color: #555; width: 40%;}
        .receipt-value { float: left; color: #111; width: 60%;}
        
        .total-price { font-size: 20px; color: #256029; font-weight: bold; border-top: 2px solid #ccc; padding-top: 15px; margin-top: 15px;}

        .back-link { display: inline-block; margin-bottom: 20px; color: #4da6ff; text-decoration: none; font-size: 16px; }
        .back-link:hover { text-decoration: underline; }
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
        <a href="order_status.php" class="back-link">&larr; Back to Order Status</a>
        
        <div class="details-container">
            
            <div class="success-banner">
                <h3>Order Approved!</h3>
                <p>Thank you for your purchase. <strong>Our representative will contact you within 24 hours</strong> using the phone number provided below to arrange final delivery and handover.</p>
            </div>

            <div class="image-box">
                <img src="<?php echo $imagePath; ?>" alt="Car Image">
            </div>
            
            <div class="info-box">
                <h1><?php echo $order["Year"] . " " . $order["Make"] . " " . $order["Model"]; ?></h1>
                <p class="car-sub"><?php echo $order["Color"]; ?> | <?php echo $order["Body_Type"]; ?></p>
                
                <div class="receipt-box">
                    <h4>Purchase Receipt</h4>
                    
                    <div class="receipt-row">
                        <div class="receipt-label">Order ID:</div>
                        <div class="receipt-value">#<?php echo str_pad($order["Sale_Id"], 5, "0", STR_PAD_LEFT); ?></div>
                    </div>
                    
                    <div class="receipt-row">
                        <div class="receipt-label">Order Date:</div>
                        <div class="receipt-value"><?php echo $orderDate; ?></div>
                    </div>

                    <div class="receipt-row">
                        <div class="receipt-label">Payment Method:</div>
                        <div class="receipt-value"><?php echo htmlspecialchars($order["Payment_Method"]); ?></div>
                    </div>
                    
                    <div class="receipt-row">
                        <div class="receipt-label">Contact Phone:</div>
                        <div class="receipt-value"><?php echo htmlspecialchars($order["Phone"]); ?></div>
                    </div>
                    
                    <div class="receipt-row">
                        <div class="receipt-label">Delivery Address:</div>
                        <div class="receipt-value"><?php echo nl2br(htmlspecialchars($order["Address"])); ?></div>
                    </div>
                    
                    <div class="receipt-row total-price">
                        <div class="receipt-label">Total Paid:</div>
                        <div class="receipt-value">$<?php echo number_format($order["Sale_Price"]); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>