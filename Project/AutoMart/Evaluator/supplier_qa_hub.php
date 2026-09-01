<?php
session_start();
require_once '../db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn->query("ALTER TABLE vehicle ADD COLUMN QA_Status VARCHAR(50) DEFAULT 'awaiting clearance'");
} catch (Exception $e) {}

try {
    $conn->query("ALTER TABLE vehicle ADD COLUMN Supplier_Name VARCHAR(100) DEFAULT 'Direct Supplier'");
} catch (Exception $e) {}


if (isset($_SESSION["role"]) && $_SESSION["role"] !== "Evaluator") {
    header("Location: ../login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $vehicle_id = intval($_POST['vehicle_id']);
    $action = $_POST['action'];
    
    if ($action === 'certify') {
        $qa_status = 'Cleared';
        $availability = 'Available';
    } else {
        $qa_status = 'Rejected';
        $availability = 'Returned';
    }

    try {
        
        $stmt = $conn->prepare("UPDATE vehicle SET QA_Status = ?, Availability = ? WHERE Vehicle_Id = ?");
        $stmt->bind_param("ssi", $qa_status, $availability, $vehicle_id);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        
        $stmt = $conn->prepare("UPDATE vehicle SET QA_Status = ? WHERE Vehicle_Id = ?");
        $stmt->bind_param("si", $qa_status, $vehicle_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: supplier_qa_hub.php?selected=" . $vehicle_id);
    exit();
}


$result = $conn->query("SELECT * FROM vehicle ORDER BY Vehicle_Id DESC");


$selected_id = $_GET['selected'] ?? null;
$selected_car = null;

if ($selected_id) {
    $stmt_sel = $conn->prepare("SELECT * FROM vehicle WHERE Vehicle_Id = ?");
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
    <title>Supplier QA Hub - AutoMart</title>
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

     
        .queue-container { display: flex; flex-direction: column; gap: 12px; }
        .qa-card { background-color: #161B22; border: 1px solid #21262D; border-radius: 12px; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: background 0.2s; }
        .qa-card:hover { background-color: #1F242C; }
        .qa-card.active-card { background-color: #1C2128; border-color: #0066FF; }
        .car-title { font-size: 17px; font-weight: 700; color: #FFFFFF; }
        .supplier-name { font-size: 13px; color: #8B949E; margin-top: 2px; }
        
        .qa-badge { padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; text-transform: lowercase; }
        .status-clearance { background-color: rgba(234, 179, 8, 0.15); color: #FACC15; }
        .status-cleared { background-color: rgba(34, 197, 94, 0.15); color: #4ADE80; }
        .status-rejected { background-color: rgba(239, 68, 68, 0.15); color: #F87171; }

       
        .inspection-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 8px; }
        .section-card { background-color: #FFFFFF; color: #0F172A; border-radius: 16px; padding: 24px; }
        .section-card h3 { font-size: 17px; font-weight: 800; color: #0F172A; margin-bottom: 4px; }
        .sub-text { font-size: 13px; color: #64748B; margin-bottom: 20px; }

     
        .doc-item { background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .doc-name { font-weight: 700; font-size: 14px; color: #0F172A; }
        .doc-sub { font-size: 12px; color: #64748B; }
        .doc-tag { font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px; }
        .tag-verified { background-color: #DCFCE7; color: #15803D; }
        .tag-pending { background-color: #FEF3C7; color: #B45309; }

       
        .check-item { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; font-size: 14px; color: #334155; font-weight: 600; }
        .check-item input { width: 18px; height: 18px; accent-color: #0066FF; cursor: pointer; }

       
        .action-bar { display: flex; gap: 16px; justify-content: flex-end; margin-top: 8px; }
        .btn-certify { background-color: #22C55E; color: white; border: none; padding: 14px 28px; border-radius: 10px; font-weight: 700; font-size: 15px; cursor: pointer; }
        .btn-certify:hover { background-color: #16A34A; }
        .btn-reject-supplier { background-color: #000000; color: #EF4444; border: 1px solid #DC2626; padding: 14px 28px; border-radius: 10px; font-weight: 700; font-size: 15px; cursor: pointer; }
        .btn-reject-supplier:hover { background-color: #7F1D1D; color: white; }
    </style>
</head>
<body>

  
    <div class="sidebar">
        <h2>AutoMart</h2>
        <a href="evaluator_dashboard.php" class="nav-link">Trade-In Queue</a>
        <a href="supplier_qa_hub.php" class="nav-link active">Supplier QA Hub</a>
        <a href="repair_logs.php" class="nav-link">Repair Logs</a>
        
        <a href="../logout.php" class="btn-logout">Logout</a>
    </div>

   
    <div class="main-content">
        <div class="page-header">Supplier Q&A Certification</div>

        
        <div class="queue-container">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php $isSelected = ($selected_car && $selected_car['Vehicle_Id'] == $row['Vehicle_Id']); ?>
                    <div class="qa-card <?php echo $isSelected ? 'active-card' : ''; ?>" onclick="window.location.href='supplier_qa_hub.php?selected=<?php echo $row['Vehicle_Id']; ?>'">
                        <div>
                            <div class="car-title"><?php echo htmlspecialchars($row['Year'] . ' ' . $row['Make'] . ' ' . $row['Model']); ?></div>
                            <div class="supplier-name">supplier: <?php echo htmlspecialchars($row['Supplier_Name'] ?? 'Direct Supplier'); ?></div>
                        </div>
                        <?php 
                            $qaStatus = $row['QA_Status'] ?? 'awaiting clearance';
                            $badgeClass = 'status-clearance';
                            if ($qaStatus === 'Cleared') $badgeClass = 'status-cleared';
                            if ($qaStatus === 'Rejected') $badgeClass = 'status-rejected';
                        ?>
                        <div class="qa-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($qaStatus); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="color: #8B949E; text-align: center; padding: 24px;">No supplier vehicles found.</div>
            <?php endif; ?>
        </div>

      
        <?php if ($selected_car): ?>
        <form method="POST" action="supplier_qa_hub.php">
            <input type="hidden" name="vehicle_id" value="<?php echo $selected_car['Vehicle_Id']; ?>">
            
            <div class="inspection-grid">
               
                <div class="section-card">
                    <h3>Paper trail</h3>
                    <div class="sub-text"><?php echo htmlspecialchars($selected_car['Make'] . ' ' . $selected_car['Model']); ?></div>

                    <div class="doc-item">
                        <div>
                            <div class="doc-name">VIN History Report</div>
                            <div class="doc-sub">CARFAX &bull; Uploaded</div>
                        </div>
                        <span class="doc-tag tag-verified">&check; Verified</span>
                    </div>

                    <div class="doc-item">
                        <div>
                            <div class="doc-name">Title Document</div>
                            <div class="doc-sub">Clean title &bull; Uploaded</div>
                        </div>
                        <span class="doc-tag tag-verified">&check; Verified</span>
                    </div>

                    <div class="doc-item">
                        <div>
                            <div class="doc-name">Odometer Disclosure</div>
                            <div class="doc-sub">Uploaded</div>
                        </div>
                        <span class="doc-tag tag-pending">Pending review</span>
                    </div>
                </div>

                <div class="section-card">
                    <h3>Mechanical checklist</h3>
                    <div class="sub-text">Verification Items</div>

                    <label class="check-item">
                        <input type="checkbox" checked> Brake pads & rotors within spec
                    </label>
                    <label class="check-item">
                        <input type="checkbox" checked> Tire tread depth &ge; 5/32"
                    </label>
                    <label class="check-item">
                        <input type="checkbox" checked> No active check-engine codes
                    </label>
                    <label class="check-item">
                        <input type="checkbox" checked> Fluid levels & leak inspection
                    </label>
                    <label class="check-item">
                        <input type="checkbox"> Suspension & steering play test
                    </label>
                    <label class="check-item">
                        <input type="checkbox"> A/C & climate control function
                    </label>
                </div>
            </div>

            <div class="action-bar">
                <button type="submit" name="action" value="certify" class="btn-certify">Certify for active inventory</button>
                <button type="submit" name="action" value="reject" class="btn-reject-supplier">Reject and return to supplier</button>
            </div>
        </form>
        <?php endif; ?>
    </div>

</body>
</html>