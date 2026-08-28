<?php
session_start();

if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Customer") {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

if(isset($_GET['id'])) {
    $trade_id = $_GET['id'];
    $email = $_SESSION["email"];
    
    
    $stmt = $conn->prepare("UPDATE TRADE_IN SET Status = 'Cancelled' WHERE Trade_Id = ? AND Customer_Email = ?");
    $stmt->bind_param("is", $trade_id, $email);
    
    if($stmt->execute()) {
        echo "<script>
            alert('Trade-in request cancelled successfully.');
            window.location.href = 'trade_in.php';
        </script>";
    } else {
        echo "<script>
            alert('Error cancelling request. Please try again.');
            window.location.href = 'trade_in.php';
        </script>";
    }
    $stmt->close();
} else {
    header("Location: trade_in.php");
    exit();
}
?>