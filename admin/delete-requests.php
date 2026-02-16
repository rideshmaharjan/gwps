<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';

// Handle approval/rejection
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $purchase_id = (int)$_GET['approve'];
    
    try {
        $stmt = $pdo->prepare("UPDATE purchases SET 
                               delete_approved = 1, 
                               delete_approved_date = NOW(), 
                               delete_approved_by = ? 
                               WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $purchase_id]);
        
        $_SESSION['success'] = "Delete request approved successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to approve request: " . $e->getMessage();
    }
    
    header('Location: delete-requests.php');
    exit();
}

if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $purchase_id = (int)$_GET['reject'];
    
    try {
        $stmt = $pdo->prepare("UPDATE purchases SET 
                               delete_requested = 0, 
                               delete_request_date = NULL 
                               WHERE id = ?");
        $stmt->execute([$purchase_id]);
        
        $_SESSION['success'] = "Delete request rejected.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to reject request: " . $e->getMessage();
    }
    
    header('Location: delete-requests.php');
    exit();
}

// Get all pending delete requests
$stmt = $pdo->query("
    SELECT p.*, u.full_name as user_name, u.email, pk.name as package_name
    FROM purchases p
    JOIN users u ON p.user_id = u.id
    JOIN packages pk ON p.package_id = pk.id
    WHERE p.delete_requested = 1 AND p.delete_approved = 0
    ORDER BY p.delete_request_date DESC
");
$requests = $stmt->fetchAll();

// Get approved requests
$approved_stmt = $pdo->query("
    SELECT p.*, u.full_name as user_name, u.email, pk.name as package_name, a.full_name as approved_by_name
    FROM purchases p
    JOIN users u ON p.user_id = u.id
    JOIN packages pk ON p.package_id = pk.id
    LEFT JOIN users a ON p.delete_approved_by = a.id
    WHERE p.delete_approved = 1
    ORDER BY p.delete_approved_date DESC
    LIMIT 10
");
$approved_requests = $approved_stmt->fetchAll();

// Count pending requests for badge
$pending_count = count($requests);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Requests - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .request-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .pending-badge {
            background: #f39c12;
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 1rem;
        }
        
        .request-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            border-left: 5px solid #f39c12;
            transition: transform 0.3s;
        }
        
        .request-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        
        .request-card.approved {
            border-left-color: #27ae60;
            opacity: 0.9;
        }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .user-info {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .user-email {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-left: 10px;
        }
        
        .package-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .request-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn-approve {
            background: #27ae60;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            flex: 1;
            text-align: center;
        }
        
        .btn-approve:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39,174,96,0.3);
        }
        
        .btn-reject {
            background: #e74c3c;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            flex: 1;
            text-align: center;
        }
        
        .btn-reject:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231,76,60,0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 10px;
            color: #7f8c8d;
            font-size: 1.2rem;
        }
        
        .section-title {
            margin: 40px 0 20px;
            color: #2c3e50;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .approved-count {
            background: #27ae60;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .notification-bell {
            position: relative;
            display: inline-block;
        }
        
        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 0.7rem;
        }
    </style>
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="request-container">
        <div class="page-header">
            <h1>Package Delete Requests</h1>
            <div class="pending-badge">
                <?php echo $pending_count; ?> Pending Request<?php echo $pending_count != 1 ? 's' : ''; ?>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <!-- Pending Requests -->
        <div class="section-title">
            <span>⏳ Pending Requests</span>
            <?php if ($pending_count > 0): ?>
                <span class="approved-count"><?php echo $pending_count; ?> new</span>
            <?php endif; ?>
        </div>
        
        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <p>✨ No pending delete requests</p>
                <p style="font-size: 0.9rem; margin-top: 10px;">When users request to remove packages, they'll appear here</p>
            </div>
        <?php else: ?>
            <?php foreach ($requests as $request): ?>
            <div class="request-card">
                <div class="request-header">
                    <div>
                        <span class="user-info"><?php echo htmlspecialchars($request['user_name']); ?></span>
                        <span class="user-email">(<?php echo htmlspecialchars($request['email']); ?>)</span>
                    </div>
                    <div class="request-date">
                        Requested: <?php echo date('M d, Y h:i A', strtotime($request['delete_request_date'])); ?>
                    </div>
                </div>
                
                <div class="package-info">
                    <div class="info-item">
                        <span class="info-label">Package Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($request['package_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Amount Paid</span>
                        <span class="info-value">Rs. <?php echo number_format($request['amount'], 2); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Purchase Date</span>
                        <span class="info-value"><?php echo date('M d, Y', strtotime($request['purchase_date'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Purchase ID</span>
                        <span class="info-value">#<?php echo $request['id']; ?></span>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="?approve=<?php echo $request['id']; ?>" 
                       class="btn-approve"
                       onclick="return confirm('Approve this delete request?\n\nUser: <?php echo addslashes($request['user_name']); ?>\nPackage: <?php echo addslashes($request['package_name']); ?>\n\nThe user will be able to remove this package from their profile.')">
                        ✅ Approve Request
                    </a>
                    <a href="?reject=<?php echo $request['id']; ?>" 
                       class="btn-reject"
                       onclick="return confirm('Reject this delete request?\n\nUser: <?php echo addslashes($request['user_name']); ?>\nPackage: <?php echo addslashes($request['package_name']); ?>')">
                        ❌ Reject Request
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Recently Approved -->
        <?php if (!empty($approved_requests)): ?>
            <div class="section-title">
                <span>✅ Recently Approved</span>
            </div>
            
            <?php foreach ($approved_requests as $request): ?>
            <div class="request-card approved">
                <div class="request-header">
                    <div>
                        <span class="user-info"><?php echo htmlspecialchars($request['user_name']); ?></span>
                        <span class="user-email">(<?php echo htmlspecialchars($request['email']); ?>)</span>
                    </div>
                    <div class="request-date">
                        Approved: <?php echo date('M d, Y h:i A', strtotime($request['delete_approved_date'])); ?>
                    </div>
                </div>
                
                <div class="package-info">
                    <div class="info-item">
                        <span class="info-label">Package</span>
                        <span class="info-value"><?php echo htmlspecialchars($request['package_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Approved By</span>
                        <span class="info-value"><?php echo htmlspecialchars($request['approved_by_name'] ?? 'Admin'); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="margin-top: 40px; text-align: center;">
            <a href="dashboard.php" class="btn-cancel" style="display: inline-block; padding: 12px 30px;">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>