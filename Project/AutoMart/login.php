<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    
    $stmt = $conn->prepare("SELECT id, role FROM users WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        
        $_SESSION["email"] = $email;
        $_SESSION["role"] = $row["role"];

        
        $folderName = $row["role"]; 

        echo "<script>
            alert('Login successful!');
            window.location.href = '" . $folderName . "/';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Wrong credentials!');
        </script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - AutoMart</title>
    <style>
    * {
        box-sizing: border-box;
    }
    body {
        margin: 0;
        padding: 0;
    }
    .split-left {
        width: 50%;
        display: inline-block;
        vertical-align: top;
        padding: 80px;
    }
    .split-right {
        width: 50%;
        display: inline-block;
        vertical-align: top;
        
        margin-left: -4px; 
    }
    img {
        width: 100%;
        height: 100vh; 
        object-fit: cover;
    }
</style>
</head>
<body>
    <div class="split-left">
        <h1>WELCOME BACK</h1>
        <p>Welcome back! Please enter your details.</p>
        <form action="">
            <label>Email</label><br>
            <input type="text" placeholder="Enter your email"><br><br>
            
            <label>Password</label><br>
            <input type="password" placeholder="*********"><br><br>
            
            <button type="button">Sign in</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign up for free!</a></p>
    </div>
    <div class="split-right">
        
        <img src="assets/sign_in_jeep.png" alt="Jeep on road">
    </div>
</body>
</html>