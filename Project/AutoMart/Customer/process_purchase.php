<?php
session_start();


if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION["email"];
    $vehicle_id = $_POST["vehicle_id"];
    $price = $_POST["price"];
    $status = "Pending";

    
    $stmt = $conn->prepare("INSERT INTO SALE (Customer_Email, Vehicle_Id, Sale_Price, Status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $email, $vehicle_id, $price, $status);
    
    if ($stmt->execute()) {
        
        
        $updateStmt = $conn->prepare("UPDATE VEHICLE SET Availability = 'Pending' WHERE Vehicle_Id = ?");
        $updateStmt->bind_param("i", $vehicle_id);
        $updateStmt->execute();
        $updateStmt->close();

        
        echo "<script>
            alert('Purchase request submitted successfully! Awaiting Owner approval.');
            window.location.href = 'order_status.php';
        </script>";
    } else {
        echo "<script>
            alert('Error processing purchase. Please try again.');
            window.location.href = 'customer_dashboard.php';
        </script>";
    }
    
    $stmt->close();
} else {
    
    header("Location: customer_dashboard.php");
    exit();
}
?>