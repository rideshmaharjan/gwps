<?php
session_start();
require_once '../includes/database.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;

$packages = [];
$categories = ['weight_loss', 'strength', 'muscle_building', 'yoga', 'cardio', 'beginner', 'advanced'];

if (!empty($search) || !empty($category) || $min_price > 0 || $max_price < 999999) {
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
    
    if ($min_price > 0) {
        $sql .= " AND price >= ?";
        $params[] = $min_price;
    }
    
    if ($max_price < 999999) {
        $sql .= " AND price <= ?";
        $params[] = $max_price;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $packages = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Packages - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .search-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .search-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .price-range {
            display: flex;
            gap: 10px;
        }
        .search-stats {
            margin-bottom: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="search-container">
        <h1>Search Workout Packages</h1>
        
        <div class="search-form">
            <form method="GET" action="">
                <div class="search-grid">
                    <div class="form-group">
                        <label>Search</label>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Package name or description...">
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                    <?php echo ucwords(str_replace('_', ' ', $cat)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Price Range</label>
                        <div class="price-range">
                            <input type="number" name="min_price" value="<?php echo $min_price > 0 ? $min_price : ''; ?>" 
                                   placeholder="Min" step="100">
                            <input type="number" name="max_price" value="<?php echo $max_price < 999999 ? $max_price : ''; ?>" 
                                   placeholder="Max" step="100">
                        </div>
                    </div>
                    
                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn-primary" style="width: 100%;">Search</button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if (!empty($search) || !empty($category) || $min_price > 0 || $max_price < 999999): ?>
            <div class="search-stats">
                Found <strong><?php echo count($packages); ?></strong> packages
                <?php if (!empty($search)): ?>
                    matching "<?php echo htmlspecialchars($search); ?>"
                <?php endif; ?>
            </div>
            
            <div class="packages-grid">
                <?php if (empty($packages)): ?>
                    <p>No packages found matching your criteria.</p>
                <?php else: ?>
                    <?php foreach ($packages as $package): ?>
                        <div class="package-card">
                            <div class="level-badge"><?php echo htmlspecialchars($package['category'] ?? 'General'); ?></div>
                            <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                            <p class="price">Rs. <?php echo number_format($package['price'], 2); ?></p>
                            <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></p>
                            <p><?php echo htmlspecialchars(substr($package['short_description'] ?? '', 0, 100)); ?>...</p>
                            <a href="package-details.php?id=<?php echo $package['id']; ?>" class="btn-details">View Details</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>