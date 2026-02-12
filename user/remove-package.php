<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../includes/database.php';

// Validate purchase ID
$purchase_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$purchase_id || $purchase_id <= 0) {
    $_SESSION['error'] = 'Invalid package ID';
    header('Location: my-packages.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Verify this purchase belongs to the logged-in user
$stmt = $pdo->prepare("SELECT id FROM purchases WHERE id = ? AND user_id = ?");
$stmt->execute([$purchase_id, $user_id]);

if ($stmt->fetch()) {
    // SOFT DELETE: Mark as inactive instead of deleting
    $stmt = $pdo->prepare("UPDATE purchases SET is_active = 0, deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$purchase_id]);
    
    $_SESSION['success'] = "Package removed from your profile!";
} else {
    $_SESSION['error'] = "Invalid request!";
}

header('Location: my-packages.php');
exit();
?>