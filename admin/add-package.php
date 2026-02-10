<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';

$errors = [];
$success = '';

// Form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    $name = htmlspecialchars(strip_tags($name));
$description = htmlspecialchars(strip_tags($description));
    
    // If no errors, insert into database
    if (empty($errors)) {
       $stmt = $pdo->prepare("
        INSERT INTO packages (name, short_description, description, price, duration, category, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
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
        
        $success = 'Package added successfully!';
        
        // Clear form
        $name = $description = $price = $duration = $category = '';
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
    <?php include 'admin-nav.php'; ?>
    
    <div class="form-container">
        <h1>Add New Workout Package</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
            <div style="margin: 15px 0;">
                <a href="manage-packages.php" class="btn-primary">View All Packages</a>
                <a href="add-package.php" class="btn-secondary">Add Another</a>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Package Name *</label>
                <input type="text" name="name" id="name" 
                       value="<?php echo htmlspecialchars($name ?? ''); ?>"
                       placeholder="e.g., 30-Day Weight Loss Challenge">
                <?php if (isset($errors['name'])): ?>
                    <span class="error"><?php echo $errors['name']; ?></span>
                <?php endif; ?>
            </div>
            
             <!-- Short Description (Public) -->
            <div class="form-group">
                <label for="short_description">Short Description (Public) *</label>
                <input type="text" name="short_description" id="short_description" 
                    value="<?php echo htmlspecialchars($short_description ?? ''); ?>"
                    placeholder="Brief overview shown to everyone (e.g., '30-day weight loss program')">
                <?php if (isset($errors['short_description'])): ?>
                    <span class="error"><?php echo $errors['short_description']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Full Workout Plan (After Purchase) *</label>
                <textarea name="description" id="description" rows="8"
                        placeholder="COMPLETE workout details - ONLY visible after purchase (include exercises, sets, reps, schedule)"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <span class="error"><?php echo $errors['description']; ?></span>
                <?php endif; ?>
            </div>
                        
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price (Rs.) *</label>
                    <input type="number" name="price" id="price" step="0.01"
                           value="<?php echo htmlspecialchars($price ?? ''); ?>"
                           placeholder="2999.00">
                    <?php if (isset($errors['price'])): ?>
                        <span class="error"><?php echo $errors['price']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration *</label>
                    <input type="text" name="duration" id="duration"
                           value="<?php echo htmlspecialchars($duration ?? ''); ?>"
                           placeholder="e.g., 30 days, 12 weeks">
                    <?php if (isset($errors['duration'])): ?>
                        <span class="error"><?php echo $errors['duration']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="category">Category *</label>
                <select name="category" id="category">
                    <option value="">Select Category</option>
                    <option value="weight_loss" <?php echo ($category ?? '') == 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                    <option value="strength" <?php echo ($category ?? '') == 'strength' ? 'selected' : ''; ?>>Strength Training</option>
                    <option value="muscle_building" <?php echo ($category ?? '') == 'muscle_building' ? 'selected' : ''; ?>>Muscle Building</option>
                    <option value="yoga" <?php echo ($category ?? '') == 'yoga' ? 'selected' : ''; ?>>Yoga & Flexibility</option>
                    <option value="cardio" <?php echo ($category ?? '') == 'cardio' ? 'selected' : ''; ?>>Cardio & Endurance</option>
                    <option value="beginner" <?php echo ($category ?? '') == 'beginner' ? 'selected' : ''; ?>>Beginner Program</option>
                    <option value="advanced" <?php echo ($category ?? '') == 'advanced' ? 'selected' : ''; ?>>Advanced Training</option>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <span class="error"><?php echo $errors['category']; ?></span>
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