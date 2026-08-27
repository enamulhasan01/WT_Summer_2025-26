<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $secAnswer = $_POST["security_question"];
    $newPassword = $_POST["new_password"];

    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND security_question = ?");
    $stmt->bind_param("ss", $email, $secAnswer);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $updateStmt->bind_param("ss", $newPassword, $email);
        
        if ($updateStmt->execute()) {
            echo "<script>
                alert('Password updated successfully!');
                window.location.href = 'login.php';
            </script>";
            exit();
        }
        $updateStmt->close();
    } else {
        echo "<script>
            alert('Invalid email or security answer!');
        </script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - AutoMart</title>
</head>
<body>
    <div style="width: 400px; margin: 50px auto; padding: 30px;">
        <h2>Reset Password</h2>
        <form method="post">
            <label>Email address</label><br>
            <input type="email" name="email" required><br><br>

            <label>What is your favorite faculty?</label><br>
            <input type="text" name="security_question" required><br><br>

            <label>New Password</label><br>
            <input type="password" name="new_password" required><br><br>

            <button type="submit">Reset Password</button>
        </form>
        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>