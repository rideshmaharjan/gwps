<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';

// Change to see ALL purchases including soft-deleted:
$stmt = $pdo->query("
    SELECT p.*, u.full_name as customer_name, u.email, 
           pk.name as package_name, pk.price,
           CASE WHEN p.is_active = 1 THEN 'active' ELSE 'removed' END as display_status
    FROM purchases p
    JOIN users u ON p.user_id = u.id
    JOIN packages pk ON p.package_id = pk.id
    ORDER BY p.purchase_date DESC
");
$purchases = $stmt->fetchAll();

// Calculate total revenue
$revenue_stmt = $pdo->query("SELECT SUM(amount) as total FROM purchases WHERE status = 'completed'");
$revenue = $revenue_stmt->fetch()['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Purchases - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
$base_path = '../';
include '../includes/navigation.php';
?>
    
    <div class="manage-container">
        <h1>Customer Purchases</h1>
        
        <div class="stats-card">
            <h3>Total Revenue: Rs. <?php echo number_format($revenue, 2); ?></h3>
            <p>Total Purchases: <?php echo count($purchases); ?></p>
        </div>
        
        <div class="purchases-table">
            <?php if (empty($purchases)): ?>
                <p>No purchases yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Purchase ID</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchases as $purchase): ?>
                        <tr>
                            <td>#<?php echo $purchase['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($purchase['customer_name']); ?><br>
                                <small><?php echo htmlspecialchars($purchase['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($purchase['package_name']); ?></td>
                            <td>Rs. <?php echo number_format($purchase['amount'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($purchase['purchase_date'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $purchase['status']; ?>">
                                    <?php echo $purchase['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>