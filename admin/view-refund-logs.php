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