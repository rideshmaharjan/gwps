<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');  // CORRECT PATH
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
    <!-- Admin Navigation -->
    <?php include 'admin-nav.php'; ?>
    
    <div class="manage-container">
        <div class="page-header">
            <h1>Manage Workout Packages</h1>
            <a href="add-package.php" class="btn-add">➕ Add New Package</a>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="success">Package deleted successfully!</div>
        <?php endif; ?>
        
        <div class="packages-table">
            <?php if (empty($packages)): ?>
                <div class="empty-state">
                    <p>No packages found.</p>
                    <a href="add-package.php" class="btn-primary">Add Your First Package</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Package Name</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Category</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $package): ?>
                            <tr>
                                <td>#<?php echo $package['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($package['name']); ?></strong><br>
                                    <small><?php echo substr(htmlspecialchars($package['description']), 0, 50); ?>...</small>
                                </td>
                                <td>Rs. <?php echo number_format($package['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($package['duration']); ?></td>
                                <td>
                                    <span class="category-badge"><?php echo htmlspecialchars($package['category']); ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($package['created_at'])); ?></td>
                                <td class="action-buttons">
                                    <a href="edit-package.php?id=<?php echo $package['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="?delete=<?php echo $package['id']; ?>" 
                                       class="btn-delete"
                                       onclick="return confirm('Are you sure you want to delete this package?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>