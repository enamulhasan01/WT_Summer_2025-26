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


$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM vehicle WHERE Make LIKE ? OR Model LIKE ? ORDER BY Vehicle_Id DESC");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM vehicle ORDER BY Vehicle_Id DESC");
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
    <title>Proposed Vehicles - AutoMart</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { display: flex; background-color: #0B1622; color: #FFFFFF; min-height: 100vh; }

        
        .sidebar { width: 260px; background-color: #0F1216; padding: 24px 16px; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar h2 { font-size: 24px; font-weight: bold; margin-bottom: 32px; padding-left: 8px; color: #FFFFFF; }
        .nav-link { display: block; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; color: #8B949E; text-decoration: none; font-size: 15px; font-weight: 500; }
        .nav-link.active { background-color: #0066FF; color: #FFFFFF; }
        .nav-link:hover:not(.active) { background-color: #161B22; color: #FFFFFF; }
        .btn-logout { margin-top: auto; background-color: #DC2626; color: white; padding: 12px; border-radius: 8px; border: none; text-align: center; text-decoration: none; font-weight: bold; display: block; }

        .main-content { flex: 1; padding: 32px 40px; overflow-y: auto; }
        .top-bar { display: flex; gap: 16px; align-items: center; margin-bottom: 32px; }
        .search-form { display: flex; flex: 1; gap: 12px; }
        .search-input { flex: 1; background-color: #FFFFFF; color: #1E1E1E; border: none; border-radius: 24px; padding: 14px 24px; font-size: 15px; outline: none; }
        .btn-search { background-color: #CBD5E1; color: #0F172A; border: none; border-radius: 24px; padding: 14px 32px; font-size: 15px; font-weight: bold; cursor: pointer; }
        .btn-search:hover { background-color: #94A3B8; }
        .btn-propose { background-color: #0066FF; color: white; text-decoration: none; border-radius: 24px; padding: 14px 24px; font-size: 15px; font-weight: bold; white-space: nowrap; }
        .btn-propose:hover { opacity: 0.9; }

        
        .vehicle-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .car-card { background-color: #FFFFFF; color: #0F172A; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
        .car-title { font-size: 20px; font-weight: 800; color: #0F172A; }
        .car-details { font-size: 14px; color: #64748B; margin-top: 8px; font-weight: 500; }
        .car-img { width: 140px; height: 80px; object-fit: contain; }

        .card-footer-info { display: flex; align-items: center; gap: 12px; margin-top: 12px; }
        .badge { padding: 4px 12px; border-radius: 12px; font-size: 13px; font-weight: 700; color: white; }
        .badge-available { background-color: #22C55E; }
        .badge-not-available { background-color: #EF4444; }
        .car-price { font-size: 18px; font-weight: 800; color: #0F172A; }

        .btn-view-details { margin-top: 16px; width: 100%; background-color: #FECDD3; color: #9F1239; border: none; border-radius: 12px; padding: 10px; font-weight: bold; font-size: 14px; text-align: center; text-decoration: none; display: block; }
        .btn-view-details.available-btn { background-color: #DCFCE7; color: #15803D; }
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
        
        <div class="top-bar">
            <form method="GET" action="propose_vehicle.php" class="search-form">
                <input type="text" name="search" class="search-input" placeholder="Search by Make or Model..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-search">Search</button>
            </form>
            <a href="submit_proposal.php" class="btn-propose">+ Proposal Submission</a>
        </div>

      
        <div class="vehicle-grid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php 
                        $imagePath = getCarImage($row['Image'], $row['Model']);
                        $isAvailable = (strtolower($row['Availability']) === 'available');
                    ?>
                    <div class="car-card">
                        <div>
                            <div class="card-top">
                                <div>
                                    <h3 class="car-title"><?php echo htmlspecialchars($row['Year'] . ' ' . $row['Make'] . ' ' . $row['Model']); ?></h3>
                                    <div class="car-details">
                                        <?php if (!empty($row['Mileage']) && $row['Mileage'] > 0): ?>
                                            Mileage: <?php echo number_format($row['Mileage']); ?> km | 
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($row['Condition_Status']); ?> | 
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($row['Color']); ?> | <?php echo htmlspecialchars($row['Body_Type']); ?>
                                    </div>
                                </div>
                                <img src="<?php echo $imagePath; ?>" alt="Car" class="car-img">
                            </div>

                            <div class="card-footer-info">
                                <span class="badge <?php echo $isAvailable ? 'badge-available' : 'badge-not-available'; ?>">
                                    <?php echo htmlspecialchars($row['Availability']); ?>
                                </span>
                                <span class="car-price">$<?php echo number_format($row['Listed_Price']); ?></span>
                            </div>
                        </div>

                        <a href="car_details.php?id=<?php echo $row['Vehicle_Id']; ?>" class="btn-view-details <?php echo $isAvailable ? 'available-btn' : ''; ?>">View Details</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #8B949E; grid-column: span 2;">No proposed vehicles found.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>