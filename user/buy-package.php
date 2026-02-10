<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../includes/database.php';

$package_id = $_GET['id'] ?? 0;

// Get package details
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch();

if (!$package) {
    header('Location: ../public/packages.php');
    exit();
}

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $amount = $package['price'];
    
    // Check if already purchased
    $check = $pdo->prepare("SELECT id FROM purchases WHERE user_id = ? AND package_id = ?");
    $check->execute([$user_id, $package_id]);
    
    if (!$check->fetch()) {
        // Insert purchase
        $stmt = $pdo->prepare("INSERT INTO purchases (user_id, package_id, amount) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $package_id, $amount]);
        
        $_SESSION['success'] = "Package purchased successfully!";
        header('Location: my-packages.php');
        exit();
    } else {
        $error = "You already own this package.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buy Package - <?php echo htmlspecialchars($package['name']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'user-nav.php'; ?>
    
    <div class="purchase-container">
        <h1>Confirm Purchase</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="purchase-summary">
            <h3><?php echo htmlspecialchars($package['name']); ?></h3>
            <p><strong>Price:</strong> Rs. <?php echo number_format($package['price'], 2); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($package['short_description']); ?></p>
        </div>
        
        <form method="POST">
            <p>Click confirm to complete your purchase.</p>
            <div class="form-actions">
                <button type="submit" class="btn-confirm">Confirm Purchase</button>
                <a href="../public/package-details.php?id=<?php echo $package_id; ?>" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>