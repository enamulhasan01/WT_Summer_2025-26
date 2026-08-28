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
    $year_range = $_POST["year_range"];
    $budget = $_POST["max_budget"];
    $notes = $_POST["additional_notes"];

    
    $stmt = $conn->prepare("INSERT INTO CAR_REQUEST (Customer_Email, Car_Make, Car_Model, Year_Range, Max_Budget, Additional_Notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $email, $make, $model, $year_range, $budget, $notes);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Your car request has been successfully submitted! Suppliers will review it shortly.');
            window.location.href = 'make_request.php';
        </script>";
    } else {
        echo "<script>alert('Error submitting your request. Please try again.');</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Make a Request - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        
        
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; }
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; }
        .sidebar a { display: block; color: white; padding: 15px; text-decoration: none; margin-bottom: 10px; border-radius: 8px; background-color: #1a1a1a; }
        .sidebar a.active, .sidebar a:hover { background-color: #0a66c2; }
        .sidebar a.logout { background-color: #cc0000; margin-top: 50px; }

        
        .main-content { width: 80%; float: left; padding: 40px; height: 100vh; overflow-y: auto; }
        
        
        .request-form { width: 70%; margin-top: 20px; }
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border-radius: 20px;
            border: none;
            background-color: #cccccc; 
            font-size: 16px;
            color: #333333;
        }
        
        .form-group textarea {
            resize: none;
            height: 100px;
            border-radius: 15px;
        }

        
        .btn-container { text-align: left; margin-top: 30px; }
        
        .btn-clear {
            background-color: #666666;
            color: white;
            padding: 15px 30px;
            border-radius: 20px;
            border: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-right: 15px;
        }
        
        .btn-submit {
            background-color: #4CAF50; 
            color: white;
            padding: 15px 30px;
            border-radius: 20px;
            border: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .btn-clear:hover { background-color: #4d4d4d; }
        .btn-submit:hover { background-color: #45a049; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AutoMart</h2>
        <a href="customer_dashboard.php">Explore your next car</a>
        <a href="make_request.php" class="active">Make a Request</a>
        <a href="trade_in.php">Trade-in/ Sell</a>
        <a href="order_status.php">Order Status</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h2>Submit a request for a specific car</h2>
        
        <form class="request-form" action="" method="POST">
            
            <div class="form-group">
                <label>Car Make</label>
                <select name="car_make" required>
                    <option value="" disabled selected>e.g. BMW, Toyota, Honda...</option>
                    <option value="Audi">Audi</option>
                    <option value="BMW">BMW</option>
                    <option value="Ford">Ford</option>
                    <option value="Honda">Honda</option>
                    <option value="Mercedes-Benz">Mercedes-Benz</option>
                    <option value="Toyota">Toyota</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Car Model</label>
                <input type="text" name="car_model" placeholder="e.g. M4 Competition, Civic..." required>
            </div>
            
            <div class="form-group">
                <label>Preferred Year Range</label>
                <input type="text" name="year_range" placeholder="e.g. 2024-2026" required>
            </div>
            
            <div class="form-group">
                <label>Max Budget</label>
                <input type="number" name="max_budget" placeholder="e.g. 90000" required>
            </div>
            
            <div class="form-group">
                <label>Additional Notes</label>
                <textarea name="additional_notes" placeholder="e.g. Specific color preferences, must have sunroof..."></textarea>
            </div>
            
            <div class="btn-container">
                <button type="reset" class="btn-clear">Clear All</button>
                <button type="submit" class="btn-submit">Submit Request</button>
            </div>
            
        </form>

    </div>

</body>
</html>