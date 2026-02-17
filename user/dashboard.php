<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';
$user_id = $_SESSION['user_id'];

// Get user's full name and email from database
$stmt = $pdo->prepare("SELECT full_name, email, phone, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get active packages count
$active_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM purchases WHERE user_id = ? AND is_active = 1 AND (status != 'completed' OR status IS NULL)");
$active_stmt->execute([$user_id]);
$active_count = $active_stmt->fetch()['count'];

// Get completed programs count
$completed_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM purchases WHERE user_id = ? AND is_active = 1 AND status = 'completed'");
$completed_stmt->execute([$user_id]);
$completed_count = $completed_stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Dashboard - GWPS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>

    <div class="dashboard-container">
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?>! 🚀</h1>
            <p>Here's what's happening with your fitness journey</p>
        </div>
        
        <!-- User Info Card -->
        <div class="user-info-card">
            <div class="info-item">
                <span class="info-icon">📧</span>
                <div class="info-content">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
            
            <div class="info-item">
                <span class="info-icon">📅</span>
                <div class="info-content">
                    <span class="info-label">Member Since</span>
                    <span class="info-value"><?php echo date('F Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
            
            <div class="info-item">
                <span class="info-icon">📱</span>
                <div class="info-content">
                    <span class="info-label">Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-icon">📦</div>
                <div class="stat-number"><?php echo $active_count; ?></div>
                <div class="stat-label">Active Packages</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $completed_count; ?></div>
                <div class="stat-label">Completed Programs</div>
            </div>
        </div>
        
        <!-- Main Dashboard Sections -->
        <div class="dashboard-sections">
            <!-- My Packages Card -->
            <div class="dashboard-card">
                <div class="card-icon">📦</div>
                <h3>My Packages</h3>
                <p>View your purchased workout packages</p>
                <a href="my-packages.php" class="btn-primary">View Packages →</a>
            </div>
            
            <!-- Buy New Package Card -->
            <div class="dashboard-card">
                <div class="card-icon">🛒</div>
                <h3>Buy New Package</h3>
                <p>Browse and purchase available workout packages</p>
                <a href="../public/packages.php" class="btn-primary">Browse Packages →</a>
            </div>
            
            <!-- Account Settings Card -->
            <div class="dashboard-card">
                <div class="card-icon">⚙️</div>
                <h3>Account Settings</h3>
                <p>Update your profile, change password, and manage preferences</p>
                <a href="profile-settings.php" class="btn-primary btn-settings">Settings →</a>
            </div>
        </div>
    </div>

    <footer>
        <p>GWPS &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>