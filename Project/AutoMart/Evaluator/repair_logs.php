<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION["email"]) || $_SESSION["role"] !== "Evaluator") {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $trade_id = intval($_POST['trade_id']);
    $action = $_POST['action'];
    $repair_details = trim($_POST['repair_details']);
    $counter_offer = !empty($_POST['counter_offer']) ? floatval($_POST['counter_offer']) : null;

    $status = ($action === 'accept') ? 'Approved' : 'Rejected';

    
    if ($status === 'Approved' && $counter_offer !== null) {
        $stmt_update = $conn->prepare("UPDATE trade_in SET Status = ?, Repair_Details = ?, Counter_Offer = ?, Expected_Price = ? WHERE Trade_Id = ?");
        $stmt_update->bind_param("ssddi", $status, $repair_details, $counter_offer, $counter_offer, $trade_id);
    } else {
        $stmt_update = $conn->prepare("UPDATE trade_in SET Status = ?, Repair_Details = ?, Counter_Offer = ? WHERE Trade_Id = ?");
        $stmt_update->bind_param("ssdi", $status, $repair_details, $counter_offer, $trade_id);
    }
    
    $stmt_update->execute();
    $stmt_update->close();

    

    
    $msg = ($action === 'accept') ? 'accept' : 'rejected';
    header("Location: repair_logs.php?selected=" . $trade_id . "&msg=" . $msg);
    exit();
}


$query = "SELECT * FROM trade_in WHERE Condition_Status = 'Poor' OR Status LIKE '%Repair%' ORDER BY Trade_Id DESC";
$result = $conn->query($query);

$selected_id = $_GET['selected'] ?? null;
$selected_car = null;

