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

// Handle mark as completed
if (isset($_GET['complete']) && is_numeric($_GET['complete'])) {
    $purchase_id = $_GET['complete'];
    
    // Verify ownership
    $check = $pdo->prepare("SELECT id FROM purchases WHERE id = ? AND user_id = ?");
    $check->execute([$purchase_id, $_SESSION['user_id']]);
    
    if ($check->fetch()) {
        // Option 1: Update status column
        $update = $pdo->prepare("UPDATE purchases SET status = 'completed', completed_at = NOW() WHERE id = ?");
        
        // OR Option 2: Set completed_at timestamp
        // $update = $pdo->prepare("UPDATE purchases SET completed_at = NOW() WHERE id = ?");
        
        $update->execute([$purchase_id]);
        
        $_SESSION['success'] = "Package marked as completed! 🎉 Congratulations!";
    }
    header('Location: my-packages.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's purchased packages
$stmt = $pdo->prepare("
    SELECT p.*, pur.purchase_date, pur.id as purchase_id, pur.status, pur.completed_at
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
        /* Main container */
        .my-packages-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .my-packages-container h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 25px;
        }
        
        /* Package grid */
        .purchased-packages {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        /* Package card */
        .purchased-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border: 1px solid #eee;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .purchased-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        /* Completed badge */
        .completed-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #27ae60;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        /* Package icon */
        .package-icon {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        /* Package name */
        .purchased-card h3 {
            font-size: 1.3rem;
            color: #2c3e50;
            margin: 5px 0 10px 0;
            font-weight: 600;
            padding-right: 70px;
        }
        
        /* Package details */
        .purchased-card .purchase-date {
            font-size: 0.8rem;
            color: #95a5a6;
            margin: 5px 0;
        }
        
        .purchased-card .duration {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin: 5px 0;
        }
        
        .purchased-card .duration strong {
            color: #2c3e50;
        }
        
        .purchased-card .description {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin: 10px 0;
            line-height: 1.4;
            flex-grow: 1;
        }
        
        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-view {
            flex: 1;
            padding: 8px 12px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.85rem;
            text-align: center;
            transition: background 0.2s;
        }
        
        .btn-view:hover {
            background: #2980b9;
        }
        
        .btn-complete {
            flex: 1;
            padding: 8px 12px;
            background: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.85rem;
            text-align: center;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .btn-complete:hover {
            background: #229954;
        }
        
        .btn-complete.completed {
            background: #95a5a6;
            pointer-events: none;
            opacity: 0.7;
        }
        
        /* Completed date */
        .completed-date {
            font-size: 0.75rem;
            color: #27ae60;
            margin-top: 5px;
            text-align: right;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            grid-column: 1/-1;
        }
        
        .empty-state p {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        
        .empty-state .btn-browse {
            display: inline-block;
            padding: 12px 30px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 1rem;
        }
        
        /* Success/Error messages */
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .purchased-packages {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .my-packages-container h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="my-packages-container">
        <h1>📦 My Purchased Packages</h1>
        <p class="subtitle">Track your fitness journey</p>
        
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
                <?php foreach ($purchased_packages as $package): 
                    // Set icon based on category
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
                    
                    // Check if package is completed
                    $is_completed = !empty($package['completed_at']) || $package['status'] == 'completed';
                ?>
                <div class="purchased-card">
                    <?php if ($is_completed): ?>
                        <div class="completed-badge">
                            ✅ Completed
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
                    
                    <?php if ($is_completed && !empty($package['completed_at'])): ?>
                        <div class="completed-date">
                            Completed on: <?php echo date('M d, Y', strtotime($package['completed_at'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <a href="../public/package-details.php?id=<?php echo $package['id']; ?>" class="btn-view">
                            View Details
                        </a>
                        
                        <?php if (!$is_completed): ?>
                            <a href="?complete=<?php echo $package['purchase_id']; ?>" 
                               class="btn-complete"
                               onclick="return confirm('Mark this package as completed?')">
                                ✅ Mark Complete
                            </a>
                        <?php else: ?>
                            <span class="btn-complete completed">
                                ✅ Completed
                            </span>
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