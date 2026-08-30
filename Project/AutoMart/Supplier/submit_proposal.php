<?php
session_start();
require_once '../db.php';

$message = '';
$status_type = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $make             = trim($_POST['make']);
    $model            = trim($_POST['model']);
    $year             = intval($_POST['year']);
    $condition_status = trim($_POST['condition_status']);
    $color            = trim($_POST['color']);
    $body_type        = trim($_POST['body_type']);
    $mileage          = intval($_POST['mileage']);
    $listed_price     = floatval($_POST['listed_price']);
    $availability     = trim($_POST['availability']);
    $image            = trim($_POST['image']);

    // Default fallback image if left blank
    if (empty($image)) {
        $image = 'default_car.png';
    }

    $stmt = $conn->prepare("INSERT INTO vehicle (Make, Model, Year, Condition_Status, Color, Body_Type, Mileage, Listed_Price, Availability, Image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssidss", $make, $model, $year, $condition_status, $color, $body_type, $mileage, $listed_price, $availability, $image);

    if ($stmt->execute()) {
        $message = "Vehicle proposed and inserted into database successfully!";
        $status_type = "success";
    } else {
        $message = "Error inserting vehicle: " . $conn->error;
        $status_type = "error";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Propose Vehicle - AutoMart</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { display: flex; background-color: #0B1622; color: #FFFFFF; min-height: 100vh; }

        /* Sidebar Styling */
        .sidebar { width: 260px; background-color: #0F1216; padding: 24px 16px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 24px; font-weight: bold; margin-bottom: 32px; padding-left: 8px; color: #FFFFFF; }
        .nav-link { display: block; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; color: #8B949E; text-decoration: none; font-size: 15px; font-weight: 500; }
        .nav-link.active { background-color: #0066FF; color: #FFFFFF; }
        .nav-link:hover:not(.active) { background-color: #161B22; color: #FFFFFF; }
        .btn-logout { margin-top: auto; background-color: #DC2626; color: white; padding: 12px; border-radius: 8px; border: none; text-align: center; text-decoration: none; font-weight: bold; display: block; }

        /* Main Form Area */
        .main-content { flex: 1; padding: 40px 80px; overflow-y: auto; display: flex; flex-direction: column; align-items: center; }
        .form-container { width: 100%; max-width: 720px; }
        
        .form-group { margin-bottom: 24px; }
        .label-pill { 
            display: inline-block; 
            background-color: #E2C974; 
            color: #1E1E1E; 
            padding: 6px 16px; 
            border-radius: 16px; 
            font-size: 14px; 
            font-weight: 700; 
            margin-bottom: 10px; 
        }

        .input-field, .select-field { 
            width: 100%; 
            background-color: #CBD5E1; 
            color: #0F172A; 
            border: none; 
            border-radius: 24px; 
            padding: 14px 20px; 
            font-size: 16px; 
            font-weight: 600; 
            outline: none; 
        }

        .btn-submit { 
            background-color: #0066FF; 
            color: white; 
            border: none; 
            padding: 14px 28px; 
            border-radius: 24px; 
            font-size: 16px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: opacity 0.2s; 
            margin-top: 10px;
        }
        .btn-submit:hover { opacity: 0.9; }

        .alert { padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background-color: #059669; color: white; }
        .alert-error { background-color: #DC2626; color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AutoMart</h2>
        <a href="supplier_demands.php" class="nav-link">Customer Demands</a>
        <a href="propose_vehicle.php" class="nav-link active">Propose Vehicle</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="form-container">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="submit_proposal.php">
                <div class="form-group">
                    <span class="label-pill">Car Make</span>
                    <input type="text" name="make" class="input-field" placeholder="e.g. BMW" required>
                </div>

                <div class="form-group">
                    <span class="label-pill">Car Model</span>
                    <input type="text" name="model" class="input-field" placeholder="e.g. M4 Competition" required>
                </div>

                <div class="form-group">
                    <span class="label-pill">Year</span>
                    <input type="number" name="year" class="input-field" placeholder="e.g. 2025" required>
                </div>

                <div class="form-group">
                    <span class="label-pill">Condition Status</span>
                    <select name="condition_status" class="select-field" required>
                        <option value="Brand New">Brand New</option>
                        <option value="Used">Used</option>
                    </select>
                </div>

                <div class="form-group">
                    <span class="label-pill">Color</span>
                    <input type="text" name="color" class="input-field" placeholder="e.g. Silver">
                </div>

                <div class="form-group">
                    <span class="label-pill">Body Type</span>
                    <input type="text" name="body_type" class="input-field" placeholder="e.g. Coupe, Sedan, SUV">
                </div>

                <div class="form-group">
                    <span class="label-pill">Mileage</span>
                    <input type="number" name="mileage" class="input-field" placeholder="e.g. 0" value="0">
                </div>

                <div class="form-group">
                    <span class="label-pill">Listed Price ($)</span>
                    <input type="number" step="0.01" name="listed_price" class="input-field" placeholder="e.g. 90000" required>
                </div>

                <div class="form-group">
                    <span class="label-pill">Availability</span>
                    <select name="availability" class="select-field">
                        <option value="Available">Available</option>
                        <option value="Not Available">Not Available</option>
                    </select>
                </div>

                <div class="form-group">
                    <span class="label-pill">Image Filename</span>
                    <input type="text" name="image" class="input-field" placeholder="e.g. bmw_m4.png (optional)">
                </div>

                <button type="submit" class="btn-submit">Submit Proposal</button>
            </form>
        </div>
    </div>

</body>
</html>