<?php
session_start();
require_once '../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// PREVENT ADMIN FROM BUYING
if ($_SESSION['role'] == 'admin') {
    $_SESSION['error'] = 'Admins have automatic access to all packages';
    header('Location: ../admin/dashboard.php');
    exit();
}

$package_id = filter_input(INPUT_POST, 'package_id', FILTER_VALIDATE_INT);
if (!$package_id) {
    $package_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

if (!$package_id) {
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

// Check if already purchased
$check = $pdo->prepare("SELECT id FROM purchases WHERE user_id = ? AND package_id = ? AND is_active = 1");
$check->execute([$_SESSION['user_id'], $package_id]);

if ($check->fetch()) {
    $_SESSION['error'] = 'You already own this package';
    header('Location: my-packages.php');
    exit();
}

try {
    // Insert purchase record
    // Status set to 'active' so package isn't marked completed immediately upon purchase.
    $stmt = $pdo->prepare("INSERT INTO purchases (user_id, package_id, amount, transaction_id, payment_method, status, payment_status, purchase_date) 
                           VALUES (?, ?, ?, ?, 'mock', 'active', 'completed', NOW())");
    $stmt->execute([
        $_SESSION['user_id'],
        $package_id,
        $package['price'],
        'MOCK_' . uniqid() . '_' . time()
    ]);
    
    $_SESSION['success'] = 'Package purchased successfully! You can now view the full workout plan.';
    header('Location: my-packages.php');
    exit();
    
} catch (PDOException $e) {
    error_log("Mock payment error: " . $e->getMessage());
    $_SESSION['error'] = 'Purchase failed. Please try again.';
    header('Location: buy-package.php?id=' . $package_id);
    exit();
}
?>