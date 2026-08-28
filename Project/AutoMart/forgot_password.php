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
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: #f4f7f6; 
            display: flex; 
            justify-content: center;
            align-items: center;
        }

        
        .reset-container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); 
            width: 100%;
            max-width: 450px;
        }

        h2 {
            color: #0a192f;
            margin-top: 0;
            font-size: 28px;
            text-align: center;
            margin-bottom: 30px;
        }

        
        form label {
            display: block;
            font-weight: bold;
            color: #333333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        form input[type="email"],
        form input[type="text"],
        form input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            background-color: #f8f9fa;
            font-size: 16px;
            color: #333;
            transition: all 0.3s ease;
        }

        
        form input:focus {
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
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }
        
        button:hover {
            background-color: #004182;
        }

        
        p {
            text-align: center;
            margin-top: 25px;
            margin-bottom: 0;
        }
        
        a {
            color: #0a66c2;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
        }
        
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="reset-container">
        <h2>Reset Password</h2>
        
        <form method="post">
            <label>Email address</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>What is your favorite faculty?</label>
            <input type="text" name="security_question" placeholder="Your answer" required>

            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Enter new password" required>

            <button type="submit">Reset Password</button>
        </form>
        
        <p><a href="login.php">&larr; Back to Login</a></p>
    </div>

</body>
</html>