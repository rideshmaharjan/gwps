<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Handle mark as completed
if (isset($_GET['complete']) && is_numeric($_GET['complete'])) {
    $purchase_id = $_GET['complete'];
    
    $check = $pdo->prepare("SELECT id FROM purchases WHERE id = ? AND user_id = ?");
    $check->execute([$purchase_id, $_SESSION['user_id']]);
    
    if ($check->fetch()) {
        $update = $pdo->prepare("UPDATE purchases SET status = 'completed', completed_at = NOW() WHERE id = ?");
        $update->execute([$purchase_id]);
        
        $_SESSION['success'] = "Package marked as completed! 🎉 Congratulations!";
    }
    header('Location: my-packages.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's purchased packages - SIMPLE QUERY (no refund fields)
$stmt = $pdo->prepare("
    SELECT p.*, 
           pur.purchase_date, 
           pur.id as purchase_id, 
           pur.status, 
           pur.completed_at,
           pur.is_active
    FROM purchases pur
    JOIN packages p ON pur.package_id = p.id
    WHERE pur.user_id = ? 
    ORDER BY pur.purchase_date DESC
");
$stmt->execute([$user_id]);
$purchased_packages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Packages - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="my-packages-container">
        <h1>📦 My Purchased Packages</h1>
        <p class="subtitle">Track your fitness journey</p>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if (empty($purchased_packages)): ?>
            <div class="empty-state">
                <p>You haven't purchased any packages yet.</p>
                <a href="../public/packages.php" class="btn-browse">Browse Packages</a>
            </div>
        <?php else: ?>
            <div class="purchased-packages">
                <?php foreach ($purchased_packages as $package): 
                    $icon = '💪';
                    switch($package['category']) {
                        case 'weight_loss': $icon = '⚖️'; break;
                        case 'muscle_building': $icon = '💪'; break;
                        case 'strength': $icon = '🏋️'; break;
                        case 'yoga': $icon = '🧘'; break;
                        case 'cardio': $icon = '🏃'; break;
                        case 'beginner': $icon = '🌱'; break;
                        case 'advanced': $icon = '🔥'; break;
                    }
                    
                    $is_completed = !empty($package['completed_at']) || $package['status'] == 'completed';
                ?>
                <div class="purchased-card <?php echo !$package['is_active'] ? 'inactive' : ''; ?>">
                    <?php if ($is_completed): ?>
                        <div class="completed-badge">✅ Completed</div>
                    <?php endif; ?>
                    
                    <?php if (!$package['is_active']): ?>
                        <div class="completed-badge" style="background: #95a5a6;">🗑️ Removed</div>
                    <?php endif; ?>
                    
                    <div class="package-icon"><?php echo $icon; ?></div>
                    <h3><?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    
                    <p class="purchase-date">
                        Purchased: <?php echo date('M d, Y', strtotime($package['purchase_date'])); ?>
                    </p>
                    
                    <p class="duration">
                        <strong>Duration:</strong> <?php echo htmlspecialchars($package['duration'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    
                    <p class="description">
                        <?php 
                        $desc = $package['short_description'] ?? $package['description'];
                        echo htmlspecialchars(substr($desc, 0, 100), ENT_QUOTES, 'UTF-8');
                        if (strlen($desc) > 100) echo '...';
                        ?>
                    </p>
                    
                    <?php if ($is_completed && !empty($package['completed_at'])): ?>
                        <div class="completed-date">
                            Completed on: <?php echo date('M d, Y', strtotime($package['completed_at'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <a href="../public/package-details.php?id=<?php echo $package['id']; ?>" class="btn-view">
                            View Details
                        </a>
                        
                        <?php if (!$is_completed && $package['is_active']): ?>
                            <a href="?complete=<?php echo $package['purchase_id']; ?>" 
                               class="btn-complete"
                               onclick="return confirm('Mark this package as completed?')">
                                ✅ Mark Complete
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>