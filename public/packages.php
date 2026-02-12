<?php
session_start();
require_once '../includes/database.php';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get all packages from database
$stmt = $pdo->query("SELECT * FROM packages ORDER BY created_at DESC");
$packages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Workout Plans - FitLife Fitness</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    // Use the unified navigation file
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="container">
        <h1>Custom Workout Plans</h1>
        <p class="subtitle">Choose a workout routine tailored to your fitness goals</p>

        <div class="packages-grid">
            <?php if (empty($packages)): ?>
                <p>No workout packages available yet.</p>
            <?php else: ?>
                <?php foreach ($packages as $package): ?>
                <div class="package-card">
                    <div class="level-badge"><?php echo htmlspecialchars($package['category'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?></div>
                    <h3><?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="price">Rs. <?php echo number_format($package['price'], 2); ?></p>
                    
                    <div class="workout-details">
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Description:</strong></p>
                        <p><?php 
                            echo htmlspecialchars($package['short_description'] ?? 'Workout package - purchase for full details', ENT_QUOTES, 'UTF-8');
                        ?></p>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <a href="package-details.php?id=<?php echo $package['id']; ?>" class="btn-details">View Details</a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="../user/buy-package.php?id=<?php echo $package['id']; ?>" class="btn-book">Buy Now</a>
                        <?php else: ?>
                            <a href="../login.php" class="btn-book">Login to Purchase</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>FitLife Fitness &copy; 2025 | <a href="about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>