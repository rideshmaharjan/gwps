<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$email = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Invalid form submission';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        // Get form data
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Basic validation
        if (empty($email)) {
            $errors['email'] = 'Please enter your email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Please enter your password';
        }
        
        // Only proceed if no validation errors
        if (empty($errors)) {
            require_once 'includes/database.php';
            
            try {
                // Get user from database
                $sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
             
                if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
                
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_success'] = "Welcome back, " . $user['full_name'] . "!";
                    
                  
                    if ($user['role'] == 'admin') {
                        header('Location: admin/dashboard.php');
                        exit();
                    } else {
                        header('Location: user/dashboard.php');
                        exit();
                    }
                } else {
                   
                    $errors['login'] = 'Invalid email or password';
                }
                
            } catch (PDOException $e) {
                $errors['database'] = 'Login failed. Please try again.';
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - GWPS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <div class="logo">GWPS</div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="public/packages.php">Packages</a>
            <a href="public/about.php">About Us</a>
            <a href="login.php" class="active">Login</a>
        </div>
    </nav>
    
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Welcome Back</h2>
                <p>Login to your FitLife account</p>
            </div>

            <div class="auth-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($errors['csrf'])): ?>
                    <div class="error" style="margin-bottom: 15px; padding: 10px; background: #f8d7da; border-radius: 5px; color: #721c24;">
                        <?php echo htmlspecialchars($errors['csrf'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors['login'])): ?>
                    <div class="error" style="margin-bottom: 15px; padding: 10px; background: #f8d7da; border-radius: 5px; color: #721c24;">
                        <?php echo htmlspecialchars($errors['login'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors['database'])): ?>
                    <div class="error" style="margin-bottom: 15px; padding: 10px; background: #f8d7da; border-radius: 5px; color: #721c24;">
                        <?php echo htmlspecialchars($errors['database'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <form class="auth-form" method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" 
                               id="email"
                               name="email" 
                               value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" 
                               placeholder="Enter your email"
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="error"><?php echo htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" 
                               id="password"
                               name="password" 
                               placeholder="Enter your password"
                               required>
                        <?php if (isset($errors['password'])): ?>
                            <span class="error"><?php echo htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="auth-submit" style="width: 100%; padding: 14px; font-size: 1.1rem;">
                        Login
                    </button>
                </form>

                <div class="auth-footer" style="margin-top: 25px; text-align: center;">
                    <p>Don't have an account? <a href="user/register.php" style="color: #3498db; font-weight: 600;">Register</a></p>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>GWPS &copy; 2025 | <a href="public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>