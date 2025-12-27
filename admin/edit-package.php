<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../user/login.php');
    exit();
}

require_once '../includes/database.php';

$id = $_GET['id'] ?? 0;

// Get package details
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$id]);
$package = $stmt->fetch();

if (!$package) {
    header('Location: manage-packages.php');
    exit();
}

$errors = [];
$success = '';

// Form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $category = trim($_POST['category'] ?? '');
    
    // Validation
    if (empty($name)) {
        $errors['name'] = 'Package name is required';
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
    
    // If no errors, update database
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE packages 
            SET name = ?, description = ?, price = ?, duration = ?, category = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $name,
            $description,
            $price,
            $duration,
            $category,
            $id
        ]);
        
        $success = 'Package updated successfully!';
        
        // Refresh package data
        $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->execute([$id]);
        $package = $stmt->fetch();
    }
} else {
    // Pre-fill form with existing data
    $name = $package['name'];
    $description = $package['description'];
    $price = $package['price'];
    $duration = $package['duration'];
    $category = $package['category'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Package - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    
    <div class="form-container">
        <h1>Edit Package: <?php echo htmlspecialchars($package['name']); ?></h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Package Name *</label>
                <input type="text" name="name" id="name" 
                       value="<?php echo htmlspecialchars($name); ?>"
                       placeholder="e.g., 30-Day Weight Loss Challenge">
                <?php if (isset($errors['name'])): ?>
                    <span class="error"><?php echo $errors['name']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea name="description" id="description" rows="5"
                          placeholder="Describe the workout package in detail..."><?php echo htmlspecialchars($description); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <span class="error"><?php echo $errors['description']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price (Rs.) *</label>
                    <input type="number" name="price" id="price" step="0.01"
                           value="<?php echo htmlspecialchars($price); ?>"
                           placeholder="2999.00">
                    <?php if (isset($errors['price'])): ?>
                        <span class="error"><?php echo $errors['price']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration *</label>
                    <input type="text" name="duration" id="duration"
                           value="<?php echo htmlspecialchars($duration); ?>"
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
                    <option value="weight_loss" <?php echo $category == 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                    <option value="strength" <?php echo $category == 'strength' ? 'selected' : ''; ?>>Strength Training</option>
                    <option value="muscle_building" <?php echo $category == 'muscle_building' ? 'selected' : ''; ?>>Muscle Building</option>
                    <option value="yoga" <?php echo $category == 'yoga' ? 'selected' : ''; ?>>Yoga & Flexibility</option>
                    <option value="cardio" <?php echo $category == 'cardio' ? 'selected' : ''; ?>>Cardio & Endurance</option>
                    <option value="beginner" <?php echo $category == 'beginner' ? 'selected' : ''; ?>>Beginner Program</option>
                    <option value="advanced" <?php echo $category == 'advanced' ? 'selected' : ''; ?>>Advanced Training</option>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <span class="error"><?php echo $errors['category']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Update Package</button>
                <a href="manage-packages.php" class="btn-cancel">Cancel</a>
                <a href="?delete=<?php echo $id; ?>" 
                   class="btn-delete"
                   onclick="return confirm('Are you sure you want to delete this package?')">Delete Package</a>
            </div>
        </form>
    </div>
</body>
</html>