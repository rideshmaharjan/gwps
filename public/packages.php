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
    <style>
        .admin-badge {
            background: #f39c12;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-left: 8px;
            font-weight: normal;
        }
        
        .admin-view-btn {
            background: #9b59b6;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .admin-view-btn:hover {
            background: #8e44ad;
            transform: translateY(-2px);
        }
        
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        /* Search Bar Styles */
        .search-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .search-group {
            flex: 2;
            min-width: 250px;
        }
        
        .category-group {
            flex: 1;
            min-width: 180px;
        }
        
        .search-group label,
        .category-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        
        .search-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            height: 48px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .search-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52,152,219,0.3);
        }
        
        .clear-btn {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            height: 48px;
        }
        
        .clear-btn:hover {
            background: #7f8c8d;
        }
        
        .search-stats {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            grid-column: 1/-1;
        }
        
        .no-results p {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        
        .reset-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }
        
        .reset-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="container">
        <h1>Custom Workout Plans</h1>
        <p class="subtitle">Choose a workout routine tailored to your fitness goals</p>
        
        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <div class="search-group">
                    <label for="search">🔍 Search Packages</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           class="search-input" 
                           placeholder="Search by name or description..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="category-group">
                    <label for="category">📁 Filter by Category</label>
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
                    <span>🔍</span> Search
                </button>
                
                <?php if (!empty($search) || !empty($category)): ?>
                    <a href="packages.php" class="clear-btn">✕ Clear</a>
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
                    <p>😕 No packages found matching your criteria.</p>
                    <a href="packages.php" class="reset-link">View all packages</a>
                </div>
            <?php else: ?>
                <?php foreach ($packages as $package): ?>
                <div class="package-card">
                    <div class="level-badge">
                        <?php echo htmlspecialchars($package['category'] ?? 'General'); ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <span class="admin-badge">Admin</span>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                    <p class="price">Rs. <?php echo number_format($package['price'], 2); ?></p>
                    
                    <div class="workout-details">
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></p>
                        <p><strong>Description:</strong></p>
                        <p><?php echo htmlspecialchars(substr($package['short_description'] ?? $package['description'], 0, 50)); ?>...</p>
                    </div>
                    
                    <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
                        <a href="package-details.php?id=<?php echo $package['id']; ?>" class="btn-details">View Details</a>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                <!-- Admin sees Edit button instead of Buy Now -->
                                <a href="../admin/edit-package.php?id=<?php echo $package['id']; ?>" class="admin-view-btn">Edit Package</a>
                            <?php else: ?>
                                <!-- Regular user sees Buy Now -->
                                <a href="../user/buy-package.php?id=<?php echo $package['id']; ?>" class="btn-book">Buy Now</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Not logged in sees Login to Purchase -->
                            <a href="../login.php" class="btn-book">Login to Purchase</a>
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