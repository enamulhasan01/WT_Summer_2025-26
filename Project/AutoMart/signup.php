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

$VALID_APPROVAL_CODE = "AUTOMART2026"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST["full_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];
    $approvalCode = $_POST["approval_code"];
    $securityQuestion = $_POST["security_question"];

    if ($approvalCode !== $VALID_APPROVAL_CODE) {
        echo "<script>
            alert('Error: Invalid account approval code!');
            window.location.href = 'signup.php';
        </script>";
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo "<script>
                alert('Error: This email is already registered!');
                window.location.href = 'signup.php';
            </script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, security_question) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fullName, $email, $password, $role, $securityQuestion);
            
            if ($stmt->execute()) {
                echo "<script>
                    alert('Signup successful!');
                    window.location.href = 'login.php';
                </script>";
                exit();
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - AutoMart</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body, html {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-image: url('assets/car-bg.jpg'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        
        .signup-box {
            width: 100%;
            max-width: 480px; 
            margin: 30px auto; 
            background-color: rgba(0, 0, 0, 0.75); 
            padding: 20px 30px; 
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6); 
            color: white;
        }

        h2 {
            text-align: center;
            font-size: 24px; 
            margin-top: 0;
            margin-bottom: 15px;
            color: #ffffff;
        }

        
        form label {
            display: block;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
            color: #ffffff; 
        }

        form input[type="text"], 
        form input[type="email"], 
        form input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 12px; 
            border: 2px solid transparent;
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.95);
            font-size: 13px;
            color: #333;
            transition: all 0.3s ease;
        }

        form input:focus {
            border-color: #0a66c2; 
            background-color: #ffffff;
            outline: none;
        }

        
        .radio-group {
            text-align: center;
            margin-bottom: 12px;
            background-color: rgba(0, 0, 0, 0.6); 
            padding: 8px;
            border-radius: 6px;
        }

        .radio-item {
            display: inline-block;
            margin: 0 8px; 
        }

        .radio-item label {
            display: inline;
            font-weight: normal;
            font-size: 12px;
            color: #ffffff;
            cursor: pointer;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #0a66c2;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 5px;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #004182;
        }

        .login-link {
            text-align: center;
            margin-top: 12px;
            margin-bottom: 0;
            font-size: 12px;
        }

        .login-link a {
            color: #4da6ff;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    
    <div class="signup-box">
        <h2>Create Account</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            
            <label>Join AutoMart as:</label>
            <div class="radio-group">
                <div class="radio-item">
                    <input type="radio" name="role" value="Owner" id="role_owner" required> 
                    <label for="role_owner">Owner</label>
                </div>
                <div class="radio-item">
                    <input type="radio" name="role" value="Customer" id="role_customer"> 
                    <label for="role_customer">Customer</label>
                </div>
                <div class="radio-item">
                    <input type="radio" name="role" value="Supplier" id="role_supplier"> 
                    <label for="role_supplier">Supplier</label>
                </div>
                <div class="radio-item">
                    <input type="radio" name="role" value="Evaluator" id="role_evaluator"> 
                    <label for="role_evaluator">Evaluator</label>
                </div>
            </div>

            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="hasan ahmad" required>

            <label>Email address</label>
            <input type="email" name="email" placeholder="hasan@example.com" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Create a strong password" required>

            <label>Security Question: Who is your favorite faculty?</label>
            <input type="text" name="security_question" placeholder="e.g. Jose Mourinho" required>


            <label>Account Approval Code</label>
            <input type="text" name="approval_code" placeholder="Enter registration code (e.g., AUTOMART2026)" required>


            <input type="submit" value="Create Account">
        </form>
        
        
        <p class="login-link">Already have an account? <a href="login.php">Log in here</a></p>
    </div>

</body>
</html>