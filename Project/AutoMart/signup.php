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
        body {
            background-image: url('assets/car-bg.jpg');
            background-size: cover;
            color: white;
            text-align: center;
        }
        
        
        .signup-box {
            width: 400px;
            margin: 50px auto; 
            background-color: rgba(0, 0, 0, 0.5); 
            padding: 30px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    
    <div class="signup-box">
        <h2>Create Account</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <label>Join as:</label>
            <input type="radio" name="role" value="Owner" required> Owner
            <input type="radio" name="role" value="Customer"> Customer
            <input type="radio" name="role" value="Supplier"> Supplier
            <input type="radio" name="role" value="Evaluator"> Evaluator
            <br><br>

            <label>Full Name</label><br>
            <input type="text" name="full_name" required><br><br>

            <label>Email address</label><br>
            <input type="email" name="email" required><br><br>

            <label>Password</label><br>
            <input type="password" name="password" required><br><br>

            <label>Security Question For Password Reset: <br> <br> Who is your favorite faculty?</label><br>
            <input type="text" name="security_question" placeholder="e.g. Jose Mourinho" required><br><br>

            <label>Account Approval Code</label><br>
            <input type="text" name="approval_code" placeholder="Enter registration code" required><br><br>

            <input type="submit" value="Create Account">
        </form>
    </div>
</body>
</html>