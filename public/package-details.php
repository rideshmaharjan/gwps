<?php
session_start();
require_once '../includes/database.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$id]);
$package = $stmt->fetch();

if (!$package) {
    header('Location: packages.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($package['name']); ?> - Details</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav>
        <div class="logo">FitLife Fitness</div>
        <?php include 'public-nav.php'; ?>
    </nav>

    <div class="container">
        <h1><?php echo htmlspecialchars($package['name']); ?></h1>
        
        <div class="package-detail-card">
            <div class="price-tag">Rs. <?php echo number_format($package['price'], 2); ?></div>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($package['category']); ?></p>
            
           
        <div class="description">
            <h3>Package Overview</h3>
            <p><strong>What's included:</strong> <?php echo htmlspecialchars($package['short_description']); ?></p>
            
            <?php 
            // Check if user has purchased
            if (isset($_SESSION['user_id'])) {
                $check_stmt = $pdo->prepare("SELECT id FROM purchases WHERE user_id = ? AND package_id = ?");
                $check_stmt->execute([$_SESSION['user_id'], $package['id']]);
                $has_purchased = $check_stmt->fetch();
            }
            ?>
            
            <?php if (isset($has_purchased) && $has_purchased): ?>
                <!-- SHOW FULL WORKOUT PLAN to purchasers -->
                <div class="full-workout-plan">
                    <h4>Your Complete Workout Plan:</h4>
                    <div class="workout-content">
                        <?php echo nl2br(htmlspecialchars($package['description'])); ?>
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
                    <a href="../user/buy-package.php?id=<?php echo $package['id']; ?>" class="btn-book">Buy This Package</a>
                <?php else: ?>
                    <a href="../user/register.php" class="btn-register">Register to Purchase</a>
                    <a href="../login.php" class="btn-login">Login</a>
                <?php endif; ?>
                <a href="packages.php" class="btn-back">← Back to Packages</a>
            </div>
        </div>
    </div>
</body>
</html>