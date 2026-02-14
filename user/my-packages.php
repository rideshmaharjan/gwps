<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';

// Handle success/error messages from session
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$user_id = $_SESSION['user_id'];

// Get user's purchased packages
$stmt = $pdo->prepare("
    SELECT p.*, pur.purchase_date, pur.id as purchase_id,
           pur.delete_requested, pur.delete_approved
    FROM purchases pur
    JOIN packages p ON pur.package_id = p.id
    WHERE pur.user_id = ? AND pur.is_active = 1
    ORDER BY pur.purchase_date DESC
");
$stmt->execute([$user_id]);
$purchased_packages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Packages - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .request-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }
        .badge-pending {
            background: #f39c12;
            color: white;
        }
        .badge-approved {
            background: #27ae60;
            color: white;
        }
        .btn-request {
            background: #e67e22;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 10px;
        }
        .btn-request:hover {
            background: #d35400;
        }
        .btn-request:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 10px;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .status-message {
            margin-top: 10px;
            padding: 8px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .status-pending {
            background: #fef5e7;
            color: #f39c12;
            border-left: 3px solid #f39c12;
        }
        .status-approved {
            background: #e8f5e9;
            color: #27ae60;
            border-left: 3px solid #27ae60;
        }
    </style>
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="my-packages-container">
        <h1>My Purchased Packages</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if (empty($purchased_packages)): ?>
            <div class="empty-state">
                <p>You haven't purchased any packages yet.</p>
                <a href="../public/packages.php" class="btn-browse">Browse Packages</a>
            </div>
        <?php else: ?>
            <div class="purchased-packages">
                <?php foreach ($purchased_packages as $package): ?>
                <div class="purchased-card">
                    <h3>
                        <?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ($package['delete_requested'] == 1): ?>
                            <span class="request-badge badge-pending">Delete Requested</span>
                        <?php elseif ($package['delete_approved'] == 1): ?>
                            <span class="request-badge badge-approved">Delete Approved</span>
                        <?php endif; ?>
                    </h3>
                    <p><strong>Purchased:</strong> <?php echo date('M d, Y', strtotime($package['purchase_date'])); ?></p>
                    <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><?php echo htmlspecialchars(substr($package['short_description'] ?? $package['description'], 0, 100), ENT_QUOTES, 'UTF-8'); ?>...</p>
                    <a href="../public/package-details.php?id=<?php echo $package['id']; ?>" class="btn-view">View Details</a>
                    
                    <div style="margin-top: 15px;">
                        <?php if ($package['delete_requested'] == 0 && $package['delete_approved'] == 0): ?>
                            <!-- Show request delete button -->
                            <a href="request-delete.php?id=<?php echo $package['purchase_id']; ?>" 
                               class="btn-request"
                               onclick="return confirm('Request admin to remove this package from your profile?')">
                                📝 Request to Remove
                            </a>
                        <?php elseif ($package['delete_requested'] == 1 && $package['delete_approved'] == 0): ?>
                            <!-- Show pending message -->
                            <div class="status-message status-pending">
                                ⏳ Removal request pending admin approval
                            </div>
                        <?php elseif ($package['delete_approved'] == 1): ?>
                            <!-- Show approved message with remove button -->
                            <div class="status-message status-approved">
                                ✅ Removal approved! You can now remove this package.
                            </div>
                            <a href="remove-package.php?id=<?php echo $package['purchase_id']; ?>" 
                               class="btn-danger"
                               onclick="return confirm('Remove this package from your profile? This action cannot be undone.')">
                                🗑️ Remove Package
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>