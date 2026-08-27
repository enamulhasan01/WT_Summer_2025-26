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