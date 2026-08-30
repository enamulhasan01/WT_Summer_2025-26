<?php
session_start();

// Import shared database connection
require_once '../db.php';

// Retrieve supplier email from active session
$supplier_email = $_SESSION['user_email'] ?? $_SESSION['email'] ?? 'Unknown Supplier';

// Handle Status Updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $request_id = intval($_POST['request_id']);
    $status     = ($_POST['action'] === 'approve') ? 'Approved' : 'Rejected';

    // Update both status and the supplier who handled it
    $stmt = $conn->prepare("UPDATE car_request SET Status = ?, Supplier_Email = ? WHERE Request_Id = ?");
    $stmt->bind_param("ssi", $status, $supplier_email, $request_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: supplier_demands.php");
    exit();
}

// Fetch all customer requests
$sql    = "SELECT * FROM car_request ORDER BY Request_Id DESC";
$result = $conn->query($sql);

// Helper function to map model names to relative asset images
function getCarImage($model) {
    $modelLower = strtolower($model);
    
    if (strpos($modelLower, 'civic') !== false) {
        return '../Assets/honda_civic.png';
    } elseif (strpos($modelLower, 'rav4') !== false) {
        return '../Assets/toyota_rav4.png';
    } elseif (strpos($modelLower, 'bmw') !== false) {
        return '../Assets/bmw_m4.png';
    }
    
        return '../Assets/default_car.png'; }
   
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total_approved FROM car_request WHERE Supplier_Email = ? AND Status = 'Approved'");
$stmt_count->bind_param("s", $supplier_email);
$stmt_count->execute();
$count_result = $stmt_count->get_result()->fetch_assoc();
$total_approved = $count_result['total_approved'];
$stmt_count->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Portal - AutoMart</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { display: flex; background-color: #0D1117; color: #FFFFFF; min-height: 100vh; }

        /* AutoMart Sidebar */
        .sidebar { width: 260px; background-color: #0F1216; padding: 24px 16px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 24px; font-weight: bold; margin-bottom: 32px; padding-left: 8px; }
        .nav-link { display: block; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; color: #8B949E; text-decoration: none; font-size: 15px; font-weight: 500; }
        .nav-link.active { background-color: #0066FF; color: #FFFFFF; }
        .nav-link:hover:not(.active) { background-color: #161B22; color: #FFFFFF; }
        .btn-logout { margin-top: auto; background-color: #DC2626; color: white; padding: 12px; border-radius: 8px; border: none; text-align: center; text-decoration: none; font-weight: bold; display: block; }

        /* Main Dashboard Content */
        .main-content { flex: 1; padding: 32px; overflow-y: auto; }
        .page-title { font-size: 24px; font-weight: bold; margin-bottom: 24px; }

        /* Card Layout matching Figma */
        .cards-container { display: flex; flex-direction: column; gap: 20px; }
        .card { 
            background-color: #E2E8F0; 
            color: #111827; 
            border-radius: 20px; 
            padding: 24px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .card-left { flex: 1; padding-right: 20px; }
        .card-title { font-size: 22px; font-weight: 800; margin-bottom: 6px; color: #0F1216; }
        .card-meta { font-size: 15px; color: #1F2937; margin-bottom: 12px; font-weight: 600; }

        /* Dynamic Status Badges */
        .badge { display: inline-block; padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-Pending { background-color: #FEF08A; color: #854D0E; }
        .badge-Approved { background-color: #DCFCE7; color: #166534; }
        .badge-Rejected, .badge-Cancelled { background-color: #FEE2E2; color: #991B1B; }

        /* Card Right Area (Action Buttons + Car Thumbnail) */
        .card-right { display: flex; align-items: center; gap: 24px; }
        .car-img { width: 220px; height: 110px; object-fit: contain; }

        .card-actions { display: flex; gap: 10px; align-items: center; }
        .btn-action { padding: 10px 20px; border-radius: 20px; border: none; font-weight: 700; cursor: pointer; font-size: 14px; transition: opacity 0.2s; }
        .btn-approve { background-color: #16A34A; color: #FFFFFF; }
        .btn-reject { background-color: #DC2626; color: #FFFFFF; }
        .btn-action:hover { opacity: 0.85; }
    </style>
</head>
<body>

  <div class="sidebar">
    <h2>AutoMart</h2>
    <a href="supplier_demands.php" class="nav-link active">Customer Demands</a>
    <a href="#" class="nav-link">All Proposed Vehicles</a>
    
    <!-- Display Total Approvals -->
    <div style="padding: 12px 16px; color: #8B949E; font-size: 14px;">
        Approved Demands: <strong style="color: #4ADE80;"><?php echo $total_approved; ?></strong>
    </div>

    <a href="logout.php" class="btn-logout">Logout</a>
</div>

    <div class="main-content">
        <h1 class="page-title">Customer Demands :-</h1>
        
        <div class="cards-container">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-left">
                            <div class="card-title"><?php echo htmlspecialchars($row['Car_Make'] . ' ' . $row['Car_Model']); ?> (<?php echo htmlspecialchars($row['Year_Range']); ?>)</div>
                            <div class="card-meta">
                                Budget: $<?php echo htmlspecialchars($row['Max_Budget']); ?> | 
                                Customer: <?php echo htmlspecialchars($row['Customer_Email']); ?> | 
                                Color: <?php echo htmlspecialchars($row['Color'] ?? 'N/A'); ?>
                            </div>
                            <?php if(!empty($row['Additional_Notes'])): ?>
                                <div class="card-meta" style="font-weight: 400; font-size: 14px;"><strong>Notes:</strong> <?php echo htmlspecialchars($row['Additional_Notes']); ?></div>
                            <?php endif; ?>
                            
                            <span class="badge badge-<?php echo htmlspecialchars($row['Status']); ?>">
                                Status: <?php echo htmlspecialchars($row['Status']); ?>
                            </span>
                        </div>

                        <div class="card-right">
                            <div class="card-actions">
                                <?php if ($row['Status'] === 'Pending'): ?>
                                    <form method="POST" style="display: flex; gap: 10px;">
                                        <input type="hidden" name="request_id" value="<?php echo $row['Request_Id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn-action btn-approve">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn-action btn-reject">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-<?php echo htmlspecialchars($row['Status']); ?>">
                                        Action Completed (<?php echo htmlspecialchars($row['Status']); ?>)
                                    </span>
                                <?php endif; ?>
                            </div>
                            <!-- Image aligned to right side of card -->
                            <img src="<?php echo getCarImage($row['Car_Model']); ?>" alt="<?php echo htmlspecialchars($row['Car_Model']); ?>" class="car-img" onerror="this.style.display='none'">
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No customer demands found.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>