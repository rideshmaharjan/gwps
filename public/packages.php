<?php
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
    <style>
        .admin-badge {
            background: #f39c12;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-left: 8px;
            font-weight: normal;
        }
        
        .admin-view-btn {
            background: #9b59b6;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .admin-view-btn:hover {
            background: #8e44ad;
            transform: translateY(-2px);
        }
        
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php
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
                    <div class="level-badge">
                        <?php echo htmlspecialchars($package['category'] ?? 'General'); ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <span class="admin-badge">Admin</span>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                    <p class="price">Rs. <?php echo number_format($package['price'], 2); ?></p>
                    
                    <div class="workout-details">
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></p>
                        <p><strong>Description:</strong></p>
                        <p><?php echo htmlspecialchars(substr($package['short_description'] ?? $package['description'], 0, 50)); ?>...</p>
                    </div>
                    
                    <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
                        <a href="package-details.php?id=<?php echo $package['id']; ?>" class="btn-details">View Details</a>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                <!-- Admin sees Edit button instead of Buy Now -->
                                <a href="../admin/edit-package.php?id=<?php echo $package['id']; ?>" class="admin-view-btn">Edit Package</a>
                            <?php else: ?>
                                <!-- Regular user sees Buy Now -->
                                <a href="../user/buy-package.php?id=<?php echo $package['id']; ?>" class="btn-book">Buy Now</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Not logged in sees Login to Purchase -->
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