<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION["email"];
    $make = $_POST["car_make"];
    $model = $_POST["car_model"];
    $year = $_POST["car_year"];
    $mileage = $_POST["car_mileage"];
    $condition = $_POST["car_condition"];
    $price = $_POST["expected_price"];

    $stmt = $conn->prepare("INSERT INTO TRADE_IN (Customer_Email, Car_Make, Car_Model, Year, Mileage, Condition_Status, Expected_Price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiisi", $email, $make, $model, $year, $mileage, $condition, $price);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Your trade-in request has been successfully submitted! Our team will evaluate it shortly.');
            window.location.href = 'trade_in.php';
        </script>";
    } else {
        echo "<script>alert('Error submitting your request. Please try again.');</script>";
    }
    $stmt->close();
}


$email = $_SESSION["email"];
$creditStmt = $conn->prepare("SELECT SUM(Expected_Price) AS Total_Credits FROM TRADE_IN WHERE Customer_Email = ? AND Status = 'Approved'");
$creditStmt->bind_param("s", $email);
$creditStmt->execute();
$creditResult = $creditStmt->get_result();
$creditRow = $creditResult->fetch_assoc();
$totalCredits = $creditRow['Total_Credits'] ? $creditRow['Total_Credits'] : 0;
$creditStmt->close();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Trade-in - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; position: fixed; }
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; }
        .sidebar a { display: block; color: white; padding: 15px; text-decoration: none; margin-bottom: 10px; border-radius: 8px; background-color: #1a1a1a; }
        .sidebar a.active, .sidebar a:hover { background-color: #0a66c2; }
        .sidebar a.logout { background-color: #cc0000; margin-top: 50px; }
        
        .main-content { width: 80%; float: right; padding: 40px; height: 100vh; overflow-y: auto; position: relative; }
        
        
        .credits-box {
            position: absolute;
            top: 40px;
            right: 40px;
            background-color: #1a2a42;
            border: 2px solid #4CAF50;
            padding: 15px 25px;
            border-radius: 12px;
            text-align: right;
        }
        .credits-box span { display: block; font-size: 14px; color: #a0aec0; margin-bottom: 5px; }
        .credits-box strong { font-size: 22px; color: #4CAF50; }

        
        .row-container { width: 100%; overflow: hidden; clear: both; margin-top: 60px; }
        .col-half { width: 48%; float: left; }
        .col-half.right { float: right; }

        .request-form { width: 100%; margin-top: 20px; } 
        .form-group { margin-bottom: 20px; }
        
        .form-group label {
            display: inline-block;
            background-color: #e3d596; 
            color: black;
            padding: 5px 15px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
       .form-group input[type="text"], 
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 15px;
            border-radius: 20px;
            border: none;
            background-color: #cccccc; 
            font-size: 16px;
            color: #333333;
        }
        
        .btn-container { text-align: left; margin-top: 30px; }
        
        .btn-clear, .btn-submit {
            padding: 15px 30px;
            border-radius: 20px;
            border: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-clear { background-color: #666666; color: white; margin-right: 15px; }
        .btn-submit { background-color: #4CAF50; color: white; }
        
        .btn-clear:hover { background-color: #4d4d4d; }
        .btn-submit:hover { background-color: #45a049; }

        
        .car-card {
            background-color: white;
            color: black;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
        }
        .car-info h3 { margin-top: 0; font-size: 17px; margin-bottom: 8px; }
        .car-info p { color: #666666; margin-bottom: 15px; font-size: 14px; margin-top: 0; }
        
        .status-badge { color: white; padding: 5px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; }
        .transaction-info { font-weight: bold; font-size: 14px; margin-left: 8px; }
        .date-text { font-size: 12px; color: #666666; font-weight: normal; display: block; margin-top: 5px;}
        
        
        .action-btn { display: block; width: 100%; text-align: center; padding: 10px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-top: 15px; font-size: 14px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AutoMart</h2>
        <a href="customer_dashboard.php">Explore your next car</a>
        <a href="make_request.php">Make a Request</a>
        <a href="trade_in.php" class="active">Trade-in</a>
        <a href="order_status.php">Order Status</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        
        
        <div class="credits-box">
            <span>Available Trade-in Credits</span>
            <strong>$<?php echo number_format($totalCredits); ?></strong>
        </div>
        
        <div class="row-container">
            
            <div class="col-half">
                <h2>Submit your car for a Trade-in</h2>
                
                <form class="request-form" action="" method="POST">
                    <div class="form-group">
                        <label>Car Make</label>
                        <input type="text" name="car_make" list="car_brands" placeholder="e.g. BMW, Toyota, Honda..." required>
                        <datalist id="car_brands">
                            <option value="Audi">
                            <option value="BMW">
                            <option value="Ford">
                            <option value="Honda">
                            <option value="Mercedes-Benz">
                            <option value="Porsche">
                            <option value="Toyota">
                        </datalist>
                    </div>
                    
                    <div class="form-group">
                        <label>Car Model</label>
                        <input type="text" name="car_model" placeholder="e.g. Camry, F-150..." required>
                    </div>
                    
                    <div class="form-group">
                        <label>Manufacture Year</label>
                        <input type="number" name="car_year" placeholder="e.g. 2019" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Current Mileage (km)</label>
                        <input type="number" name="car_mileage" placeholder="e.g. 45000" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Overall Condition</label>
                        <select name="car_condition" required>
                            <option value="" disabled selected>Select condition...</option>
                            <option value="Excellent">Excellent (Like New)</option>
                            <option value="Good">Good (Minor wear)</option>
                            <option value="Fair">Fair (Needs minor repairs)</option>
                            <option value="Poor">Poor (Needs major repairs)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Expected Value ($)</label>
                        <input type="number" name="expected_price" placeholder="e.g. 15000" required>
                    </div>
                    
                    <div class="btn-container">
                        <button type="reset" class="btn-clear">Clear All</button>
                        <button type="submit" class="btn-submit">Submit Details</button>
                    </div>
                </form>
            </div>

            
            <div class="col-half right">
                <h2>Your Trade-in Requests</h2>
                
                <?php
                $reqStmt = $conn->prepare("SELECT * FROM TRADE_IN WHERE Customer_Email = ? ORDER BY Request_Date DESC");
                $reqStmt->bind_param("s", $email);
                $reqStmt->execute();
                $reqResult = $reqStmt->get_result();

                if ($reqResult && $reqResult->num_rows > 0) {
                    while($req = $reqResult->fetch_assoc()) {
                        
                        $status = $req["Status"];
                        
                        if ($status == 'Pending') {
                            $badgeBg = '#f1c40f'; 
                        } elseif ($status == 'Approved' || $status == 'Accepted') {
                            $badgeBg = '#4CAF50'; 
                        } else {
                            $badgeBg = '#d9534f'; 
                        }

                        $reqDate = date("F j, Y", strtotime($req["Request_Date"]));
                        
                        echo '
                        <div class="car-card">
                            <div class="car-info">
                                <h3>' . $req["Year"] . ' ' . $req["Car_Make"] . ' ' . $req["Car_Model"] . '</h3>
                                <p>Mileage: ' . number_format($req["Mileage"]) . ' km | Condition: ' . $req["Condition_Status"] . '</p>
                                <span class="status-badge" style="background-color: ' . $badgeBg . ';">' . $status . '</span>
                                <span class="transaction-info">Asking: $' . number_format($req["Expected_Price"]) . ' <br><span class="date-text">Submitted on ' . $reqDate . '</span></span>
                            </div>';
                            
                            
                            if($status == 'Pending') {
                                echo '<a href="cancel_trade_in.php?id=' . $req["Trade_Id"] . '" class="action-btn" style="background-color: #e3d596; color: black;">Cancel Request</a>';
                            } elseif ($status == 'Approved') {
                                echo '<div class="action-btn" style="background-color: #c8e6c9; color: #256029;">Credits Added to Account</div>';
                            } else {
                                echo '<div class="action-btn" style="background-color: #ffcdd2; color: #c62828;">Request Cancelled</div>';
                            }
                            
                        echo '</div>';
                    }
                } else {
                    echo "<p>You have no pending trade-in requests.</p>";
                }
                $reqStmt->close();
                ?>
            </div>
        </div> 
    </div>

</body>
</html>