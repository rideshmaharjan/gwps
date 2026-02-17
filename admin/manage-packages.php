<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/pagination.php';

// ... existing authentication code ...

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;

// Get total count
$count_stmt = $pdo->query("SELECT COUNT(*) FROM packages");
$total_packages = $count_stmt->fetchColumn();

// Create pagination object
$pagination = new Pagination($total_packages, $per_page, $page);

// Get paginated packages
$stmt = $pdo->prepare("
    SELECT * FROM packages 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $pagination->getLimit(), PDO::PARAM_INT);
$stmt->bindValue(2, $pagination->getOffset(), PDO::PARAM_INT);
$stmt->execute();
$packages = $stmt->fetchAll();

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

// Handle session messages
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Handle package deletion (simple delete - client-side will prompt/stop if active purchases exist)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Package deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }

    header('Location: manage-packages.php');
    exit();
}

// (Deactivate toggle removed) 

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
    <?php
$base_path = '../';
include '../includes/navigation.php';
?>
    
    <div class="manage-container">
        <div class="page-header">
            <h1>Manage Workout Packages</h1>
            <a href="add-package.php" class="btn-add">➕ Add New Package</a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php echo $pagination->render('manage-packages.php?page={page}'); ?>
        
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
                                    <strong><?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small><?php echo htmlspecialchars(substr($package['short_description'] ?? $package['description'], 0, 50), ENT_QUOTES, 'UTF-8'); ?>...</small>
                                </td>
                                <td>Rs. <?php echo number_format($package['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($package['duration'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="category-badge"><?php echo htmlspecialchars($package['category'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($package['created_at'])); ?></td>
                                <td class="action-buttons">
                                    <a href="edit-package.php?id=<?php echo $package['id']; ?>" class="btn-edit">Edit</a>
                                    <!-- Deactivate button removed per request -->
                                    <?php
                                         $active_check = $pdo->prepare("SELECT COUNT(*) as count FROM purchases WHERE package_id = ? AND is_active = 1");
                                         $active_check->execute([$package['id']]);
                                         $active_count = (int)$active_check->fetch()['count'];
                                    ?>
                                    <a href="?delete=<?php echo $package['id']; ?>" 
                                        class="btn-delete"
                                        onclick="if(<?php echo $active_count; ?> > 0){alert('Delete failed: This package has <?php echo $active_count; ?> active purchase(s).'); return false;} return confirm('Are you sure you want to delete this package? This action cannot be undone.');">Delete</a>
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