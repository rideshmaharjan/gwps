<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');  // FIXED PATH
    exit();
}

// Redirect to user dashboard if not admin
if ($_SESSION['role'] != 'admin') {
    header('Location: ../user/dashboard.php');  // FIXED PATH
    exit();
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
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Admin Navigation -->
    <?php include 'admin-nav.php'; ?>

    <div class="admin-dashboard">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p>Administrator Dashboard</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-number"><?php echo $total_packages; ?></div>
                <div class="stat-label">Packages</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number"><?php echo $total_purchases; ?></div>
                <div class="stat-label">Total Sales</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏷️</div>
                <div class="stat-number">Rs. <?php echo number_format($total_revenue, 2); ?></div>
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
            
            <a href="../index.php" class="action-card">
                <h3>🌐 View Website</h3>
                <p>See how users see the site</p>
            </a>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👑</div>
            <div class="stat-number"><?php echo $admin_count; ?></div>
            <div class="stat-label">Admins</div>
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
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
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
</body>
</html>