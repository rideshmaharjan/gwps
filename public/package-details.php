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
        <div class="nav-links">
            <a href="../index.php">Home</a>
            <a href="packages.php">Packages</a>
            <a href="about.php" class="active">About Us</a>
            <a href="../login.php">Login</a>
        </div>
    </nav>

    <div class="container">
        <h1><?php echo htmlspecialchars($package['name']); ?></h1>
        
        <div class="package-detail-card">
            <div class="price-tag">Rs. <?php echo number_format($package['price'], 2); ?></div>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($package['category']); ?></p>
            
            <div class="description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
            </div>
            
            <div class="action-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="../user/buy-package.php?id=<?php echo $package['id']; ?>" class="btn-book">Buy This Package</a>
                <?php else: ?>
                    <a href="../user/register.php" class="btn-register">Register to Purchase</a>
                    <a href="../user/login.php" class="btn-login">Login</a>
                <?php endif; ?>
                <a href="packages.php" class="btn-back">← Back to Packages</a>
            </div>
        </div>
    </div>
</body>
</html>