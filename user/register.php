<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'dashboard.php'));
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Rate limiting for registration
$ip = $_SERVER['REMOTE_ADDR'];
$reg_attempts_key = 'register_attempts_' . $ip;

if (!isset($_SESSION[$reg_attempts_key])) {
    $_SESSION[$reg_attempts_key] = [
        'count' => 0,
        'first_attempt' => time(),
        'locked_until' => null
    ];
}

// Check if currently locked out
if ($_SESSION[$reg_attempts_key]['locked_until'] !== null) {
    if (time() < $_SESSION[$reg_attempts_key]['locked_until']) {
        $remaining = ceil(($_SESSION[$reg_attempts_key]['locked_until'] - time()) / 60);
        $errors['rate_limit'] = "Too many registration attempts. Please try again in $remaining minutes.";
    } else {
        // Lock expired, reset
        $_SESSION[$reg_attempts_key] = [
            'count' => 0,
            'first_attempt' => time(),
            'locked_until' => null
        ];
    }
}

// Initialize variables
$full_name = $email = $phone = '';
$errors = [];

// Only validate if form is submitted and not rate limited
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($errors['rate_limit'])) {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Invalid form submission';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        // ========== VALIDATION CODE ==========
        
        // Validate Full Name
        if (isset($_POST['full_name']) && !empty(trim($_POST['full_name']))) {
            $full_name = trim($_POST['full_name']);
            if (!preg_match("/^[a-zA-Z\s.'-]+$/", $full_name)) {
                $errors['full_name'] = 'Full name can only contain letters, spaces';
            } elseif (strlen($full_name) < 2) {
                $errors['full_name'] = 'Full name must be at least 2 characters long';
            } elseif (strlen($full_name) > 50) {
                $errors['full_name'] = 'Full name must not exceed 50 characters';
            }
        } else {
            $errors['full_name'] = 'Please enter your full name';
        }

        // Validate Email
        if (isset($_POST['email']) && !empty(trim($_POST['email']))) {
            $email = trim($_POST['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address';
            } elseif (strlen($email) > 100) {
                $errors['email'] = 'Email address is too long';
            }
        } else {
            $errors['email'] = 'Please enter your email';
        }

        // Validate Phone
        if (isset($_POST['phone']) && !empty(trim($_POST['phone']))) {
            $phone = trim($_POST['phone']);
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            if (!preg_match('/^(98|97)\d{8}$/', $phone)) {
                $errors['phone'] = 'Phone number must be 10 digits starting with 98 or 97';
            }
        } else {
            $errors['phone'] = 'Please enter your phone number';
        }

        // Validate Password - CRITICAL FIX
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        if (empty($password)) {
            $errors['password'] = 'Please enter your password';
        } else {
            $password_errors = [];
            if (strlen($password) < 8) {
                $password_errors[] = 'at least 8 characters';
            }
            if (strlen($password) > 32) {
                $password_errors[] = 'max 32 characters';
            }
            if (!preg_match('/[A-Z]/', $password)) {
                $password_errors[] = 'one uppercase letter';
            }
            if (!preg_match('/[a-z]/', $password)) {
                $password_errors[] = 'one lowercase letter';
            }
            if (!preg_match('/[0-9]/', $password)) {
                $password_errors[] = 'one number';
            }
            
            if (!empty($password_errors)) {
                $errors['password'] = 'Password must contain: ' . implode(', ', $password_errors);
            }
        }

        // Validate Confirm Password
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        if (empty($confirm_password)) {
            $errors['confirm_password'] = 'Please confirm your password';
        } elseif ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        // ========== DATABASE CODE ==========
        if (empty($errors)) {
            require_once '../includes/database.php';
            
            try {
                // Check if email already exists
                $check_sql = "SELECT id FROM users WHERE email = ?";
                $check_stmt = $pdo->prepare($check_sql);
                $check_stmt->execute([$email]);
                
                if ($check_stmt->rowCount() > 0) {
                    $errors['email'] = 'Email already registered';
                    // Increment failed attempts
                    $attempts = &$_SESSION[$reg_attempts_key];
                    $attempts['count']++;
                    
                    if ($attempts['count'] >= 5) {
                        $attempts['locked_until'] = time() + 900;
                        $errors['rate_limit'] = "Too many registration attempts. Please try again in 15 minutes.";
                    }
                } else {
                    // Sanitize inputs
                    $full_name = htmlspecialchars(strip_tags($full_name), ENT_QUOTES, 'UTF-8');
                    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                    $phone = htmlspecialchars(strip_tags($phone), ENT_QUOTES, 'UTF-8');
                    
                    // CRITICAL: Hash the password correctly
                    // First ensure password is not empty
                    if (empty($password)) {
                        $errors['password'] = 'Password cannot be empty';
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Verify hash was created successfully
                        if ($hashed_password === false) {
                            $errors['database'] = 'Password hashing failed. Please try again.';
                            error_log("Password hashing failed for email: " . $email);
                        } else {
                        // Insert new user
                        $sql = "INSERT INTO users (full_name, email, phone, password, role, created_at) 
                                VALUES (?, ?, ?, ?, 'user', NOW())";
                        
                        $stmt = $pdo->prepare($sql);
                        $result = $stmt->execute([$full_name, $email, $phone, $hashed_password]);
                        
                        if ($result) {
                            // Clear registration attempts on success
                            unset($_SESSION[$reg_attempts_key]);
                            
                            $_SESSION['success'] = 'Registration successful! Please login.';
                            header('Location: ../login.php');
                            exit();
                        } else {
                            $errors['database'] = 'Registration failed. Please try again.';
                            error_log("User insert failed for email: " . $email);
                        }
                        }
                    }
                }
                
            } catch (PDOException $e) {
                $errors['database'] = 'Registration failed. Please try again.';
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav>
        <div class="logo">FitLife Gym</div>
        <div class="nav-links">
            <a href="../index.php">Home</a>
            <a href="../public/packages.php">Packages</a>
            <a href="../public/about.php">About Us</a>
            <a href="../login.php">Login</a>
            <a href="register.php" class="active">Register</a>
        </div>
    </nav>

    <div class="register-container">
        <h1>Create Account</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors['csrf'])): ?>
            <div class="error"><?php echo htmlspecialchars($errors['csrf'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors['rate_limit'])): ?>
            <div class="error"><?php echo htmlspecialchars($errors['rate_limit'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors['database'])): ?>
            <div class="error"><?php echo htmlspecialchars($errors['database'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registrationForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name"
                       value="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Enter your full name"
                       pattern="[a-zA-Z\s.'-]+"
                       title="Only letters, spaces, dots and apostrophes"
                       <?php echo isset($errors['rate_limit']) ? 'disabled' : ''; ?>>
                <?php if (isset($errors['full_name'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" 
                       value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Enter your email"
                       <?php echo isset($errors['rate_limit']) ? 'disabled' : ''; ?>>
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" name="phone" id="phone" 
                       value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="98XXXXXXXX or 97XXXXXXXX"
                       pattern="(98|97)[0-9]{8}"
                       title="10-digit number starting with 98 or 97"
                       <?php echo isset($errors['rate_limit']) ? 'disabled' : ''; ?>>
                <small style="color: #666;">Format: 98XXXXXXXX or 97XXXXXXXX (10 digits)</small>
                <?php if (isset($errors['phone'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" 
                       placeholder="Minimum 8 characters with uppercase, lowercase, and number"
                       minlength="8"
                       autocomplete="new-password"
                       <?php echo isset($errors['rate_limit']) ? 'disabled' : ''; ?>>
                <small style="color: #666;">Must contain: uppercase letter, lowercase letter, number</small>
                <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                       placeholder="Confirm your password"
                       autocomplete="new-password"
                       <?php echo isset($errors['rate_limit']) ? 'disabled' : ''; ?>>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn-register" <?php echo isset($errors['rate_limit']) ? 'disabled' : ''; ?>>
                Create Account
            </button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="../login.php">Login here</a>
        </div>
    </div>
</body>
</html>