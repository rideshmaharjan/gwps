<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav>
        <div class="logo">FitLife Gym</div>
        <div class="nav-links">
            <a href="../index.php">← Back to Site</a>
        </div>
    </nav>

    <div class="admin-login-container">
        <h1>Admin Panel Login</h1>
        <p>Restricted access - authorized personnel only</p>
        
        <!-- You will add: action="login-process.php" method="POST" -->
        <form class="admin-login-form">
            <input type="text" placeholder="Admin Username" required>
            <input type="password" placeholder="Admin Password" required>
            <button type="submit" class="btn-primary">Login as Admin</button>
        </form>
    </div>
</body>
</html>