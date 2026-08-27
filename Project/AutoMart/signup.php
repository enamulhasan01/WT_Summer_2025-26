<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST["full_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullName, $email, $password, $role);
    
    if ($stmt->execute()) {
        
        echo "<script>
            alert('Signup successful!');
            window.location.href = 'login.php';
        </script>";
        exit();
    } else {
        
        echo "<script>
            alert('Error creating account. Please try again.');
        </script>";
    }
    $stmt->close();
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
    <!-- Updated the class attribute to match the CSS -->
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
            <input type="text" name="email" required><br><br>

            <label>Password</label><br>
            <input type="password" name="password" required><br><br>

            <input type="submit" value="Create Account">
        </form>
    </div>
</body>
</html>