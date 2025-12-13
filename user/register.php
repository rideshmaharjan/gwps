<?php
session_start();

// Initialize variables
$full_name = $email = $phone = $password = $confirm_password = '';
$errors = [];

// Only validate if form is submitted
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // ========== VALIDATION CODE ==========
    
    // Validate Full Name
    if (isset($_POST['full_name']) && !empty(trim($_POST['full_name']))) {
        $full_name = trim($_POST['full_name']);
    } else {
        $errors['full_name'] = 'Please enter your full name';
    }

    // Validate Email
    if (isset($_POST['email']) && !empty(trim($_POST['email']))) {
        $email = trim($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
    } else {
        $errors['email'] = 'Please enter your email';
    }

    // Validate Phone
    if (isset($_POST['phone']) && !empty(trim($_POST['phone']))) {
        $phone = trim($_POST['phone']);
    } else {
        $errors['phone'] = 'Please enter your phone number';
    }

    // Validate Password
    if (isset($_POST['password']) && !empty(trim($_POST['password']))) {
        $password = $_POST['password'];
        if (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
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
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (full_name, email, phone, password, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $email, $phone, $hashed_password]);
            
            $_SESSION['success'] = 'Registration successful! Please login.';
            header('Location: login.php');
            exit();
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors['email'] = 'Email already registered';
            } else {
                $errors['database'] = 'Registration failed. Please try again.';
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
    <!-- Navigation -->
    <nav>
        <div class="logo">FitLife Gym</div>
        <div class="nav-links">
            <a href="../index.php">Home</a>
            <a href="../public/packages.php">Packages</a>
            <a href="../public/about.php">About Us</a>
            <a href="login.php">Login</a>
        </div>
    </nav>

    <div class="register-container">
        <h1>Create Account</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors['database'])): ?>
            <div class="error"><?php echo $errors['database']; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" 
                       value="<?php echo htmlspecialchars($full_name); ?>">
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
                       placeholder="Enter your phone number">
                <?php if (isset($errors['phone'])): ?>
                    <span class="error"><?php echo $errors['phone']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" 
                       placeholder="Enter password (min. 6 characters)">
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
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>