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
        
        if (empty($duration)) {
            $errors['duration'] = 'Duration is required';
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
                // First check if short_description column exists
                $checkColumn = $pdo->query("SHOW COLUMNS FROM packages LIKE 'short_description'");
                $columnExists = $checkColumn->rowCount() > 0;
                
                if ($columnExists) {
                    // Column exists - use full query
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
                    // Column doesn't exist - use query without short_description
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
                
                $success = 'Package added successfully!';
                
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
        <h1>Add New Workout Package</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <div style="margin: 15px 0;">
                <a href="manage-packages.php" class="btn-primary">View All Packages</a>
                <a href="add-package.php" class="btn-secondary">Add Another</a>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['csrf'])): ?>
            <div class="error"><?php echo htmlspecialchars($errors['csrf'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors['database'])): ?>
            <div class="error"><?php echo htmlspecialchars($errors['database'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="name">Package Name *</label>
                <input type="text" name="name" id="name" 
                       value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="e.g., 30-Day Weight Loss Challenge">
                <?php if (isset($errors['name'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Short Description (Public) -->
            <div class="form-group">
                <label for="short_description">Short Description (Public) *</label>
                <input type="text" name="short_description" id="short_description" 
                    value="<?php echo htmlspecialchars($short_description, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="Brief overview shown to everyone (e.g., '30-day weight loss program')">
                <?php if (isset($errors['short_description'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['short_description'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            

            <div class="form-group">
                <label for="description">Full Workout Plan (After Purchase) *</label>
                <textarea name="description" id="description" rows="8"
                        placeholder="COMPLETE workout details - ONLY visible after purchase (include exercises, sets, reps, schedule)"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price (Rs.) *</label>
                    <input type="number" name="price" id="price" step="0.01"
                           value="<?php echo htmlspecialchars($price, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="2999.00">
                    <?php if (isset($errors['price'])): ?>
                        <span class="error"><?php echo htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration *</label>
                    <input type="text" name="duration" id="duration"
                           value="<?php echo htmlspecialchars($duration, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="e.g., 30 days, 12 weeks">
                    <?php if (isset($errors['duration'])): ?>
                        <span class="error"><?php echo htmlspecialchars($errors['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="category">Category *</label>
                <select name="category" id="category">
                    <option value="">Select Category</option>
                    <option value="weight_loss" <?php echo $category == 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                    <option value="strength" <?php echo $category == 'strength' ? 'selected' : ''; ?>>Strength Training</option>
                    <option value="muscle_building" <?php echo $category == 'muscle_building' ? 'selected' : ''; ?>>Muscle Building</option>
                    <option value="yoga" <?php echo $category == 'yoga' ? 'selected' : ''; ?>>Yoga & Flexibility</option>
                    <option value="cardio" <?php echo $category == 'cardio' ? 'selected' : ''; ?>>Cardio & Endurance</option>
                    <option value="beginner" <?php echo $category == 'beginner' ? 'selected' : ''; ?>>Beginner Program</option>
                    <option value="advanced" <?php echo $category == 'advanced' ? 'selected' : ''; ?>>Advanced Training</option>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Add Package</button>
                <a href="manage-packages.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>