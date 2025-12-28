<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit();
}

$errors = [];
$email = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email)) {
        $errors['email'] = 'Please enter your email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Please enter your password';
    }
    
    // If no validation errors, check database
    if (empty($errors)) {
        require_once 'includes/database.php';
        
        try {
            // Check if user exists - GET THE ROLE TOO
            $sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Login successful - SET ROLE IN SESSION
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role']; // 'user' or 'admin'
                    $_SESSION['logged_in'] = true;
                    
                    // REDIRECT BASED ON ROLE
                    if ($user['role'] == 'admin') {
                        header('Location: admin/dashboard.php');
                    } else {
                        header('Location: user/dashboard.php');
                    }
                    exit();
                } else {
                    $errors['password'] = 'Incorrect password';
                }
            } else {
                $errors['email'] = 'No account found with this email';
            }
            
        } catch (PDOException $e) {
            $errors['database'] = 'Login failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - FitLife Gym</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
    <div class="logo">FitLife Gym</div>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="public/packages.php">Packages</a>  <!-- CORRECT -->
        <a href="public/about.php">About Us</a>     <!-- CORRECT -->
        <a href="login.php" class="active">Login</a>  <!-- REMOVE ../ -->
        <a href="user/register.php">Register</a>    <!-- CORRECT -->
    </div>
</nav>

    <div class="login-container">
        <h1>Login to FitLife Gym</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors['database'])): ?>
            <div class="error"><?php echo $errors['database']; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" 
                       value="<?php echo htmlspecialchars($email); ?>"
                       placeholder="Enter your email">
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" 
                       placeholder="Enter your password">
                <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        <div class="login-links">
            <p>New member? <a href="user/register.php">Create an account</a></p>
            <p><a href="index.php">← Back to Home</a></p>
            <p class="admin-note">Administrators: Use your admin email to access the admin panel</p>
        </div>
    </div>

    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>