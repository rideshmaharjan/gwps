<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../includes/database.php';
$user_id = $_SESSION['user_id'];

// Get user's purchased packages
$stmt = $pdo->prepare("
    SELECT p.*, pur.purchase_date, pur.id as purchase_id
    FROM purchases pur
    JOIN packages p ON pur.package_id = p.id
    WHERE pur.user_id = ? AND pur.is_active = 1
    ORDER BY pur.purchase_date DESC
");
$stmt->execute([$user_id]);
$purchased_packages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Packages</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'user-nav.php'; ?>
    
    <div class="my-packages-container">
        <h1>My Purchased Packages</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (empty($purchased_packages)): ?>
            <div class="empty-state">
                <p>You haven't purchased any packages yet.</p>
                <a href="../public/packages.php" class="btn-browse">Browse Packages</a>
            </div>
        <?php else: ?>
            <div class="purchased-packages">
                <?php foreach ($purchased_packages as $package): ?>
                <div class="purchased-card">
                    <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                    <p><strong>Purchased:</strong> <?php echo date('M d, Y', strtotime($package['purchase_date'])); ?></p>
                    <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></p>
                    <p><?php echo htmlspecialchars(substr($package['short_description'], 0, 100)); ?>...</p>
                    <a href="../public/package-details.php?id=<?php echo $package['id']; ?>" class="btn-view">View Details</a>
                    <div style="margin-top: 10px;">
                        <a href="remove-package.php?id=<?php echo $package['purchase_id']; ?>" 
                                class="btn-danger"
                                onclick="return confirm('Remove this package from your profile?')">
                                Remove from My Profile
                        </a>
                    </div>
                </div>
                
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>