<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../includes/database.php';

$id = $_GET['id'] ?? 0;

// Validate package ID
if (!is_numeric($id) || $id <= 0) {
    header('Location: manage-packages.php');
    exit();
}

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

// Pre-fill form with existing data
$name = $package['name'];
$short_description = $package['short_description'] ?? '';
$description = $package['description'];
$price = $package['price'];
$duration = $package['duration'];
$category = $package['category'];

// Form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Invalid form submission. Please try again.';
        // Regenerate token
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
} else {
    $duration = trim($duration);

    
    $duration_lower = strtolower($duration);
    
 
    $valid_patterns = [
        '/^\d+\s*(day|days|day[s]?)$/i', 
        '/^\d+\s*(week|weeks|week[s]?)$/i',     
        '/^\d+\s*(month|months|month[s]?)$/i',
        '/^\d+\s*(year|years|year[s]?)$/i',       
        '/^\d+\s*-?\s*(day|week|month|year)/i',    
    ];
    
    $is_valid = false;
    foreach ($valid_patterns as $pattern) {
        if (preg_match($pattern, $duration_lower)) {
            $is_valid = true;
            break;
        }
    }
    preg_match('/\d+/', $duration, $matches);
    $number = $matches[0] ?? 0;
    
    if (!$is_valid) {
        $errors['duration'] = 'Please use a valid format (e.g., "30 days", "12 weeks", "3 months", "1 year")';
    } elseif ($number <= 0) {
        $errors['duration'] = 'Duration must be greater than zero';
    } elseif ($number > 365 && strpos($duration_lower, 'day') !== false) {
        $errors['duration'] = 'Maximum 365 days allowed';
    } elseif ($number > 52 && strpos($duration_lower, 'week') !== false) {
        $errors['duration'] = 'Maximum 52 weeks allowed';
    } elseif ($number > 12 && strpos($duration_lower, 'month') !== false) {
        $errors['duration'] = 'Maximum 12 months allowed';
    }
}
        
        if (empty($category)) {
            $errors['category'] = 'Category is required';
        }

        // Sanitize inputs
        $name = htmlspecialchars(strip_tags($name), ENT_QUOTES, 'UTF-8');
        $short_description = htmlspecialchars(strip_tags($short_description), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(strip_tags($description), ENT_QUOTES, 'UTF-8');
        
        // If no errors, update database
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE packages 
                    SET name = ?, short_description = ?, description = ?, price = ?, duration = ?, category = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $name,
                    $short_description,
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
                
                // Update form variables with new data
                $name = $package['name'];
                $short_description = $package['short_description'] ?? '';
                $description = $package['description'];
                $price = $package['price'];
                $duration = $package['duration'];
                $category = $package['category'];
                
            } catch (PDOException $e) {
                $errors['database'] = 'Update failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Package - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
   <?php
$base_path = '../';
include '../includes/navigation.php';
?>
    
    <div class="form-container">
        <h1>Edit Package: <?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors['csrf'])): ?>
            <div class="error"><?php echo htmlspecialchars($errors['csrf'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors['database'])): ?>
            <div class="error"><?php echo htmlspecialchars($errors['database'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <!-- CSRF Protection -->
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
                <button type="submit" class="btn-primary">Update Package</button>
                <a href="manage-packages.php" class="btn-cancel">Cancel</a>
                <a href="manage-packages.php?delete=<?php echo $id; ?>" 
                   class="btn-delete"
                   onclick="return confirm('Are you sure you want to delete this package? This action cannot be undone.')">Delete Package</a>
            </div>
        </form>
    </div>
</body>
</html>