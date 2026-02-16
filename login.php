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
        // Get form data - make sure password is captured correctly
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Debug - REMOVE AFTER TESTING
        error_log("Login attempt - Email: " . $email);
        error_log("Login attempt - Password length: " . strlen($password));
        
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
                // Check if user exists
                $sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // ===== DEBUG CODE - REMOVE AFTER TESTING =====
                    echo "<!-- DEBUG START -->\n";
                    echo "<!-- Email from DB: " . $user['email'] . " -->\n";
                    echo "<!-- Email from form: " . $email . " -->\n";
                    echo "<!-- Stored hash: " . $user['password'] . " -->\n";
                    echo "<!-- Hash length: " . strlen($user['password']) . " -->\n";
                    echo "<!-- Password from form length: " . strlen($password) . " -->\n";
                    echo "<!-- First char of password: " . ($password ? substr($password, 0, 1) : 'EMPTY') . " -->\n";
                    $verify_result = password_verify($password, $user['password']);
                    echo "<!-- password_verify result: " . ($verify_result ? 'true' : 'false') . " -->\n";
                    echo "<!-- DEBUG END -->\n";
                    // ===== END DEBUG =====
                    
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Regenerate session ID to prevent fixation
                        session_regenerate_id(true);
                        
                        // Login successful
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['full_name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['logged_in'] = true;
                        
                        // Set login success message
                        $_SESSION['login_success'] = "Welcome back, " . $user['full_name'] . "!";
                        
                        // Redirect based on role
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
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - FitLife Gym</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Additional styles */
        .error {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        
        .form-group.error input {
            border-color: #e74c3c;
            background-color: #fff8f8;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .auth-container {
            min-height: 70vh;
            display: flex;
            align-items: center;
        }
        
        .auth-card {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">FitLife Gym</div>
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
                    <div class="error" style="margin-bottom: 15px; padding: 10px; background: #f8d7da; border-radius: 5px;">
                        <?php echo htmlspecialchars($errors['csrf'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors['database'])): ?>
                    <div class="error" style="margin-bottom: 15px; padding: 10px; background: #f8d7da; border-radius: 5px;">
                        <?php echo htmlspecialchars($errors['database'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <form class="auth-form" method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group <?php echo isset($errors['email']) ? 'error' : ''; ?>">
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

                    <div class="form-group <?php echo isset($errors['password']) ? 'error' : ''; ?>">
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
        <p>FitLife Gym &copy; 2025 | <a href="public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>