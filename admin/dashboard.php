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

// Get statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
    $total_users = $stmt->fetch()['total_users'];
    
    // Total packages
    $stmt = $pdo->query("SELECT COUNT(*) as total_packages FROM packages");
    $total_packages = $stmt->fetch()['total_packages'];
    
    // Total purchases
    $stmt = $pdo->query("SELECT COUNT(*) as total_purchases FROM purchases");
    $total_purchases = $stmt->fetch()['total_purchases'];
    
    // Total revenue
    $stmt = $pdo->query("SELECT SUM(amount) as total_revenue FROM purchases");
    $total_revenue = $stmt->fetch()['total_revenue'] ?? 0;
    
    // Recent users (last 5)
    $stmt = $pdo->query("SELECT full_name, email, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
    $recent_users = $stmt->fetchAll();
    
    // Get admin count
    $admin_stmt = $pdo->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
    $admin_count = $admin_stmt->fetch()['admin_count'];
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log("Admin dashboard error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    // USE THE UNIFIED NAVIGATION - THIS FIXES THE ERROR
    $base_path = '../';
    include '../includes/navigation.php';
    ?>

    <div class="admin-dashboard">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?>!</h1>
            <p>Administrator Dashboard</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo $total_users ?? 0; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-number"><?php echo $total_packages ?? 0; ?></div>
                <div class="stat-label">Packages</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number"><?php echo $total_purchases ?? 0; ?></div>
                <div class="stat-label">Total Sales</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏷️</div>
                <div class="stat-number">Rs. <?php echo number_format($total_revenue ?? 0, 2); ?></div>
                <div class="stat-label">Revenue</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <h2>Quick Actions</h2>
        <div class="action-grid">
            <a href="add-package.php" class="action-card">
                <h3>➕ Add New Package</h3>
                <p>Create new workout package</p>
            </a>
            
            <a href="manage-packages.php" class="action-card">
                <h3>📦 Manage Packages</h3>
                <p>Edit or delete packages</p>
            </a>
            
            
            
            <a href="view-purchases.php" class="action-card">
                <h3>💰 View Purchases</h3>
                <p>See all customer purchases</p>
            </a>
            
            <a href="manage-users.php" class="action-card">
                <h3>👥 Manage Users</h3>
                <p>Update user roles</p>
            </a>
        </div>
        
        <!-- Admin Stats Card -->
        <div class="stats-grid" style="margin-top: 20px;">
            <div class="stat-card">
                <div class="stat-icon">👑</div>
                <div class="stat-number"><?php echo $admin_count ?? 1; ?></div>
                <div class="stat-label">Admins</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo date('M Y'); ?></div>
                <div class="stat-label">Current Month</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🔄</div>
                <div class="stat-number">
                    <?php
                    // Get today's purchases
                    $today_stmt = $pdo->query("SELECT COUNT(*) as today_count FROM purchases WHERE DATE(purchase_date) = CURDATE()");
                    echo $today_stmt->fetch()['today_count'] ?? 0;
                    ?>
                </div>
                <div class="stat-label">Today's Purchases</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💪</div>
                <div class="stat-number">
                    <?php
                    // Get most popular package
                    $popular_stmt = $pdo->query("
                        SELECT package_id, COUNT(*) as count 
                        FROM purchases 
                        GROUP BY package_id 
                        ORDER BY count DESC 
                        LIMIT 1
                    ");
                    $popular = $popular_stmt->fetch();
                    echo $popular ? $popular['count'] : 0;
                    ?>
                </div>
                <div class="stat-label">Most Purchased</div>
            </div>
        </div>
        
        <!-- Recent Users Table -->
        <div class="recent-section">
            <h2>Recent Users</h2>
            <?php if (!empty($recent_users)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users registered yet.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>