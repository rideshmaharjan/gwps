<?php
session_start();
require_once '../includes/database.php';

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Build query based on search
$sql = "SELECT * FROM packages WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR short_description LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

// Only show active packages if column exists
$col = $pdo->query("SHOW COLUMNS FROM packages LIKE 'is_active'");
if ($col->rowCount() > 0) {
    $sql .= " AND is_active = 1";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll();

// Get unique categories for filter
$categories = $pdo->query("SELECT DISTINCT category FROM packages WHERE category IS NOT NULL AND category != ''")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Workout Plans - FitLife Fitness</title>
    <link rel="stylesheet" href="../css/style.css">
    
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="container">
        <h1>Custom Workout Plans</h1>
        <p class="subtitle">Choose a workout and set it to your own pace.</p>
        
        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <div class="search-group">
                    <label for="search">Search Packages</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           class="search-input" 
                           placeholder="Search by name or description..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="category-group">
                    <label for="category">Filter by Category</label>
                    <select name="category" id="category" class="search-input">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                <?php echo $category == $cat ? 'selected' : ''; ?>>
                                <?php echo ucwords(str_replace('_', ' ', $cat)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="search-btn">
                    Search
                </button>
                
                <?php if (!empty($search) || !empty($category)): ?>
                    <a href="packages.php" class="clear-btn">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Search Results Stats -->
        <?php if (!empty($search) || !empty($category)): ?>
            <div class="search-stats">
                <strong><?php echo count($packages); ?></strong> packages found 
                <?php if (!empty($search)): ?>
                    matching "<strong><?php echo htmlspecialchars($search); ?></strong>"
                <?php endif; ?>
                <?php if (!empty($category)): ?>
                    in category "<strong><?php echo ucwords(str_replace('_', ' ', $category)); ?></strong>"
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="packages-grid">
            <?php if (empty($packages)): ?>
                <div class="no-results">
                    <p>No packages found matching your criteria.</p>
                    <a href="packages.php" class="reset-link">View all packages</a>
                </div>
            <?php else: ?>
                <?php foreach ($packages as $package): ?>
                <div class="package-card">
                    <div class="level-badge">
                        <?php echo htmlspecialchars($package['category'] ?? 'General'); ?>
                    </div>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <div class="admin-badge">ADMIN</div>
                    <?php endif; ?>
                    
                    <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                    <p class="price">Rs. <?php echo number_format($package['price'], 2); ?></p>
                    
                    <div class="workout-details">
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></p>
                        <p><strong>Description:</strong></p>
                        <p><?php echo htmlspecialchars(substr($package['short_description'] ?? $package['description'], 0, 100)); ?>...</p>
                    </div>
                    
                    <!-- ACTION BUTTONS - BOTH ALWAYS VISIBLE -->
                    <div class="action-buttons" style="display: flex !important; gap: 12px !important; margin-top: 20px !important; justify-content: center !important; flex-wrap: wrap !important; overflow: visible !important; z-index: 100 !important;">
                        <!-- View Details Button - ALWAYS VISIBLE -->
                        <a href="package-details.php?id=<?php echo $package['id']; ?>" class="btn-details" style="background: linear-gradient(135deg, #667eea, #764ba2) !important; color: white !important; padding: 12px 24px !important; border-radius: 25px !important; font-size: 1rem !important; font-weight: 700 !important; text-decoration: none !important; display: inline-block !important; border: none !important; cursor: pointer !important; box-shadow: 0 3px 8px rgba(0,0,0,0.2) !important; visibility: visible !important; opacity: 1 !important; z-index: 100 !important; transition: all 0.3s ease !important; white-space: nowrap !important;">View Details</a>
                        
                        <!-- Buy Package Button - Only for logged-in non-admin users -->
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] != 'admin'): ?>
                            <a href="../user/buy-package.php?id=<?php echo $package['id']; ?>" class="buy-btn" style="background: linear-gradient(135deg, #27ae60, #229954) !important; color: white !important; padding: 12px 24px !important; border-radius: 25px !important; font-size: 1rem !important; font-weight: 700 !important; text-decoration: none !important; display: inline-block !important; border: none !important; cursor: pointer !important; box-shadow: 0 3px 8px rgba(0,0,0,0.2) !important; visibility: visible !important; opacity: 1 !important; z-index: 100 !important; transition: all 0.3s ease !important; white-space: nowrap !important;">Buy Package</a>
                        <?php endif; ?>
                        
                        <!-- Edit Package Button - Only for Admin -->
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
                            <a href="../admin/edit-package.php?id=<?php echo $package['id']; ?>" class="admin-view-btn" style="background: #3498db !important; color: white !important; padding: 12px 24px !important; border-radius: 25px !important; font-size: 1rem !important; font-weight: 700 !important; text-decoration: none !important; display: inline-block !important; border: none !important; cursor: pointer !important; box-shadow: 0 3px 8px rgba(0,0,0,0.2) !important; visibility: visible !important; opacity: 1 !important; z-index: 100 !important; transition: all 0.3s ease !important; white-space: nowrap !important;">Edit Package</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>FitLife Fitness &copy; 2025 | <a href="about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>