<?php
session_start();
require_once '../includes/database.php';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: packages.php');
    exit();
}

// Fetch package and ensure active if column exists
$col = $pdo->query("SHOW COLUMNS FROM packages LIKE 'is_active'");
if ($col->rowCount() > 0) {
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ? AND is_active = 1");
} else {
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
}
$stmt->execute([$id]);
$package = $stmt->fetch();

if (!$package) {
    // If package missing or inactive, show a friendly message
    $_SESSION['error'] = 'Package not available';
    header('Location: packages.php');
    exit();
}

// Check if user has purchased OR if user is admin
$can_view_full_plan = false;
if (isset($_SESSION['user_id'])) {
    // Check if user is admin
    if ($_SESSION['role'] == 'admin') {
        $can_view_full_plan = true; // Admin can view all packages without purchase
    } else {
        // Check if user has purchased
        $check_stmt = $pdo->prepare("SELECT id FROM purchases WHERE user_id = ? AND package_id = ? AND is_active = 1");
        $check_stmt->execute([$_SESSION['user_id'], $package['id']]);
        $can_view_full_plan = $check_stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?> - Details</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>

    <div class="container">
        <h1><?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        
        <div class="package-detail-card">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <div class="admin-badge">👑 Admin Access - Full Preview</div>
            <?php endif; ?>
            
            <div class="price-tag">Rs. <?php echo number_format($package['price'], 2); ?></div>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($package['category'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?></p>
            
            <div class="description">
                <h3>Package Overview</h3>
                <p><strong>What's included:</strong> <?php echo htmlspecialchars($package['short_description'] ?? $package['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                
                <?php if ($can_view_full_plan): ?>
                    <!-- SHOW FULL WORKOUT PLAN to admins and purchasers -->
                    <div class="full-workout-plan">
                        <h4>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                👑 Admin Access - Complete Workout Plan:
                            <?php else: ?>
                                Your Complete Workout Plan:
                            <?php endif; ?>
                        </h4>
                        <div class="workout-content">
                            <?php echo nl2br(htmlspecialchars($package['description'], ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- HIDE full plan from non-purchasers -->
                    <div class="purchase-required">
                        <h4>🔒 Full Workout Plan Locked</h4>
                        <p>Purchase this package to unlock the complete workout plan including:</p>
                        <ul>
                            <li>Detailed exercise instructions</li>
                            <li>Weekly schedule</li>
                            <li>Sets & reps guidance</li>
                            <li>Progress tracking</li>
                        </ul>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Logged in but hasn't purchased -->
                            <a href="../user/buy-package.php?id=<?php echo $package['id']; ?>" class="btn-book">Buy Now to Unlock</a>
                        <?php else: ?>
                            <!-- Not logged in -->
                            <a href="../user/register.php" class="btn-register">Register to Purchase</a>
                            <a href="../login.php" class="btn-login">Login</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <!-- Admin options -->
                        <a href="../admin/edit-package.php?id=<?php echo $package['id']; ?>" class="btn-edit">✏️ Edit Package</a>
                        <a href="../admin/manage-packages.php" class="btn-back">← Back to Manage</a>
                    <?php else: ?>
                        <!-- Regular user options -->
                        <?php if (!$can_view_full_plan): ?>
                            <a href="../user/buy-package.php?id=<?php echo $package['id']; ?>" class="btn-book">Buy This Package</a>
                        <?php endif; ?>
                        <a href="packages.php" class="btn-back">← Back to Packages</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="packages.php" class="btn-back">← Back to Packages</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="about.php#contact">Contact Us</a></p>
    </footer>
</body>