if ($selected_id) {
    $stmt_sel = $conn->prepare("SELECT * FROM trade_in WHERE Trade_Id = ?");
    $stmt_sel->bind_param("i", $selected_id);
    $stmt_sel->execute();
    $selected_car = $stmt_sel->get_result()->fetch_assoc();
    $stmt_sel->close();
} elseif ($result && $result->num_rows > 0) {
    $result->data_seek(0);
    $selected_car = $result->fetch_assoc();
    $result->data_seek(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Repair Logs - AutoMart Evaluator</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { display: flex; background-color: #0B1622; color: #FFFFFF; min-height: 100vh; }

        .sidebar { width: 260px; background-color: #0F1216; padding: 24px 16px; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar h2 { font-size: 24px; font-weight: bold; margin-bottom: 32px; padding-left: 8px; color: #FFFFFF; }
        .nav-link { display: block; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; color: #8B949E; text-decoration: none; font-size: 15px; font-weight: 500; }
        .nav-link.active { background-color: #0066FF; color: #FFFFFF; }
        .nav-link:hover:not(.active) { background-color: #161B22; color: #FFFFFF; }
        .btn-logout { margin-top: auto; background-color: #DC2626; color: white; padding: 12px; border-radius: 8px; border: none; text-align: center; text-decoration: none; font-weight: bold; display: block; }

        .main-content { flex: 1; padding: 40px; overflow-y: auto; display: flex; flex-direction: column; gap: 24px; }
        .page-header { background-color: #161B22; padding: 12px 24px; border-radius: 12px; width: fit-content; font-size: 16px; font-weight: 700; color: #FFFFFF; border: 1px solid #21262D; }

        .table-card { background-color: #161B22; border: 1px solid #21262D; border-radius: 16px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #0F1216; padding: 16px 20px; font-size: 14px; color: #8B949E; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 16px 20px; border-top: 1px solid #21262D; font-size: 15px; color: #E6EDF3; }
        tr.active-row { background-color: #1C2128; }
        tr:hover { background-color: #1F242C; cursor: pointer; }

        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 700; }
        .status-approved { background-color: rgba(34, 197, 94, 0.15); color: #4ADE80; }
        .status-rejected { background-color: rgba(239, 68, 68, 0.15); color: #F87171; }
        .status-pending { background-color: rgba(234, 179, 8, 0.15); color: #FACC15; }

        .inspection-card { background-color: #FFFFFF; color: #0F172A; border-radius: 20px; padding: 32px; display: flex; gap: 40px; }
        
        .repair-form-section { flex: 1.2; display: flex; flex-direction: column; gap: 20px; border-right: 1px solid #E2E8F0; padding-right: 32px; }
        .repair-form-section h3 { font-size: 18px; font-weight: 800; color: #0F172A; }
        
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 14px; font-weight: 700; color: #334155; }
        .form-input, .form-textarea { padding: 10px 14px; background: #F8FAFC; border: 1px solid #CBD5E1; color: #0F172A; border-radius: 8px; font-size: 15px; font-weight: 600; }
        .form-textarea { resize: vertical; min-height: 90px; }

        .details-section { flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .car-heading { font-size: 24px; font-weight: 800; color: #0F172A; margin-bottom: 4px; }
        .car-sub { font-size: 14px; color: #64748B; margin-bottom: 16px; }
        .spec-row { font-size: 15px; color: #334155; margin-bottom: 8px; }
        .spec-row strong { color: #0F172A; display: inline-block; width: 150px; }

        .action-btns { display: flex; gap: 12px; margin-top: 24px; }
        .btn-accept { flex: 1; background-color: #16A34A; border: none; color: white; padding: 14px; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 15px; }
        .btn-accept:hover { background-color: #15803D; }
        .btn-reject { flex: 1; background-color: #DC2626; border: none; color: white; padding: 14px; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 15px; }
        .btn-reject:hover { background-color: #B91C1C; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AutoMart</h2>
        <a href="evaluator_dashboard.php" class="nav-link">Trade-In Queue</a>
        <a href="supplier_qa_hub.php" class="nav-link">Supplier QA Hub</a>
        <a href="repair_logs.php" class="nav-link active">Repair Logs</a>
        
        <a href="../logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="page-header">Repair Logs & Re-Evaluation</div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Customer</th>
                        <th>Model</th>
                        <th>Condition</th>
                        <th>Customer Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php $isSelected = ($selected_car && $selected_car['Trade_Id'] == $row['Trade_Id']); ?>
                            <tr class="<?php echo $isSelected ? 'active-row' : ''; ?>" onclick="window.location.href='repair_logs.php?selected=<?php echo $row['Trade_Id']; ?>'">
                                <td>T-<?php echo str_pad($row['Trade_Id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($row['Customer_Email']); ?></td>
                                <td><?php echo htmlspecialchars($row['Year'] . ' ' . $row['Car_Make'] . ' ' . $row['Car_Model']); ?></td>
                                <td><span style="color: #F87171; font-weight: bold;"><?php echo htmlspecialchars($row['Condition_Status']); ?></span></td>
                                <td>$<?php echo number_format($row['Expected_Price']); ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = 'status-pending';
                                        if ($row['Status'] == 'Approved') $badgeClass = 'status-approved';
                                        if ($row['Status'] == 'Rejected') $badgeClass = 'status-rejected';
                                    ?>
                                    <span class="status-badge <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($row['Status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: #8B949E; padding: 24px;">No poor-conditioned vehicles in repair log queue.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($selected_car): ?>
        <form method="POST" action="repair_logs.php" id="repairForm">
            <input type="hidden" name="trade_id" value="<?php echo $selected_car['Trade_Id']; ?>">
            <input type="hidden" name="action" id="actionInput" value="">
            
            <div class="inspection-card">
                <div class="repair-form-section">
                    <h3>Repair & Valuation Assessment</h3>
                    
                    <div class="form-group">
                        <label for="repair_details">Required Repairs / Maintenance</label>
                        <textarea name="repair_details" id="repair_details" class="form-textarea" placeholder="Describe mechanical/body repairs needed (e.g. Bumper replacement, Engine tuning)..."><?php echo htmlspecialchars($selected_car['Repair_Details'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="counter_offer">Adjusted Offer Price ($)</label>
                        <input type="number" step="0.01" name="counter_offer" id="counter_offer" class="form-input" placeholder="e.g. 800" value="<?php echo htmlspecialchars($selected_car['Counter_Offer'] ?? $selected_car['Expected_Price']); ?>">
                    </div>
                </div>

                <div class="details-section">
                    <div>
                        <h2 class="car-heading"><?php echo htmlspecialchars($selected_car['Year'] . ' ' . $selected_car['Car_Make'] . ' ' . $selected_car['Car_Model']); ?></h2>
                        <div class="car-sub">Submitted Request T-<?php echo str_pad($selected_car['Trade_Id'], 3, '0', STR_PAD_LEFT); ?></div>

                        <div class="spec-row"><strong>Customer:</strong> <?php echo htmlspecialchars($selected_car['Customer_Email']); ?></div>
                        <div class="spec-row"><strong>Mileage:</strong> <?php echo number_format($selected_car['Mileage']); ?> km</div>
                        <div class="spec-row"><strong>Condition:</strong> <span style="color: #DC2626; font-weight: bold;"><?php echo htmlspecialchars($selected_car['Condition_Status']); ?></span></div>
                        <div class="spec-row"><strong>Asked Value:</strong> <span style="color: #64748B;">$<?php echo number_format($selected_car['Expected_Price']); ?></span></div>
                        <?php if (!empty($selected_car['Counter_Offer'])): ?>
                            <div class="spec-row"><strong>Post-Repair Offer:</strong> <span style="color: #16A34A; font-weight: 800;">$<?php echo number_format($selected_car['Counter_Offer']); ?></span></div>
                        <?php endif; ?>
                    </div>

                    <div class="action-btns">
                        <button type="submit" onclick="document.getElementById('actionInput').value='accept';" class="btn-accept">Approve & Update Price</button>
                        <button type="submit" onclick="document.getElementById('actionInput').value='reject';" class="btn-reject">Reject Vehicle</button>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        if (msg === 'accept') {
            alert('Repair log updated: Vehicle approved and price revised successfully!');
        } else if (msg === 'rejected') {
            alert('Trade-in request rejected.');
        }
    </script>
</body>
</html>