<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

if(isset($_GET['req_id'])) {
    
    $req_id = $_GET['req_id'];
    $email = $_SESSION["email"];
    
    
    $checkStmt = $conn->prepare("SELECT * FROM CAR_REQUEST WHERE Request_Id = ? AND Customer_Email = ? AND Status = 'Pending'");
    $checkStmt->bind_param("is", $req_id, $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if($result->num_rows > 0) {
        
        
        $cancelStmt = $conn->prepare("UPDATE CAR_REQUEST SET Status = 'Cancelled' WHERE Request_Id = ?");
        $cancelStmt->bind_param("i", $req_id);
        $cancelStmt->execute();
        $cancelStmt->close();
        
        echo "<script>
            alert('Your custom car request has been cancelled.');
            window.location.href = 'order_status.php';
        </script>";
    } else {
        echo "<script>
            alert('Invalid action or request cannot be cancelled.');
            window.location.href = 'order_status.php';
        </script>";
    }
    
    $checkStmt->close();
} else {
    header("Location: order_status.php");
    exit();
}
?>