<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';
$user_id = $_SESSION['user_id'];

// Get current user data
$stmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$success = '';
$error = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // ========== VALIDATE NAME ==========
    if (empty($full_name)) {
        $errors['name'] = 'Full name is required';
    } elseif (strlen($full_name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters long';
    } elseif (strlen($full_name) > 50) {
        $errors['name'] = 'Name must not exceed 50 characters';
    } elseif (!preg_match("/^[a-zA-Z\s.'-]+$/", $full_name)) {
        $errors['name'] = 'Name can only contain letters, spaces, dots and hyphens';
    }
    
    // ========== VALIDATE EMAIL ==========
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email is too long';
    }
    
    // Check if email already exists (if changed)
    if ($email != $user['email']) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $user_id]);
        if ($check->fetch()) {
            $errors['email'] = 'This email is already registered';
        }
    }
    
    // ========== VALIDATE PHONE ==========
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    } else {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Validate Nepal phone number (10 digits starting with 98 or 97)
        if (!preg_match('/^(98|97)\d{8}$/', $phone)) {
            $errors['phone'] = 'Phone must be 10 digits starting with 98 or 97';
        }
    }
    
    // ========== PASSWORD CHANGE VALIDATION ==========
    $password_change_attempted = !empty($current_password) || !empty($new_password) || !empty($confirm_password);
    
    if ($password_change_attempted) {
        // Verify current password is provided
        if (empty($current_password)) {
            $errors['current_password'] = 'Current password is required to change password';
        } else {
            // Verify current password is correct
            $pass_check = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $pass_check->execute([$user_id]);
            $hashed = $pass_check->fetch()['password'];
            
            if (!password_verify($current_password, $hashed)) {
                $errors['current_password'] = 'Current password is incorrect';
            }
        }
        
        // Validate new password
        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required';
        } else {
            $password_errors = [];
            if (strlen($new_password) < 8) {
                $password_errors[] = 'at least 8 characters';
            }
            if (strlen($new_password) > 32) {
                $password_errors[] = 'max 32 characters';
            }
            if (!preg_match('/[A-Z]/', $new_password)) {
                $password_errors[] = 'one uppercase letter';
            }
            if (!preg_match('/[a-z]/', $new_password)) {
                $password_errors[] = 'one lowercase letter';
            }
            if (!preg_match('/[0-9]/', $new_password)) {
                $password_errors[] = 'one number';
            }
            
            if (!empty($password_errors)) {
                $errors['new_password'] = 'Password must contain: ' . implode(', ', $password_errors);
            }
        }
        
        // Validate confirm password
        if (empty($confirm_password)) {
            $errors['confirm_password'] = 'Please confirm your new password';
        } elseif ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        // Check if new password is same as current
        if (empty($errors) && $new_password === $current_password) {
            $errors['new_password'] = 'New password must be different from current password';
        }
    }
    
    // ========== UPDATE DATABASE IF NO ERRORS ==========
    if (empty($errors)) {
        try {
            if ($password_change_attempted && empty($errors)) {
                // Update with new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $phone, $hashed_password, $user_id]);
                $success = 'Profile and password updated successfully!';
            } else {
                // Update without password change
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $phone, $user_id]);
                $success = 'Profile updated successfully!';
            }
            
            // Update session
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            
            // Refresh user data
            $user['full_name'] = $full_name;
            $user['email'] = $email;
            $user['phone'] = $phone;
            
        } catch (PDOException $e) {
            $error = 'Update failed. Please try again.';
            error_log("Profile update error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile Settings - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="settings-container">
        <div class="settings-header">
            <h1>⚙️ Profile Settings</h1>
            <p>Manage your account information and password</p>
        </div>
        
        <div class="settings-card">
            <?php if ($success): ?>
                <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="profileForm">
                <h3 class="section-title">Personal Information</h3>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           value="<?php echo htmlspecialchars($user['full_name']); ?>"
                           placeholder="Enter your full name"
                           class="<?php echo isset($errors['name']) ? 'error-input' : ''; ?>">
                    <?php if (isset($errors['name'])): ?>
                        <span class="error-message">⚠️ <?php echo $errors['name']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           placeholder="Enter your email"
                           class="<?php echo isset($errors['email']) ? 'error-input' : ''; ?>">
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-message">⚠️ <?php echo $errors['email']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           placeholder="98XXXXXXXX or 97XXXXXXXX"
                           class="<?php echo isset($errors['phone']) ? 'error-input' : ''; ?>">
                    <div class="info-text">
                        <strong>Format:</strong> 98XXXXXXXX or 97XXXXXXXX (10 digits)
                    </div>
                    <?php if (isset($errors['phone'])): ?>
                        <span class="error-message">⚠️ <?php echo $errors['phone']; ?></span>
                    <?php endif; ?>
                </div>
                
                <h3 class="section-title">Change Password</h3>
                <div class="info-text">
                    Leave blank if you don't want to change your password
                </div>
                
                <div class="password-requirements">
                    <strong>Password Requirements:</strong>
                    <ul>
                        <li class="<?php echo (!isset($errors['new_password']) && empty($new_password)) ? '' : 'invalid'; ?>">
                            ✓ At least 8 characters
                        </li>
                        <li class="<?php echo (!isset($errors['new_password']) && empty($new_password)) ? '' : 'invalid'; ?>">
                            ✓ At least one uppercase letter
                        </li>
                        <li class="<?php echo (!isset($errors['new_password']) && empty($new_password)) ? '' : 'invalid'; ?>">
                            ✓ At least one lowercase letter
                        </li>
                        <li class="<?php echo (!isset($errors['new_password']) && empty($new_password)) ? '' : 'invalid'; ?>">
                            ✓ At least one number
                        </li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           placeholder="Enter current password"
                           class="<?php echo isset($errors['current_password']) ? 'error-input' : ''; ?>">
                    <?php if (isset($errors['current_password'])): ?>
                        <span class="error-message">⚠️ <?php echo $errors['current_password']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               placeholder="Enter new password"
                               class="<?php echo isset($errors['new_password']) ? 'error-input' : ''; ?>">
                        <?php if (isset($errors['new_password'])): ?>
                            <span class="error-message">⚠️ <?php echo $errors['new_password']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Confirm new password"
                               class="<?php echo isset($errors['confirm_password']) ? 'error-input' : ''; ?>">
                        <?php if (isset($errors['confirm_password'])): ?>
                            <span class="error-message">⚠️ <?php echo $errors['confirm_password']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn-save">Save Changes</button>
            </form>
            
            <div class="action-buttons">
                <a href="dashboard.php" class="btn-cancel">← Back to Dashboard</a>
            </div>
        </div>
    </div>

    <script>
    // Real-time phone number formatting
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 10) {
            value = value.slice(0, 10);
        }
        e.target.value = value;
    });

    // Real-time password match check
    document.getElementById('confirm_password').addEventListener('input', function(e) {
        const newPass = document.getElementById('new_password').value;
        const confirmPass = e.target.value;
        
        if (confirmPass.length > 0) {
            if (newPass === confirmPass) {
                e.target.style.borderColor = '#27ae60';
            } else {
                e.target.style.borderColor = '#e74c3c';
            }
        } else {
            e.target.style.borderColor = '#e0e0e0';
        }
    });

    // Prevent form submission if there are errors
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const phone = document.getElementById('phone').value;
        const phonePattern = /^(98|97)\d{8}$/;
        
        if (phone && !phonePattern.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid phone number (10 digits starting with 98 or 97)');
        }
    });
    </script>
    
    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>