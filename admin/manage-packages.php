<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';

// Handle package deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: manage-packages.php');
    exit();
}

// Get all packages
$stmt = $pdo->query("SELECT * FROM packages ORDER BY created_at DESC");
$packages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Packages - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    
    <div class="manage-container">
        <h1>Manage Workout Packages</h1>
        
        <a href="add-package.php" class="add-btn">➕ Add New Package</a>
        
        <div class="package-list">
            <?php if (empty($packages)): ?>
                <p>No packages found. <a href="add-package.php">Add your first package</a></p>
            <?php else: ?>
                <?php foreach ($packages as $package): ?>
                    <div class="package-item">
                        <div class="package-info">
                            <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                            <p>Price: Rs. <?php echo $package['price']; ?> | Duration: <?php echo $package['duration']; ?></p>
                            <small>Created: <?php echo date('M d, Y', strtotime($package['created_at'])); ?></small>
                        </div>
                        <div class="package-actions">
                            <a href="edit-package.php?id=<?php echo $package['id']; ?>" class="edit-btn">Edit</a>
                            <a href="?delete=<?php echo $package['id']; ?>" 
                               class="delete-btn"
                               onclick="return confirm('Delete this package?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>