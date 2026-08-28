<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';


if(isset($_GET['sale_id']) && isset($_GET['vehicle_id'])) {
    
    $sale_id = $_GET['sale_id'];
    $vehicle_id = $_GET['vehicle_id'];
    $email = $_SESSION["email"];
    
    
    $checkStmt = $conn->prepare("SELECT * FROM SALE WHERE Sale_Id = ? AND Customer_Email = ? AND Status = 'Pending'");
    $checkStmt->bind_param("is", $sale_id, $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if($result->num_rows > 0) {
        
        
        $cancelStmt = $conn->prepare("UPDATE SALE SET Status = 'Cancelled' WHERE Sale_Id = ?");
        $cancelStmt->bind_param("i", $sale_id);
        $cancelStmt->execute();
        $cancelStmt->close();
        
        
        $updateVehStmt = $conn->prepare("UPDATE VEHICLE SET Availability = 'Available' WHERE Vehicle_Id = ?");
        $updateVehStmt->bind_param("i", $vehicle_id);
        $updateVehStmt->execute();
        $updateVehStmt->close();
        
        echo "<script>
            alert('Your request has been successfully cancelled. The vehicle is available again.');
            window.location.href = 'order_status.php';
        </script>";
    } else {
        echo "<script>
            alert('Invalid request or order cannot be cancelled.');
            window.location.href = 'order_status.php';
        </script>";
    }
    
    $checkStmt->close();
} else {
    header("Location: order_status.php");
    exit();
}
?>