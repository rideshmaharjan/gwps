<!DOCTYPE html>
<html>
<head>
    <title>Member Login - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav>
        <div class="logo">FitLife Gym</div>
        <div class="nav-links">
            <a href="../homepage.php">Home</a>
            <a href="../public/packages.php">Packages</a>
            <a href="../public/about.php">About Us</a>
            <a href="login.php" class="active">Login</a>
            <a href="register.php">Register</a>
        </div>
    </nav>

    <div class="login-container">
        <h1>Member Login</h1>
        
        <!-- You will add: action="login-process.php" method="POST" -->
        <form class="login-form">
            <input type="text" placeholder="Username or Email" required>
            <input type="password" placeholder="Password" required>
            <button type="submit" class="btn-primary">Login</button>
        </form>
        
        <p>New member? <a href="register.php">Create an account</a></p>
        <p><a href="../homepage.php">← Back to Home</a></p>
    </div>

    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>