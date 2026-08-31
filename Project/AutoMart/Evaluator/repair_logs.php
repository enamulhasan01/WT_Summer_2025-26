<?php
session_start();
require_once '../db.php';

try { $conn->query("ALTER TABLE vehicle ADD COLUMN QA_Status VARCHAR(50) DEFAULT 'Needs Repair'"); } catch (Exception $e) {}
try { $conn->query("ALTER TABLE vehicle ADD COLUMN Repair_Notes TEXT DEFAULT ''"); } catch (Exception $e) {}
try { $conn->query("ALTER TABLE vehicle ADD COLUMN Repair_Cost DECIMAL(10,2) DEFAULT 0.00"); } catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_repair') {
    $vehicle_id = intval($_POST['vehicle_id']);
    $service_desc = trim($_POST['service_description']);
    $parts_cost = floatval($_POST['parts_cost']);
    $service_cost = floatval($_POST['service_cost']);
    $total_cost = $parts_cost + $service_cost;

    $stmt = $conn->prepare("UPDATE vehicle SET QA_Status = 'Ready for Inventory', Availability = 'Available', Repair_Notes = ?, Repair_Cost = ? WHERE Vehicle_Id = ?");
    $stmt->bind_param("sdi", $service_desc, $total_cost, $vehicle_id);
    $stmt->execute();
    $stmt->close();

    header("Location: repair_logs.php?success=1");
    exit();
}

