<!DOCTYPE html>
<html>
<head>
    <title>Login - AutoMart</title>
    <style>
        .split-left {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            padding: 50px;
        }
        .split-right {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        img {
            width: 100%;
            height: auto;
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