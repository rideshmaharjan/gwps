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
    <style>
        /* ========== COMPLETE PACKAGES PAGE STYLING ========== */
        
        * {
            box-sizing: border-box;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .container h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        /* ========== SEARCH SECTION ========== */
        .search-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .search-group, .category-group {
            flex: 1;
            min-width: 200px;
        }
        
        .search-group label, .category-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        select.search-input {
            cursor: pointer;
        }
        
        .search-btn, .clear-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .search-btn {
            background: #3498db;
            color: white;
        }
        
        .search-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .clear-btn {
            background: #95a5a6;
            color: white;
        }
        
        .clear-btn:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        
        .search-stats {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        /* ========== PACKAGES GRID ========== */
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .no-results {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 12px;
            color: #7f8c8d;
            grid-column: 1/-1;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .reset-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-top: 15px;
        }
        
        .reset-link:hover {
            text-decoration: underline;
        }
        
        /* ========== PACKAGE CARD STYLING ========== */
        .package-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: visible !important;
            display: flex;
            flex-direction: column;
        }
        
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .package-card h3 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 15px 0 10px;
            font-weight: 600;
        }
        
        .package-card .price {
            font-size: 2rem;
            font-weight: 700;
            color: #3498db;
            margin: 10px 0;
        }
        
        .package-card .workout-details {
            color: #7f8c8d;
            margin: 15px 0;
            line-height: 1.6;
            flex-grow: 1;
        }
        
        .package-card .workout-details strong {
            color: #2c3e50;
        }
        
        .package-card .workout-details p {
            margin: 8px 0;
        }
        
        /* Full workout preview */
        .full-workout-preview {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #3498db;
            text-align: left;
            max-height: 200px;
            overflow-y: auto;
            font-size: 0.9rem;
            color: #2c3e50;
            white-space: pre-line;
        }
        
        .full-workout-preview h4 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1rem;
            font-weight: 600;
        }
        
        /* Category badge */
        .level-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        /* Admin badge */
        .admin-badge {
            background: #f39c12;
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            display: inline-block;
            margin: 10px 0;
            font-weight: 600;
        }
        
        /* ========== ACTION BUTTONS CONTAINER ========== */
        .package-card .action-buttons {
            display: flex !important;
            gap: 12px !important;
            margin-top: 20px !important;
            justify-content: center !important;
            flex-wrap: wrap !important;
            padding: 20px 0 0 0 !important;
            overflow: visible !important;
            position: relative !important;
            z-index: 100 !important;
        }
        
        /* ========== ALL BUTTONS BASE STYLES ========== */
        .package-card .action-buttons a {
            border-radius: 25px !important;
            padding: 12px 24px !important;
            font-size: 1rem !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            display: inline-block !important;
            text-align: center !important;
            min-width: 140px !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2) !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 100 !important;
            height: auto !important;
            line-height: 1.5 !important;
            margin: 5px !important;
            white-space: nowrap !important;
            pointer-events: auto !important;
            clip: auto !important;
        }
        
        /* ========== VIEW DETAILS BUTTON - PURPLE ========== */
        .package-card .action-buttons a.btn-details {
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            color: white !important;
            visibility: visible !important;
            opacity: 1 !important;
            display: inline-block !important;
            position: relative !important;
            z-index: 100 !important;
            pointer-events: all !important;
        }
        
        .package-card .action-buttons a.btn-details:hover {
            background: linear-gradient(135deg, #764ba2, #667eea) !important;
            transform: translateY(-4px) scale(1.08) !important;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.5) !important;
        }
        
        /* ========== EDIT PACKAGE BUTTON - BLUE ========== */
        .package-card .action-buttons a.admin-view-btn,
        .package-card .action-buttons a[href*="edit"] {
            background: #3498db !important;
            color: white !important;
            visibility: visible !important;
            opacity: 1 !important;
            display: inline-block !important;
            position: relative !important;
            z-index: 100 !important;
            pointer-events: all !important;
        }
        
        .package-card .action-buttons a.admin-view-btn:hover,
        .package-card .action-buttons a[href*="edit"]:hover {
            background: #2980b9 !important;
            transform: translateY(-4px) scale(1.08) !important;
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.5) !important;
        }
        
        /* ========== FOOTER ========== */
        footer {
            text-align: center;
            padding: 30px;
            background: #2c3e50;
            color: white;
            margin-top: 50px;
        }
        
        footer a {
            color: #3498db;
            text-decoration: none;
        }
        
        footer a:hover {
            text-decoration: underline;
        }
        
        /* ========== MOBILE RESPONSIVE ========== */
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .search-group, .category-group {
                width: 100%;
            }
            
            .packages-grid {
                grid-template-columns: 1fr;
            }
            
            .container h1 {
                font-size: 2rem;
            }
            
            .package-card {
                padding: 20px;
            }
            
            .package-card .action-buttons {
                flex-direction: row;
            }
            
            .package-card .action-buttons a {
                flex: 1;
                min-width: 100px;
            }
        }
        
        @media (max-width: 480px) {
            .container h1 {
                font-size: 1.5rem;
            }
            
            .subtitle {
                font-size: 0.95rem;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-input {
                font-size: 16px;
            }
            
            .package-card h3 {
                font-size: 1.2rem;
            }
            
            .package-card .price {
                font-size: 1.5rem;
            }
            
            .package-card .action-buttons {
                flex-direction: column;
            }
            
            .package-card .action-buttons a {
                width: 100%;
            }
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