$result = $conn->query("SELECT * FROM vehicle WHERE QA_Status LIKE '%Repair%' OR QA_Status LIKE '%Poor%' OR QA_Status LIKE '%Needs%' OR QA_Status = 'Pending Repair' OR (Condition_Status = 'Poor' AND QA_Status = 'awaiting clearance') ORDER BY Vehicle_Id DESC");

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
    <title>Repair Logs - AutoMart Evaluator</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { display: flex; background: linear-gradient(135deg, #070D14 0%, #0B1622 100%); color: #FFFFFF; min-height: 100vh; }

      
        .sidebar { width: 260px; background-color: #0F1216; padding: 24px 16px; display: flex; flex-direction: column; flex-shrink: 0; border-right: 1px solid #1F2937; }
        .sidebar h2 { font-size: 24px; font-weight: bold; margin-bottom: 32px; padding-left: 8px; color: #FFFFFF; }
        .nav-link { display: block; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; color: #8B949E; text-decoration: none; font-size: 15px; font-weight: 500; }
        .nav-link.active { background-color: #0066FF; color: #FFFFFF; }
        .nav-link:hover:not(.active) { background-color: #161B22; color: #FFFFFF; }
        .btn-logout { margin-top: auto; background-color: #DC2626; color: white; padding: 12px; border-radius: 8px; border: none; text-align: center; text-decoration: none; font-weight: bold; display: block; }

       
        .main-content { flex: 1; padding: 32px; overflow-y: auto; display: flex; flex-direction: column; gap: 24px; }
        .page-header { background-color: #161B22; padding: 12px 24px; border-radius: 12px; width: fit-content; font-size: 16px; font-weight: 700; color: #FFFFFF; border: 1px solid #21262D; letter-spacing: 1px; }

        
        .repair-cards-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
        .repair-card { background-color: #111822; border: 1px solid #1F2937; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.2s ease; position: relative; }
        .repair-card:hover { border-color: #3B82F6; transform: translateY(-2px); }
        .repair-card.active-repair { border-color: #3B82F6; background-color: #151F30; }
        
        .badge-needs-repair { background-color: rgba(239, 68, 68, 0.15); color: #F87171; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; display: inline-block; margin-bottom: 12px; text-transform: uppercase; }
        .car-title-sm { font-size: 16px; font-weight: 700; color: #FFFFFF; margin-bottom: 6px; }
        .car-desc-sm { font-size: 13px; color: #9CA3AF; line-height: 1.4; }

        .log-workspace { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        .form-card { background-color: #111822; border: 1px solid #1F2937; border-radius: 16px; padding: 28px; display: flex; flex-direction: column; gap: 20px; }
        .form-title { font-size: 18px; font-weight: 700; color: #FFFFFF; border-bottom: 1px solid #1F2937; padding-bottom: 12px; }
        
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-size: 13px; font-weight: 600; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group textarea, .input-group input { background-color: #0B121B; border: 1px solid #2A3441; border-radius: 8px; padding: 12px 16px; color: #FFFFFF; font-size: 14px; outline: none; }
        .input-group textarea:focus, .input-group input:focus { border-color: #3B82F6; }
        
        .cost-inputs-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .total-cost-box { background-color: #0B121B; border: 1px solid #2A3441; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 16px; color: #FACC15; }
        
        .form-actions { display: flex; gap: 16px; margin-top: 8px; }
        .btn-cancel { background-color: #1F2937; color: #FFFFFF; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; flex: 1; }
        .btn-cancel:hover { background-color: #374151; }
        .btn-save { background-color: #F59E0B; color: #000000; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; cursor: pointer; flex: 1; }
        .btn-save:hover { background-color: #D97706; }

    
        .info-panel { background-color: #111822; border: 1px solid #1F2937; border-radius: 16px; padding: 24px; display: flex; flex-direction: column; gap: 16px; height: fit-content; }
        .info-panel h4 { font-size: 15px; font-weight: 700; color: #FFFFFF; margin-bottom: 4px; }
        .info-step { display: flex; gap: 12px; align-items: flex-start; }
        .step-dot { width: 8px; height: 8px; background-color: #F59E0B; border-radius: 50%; margin-top: 6px; flex-shrink: 0; }
        .step-content div:first-child { font-size: 13px; font-weight: 700; color: #FFFFFF; }
        .step-content div:last-child { font-size: 12px; color: #9CA3AF; margin-top: 2px; }
    </style>
    <script>
        function calculateTotal() {
            let parts = parseFloat(document.getElementById('parts_cost').value) || 0;
            let service = parseFloat(document.getElementById('service_cost').value) || 0;
            let total = parts + service;
            document.getElementById('total_display').innerText = '$' + total.toFixed(2);
        }
    </script>
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
        <div class="page-header">REPAIR LOG</div>

       
        <div class="repair-cards-row">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php $isActive = ($selected_car && $selected_car['Vehicle_Id'] == $row['Vehicle_Id']); ?>
                    <div class="repair-card <?php echo $isActive ? 'active-repair' : ''; ?>" onclick="window.location.href='repair_logs.php?selected=<?php echo $row['Vehicle_Id']; ?>'">
                        <span class="badge-needs-repair">Needs Repair</span>
                        <div class="car-title-sm"><?php echo htmlspecialchars($row['Year'] . ' ' . $row['Make'] . ' ' . $row['Model']); ?></div>
                        <div class="car-desc-sm"><?php echo htmlspecialchars($row['Repair_Notes'] ?: 'Pending diagnostic inspection and mechanical repair log entry.'); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="color: #9CA3AF; font-size: 14px; padding: 12px;">No vehicles currently flagged for repair. All inventory is clear.</div>
            <?php endif; ?>
        </div>

        <?php if ($selected_car): ?>
        
        <div class="log-workspace">
            <form method="POST" action="repair_logs.php" class="form-card">
                <input type="hidden" name="action" value="save_repair">
                <input type="hidden" name="vehicle_id" value="<?php echo $selected_car['Vehicle_Id']; ?>">

                <div class="form-title">LOG REPAIR - <?php echo htmlspecialchars(strtoupper($selected_car['Make'] . ' ' . $selected_car['Model'])); ?></div>

                <div class="input-group">
                    <label>Service Description:</label>
                    <textarea name="service_description" rows="3" required placeholder="Enter service description, parts replaced, and notes..."><?php echo htmlspecialchars($selected_car['Repair_Notes'] ?? ''); ?></textarea>
                </div>

                <div class="cost-inputs-row">
                    <div class="input-group">
                        <label>Parts Cost ($):</label>
                        <input type="number" step="0.01" id="parts_cost" name="parts_cost" value="0.00" oninput="calculateTotal()" required>
                    </div>
                    <div class="input-group">
                        <label>Service Cost ($):</label>
                        <input type="number" step="0.01" id="service_cost" name="service_cost" value="0.00" oninput="calculateTotal()" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Total Repair Cost</label>
                    <div class="total-cost-box" id="total_display">$0.00</div>
                </div>

                <div class="form-actions">
                    <a href="repair_logs.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-save">SAVE REPAIR LOG</button>
                </div>
            </form>

            <!-- Explanation Panel -->
            <div class="info-panel">
                <h4>What happens on save</h4>
                
                <div class="info-step">
                    <div class="step-dot"></div>
                    <div class="step-content">
                        <div>Ticket marked resolved</div>
                        <div>Vehicle status moves from "Needs Repair" to "Ready for Inventory" and updates Customer Portal request status.</div>
                    </div>
                </div>

                <div class="info-step">
                    <div class="step-dot"></div>
                    <div class="step-content">
                        <div>Cost basis recorded</div>
                        <div>Parts and labor costs are added securely against this VIN in the database.</div>
                    </div>
                </div>

                <div class="info-step">
                    <div class="step-dot"></div>
                    <div class="step-content">
                        <div>Profit Analytics refreshed</div>
                        <div>Shop Owner dashboard reflects updated vehicle margin instantly.</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

</body>
</html>