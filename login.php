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

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
$attempts_key = 'login_attempts_' . $ip;

if (!isset($_SESSION[$attempts_key])) {
    $_SESSION[$attempts_key] = [
        'count' => 0,
        'first_attempt' => time(),
        'locked_until' => null
    ];
}

// Check if currently locked out
if ($_SESSION[$attempts_key]['locked_until'] !== null) {
    if (time() < $_SESSION[$attempts_key]['locked_until']) {
        $remaining = ceil(($_SESSION[$attempts_key]['locked_until'] - time()) / 60);
        $errors['rate_limit'] = "Too many failed attempts. Please try again in $remaining minutes.";
    } else {
        // Lock expired, reset
        $_SESSION[$attempts_key] = [
            'count' => 0,
            'first_attempt' => time(),
            'locked_until' => null
        ];
    }
}

$errors = [];
$email = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($errors['rate_limit'])) {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Invalid form submission';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
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
                // Check if user exists
                $sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Clear failed attempts on success
                        unset($_SESSION[$attempts_key]);
                        
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
                        // Increment failed attempts
                        $attempts = &$_SESSION[$attempts_key];
                        $attempts['count']++;
                        
                        // Lock out after 5 attempts
                        if ($attempts['count'] >= 5) {
                            $attempts['locked_until'] = time() + 900; // 15 minutes
                            $errors['rate_limit'] = "Too many failed attempts. Please try again in 15 minutes.";
                        }
                    }
                } else {
                    $errors['email'] = 'No account found with this email';
                    // Increment failed attempts
                    $attempts = &$_SESSION[$attempts_key];
                    $attempts['count']++;
                    
                    // Lock out after 5 attempts
                    if ($attempts['count'] >= 5) {
                        $attempts['locked_until'] = time() + 900;
                        $errors['rate_limit'] = "Too many failed attempts. Please try again in 15 minutes.";
                    }
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
                <?php if (isset($errors['csrf'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['csrf'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                
                <?php if (isset($errors['rate_limit'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['rate_limit'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                
                <?php if (isset($errors['database'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['database'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                
                <form class="auth-form" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="error"><?php echo htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                        <?php if (isset($errors['password'])): ?>
                            <span class="error"><?php echo htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="auth-submit" <?php echo isset($errors['rate_limit']) ? 'disabled' : ''; ?>>
                        Login
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="user/register.php">Register</a></p>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>