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
$is_active = isset($package['is_active']) ? (int)$package['is_active'] : 1;

// Form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Invalid form submission. Please try again.';
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
                        try {
                            // Check if is_active column exists
                            $colCheck = $pdo->query("SHOW COLUMNS FROM packages LIKE 'is_active'");
                            $hasIsActive = $colCheck->rowCount() > 0;

                            if ($hasIsActive) {
                                $stmt = $pdo->prepare("
                                    UPDATE packages 
                                    SET name = ?, short_description = ?, description = ?, price = ?, duration = ?, category = ?, is_active = ?
                                    WHERE id = ?
                                ");
                                $stmt->execute([
                                    $name,
                                    $short_description,
                                    $description,
                                    $price,
                                    $duration,
                                    $category,
                                    $is_active,
                                    $id
                                ]);
                            } else {
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
                            }
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
                
            $is_active = isset($package['is_active']) ? (int)$package['is_active'] : 1;
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
                $is_active = isset($package['is_active']) ? (int)$package['is_active'] : 1;
                
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
    <title>Edit Package - <?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .edit-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .edit-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .edit-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 25px 30px;
        }
        
        .edit-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .edit-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .edit-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        
        .form-group input.error-input,
        .form-group textarea.error-input,
        .form-group select.error-input {
            border-color: #e74c3c;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        
        .success-message {
            background: #f0fff4;
            border-left: 4px solid #27ae60;
            color: #27ae60;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .admin-badge {
            background: #f39c12;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .category-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-block;
        }
        
        .info-text {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-top: 5px;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 5px;
            border-left: 3px solid #3498db;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn-save {
            background: #27ae60;
            color: white;
            padding: 14px 35px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            flex: 2;
            min-width: 200px;
        }
        
        .btn-save:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39,174,96,0.3);
        }
        
        .btn-cancel {
            background: #95a5a6;
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            flex: 1;
            min-width: 120px;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            flex: 1;
            min-width: 120px;
        }
        
        .btn-delete:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231,76,60,0.3);
        }
        
        .preview-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px dashed #3498db;
        }
        
        .preview-title {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .preview-content {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        
        .package-name-preview {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .package-price-preview {
            font-size: 1.3rem;
            font-weight: 600;
            color: #27ae60;
            margin-bottom: 10px;
        }
        
        .package-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #7f8c8d;
            font-size: 0.95rem;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-save,
            .btn-cancel,
            .btn-delete {
                width: 100%;
            }
            
            .edit-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="edit-container">
        <div class="edit-card">
            <div class="edit-header">
                <h1>✏️ Edit Package</h1>
                <p>Update workout package details</p>
            </div>
            
            <div class="edit-body">
                <?php if ($success): ?>
                    <div class="success-message">
                        ✅ <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors['csrf'])): ?>
                    <div class="error-message" style="background: #fef5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        ⚠️ <?php echo htmlspecialchars($errors['csrf'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors['database'])): ?>
                    <div class="error-message" style="background: #fef5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        ⚠️ <?php echo htmlspecialchars($errors['database'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Live Preview Section -->
                <div class="preview-section">
                    <div class="preview-title">🔍 LIVE PREVIEW</div>
                    <div class="preview-content">
                        <div class="package-name-preview" id="previewName"><?php echo htmlspecialchars($name ?: 'Package Name', ENT_QUOTES, 'UTF-8'); ?></div>
                        
                        <div class="package-meta">
                            <span class="meta-item">
                                <span>💰</span> 
                                <span id="previewPrice">Rs. <?php echo htmlspecialchars($price ?: '0.00', ENT_QUOTES, 'UTF-8'); ?></span>
                            </span>
                            <span class="meta-item">
                                <span>⏱️</span> 
                                <span id="previewDuration"><?php echo htmlspecialchars($duration ?: 'Duration', ENT_QUOTES, 'UTF-8'); ?></span>
                            </span>
                            <span class="meta-item">
                                <span>🏷️</span>
                                <span class="category-badge" id="previewCategory"><?php echo htmlspecialchars(str_replace('_', ' ', $category ?: 'category'), ENT_QUOTES, 'UTF-8'); ?></span>
                            </span>
                        </div>
                        
                        <div class="admin-badge" style="margin: 10px 0;">👑 ADMIN</div>
                        
                        <p style="color: #7f8c8d; margin: 10px 0;">
                            <strong>Short Description:</strong><br>
                            <span id="previewShortDesc"><?php echo htmlspecialchars($short_description ?: 'Short description will appear here', ENT_QUOTES, 'UTF-8'); ?></span>
                        </p>
                    </div>
                </div>
                
                <form method="POST" action="" id="editForm">
                    <!-- CSRF Protection -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <label for="name">Package Name *</label>
                        <input type="text" name="name" id="name" 
                                            </div>

                                            <?php
                                                // Show active toggle if column exists
                                                $colCheck = $pdo->query("SHOW COLUMNS FROM packages LIKE 'is_active'");
                                                if ($colCheck->rowCount() > 0):
                                            ?>
                                            <div class="form-group">
                                                <label for="is_active">Active</label>
                                                <select name="is_active" id="is_active">
                                                    <option value="1" <?php echo $is_active ? 'selected' : ''; ?>>Active (visible to users)</option>
                                                    <option value="0" <?php echo !$is_active ? 'selected' : ''; ?>>Inactive (hidden from users)</option>
                                                </select>
                                            </div>
                                            <?php endif; ?>
                               value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="e.g., 30-Day Weight Loss Challenge"
                               class="<?php echo isset($errors['name']) ? 'error-input' : ''; ?>"
                               oninput="updatePreview()">
                        <?php if (isset($errors['name'])): ?>
                            <span class="error-message">⚠️ <?php echo htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Short Description (Public) -->
                    <div class="form-group">
                        <label for="short_description">Short Description (Public) *</label>
                        <input type="text" name="short_description" id="short_description" 
                            value="<?php echo htmlspecialchars($short_description, ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Brief overview shown to everyone (e.g., '30-day weight loss program')"
                            class="<?php echo isset($errors['short_description']) ? 'error-input' : ''; ?>"
                            oninput="updatePreview()">
                        <?php if (isset($errors['short_description'])): ?>
                            <span class="error-message">⚠️ <?php echo htmlspecialchars($errors['short_description'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="description">Full Workout Plan (After Purchase) *</label>
                        <textarea name="description" id="description" rows="8"
                                placeholder="COMPLETE workout details - ONLY visible after purchase (include exercises, sets, reps, schedule)"
                                class="<?php echo isset($errors['description']) ? 'error-input' : ''; ?>"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="error-message">⚠️ <?php echo htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                        <div class="info-text">
                            💡 This content is ONLY visible to users who have purchased the package and admins.
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price (Rs.) *</label>
                            <input type="number" name="price" id="price" step="0.01"
                                   value="<?php echo htmlspecialchars($price, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="2999.00"
                                   class="<?php echo isset($errors['price']) ? 'error-input' : ''; ?>"
                                   oninput="updatePreview()">
                            <?php if (isset($errors['price'])): ?>
                                <span class="error-message">⚠️ <?php echo htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration">Duration *</label>
                            <input type="text" name="duration" id="duration"
                                   value="<?php echo htmlspecialchars($duration, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="e.g., 30 days, 12 weeks"
                                   class="<?php echo isset($errors['duration']) ? 'error-input' : ''; ?>"
                                   oninput="updatePreview()">
                            <?php if (isset($errors['duration'])): ?>
                                <span class="error-message">⚠️ <?php echo htmlspecialchars($errors['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select name="category" id="category" 
                                class="<?php echo isset($errors['category']) ? 'error-input' : ''; ?>"
                                onchange="updatePreview()">
                            <option value="">Select Category</option>
                            <option value="weight_loss" <?php echo $category == 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                            <option value="muscle_building" <?php echo $category == 'muscle_building' ? 'selected' : ''; ?>>Muscle Building</option>
                            <option value="strength" <?php echo $category == 'strength' ? 'selected' : ''; ?>>Strength Training</option>
                            <option value="yoga" <?php echo $category == 'yoga' ? 'selected' : ''; ?>>Yoga & Flexibility</option>
                            <option value="cardio" <?php echo $category == 'cardio' ? 'selected' : ''; ?>>Cardio & Endurance</option>
                            <option value="beginner" <?php echo $category == 'beginner' ? 'selected' : ''; ?>>Beginner Program</option>
                            <option value="advanced" <?php echo $category == 'advanced' ? 'selected' : ''; ?>>Advanced Training</option>
                        </select>
                        <?php if (isset($errors['category'])): ?>
                            <span class="error-message">⚠️ <?php echo htmlspecialchars($errors['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn-save">💾 Save Changes</button>
                        <a href="manage-packages.php" class="btn-cancel">← Cancel</a>
                        <a href="manage-packages.php?delete=<?php echo $id; ?>" 
                           class="btn-delete"
                           onclick="return confirm('Are you sure you want to delete this package?\n\nPackage: <?php echo addslashes($package['name']); ?>\n\nThis action cannot be undone!')">🗑️ Delete</a>
                    </div>
                </form>
                
                <div style="margin-top: 20px; text-align: right;">
                    <a href="package-details.php?id=<?php echo $id; ?>" target="_blank" style="color: #3498db; text-decoration: none; font-size: 0.9rem;">
                        👁️ View live package page →
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Live preview update function
    function updatePreview() {
        const name = document.getElementById('name').value || 'Package Name';
        const price = document.getElementById('price').value || '0.00';
        const duration = document.getElementById('duration').value || 'Duration';
        const shortDesc = document.getElementById('short_description').value || 'Short description will appear here';
        
        const categorySelect = document.getElementById('category');
        const categoryText = categorySelect.options[categorySelect.selectedIndex]?.text || 'category';
        
        document.getElementById('previewName').textContent = name;
        document.getElementById('previewPrice').textContent = 'Rs. ' + price;
        document.getElementById('previewDuration').textContent = duration;
        document.getElementById('previewCategory').textContent = categoryText.toLowerCase();
        document.getElementById('previewShortDesc').textContent = shortDesc;
    }
    
    // Real-time validation
    document.getElementById('price').addEventListener('input', function(e) {
        if (this.value < 0) this.value = 0;
    });
    
    // Auto-resize textarea
    const textarea = document.getElementById('description');
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    // Trigger on load
    window.addEventListener('load', function() {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    });
    </script>
</body>
</html>