<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Handle mark as completed
if (isset($_GET['complete']) && is_numeric($_GET['complete'])) {
    $purchase_id = $_GET['complete'];
    
    $check = $pdo->prepare("SELECT id FROM purchases WHERE id = ? AND user_id = ?");
    $check->execute([$purchase_id, $_SESSION['user_id']]);
    
    if ($check->fetch()) {
        $update = $pdo->prepare("UPDATE purchases SET status = 'completed', completed_at = NOW() WHERE id = ?");
        $update->execute([$purchase_id]);
        
        $_SESSION['success'] = "Package marked as completed! 🎉 Congratulations!";
    }
    header('Location: my-packages.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's purchased packages - SIMPLE QUERY (no refund fields)
$stmt = $pdo->prepare("
<<<<<<< HEAD
    SELECT 
        p.*, 
        pur.purchase_date, 
        pur.id as purchase_id, 
        pur.status, 
        pur.completed_at,
        pur.is_active,
        r.id as refund_id,
        r.status as refund_status,
        r.request_date as refund_request_date,
        r.processed_date as refund_processed_date
=======
    SELECT p.*, 
           pur.purchase_date, 
           pur.id as purchase_id, 
           pur.status, 
           pur.completed_at,
           pur.is_active
>>>>>>> 6643a48ee944353bbf489a6166170bff72266a68
    FROM purchases pur
    JOIN packages p ON pur.package_id = p.id
    LEFT JOIN refunds r ON pur.id = r.purchase_id
    WHERE pur.user_id = ? 
    ORDER BY 
        CASE 
            WHEN pur.is_active = 0 THEN 1  -- Inactive packages at the bottom
            ELSE 0 
        END,
        pur.purchase_date DESC
");
$stmt->execute([$user_id]);
$purchased_packages = $stmt->fetchAll();

// Separate active and inactive packages
$active_packages = array_filter($purchased_packages, function($pkg) {
    return $pkg['is_active'] == 1;
});

$inactive_packages = array_filter($purchased_packages, function($pkg) {
    return $pkg['is_active'] == 0;
});
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Packages - GWPS</title>
    <link rel="stylesheet" href="../css/style.css">
<<<<<<< HEAD
    <style>
        .refund-badge {
            background: #f39c12;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 10px;
        }
        
        .refund-pending {
            background: #f39c12;
        }
        
        .refund-approved {
            background: #27ae60;
        }
        
        .refund-rejected {
            background: #e74c3c;
        }
        
        .refund-processed {
            background: #3498db;
        }
        
        .btn-refund {
            background: #f39c12;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-refund:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }
        
        .btn-refund:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }
        
        .refund-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 0.85rem;
            border-left: 3px solid #f39c12;
        }
        
        .refund-info.approved {
            border-left-color: #27ae60;
            background: #f0fff4;
        }
        
        .refund-info.rejected {
            border-left-color: #e74c3c;
            background: #fef5f5;
        }
        
        .refund-info.processed {
            border-left-color: #3498db;
            background: #f0f8ff;
        }
        
        /* New styles for inactive/refunded packages */
        .inactive-packages-section {
            margin-top: 40px;
            opacity: 0.8;
        }
        
        .inactive-packages-section h2 {
            color: #95a5a6;
            font-size: 1.3rem;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .purchased-card.inactive {
            background: #f5f5f5;
            border-left: 5px solid #95a5a6;
            opacity: 0.7;
            filter: grayscale(20%);
        }
        
        .purchased-card.inactive:hover {
            transform: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .inactive-badge {
            background: #95a5a6;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 10px;
        }
        
        .removed-message {
            background: #ecf0f1;
            padding: 8px 12px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 0.85rem;
            color: #7f8c8d;
            border-left: 3px solid #95a5a6;
        }
        
        .archived-badge {
            background: #9b59b6;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px 5px 0 0;
            font-weight: 600;
            color: #7f8c8d;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #3498db;
            border-bottom: 3px solid #3498db;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .count-badge {
            background: #e0e0e0;
            color: #2c3e50;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 8px;
        }
    </style>
=======
>>>>>>> 6643a48ee944353bbf489a6166170bff72266a68
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="my-packages-container">
        <div class="page-header">
            <h1>📦 My Packages</h1>
            <p class="subtitle">Track your fitness journey</p>
        </div>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <!-- Tabs Navigation -->
        <div class="tabs">
            <div class="tab active" onclick="showTab('active')">
                Active Packages 
                <span class="count-badge"><?php echo count($active_packages); ?></span>
            </div>
<<<<<<< HEAD
            <div class="tab" onclick="showTab('inactive')">
                Archived/Refunded 
                <span class="count-badge"><?php echo count($inactive_packages); ?></span>
            </div>
        </div>
        
        <!-- Active Packages Tab -->
        <div id="active-tab" class="tab-content active">
            <?php if (empty($active_packages)): ?>
                <div class="empty-state">
                    <p>You don't have any active packages.</p>
                    <a href="../public/packages.php" class="btn-browse">Browse Packages</a>
                </div>
            <?php else: ?>
                <div class="purchased-packages">
                    <?php foreach ($active_packages as $package): 
                        $icon = '💪';
                        switch($package['category']) {
                            case 'weight_loss': $icon = '⚖️'; break;
                            case 'muscle_building': $icon = '💪'; break;
                            case 'strength': $icon = '🏋️'; break;
                            case 'yoga': $icon = '🧘'; break;
                            case 'cardio': $icon = '🏃'; break;
                            case 'beginner': $icon = '🌱'; break;
                            case 'advanced': $icon = '🔥'; break;
                        }
                        
                        $is_completed = !empty($package['completed_at']) || $package['status'] == 'completed';
                        
                        // Determine refund status
                        $refund_status = '';
                        $refund_class = '';
                        
                        if (!empty($package['refund_id'])) {
                            switch($package['refund_status']) {
                                case 'pending':
                                    $refund_status = 'Refund Pending';
                                    $refund_class = 'refund-pending';
                                    break;
                                case 'approved':
                                    $refund_status = 'Refund Approved';
                                    $refund_class = 'refund-approved';
                                    break;
                                case 'processed':
                                    $refund_status = 'Refund Processed';
                                    $refund_class = 'refund-processed';
                                    break;
                                case 'rejected':
                                    $refund_status = 'Refund Rejected';
                                    $refund_class = 'refund-rejected';
                                    break;
                            }
                        }
                    ?>
                    <div class="purchased-card">
                        <?php if ($is_completed): ?>
                            <div class="completed-badge">✅ Completed</div>
                        <?php endif; ?>
                        
                        <?php if ($refund_status): ?>
                            <div class="refund-badge <?php echo $refund_class; ?>">
                                💰 <?php echo $refund_status; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="package-icon"><?php echo $icon; ?></div>
                        <h3><?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        
                        <p class="purchase-date">
                            Purchased: <?php echo date('M d, Y', strtotime($package['purchase_date'])); ?>
                        </p>
                        
                        <p class="duration">
                            <strong>Duration:</strong> <?php echo htmlspecialchars($package['duration'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        
                        <p class="description">
                            <?php 
                            $desc = $package['short_description'] ?? $package['description'];
                            echo htmlspecialchars(substr($desc, 0, 100), ENT_QUOTES, 'UTF-8');
                            if (strlen($desc) > 100) echo '...';
                            ?>
                        </p>
                        
                        <?php if (!empty($package['refund_id'])): ?>
                            <div class="refund-info <?php echo $refund_class; ?>">
                                <strong>💰 Refund Request:</strong>
                                <?php if ($package['refund_status'] == 'pending'): ?>
                                    Your refund request is pending admin approval. Requested on <?php echo date('M d, Y', strtotime($package['refund_request_date'])); ?>
                                <?php elseif ($package['refund_status'] == 'approved'): ?>
                                    Your refund request has been approved! Admin will process your refund shortly.
                                <?php elseif ($package['refund_status'] == 'processed'): ?>
                                    Your refund has been processed. Check your payment method.
                                <?php elseif ($package['refund_status'] == 'rejected'): ?>
                                    Your refund request was rejected. Contact support for more information.
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($is_completed && !empty($package['completed_at'])): ?>
                            <div class="completed-date">
                                Completed on: <?php echo date('M d, Y', strtotime($package['completed_at'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="action-buttons">
                            <a href="../public/package-details.php?id=<?php echo $package['id']; ?>" class="btn-view">
                                View Details
                            </a>
                            
                            <?php if (!$is_completed && empty($package['refund_id'])): ?>
                                <a href="?complete=<?php echo $package['purchase_id']; ?>" 
                                   class="btn-complete"
                                   onclick="return confirm('Mark this package as completed?')">
                                    ✅ Mark Complete
                                </a>
                            <?php endif; ?>
                            
                            <?php if (empty($package['refund_id']) && !$is_completed): ?>
                                <a href="request-refund.php?id=<?php echo $package['purchase_id']; ?>" 
                                   class="btn-refund"
                                   onclick="return confirm('Request refund for this package?\n\nRefund will be processed by admin.')">
                                    💰 Request Refund
                                </a>
                            <?php endif; ?>
                        </div>
=======
        <?php else: ?>
            <div class="purchased-packages">
                <?php foreach ($purchased_packages as $package): 
                    $icon = '💪';
                    switch($package['category']) {
                        case 'weight_loss': $icon = '⚖️'; break;
                        case 'muscle_building': $icon = '💪'; break;
                        case 'strength': $icon = '🏋️'; break;
                        case 'yoga': $icon = '🧘'; break;
                        case 'cardio': $icon = '🏃'; break;
                        case 'beginner': $icon = '🌱'; break;
                        case 'advanced': $icon = '🔥'; break;
                    }
                    
                    $is_completed = !empty($package['completed_at']) || $package['status'] == 'completed';
                ?>
                <div class="purchased-card <?php echo !$package['is_active'] ? 'inactive' : ''; ?>">
                    <?php if ($is_completed): ?>
                        <div class="completed-badge">✅ Completed</div>
                    <?php endif; ?>
                    
                    <?php if (!$package['is_active']): ?>
                        <div class="completed-badge" style="background: #95a5a6;">🗑️ Removed</div>
                    <?php endif; ?>
                    
                    <div class="package-icon"><?php echo $icon; ?></div>
                    <h3><?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    
                    <p class="purchase-date">
                        Purchased: <?php echo date('M d, Y', strtotime($package['purchase_date'])); ?>
                    </p>
                    
                    <p class="duration">
                        <strong>Duration:</strong> <?php echo htmlspecialchars($package['duration'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    
                    <p class="description">
                        <?php 
                        $desc = $package['short_description'] ?? $package['description'];
                        echo htmlspecialchars(substr($desc, 0, 100), ENT_QUOTES, 'UTF-8');
                        if (strlen($desc) > 100) echo '...';
                        ?>
                    </p>
                    
                    <?php if ($is_completed && !empty($package['completed_at'])): ?>
                        <div class="completed-date">
                            Completed on: <?php echo date('M d, Y', strtotime($package['completed_at'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <a href="../public/package-details.php?id=<?php echo $package['id']; ?>" class="btn-view">
                            View Details
                        </a>
                        
                        <?php if (!$is_completed && $package['is_active']): ?>
                            <a href="?complete=<?php echo $package['purchase_id']; ?>" 
                               class="btn-complete"
                               onclick="return confirm('Mark this package as completed?')">
                                ✅ Mark Complete
                            </a>
                        <?php endif; ?>
>>>>>>> 6643a48ee944353bbf489a6166170bff72266a68
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Inactive/Refunded Packages Tab -->
        <div id="inactive-tab" class="tab-content">
            <?php if (empty($inactive_packages)): ?>
                <div class="empty-state">
                    <p>No archived or refunded packages.</p>
                </div>
            <?php else: ?>
                <div class="purchased-packages">
                    <?php foreach ($inactive_packages as $package): 
                        $icon = '📦';
                        $refund_info = '';
                        
                        // Check if this was refunded
                        if (!empty($package['refund_id'])) {
                            if ($package['refund_status'] == 'processed') {
                                $refund_info = 'Refunded on ' . date('M d, Y', strtotime($package['refund_processed_date'] ?? $package['refund_request_date']));
                            } elseif ($package['refund_status'] == 'rejected') {
                                $refund_info = 'Refund request rejected';
                            } else {
                                $refund_info = 'Refund: ' . $package['refund_status'];
                            }
                        } else {
                            $refund_info = 'Removed from active packages';
                        }
                    ?>
                    <div class="purchased-card inactive">
                        <div class="inactive-badge">🗂️ Archived</div>
                        
                        <div class="package-icon"><?php echo $icon; ?></div>
                        <h3>
                            <?php echo htmlspecialchars($package['name'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php if (!empty($package['refund_id'])): ?>
                                <span class="archived-badge">💰 Refunded</span>
                            <?php endif; ?>
                        </h3>
                        
                        <p class="purchase-date">
                            Purchased: <?php echo date('M d, Y', strtotime($package['purchase_date'])); ?>
                        </p>
                        
                        <div class="removed-message">
                            <strong>ℹ️ Status:</strong> <?php echo $refund_info; ?>
                        </div>
                        
                        <?php if (!empty($package['refund_id']) && $package['refund_status'] == 'processed'): ?>
                            <div style="background: #e8f4f8; padding: 8px 12px; border-radius: 5px; margin: 10px 0; font-size: 0.85rem;">
                                <strong>💰 Refund Details:</strong><br>
                                Amount refunded: Rs. <?php echo number_format($package['price'], 2); ?><br>
                                <?php if (!empty($package['refund_processed_date'])): ?>
                                    Processed on: <?php echo date('M d, Y', strtotime($package['refund_processed_date'])); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="action-buttons">
                            <a href="../public/package-details.php?id=<?php echo $package['id']; ?>" class="btn-view">
                                View Details (Read Only)
                            </a>
                            
                            <!-- Optional: Allow users to re-purchase? -->
                            <?php if (!empty($package['refund_id'])): ?>
                                <a href="../public/package-details.php?id=<?php echo $package['id']; ?>" class="btn-secondary" style="background: #27ae60;">
                                    🔄 Buy Again
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
    </script>
    
    <footer>
        <p>GWPS &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>