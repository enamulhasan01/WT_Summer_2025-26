<?php
session_start();
if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Owner") {
    header("Location: ../login.php");
    exit();
}
include '../db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supplier Performance - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; position: fixed; }
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; font-size: 18px; text-transform: uppercase;}
        .sidebar a { display: block; color: white; padding: 12px 15px; text-decoration: none; margin-bottom: 10px; border-radius: 20px; background-color: #555555; text-align: center; font-weight: bold; font-size: 13px;}
        .sidebar a.active, .sidebar a:hover { background-color: #6b6bff; }
        .sidebar a.logout { background-color: #cc0000; position: absolute; bottom: 20px; width: calc(100% - 40px); }

        .main-content { width: 80%; float: right; padding: 40px; height: 100vh; overflow-y: auto; }
        h1 { margin-top: 0; font-size: 24px; font-weight: normal; text-transform: uppercase; margin-bottom: 30px;}

        .supplier-card { 
            background-color: #dbe4f0; 
            color: black; 
            padding: 25px 40px; 
            border-radius: 20px; 
            margin-bottom: 25px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        
        .supplier-info h3 { margin: 0 0 15px 0; font-size: 18px; color: #111;}
        .supplier-info p { margin: 0; font-size: 14px; font-weight: bold; color: #333;}
        .supplier-info span { font-size: 16px; font-weight: normal; display: block; margin-top: 5px; color: #000;}
        
        .approval-box { text-align: center; margin-right: 50px;}
        .approval-box p { margin: 0 0 8px 0; font-size: 14px; font-weight: bold; color: #333;}
        .approval-box .rate { font-size: 22px; font-weight: bold; }
        
        /* Dynamic colors for approval rates */
        .rate-high { color: #28a745; } /* Green */
        .rate-mid { color: #f39c12; }  /* Orange */
        .rate-low { color: #dc3545; }  /* Red */
        .rate-none { color: #555555; } /* Grey for 0 activity */
        
        .no-data { text-align: center; color: #a0aec0; margin-top: 50px; font-size: 18px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard Overview</h2>
        <a href="owner_dashboard.php">DASHBOARD</a>
        <a href="pending_list.php">PENDING LIST</a>
        <a href="manage_inventory.php">MANAGE INVENTORY</a>
        <a href="supplier_performance.php" class="active">SUPPLIER PERFORMANCE</a>
        <a href="active_requests.php">ACTIVE CUSTOMER WISHLIST</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Supplier Performance</h1>

        <?php
        
        $sql = "SELECT 
                    u.email AS Supplier_Email, 
                    COUNT(c.Request_Id) AS Total_Handled,
                    SUM(CASE WHEN c.Status = 'Approved' THEN 1 ELSE 0 END) AS Total_Approved
                FROM users u
                LEFT JOIN car_request c ON u.email = c.Supplier_Email
                WHERE u.role = 'Supplier'
                GROUP BY u.email 
                ORDER BY Total_Handled DESC, u.email ASC";
                
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $supplierEmail = htmlspecialchars($row['Supplier_Email']);
                $totalHandled = $row['Total_Handled'] ? $row['Total_Handled'] : 0;
                $totalApproved = $row['Total_Approved'] ? $row['Total_Approved'] : 0;
                
                
                $approvalRate = ($totalHandled > 0) ? round(($totalApproved / $totalHandled) * 100) : 0;
                
                
                if ($totalHandled == 0) {
                    $rateClass = "rate-none"; // No activity yet
                } elseif ($approvalRate >= 80) {
                    $rateClass = "rate-high";
                } elseif ($approvalRate >= 50) {
                    $rateClass = "rate-mid";
                } else {
                    $rateClass = "rate-low";
                }

                echo '
                <div class="supplier-card">
                    <div class="supplier-info">
                        <h3>' . $supplierEmail . '</h3>
                        <p>Total Demands Handled: <span>' . $totalHandled . '</span></p>
                        <p style="margin-top:10px; font-weight:normal; font-size:13px; color:#555;">(Approved: ' . $totalApproved . ')</p>
                    </div>
                    <div class="approval-box">
                        <p>Approval Rate</p>
                        <div class="rate ' . $rateClass . '">' . $approvalRate . '%</div>
                    </div>
                </div>';
            }
        } else {
            echo '<div class="no-data">No suppliers found in the database.</div>';
        }
        ?>

    </div>
</body>
</html>