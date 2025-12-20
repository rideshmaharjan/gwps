<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Redirect to user dashboard if not admin
if ($_SESSION['role'] != 'admin') {
    header('Location: ../user/dashboard.php');
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
    
    // Recent users (last 5)
    $stmt = $pdo->query("SELECT full_name, email, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
    $recent_users = $stmt->fetchAll();
    
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
    <nav class="admin-nav">
        <div class="logo" style="color: white; font-size: 1.2rem;">
            FitLife Gym Admin
        </div>
        <div class="admin-nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="manage-packages.php">Manage Packages</a>
            <a href="view-users.php">Users</a>
            <a href="../index.php">View Site</a>
            <a href="../user/logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="admin-dashboard">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p>Last login: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Users</div>
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Registered Members</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Total Packages</div>
                <div class="stat-number"><?php echo $total_packages; ?></div>
                <div class="stat-label">Active Workout Plans</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Admin</div>
                <div class="stat-number"><?php echo $_SESSION['user_name']; ?></div>
                <div class="stat-label">Logged in as Administrator</div>
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
            
            <a href="view-users.php" class="action-card">
                <h3>👥 View Users</h3>
                <p>See all registered users</p>
            </a>
            
            <a href="../index.php" class="action-card">
                <h3>🌐 View Website</h3>
                <p>See how users see the site</p>
            </a>
        </div>
        
        <!-- Recent Users Table -->
        <div class="recent-users">
            <h2>Recent Users</h2>
            <?php if (!empty($recent_users)): ?>
                <table class="user-table">
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
        
        <!-- Quick Info -->
        <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
            <h3>System Information</h3>
            <p>Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>PHP Version: <?php echo phpversion(); ?></p>
            <p>Logged in as: <?php echo $_SESSION['user_email']; ?> (Administrator)</p>
        </div>
    </div>
</body>
</html>