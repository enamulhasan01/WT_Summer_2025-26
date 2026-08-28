<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION["email"];
    $vehicle_id = $_POST['vehicle_id'];
    $final_price = $_POST['final_price'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    $status = 'Approved'; 

    
    $saleStmt = $conn->prepare("INSERT INTO SALE (Customer_Email, Vehicle_Id, Sale_Price, Phone, Address, Payment_Method, Status, Order_Date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $saleStmt->bind_param("sidisss", $email, $vehicle_id, $final_price, $phone, $address, $payment_method, $status);
    
    if($saleStmt->execute()) {
        $saleStmt->close();

        
        $updateCar = $conn->prepare("UPDATE VEHICLE SET Availability = 'Not Available' WHERE Vehicle_Id = ?");
        $updateCar->bind_param("i", $vehicle_id);
        $updateCar->execute();
        $updateCar->close();

        echo "<script>
            alert('Order placed successfully! You can view your purchase in the Order Status tab.');
            window.location.href = 'order_status.php';
        </script>";
    } else {
        echo "<script>
            alert('Error processing your purchase. Please try again.');
            window.location.href = 'customer_dashboard.php';
        </script>";
    }
} else {
    header("Location: customer_dashboard.php");
    exit();
}
?>