<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../includes/database.php';
$user_id = $_SESSION['user_id'];

// Get user's full name and email from database
$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get purchase count
$purchase_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM purchases WHERE user_id = ?");
$purchase_stmt->execute([$user_id]);
$purchase_count = $purchase_stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Dashboard - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
      <?php include 'user-nav.php'; ?>

    <div class="dashboard-container">
        <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
        
        <div class="user-info">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Member Since:</strong> 
                <?php 
                    $member_date = date('F Y', strtotime($_SESSION['created_at'] ?? 'now'));
                    echo $member_date;
                ?>
            </p>
            <p><strong>Packages Purchased:</strong> <?php echo $purchase_count; ?></p>
        </div>
        
        <div class="dashboard-sections">
            <div class="dashboard-card">
                <h3>📦 My Packages</h3>
                <p>View your purchased workout packages</p>
                <a href="my-packages.php" class="btn-primary">View Packages</a>
            </div>
            
            <div class="dashboard-card">
                <h3>🛒 Buy New Package</h3>
                <p>Browse and purchase available workout plans</p>
                <a href="../public/packages.php" class="btn-primary">Browse Packages</a>
            </div>
            
            <div class="dashboard-card">
                <h3>👤 Account</h3>
                <p>Manage your account settings</p>
                <a href="logout.php" class="btn-primary">Logout</a>
            </div>
        </div>
    </div>

    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>