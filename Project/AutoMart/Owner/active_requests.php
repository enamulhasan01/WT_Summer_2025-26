<?php
session_start();
if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Owner") {
    header("Location: ../login.php");
    exit();
}
include '../db.php';


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $req_id = $_POST['req_id'];
    
    if ($_POST['action'] == 'accept') {
        $updateStmt = $conn->prepare("UPDATE CAR_REQUEST SET Status = 'Approved' WHERE Request_Id = ?");
        if($updateStmt) {
            $updateStmt->bind_param("i", $req_id);
            $updateStmt->execute();
            $updateStmt->close();
            echo "<script>alert('Customer Request Accepted!'); window.location.href='active_requests.php';</script>";
        }
    } elseif ($_POST['action'] == 'reject') {
        $rejectStmt = $conn->prepare("UPDATE CAR_REQUEST SET Status = 'Rejected' WHERE Request_Id = ?");
        if($rejectStmt) {
            $rejectStmt->bind_param("i", $req_id);
            $rejectStmt->execute();
            $rejectStmt->close();
            echo "<script>alert('Customer Request Rejected.'); window.location.href='active_requests.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Active Requests - AutoMart</title>
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

        .car-card { background-color: #cccccc; color: black; padding: 20px; border-radius: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;}
        .car-info h3 { margin: 0 0 5px 0; font-size: 18px; }
        .car-info p { margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: #333;}
        
        .action-buttons { display: flex; gap: 10px; margin-top: 10px;}
        .btn { padding: 8px 25px; border-radius: 20px; border: none; font-weight: bold; cursor: pointer; color: white;}
        .btn-reject { background-color: #f28b82; }
        .btn-approve { background-color: #81c784; }
        
        .no-data { text-align: center; color: #a0aec0; margin-top: 50px; font-size: 18px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard Overview</h2>
        <a href="owner_dashboard.php">DASHBOARD</a>
        <a href="pending_list.php">PENDING LIST</a>
        <a href="manage_inventory.php">MANAGE INVENTORY</a>
        <a href="supplier_performance.php">SUPPLIER PERFORMANCE</a>
        <a href="active_requests.php" class="active">ACTIVE CUSTOMER WISHLIST</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Active Customer Requests</h1>

        <?php
        // Updated Query: Covers 'Pending', NULL, and empty status to ensure it shows up
        $sql = "SELECT * FROM CAR_REQUEST WHERE Status = 'Pending' OR Status IS NULL OR Status = '' ORDER BY Request_Id DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Check if date exists to avoid errors, otherwise display 'Recently'
                $reqDate = !empty($row["Request_Date"]) ? date("F j, Y", strtotime($row["Request_Date"])) : "Recently";
                
                echo '
                <div class="car-card">
                    <div class="car-info">
                        <h3>' . htmlspecialchars($row["Year_Range"]) . ' ' . htmlspecialchars($row["Car_Make"]) . ' ' . htmlspecialchars($row["Car_Model"]) . '</h3>
                        <p>Customer: ' . htmlspecialchars($row["Customer_Email"]) . '</p>
                        <p>Budget: $' . number_format($row["Max_Budget"]) . ' | Requested: ' . $reqDate . '</p>
                        <p>Notes: ' . htmlspecialchars($row["Additional_Notes"]) . '</p>
                        
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="req_id" value="' . $row["Request_Id"] . '">
                            <div class="action-buttons">
                                <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                                <button type="submit" name="action" value="accept" class="btn btn-approve">Accept</button>
                            </div>
                        </form>
                    </div>
                </div>';
            } 
        } else {
            echo '<div class="no-data">No active customer requests at the moment.</div>';
        }
        ?>
    </div>
</body>
</html>