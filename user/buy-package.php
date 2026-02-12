<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to purchase a package';
    header('Location: ../login.php');
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../includes/database.php';

// Validate package ID
$package_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$package_id || $package_id <= 0) {
    $_SESSION['error'] = 'Invalid package ID';
    header('Location: ../public/packages.php');
    exit();
}

// Get package details
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch();

if (!$package) {
    $_SESSION['error'] = 'Package not found';
    header('Location: ../public/packages.php');
    exit();
}

$error = '';

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid form submission';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $user_id = $_SESSION['user_id'];
        $amount = $package['price'];
        
        try {
            // Check if already purchased and active
            $check = $pdo->prepare("SELECT id FROM purchases WHERE user_id = ? AND package_id = ? AND is_active = 1");
            $check->execute([$user_id, $package_id]);
            
            if (!$check->fetch()) {
                // Insert purchase
                $stmt = $pdo->prepare("INSERT INTO purchases (user_id, package_id, amount, status, purchase_date) VALUES (?, ?, ?, 'completed', NOW())");
                $stmt->execute([$user_id, $package_id, $amount]);
                
                $_SESSION['success'] = "Package purchased successfully! You can now view the full workout plan.";
                header('Location: my-packages.php');
                exit();
            } else {
                $error = "You already own this package.";
            }
        } catch (PDOException $e) {
            $error = "Purchase failed. Please try again.";
            error_log("Purchase error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buy Package - <?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="purchase-container">
        <h1>Confirm Purchase</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <div class="purchase-summary">
            <h3><?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p><strong>Price:</strong> Rs. <?php echo number_format($package['price'], 2); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($package['short_description'] ?? $package['description'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <p>Click confirm to complete your purchase. You will get immediate access to the full workout plan.</p>
            <div class="form-actions">
                <button type="submit" class="btn-confirm">✓ Confirm Purchase</button>
                <a href="../public/package-details.php?id=<?php echo $package_id; ?>" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
    
    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>