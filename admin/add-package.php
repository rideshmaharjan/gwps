<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../includes/database.php';

$errors = [];
$success = '';
$name = $short_description = $description = $price = $duration = $category = '';

// Form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Invalid form submission';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $name = trim($_POST['name'] ?? '');
        $short_description = trim($_POST['short_description'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $category = trim($_POST['category'] ?? '');
        
        // Validation
        if (empty($name)) {
            $errors['name'] = 'Package name is required';
        }
        
        if (empty($short_description)) {
            $errors['short_description'] = 'Short description is required';
        } elseif (strlen($short_description) > 1000) {
            $errors['short_description'] = 'Short description too long (max 1000 characters)';
        }
        
        if (empty($description)) {
            $errors['description'] = 'Description is required';
        }
        
        if (empty($price) || !is_numeric($price) || $price <= 0) {
            $errors['price'] = 'Valid price is required';
        }
        
        // ========== STRICT DURATION VALIDATION ==========
        if (empty($duration)) {
            $errors['duration'] = 'Duration is required';
        } else {
            $duration_lower = strtolower($duration);
            
            // ALLOWED PATTERNS:
            // - Number + days (e.g., "30 days", "7 day", "90days")
            // - Number + weeks (e.g., "12 weeks", "8 week", "6weeks")
            // - Number + months (e.g., "3 months", "1 month", "6months")
            // - Number + year/years (e.g., "1 year", "2 years")
            
            $valid_patterns = [
                '/^\d+\s*(day|days|day[s]?)$/i',     // matches: 30 days, 7 day, 90days
                '/^\d+\s*(week|weeks|week[s]?)$/i',  // matches: 12 weeks, 8 week, 6weeks
                '/^\d+\s*(month|months|month[s]?)$/i', // matches: 3 months, 1 month, 6months
                '/^\d+\s*(year|years|year[s]?)$/i',  // matches: 1 year, 2 years, 1year
            ];
            
            $is_valid = false;
            
            foreach ($valid_patterns as $pattern) {
                if (preg_match($pattern, $duration_lower)) {
                    $is_valid = true;
                    break;
                }
            }
            
            // Extract the number for range validation
            preg_match('/\d+/', $duration, $matches);
            $number = isset($matches[0]) ? (int)$matches[0] : 0;
            
            // Check if it's valid format
            if (!$is_valid) {
                $errors['duration'] = '❌ Invalid format! Use: "30 days", "12 weeks", "3 months", or "1 year"';
            } 
            // Validate ranges
            elseif (strpos($duration_lower, 'day') !== false) {
                if ($number < 1) {
                    $errors['duration'] = '❌ Days must be at least 1';
                } elseif ($number > 365) {
                    $errors['duration'] = '❌ Maximum 365 days allowed';
                }
            }
            elseif (strpos($duration_lower, 'week') !== false) {
                if ($number < 1) {
                    $errors['duration'] = '❌ Weeks must be at least 1';
                } elseif ($number > 52) {
                    $errors['duration'] = '❌ Maximum 52 weeks allowed';
                }
            }
            elseif (strpos($duration_lower, 'month') !== false) {
                if ($number < 1) {
                    $errors['duration'] = '❌ Months must be at least 1';
                } elseif ($number > 12) {
                    $errors['duration'] = '❌ Maximum 12 months allowed';
                }
            }
            elseif (strpos($duration_lower, 'year') !== false) {
                if ($number < 1) {
                    $errors['duration'] = '❌ Years must be at least 1';
                } elseif ($number > 5) {
                    $errors['duration'] = '❌ Maximum 5 years allowed';
                }
            }
        }
        
        if (empty($category)) {
            $errors['category'] = 'Category is required';
        }

        // Sanitize inputs
        $name = htmlspecialchars(strip_tags($name), ENT_QUOTES, 'UTF-8');
        $short_description = htmlspecialchars(strip_tags($short_description), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(strip_tags($description), ENT_QUOTES, 'UTF-8');
        
        // If no errors, insert into database
        if (empty($errors)) {
            try {
                // Check if short_description column exists
                $checkColumn = $pdo->query("SHOW COLUMNS FROM packages LIKE 'short_description'");
                $columnExists = $checkColumn->rowCount() > 0;
                
                if ($columnExists) {
                    $stmt = $pdo->prepare("
                        INSERT INTO packages (name, short_description, description, price, duration, category, created_by, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $stmt->execute([
                        $name,
                        $short_description,
                        $description,
                        $price,
                        $duration,
                        $category,
                        $_SESSION['user_id']
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO packages (name, description, price, duration, category, created_by, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $stmt->execute([
                        $name,
                        $description,
                        $price,
                        $duration,
                        $category,
                        $_SESSION['user_id']
                    ]);
                }
                
                $success = '✅ Package added successfully!';
                
                // Clear form
                $name = $short_description = $description = $price = $duration = $category = '';
                
            } catch (PDOException $e) {
                $errors['database'] = 'Failed to add package: ' . $e->getMessage();
                error_log("Add package error: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Package - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="form-container">
        <h1>➕ Add New Workout Package</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="action-buttons">
                <a href="manage-packages.php" class="btn-primary">View All Packages</a>
                <a href="add-package.php" class="btn-secondary">Add Another</a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <strong>⚠️ Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group <?php echo isset($errors['name']) ? 'error' : ''; ?>">
                <label for="name">Package Name *</label>
                <input type="text" name="name" id="name" 
                       value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="e.g., 30-Day Weight Loss Challenge"
                       class="<?php echo isset($errors['name']) ? 'error-input' : ''; ?>">
            </div>
            
            <div class="form-group <?php echo isset($errors['short_description']) ? 'error' : ''; ?>">
                <label for="short_description">Short Description (Public) *</label>
                <input type="text" name="short_description" id="short_description" 
                    value="<?php echo htmlspecialchars($short_description, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="Brief overview shown to everyone (e.g., '30-day weight loss program')"
                    class="<?php echo isset($errors['short_description']) ? 'error-input' : ''; ?>">
            </div>
            
            <div class="form-group <?php echo isset($errors['description']) ? 'error' : ''; ?>">
                <label for="description">Full Workout Plan (After Purchase) *</label>
                <textarea name="description" id="description" rows="8"
                        placeholder="COMPLETE workout details - ONLY visible after purchase (include exercises, sets, reps, schedule)"
                        class="<?php echo isset($errors['description']) ? 'error-input' : ''; ?>"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group <?php echo isset($errors['price']) ? 'error' : ''; ?>">
                    <label for="price">Price (Rs.) *</label>
                    <input type="number" name="price" id="price" step="0.01"
                           value="<?php echo htmlspecialchars($price, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="2999.00"
                           class="<?php echo isset($errors['price']) ? 'error-input' : ''; ?>">
                </div>
                
                <div class="form-group <?php echo isset($errors['duration']) ? 'error' : ''; ?>">
                    <label for="duration">Duration *</label>
                    <input type="text" name="duration" id="duration"
                           value="<?php echo htmlspecialchars($duration, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="e.g., 30 days, 12 weeks, 3 months"
                           class="<?php echo isset($errors['duration']) ? 'error-input' : ''; ?>">
                    
                    <!-- Using existing info-text class from style.css -->
                    <div class="info-text">
                        <strong>✅ Allowed formats:</strong> 30 days, 12 weeks, 3 months, 1 year, 7 day, 8 week, 6 months, 90days<br>
                        <strong style="color: #e74c3c;">❌ NOT allowed:</strong> 7d, kljojikjijk, abc, 30, 12wk
                    </div>
                </div>
            </div>
            
            <div class="form-group <?php echo isset($errors['category']) ? 'error' : ''; ?>">
                <label for="category">Category *</label>
                <select name="category" id="category" class="<?php echo isset($errors['category']) ? 'error-input' : ''; ?>">
                    <option value="">Select Category</option>
                    <option value="weight_loss" <?php echo $category == 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                    <option value="muscle_building" <?php echo $category == 'muscle_building' ? 'selected' : ''; ?>>Muscle Building</option>
                    <option value="strength" <?php echo $category == 'strength' ? 'selected' : ''; ?>>Strength Training</option>
                    <option value="yoga" <?php echo $category == 'yoga' ? 'selected' : ''; ?>>Yoga & Flexibility</option>
                    <option value="cardio" <?php echo $category == 'cardio' ? 'selected' : ''; ?>>Cardio & Endurance</option>
                    <option value="beginner" <?php echo $category == 'beginner' ? 'selected' : ''; ?>>Beginner Program</option>
                    <option value="advanced" <?php echo $category == 'advanced' ? 'selected' : ''; ?>>Advanced Training</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">➕ Add Package</button>
                <a href="manage-packages.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
    
    <script>
    // Real-time validation for duration field (just visual feedback)
    document.getElementById('duration').addEventListener('input', function(e) {
        const value = e.target.value.toLowerCase();
        const validPatterns = [
            /^\d+\s*(day|days|day[s]?)$/i,
            /^\d+\s*(week|weeks|week[s]?)$/i,
            /^\d+\s*(month|months|month[s]?)$/i,
            /^\d+\s*(year|years|year[s]?)$/i,
        ];
        
        let isValid = false;
        for (let pattern of validPatterns) {
            if (pattern.test(value)) {
                isValid = true;
                break;
            }
        }
        
        if (value.length > 0) {
            if (isValid) {
                e.target.classList.remove('error-input');
                e.target.classList.add('valid-input');
            } else {
                e.target.classList.remove('valid-input');
                e.target.classList.add('error-input');
            }
        } else {
            e.target.classList.remove('valid-input', 'error-input');
        }
    });
    </script>
</body>
</html>