<?php
session_start();

// Initialize variables
$full_name = $email = $phone = $password = $confirm_password = '';
$errors = [];

// Only validate if form is submitted
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // ========== VALIDATION CODE ==========
    
    // Validate Full Name - Only letters and spaces allowed
    if (isset($_POST['full_name']) && !empty(trim($_POST['full_name']))) {
        $full_name = trim($_POST['full_name']);
        // Check if name contains only letters, spaces, dots, and apostrophes
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

    // Validate Phone - Must start with 98 or 97 and be 10 digits
    if (isset($_POST['phone']) && !empty(trim($_POST['phone']))) {
        $phone = trim($_POST['phone']);
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if phone number is valid (10 digits starting with 98 or 97)
        if (!preg_match('/^(98|97)\d{8}$/', $phone)) {
            $errors['phone'] = 'Phone number must be 10 digits starting with 98 or 97';
        }
    } else {
        $errors['phone'] = 'Please enter your phone number';
    }

    // Validate Password - Minimum 8 characters
    if (isset($_POST['password']) && !empty(trim($_POST['password']))) {
        $password = $_POST['password'];
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif (strlen($password) > 32) {
            $errors['password'] = 'Password must not exceed 32 characters';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors['password'] = 'Password must contain at least one lowercase letter';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password must contain at least one number';
        }
    } else {
        $errors['password'] = 'Please enter your password';
    }

    // Validate Confirm Password
    if (isset($_POST['confirm_password']) && !empty(trim($_POST['confirm_password']))) {
        $confirm_password = $_POST['confirm_password'];
        if (isset($password) && $password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
    } else {
        $errors['confirm_password'] = 'Please confirm your password';
    }

    // ========== DATABASE CODE ==========
    // Only save to database if NO errors
   if (empty($errors)) {
        require_once '../includes/database.php';
        
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$email]);
        
        if ($check_stmt->rowCount() > 0) {
            $errors['email'] = 'Email already registered';
        } else {
            // ========== ADD SANITIZATION HERE ==========
            $full_name = htmlspecialchars(strip_tags($full_name));
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $phone = htmlspecialchars(strip_tags($phone));
            // ===========================================
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (full_name, email, phone, password, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$full_name, $email, $phone, $hashed_password]);
                
                $_SESSION['success'] = 'Registration successful! Please login.';
                header('Location: ../login.php');  // FIXED PATH
                exit();
                
            } catch (PDOException $e) {
                $errors['database'] = 'Registration failed. Please try again. Error: ' . $e->getMessage();
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
    <style>
        /* Add some additional styling for better UX */
        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        input:invalid {
            border-color: #dc3545;
        }
        
        input:valid {
            border-color: #28a745;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
        <nav>
    <div class="logo">FitLife Gym</div>
    <div class="nav-links">
        <a href="../index.php">Home</a>
        <a href="../public/packages.php">Packages</a>
        <a href="../public/about.php">About Us</a>
        <a href="../login.php">Login</a>
        <a href="register.php" class="active">Register</a>
    </div>
</nav>>

    <div class="register-container">
        <h1>Create Account</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors['database'])): ?>
            <div class="error"><?php echo $errors['database']; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registrationForm">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name"
                       value="<?php echo htmlspecialchars($full_name); ?>"
                       placeholder="Enter your full name (letters only)"
                       pattern="[a-zA-Z\s.'-]+"
                       title="Only letters">
                <?php if (isset($errors['full_name'])): ?>
                    <span class="error"><?php echo $errors['full_name']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" 
                       value="<?php echo htmlspecialchars($email); ?>"
                       placeholder="Enter your email">
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" name="phone" id="phone" 
                       value="<?php echo htmlspecialchars($phone); ?>"
                       placeholder="98XXXXXXXX or 97XXXXXXXX"
                       pattern="(98|97)[0-9]{8}"
                       title="10-digit number starting with 98 or 97">
                <small style="color: #666;">Format: 98XXXXXXXX or 97XXXXXXXX (10 digits)</small>
                <?php if (isset($errors['phone'])): ?>
                    <span class="error"><?php echo $errors['phone']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" 
                       placeholder="Minimum 8 characters with uppercase, lowercase, and number"
                       minlength="8">
                <small style="color: #666;">Must contain: uppercase letter, lowercase letter, number</small>
                <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                       placeholder="Confirm your password">
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="error"><?php echo $errors['confirm_password']; ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn-register">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="../login.php">Login here</a>  <!-- FIXED PATH -->
        </div>
    </div>

    <script>
        // Client-side validation for better UX
        document.getElementById('registrationForm').addEventListener('submit', function(event) {
            let isValid = true;
            const form = event.target;
            
            // Validate Full Name
            const fullName = document.getElementById('full_name');
            const nameRegex = /^[a-zA-Z\s.'-]+$/;
            if (!nameRegex.test(fullName.value)) {
                isValid = false;
                if (!document.getElementById('full_name').nextElementSibling.classList.contains('error')) {
                    const errorSpan = document.createElement('span');
                    errorSpan.className = 'error';
                    errorSpan.textContent = 'Full name can only contain letters, spaces, dots, and apostrophes';
                    fullName.parentNode.insertBefore(errorSpan, fullName.nextSibling);
                }
            }
            
            // Validate Phone
            const phone = document.getElementById('phone');
            const phoneRegex = /^(98|97)[0-9]{8}$/;
            const cleanPhone = phone.value.replace(/[^0-9]/g, '');
            
            if (!phoneRegex.test(cleanPhone)) {
                isValid = false;
                if (!document.getElementById('phone').nextElementSibling.classList.contains('error')) {
                    const errorSpan = document.createElement('span');
                    errorSpan.className = 'error';
                    errorSpan.textContent = 'Phone must be 10 digits starting with 98 or 97';
                    phone.parentNode.insertBefore(errorSpan, phone.nextSibling);
                }
            }
            
            // Validate Password
            const password = document.getElementById('password');
            if (password.value.length < 8) {
                isValid = false;
            }
            
            // Validate Password Match
            const confirmPassword = document.getElementById('confirm_password');
            if (password.value !== confirmPassword.value) {
                isValid = false;
                if (!document.getElementById('confirm_password').nextElementSibling.classList.contains('error')) {
                    const errorSpan = document.createElement('span');
                    errorSpan.className = 'error';
                    errorSpan.textContent = 'Passwords do not match';
                    confirmPassword.parentNode.insertBefore(errorSpan, confirmPassword.nextSibling);
                }
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>