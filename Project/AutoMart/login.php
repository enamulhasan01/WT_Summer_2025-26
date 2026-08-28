<?php
session_start();

if(isset($_SESSION["email"]) && isset($_SESSION["role"]))
{
    $folderName = $_SESSION["role"]; 
    $fileName = strtolower($_SESSION["role"]) . "_dashboard.php"; 
    header("Location: " . $folderName . "/" . $fileName);
    exit();
}

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
        $fileName = strtolower($row["role"]) . "_dashboard.php"; 

        echo "<script>
            alert('Login successful!');
            window.location.href = '" . $folderName . "/" . $fileName . "';
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: #ffffff;
        }

        
        .split-left {
            width: 50%;
            float: left; 
            height: 100vh;
            padding: 10% 8%; 
        }
        
        .split-right {
            width: 50%;
            float: right;
            height: 100vh;
        }
        
        img {
            width: 100%;
            height: 100%; 
            object-fit: cover;
        }

        
        h1 {
            color: #0a192f;
            font-size: 38px;
            margin-bottom: 10px;
        }
        
        p.subtitle {
            color: #666666;
            margin-bottom: 40px;
            font-size: 16px;
        }

        
        form label {
            display: block;
            font-weight: bold;
            color: #333333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        form input[type="text"], 
        form input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 25px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            background-color: #f8f9fa; 
            font-size: 16px;
            color: #333;
            transition: all 0.3s ease; 
        }

        
        form input[type="text"]:focus, 
        form input[type="password"]:focus {
            border-color: #0a66c2;
            background-color: #ffffff;
            outline: none;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background-color: #0a66c2; 
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }
        
        button:hover {
            background-color: #004182;
        }

        
        a {
            color: #0a66c2;
            text-decoration: none;
            font-weight: bold;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        .footer-text {
            color: #666666;
            font-size: 15px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="split-left">
        <h1>WELCOME BACK</h1>
        <p class="subtitle">Welcome back! Please enter your details.</p>
        
        <form action="" method="post">
            <label>Email</label>
            <input type="text" name="email" placeholder="Enter your email" required>
            
            <label>Password</label>
            <input type="password" name="password" placeholder="*********" required>
            
            <button type="submit">Sign in</button>
            
            <p class="footer-text"><a href="forgot_password.php">Forgot password?</a></p>
        </form>
        
        <p class="footer-text">Don't have an account? <a href="signup.php">Sign up for free!</a></p>
    </div>
    
    <div class="split-right">
        <img src="assets/sign_in_jeep.png" alt="Jeep on road">
    </div>
</body>
</html>