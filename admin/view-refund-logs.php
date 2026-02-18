<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';

// Get all refund logs
$logs_stmt = $pdo->query("
    SELECT rl.*, u.full_name as user_name, a.full_name as processed_by_name
    FROM refund_logs rl
    LEFT JOIN users u ON rl.user_id = u.id
    LEFT JOIN users a ON rl.processed_by = a.id
    ORDER BY rl.refund_date DESC
");
$refund_logs = $logs_stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Refund Logs - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .logs-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .log-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #9b59b6;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .log-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .log-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .log-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .package-content {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            border: 1px solid #e0e0e0;
            white-space: pre-wrap;
            font-family: monospace;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 10px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="logs-container">
        <div class="page-header">
            <h1>📦 Refund Logs</h1>
            <p>Complete package details saved at time of refund</p>
        </div>
        
        <?php if (empty($refund_logs)): ?>
            <div class="empty-state">
                <p>No refund logs found</p>
            </div>
        <?php else: ?>
            <?php foreach ($refund_logs as $log): ?>
            <div class="log-card">
                <div class="log-header">
                    <span class="log-title"><?php echo htmlspecialchars($log['package_name']); ?></span>
                    <span class="log-date">Refunded: <?php echo date('M d, Y h:i A', strtotime($log['refund_date'])); ?></span>
                </div>
                
                <div class="log-details">
                    <div class="detail-item">
                        <span class="detail-label">User</span>
                        <span class="detail-value"><?php echo htmlspecialchars($log['user_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Amount Refunded</span>
                        <span class="detail-value">Rs. <?php echo number_format($log['amount_refunded'], 2); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Refund Method</span>
                        <span class="detail-value"><?php echo ucfirst($log['refund_method']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Transaction ID</span>
                        <span class="detail-value"><?php echo htmlspecialchars($log['transaction_id']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Processed By</span>
                        <span class="detail-value"><?php echo htmlspecialchars($log['processed_by_name']); ?></span>
                    </div>
                </div>
                
                <div>
                    <strong>Package Details:</strong>
                    <div class="package-content">
                        <?php echo nl2br(htmlspecialchars($log['package_details'] ?? 'No details available')); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="refund-requests.php" class="btn-cancel">← Back to Refund Requests</a>
        </div>
    </div>
</body>
</html>