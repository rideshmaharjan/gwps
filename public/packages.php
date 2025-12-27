<?php
// File: public/packages.php
session_start();
require_once '../includes/database.php';

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
    <nav>
        <div class="logo">FitLife Fitness</div>
        <div class="nav-links">
           <a href="../index.php">Home</a>
            <a href="packages.php">Packages</a>
            <a href="about.php" class="active">About Us</a>
            <a href="../login.php">Login</a>
        </div>
    </nav>

    <div class="container">
        <h1>Custom Workout Plans</h1>
        <p class="subtitle">Choose a workout routine tailored to your fitness goals</p>

        <div class="packages-grid">
            <?php if (empty($packages)): ?>
                <p>No workout packages available yet.</p>
            <?php else: ?>
                <?php foreach ($packages as $package): ?>
                <div class="package-card">
                    <div class="level-badge"><?php echo htmlspecialchars($package['category'] ?? 'General'); ?></div>
                    <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                    <p class="price">Rs. <?php echo number_format($package['price'], 2); ?></p>
                    
                    <div class="workout-details">
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></p>
                        <p><strong>Description:</strong></p>
                        <p><?php echo htmlspecialchars(substr($package['description'], 0, 150)); ?>...</p>
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