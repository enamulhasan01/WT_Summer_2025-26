<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../db.php';

$supplier_email = $_SESSION['email'] ?? $_SESSION['user_email'] ?? $_SESSION['user'] ?? '';
$total_approved = 0;
if (!empty($supplier_email)) {
    $stmt_count = $conn->prepare("SELECT COUNT(*) AS total_approved FROM car_request WHERE Supplier_Email = ? AND Status = 'Approved'");
    $stmt_count->bind_param("s", $supplier_email);
    $stmt_count->execute();
    $count_result = $stmt_count->get_result()->fetch_assoc();
    $total_approved = $count_result['total_approved'] ?? 0;
    $stmt_count->close();
}


$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$car = null;

if ($vehicle_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM vehicle WHERE Vehicle_Id = ?");
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $car = $result->fetch_assoc();
    }
    $stmt->close();
}


function getCarImage($imageName, $model) {
    if (!empty($imageName) && file_exists("../Assets/" . $imageName)) {
        return "../Assets/" . $imageName;
    }
    $modelLower = strtolower($model);
    if (strpos($modelLower, 'civic') !== false) {
        return '../Assets/honda_civic.png';
    } elseif (strpos($modelLower, 'rav4') !== false) {
        return '../Assets/toyota_rav4.png';
    } elseif (strpos($modelLower, 'bmw') !== false) {
        return '../Assets/bmw_m4.png';
    }
    return '../Assets/default_car.png';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $car ? htmlspecialchars($car['Year'] . ' ' . $car['Make'] . ' ' . $car['Model']) : 'Car Details'; ?> - AutoMart</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { display: flex; background-color: #0B1622; color: #FFFFFF; min-height: 100vh; }

      
        .sidebar { width: 260px; background-color: #0F1216; padding: 24px 16px; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar h2 { font-size: 24px; font-weight: bold; margin-bottom: 32px; padding-left: 8px; color: #FFFFFF; }
        .nav-link { display: block; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; color: #8B949E; text-decoration: none; font-size: 15px; font-weight: 500; }
        .nav-link.active { background-color: #0066FF; color: #FFFFFF; }
        .nav-link:hover:not(.active) { background-color: #161B22; color: #FFFFFF; }
        .btn-logout { margin-top: auto; background-color: #DC2626; color: white; padding: 12px; border-radius: 8px; border: none; text-align: center; text-decoration: none; font-weight: bold; display: block; }

        .main-content { flex: 1; padding: 40px; overflow-y: auto; }

        .back-link { display: inline-block; color: #3B82F6; text-decoration: none; font-size: 15px; font-weight: 600; margin-bottom: 24px; }
        .back-link:hover { text-decoration: underline; }

        
        .details-card { background-color: #FFFFFF; color: #0F172A; border-radius: 20px; padding: 32px; display: flex; gap: 40px; align-items: flex-start; max-width: 950px; }
        .details-image-container { flex: 1; display: flex; align-items: center; justify-content: center; background-color: #F8FAFC; border-radius: 16px; padding: 20px; min-height: 320px; }
        .details-image-container img { max-width: 100%; max-height: 300px; object-fit: contain; }

        .details-info { flex: 1; display: flex; flex-direction: column; gap: 16px; }
        .car-title { font-size: 28px; font-weight: 800; color: #0F172A; }
        .car-price { font-size: 26px; font-weight: 800; color: #16A34A; margin-bottom: 8px; }

    
        .spec-box { background-color: #F1F5F9; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; gap: 12px; }
        .spec-item { font-size: 15px; color: #334155; }
        .spec-item strong { color: #0F172A; display: inline-block; width: 120px; }

        .not-found { background-color: #FFFFFF; color: #0F172A; padding: 40px; border-radius: 16px; text-align: center; }
    </style>
</head>
<body>
  
    <div class="sidebar">
        <h2>AutoMart</h2>
        <a href="supplier_demands.php" class="nav-link">Customer Demands</a>
        <a href="propose_vehicle.php" class="nav-link active">All Proposed Vehicles</a>
        <a href="submit_proposal.php" class="nav-link">Proposal Submission</a>
        
        <div style="padding: 12px 16px; color: #8B949E; font-size: 14px;">
            Approved Demands: <strong style="color: #4ADE80;"><?php echo $total_approved; ?></strong>
        </div>

        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

   
    <div class="main-content">
        <a href="propose_vehicle.php" class="back-link">&larr; Back to All Proposed Vehicles</a>

        <?php if ($car): ?>
            <?php $imagePath = getCarImage($car['Image'], $car['Model']); ?>
            <div class="details-card">
                <div class="details-image-container">
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($car['Model']); ?>">
                </div>

                <div class="details-info">
                    <h1 class="car-title"><?php echo htmlspecialchars($car['Year'] . ' ' . $car['Make'] . ' ' . $car['Model']); ?></h1>
                    <div class="car-price">$<?php echo number_format($car['Listed_Price']); ?></div>

                    <div class="spec-box">
                        <div class="spec-item"><strong>Condition:</strong> <?php echo htmlspecialchars($car['Condition_Status']); ?></div>
                        <div class="spec-item"><strong>Mileage:</strong> <?php echo number_format($car['Mileage']); ?> km</div>
                        <div class="spec-item"><strong>Color:</strong> <?php echo htmlspecialchars($car['Color']); ?></div>
                        <div class="spec-item"><strong>Body Type:</strong> <?php echo htmlspecialchars($car['Body_Type']); ?></div>
                        <div class="spec-item"><strong>Availability:</strong> <?php echo htmlspecialchars($car['Availability']); ?></div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="not-found">
                <h2>Vehicle Not Found</h2>
                <p style="margin-top: 8px; color: #64748B;">The requested vehicle details are unavailable.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>