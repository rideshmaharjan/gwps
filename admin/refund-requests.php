<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

// Handle refund actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $purchase_id = filter_input(INPUT_POST, 'purchase_id', FILTER_VALIDATE_INT);
        $action = $_POST['action'] ?? '';
        
        if ($purchase_id && in_array($action, ['approve', 'reject', 'process'])) {
            try {
                if ($action == 'approve') {
                    // Approve refund
                    $stmt = $pdo->prepare("
                        UPDATE purchases 
                        SET refund_approved = 1, 
                            refund_approved_date = NOW(), 
                            refund_approved_by = ?,
                            refund_status = 'approved'
                        WHERE id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id'], $purchase_id]);
                    
                    $message = "Refund request approved successfully!";
                    
                } elseif ($action == 'reject') {
                    // Reject refund
                    $reject_reason = trim($_POST['reject_reason'] ?? '');
                    
                    $stmt = $pdo->prepare("
                        UPDATE purchases 
                        SET refund_status = 'rejected',
                            refund_notes = CONCAT(IFNULL(refund_notes, ''), '\n\nRejection Reason: ', ?)
                        WHERE id = ?
                    ");
                    $stmt->execute([$reject_reason, $purchase_id]);
                    
                    $message = "Refund request rejected.";
                    
                } elseif ($action == 'process') {
                    // Process refund (mark as processed)
                    $refund_method = $_POST['refund_method'] ?? 'original';
                    $refund_transaction = trim($_POST['refund_transaction'] ?? '');
                    
                                $stmt = $pdo->prepare("
                                    UPDATE purchases 
                                    SET refund_status = 'processed',
                                        refund_method = ?,
                                        refund_transaction_id = ?,
                                        is_active = 0,
                                        deleted_at = DATE_ADD(NOW(), INTERVAL 1 DAY)
                                    WHERE id = ?
                                ");
                                $stmt->execute([$refund_method, $refund_transaction, $purchase_id]);
                    
                    $message = "Refund processed successfully! Package has been removed from user's account.";
                }
                
            } catch (PDOException $e) {
                $error = "Failed to process request: " . $e->getMessage();
                error_log("Refund admin error: " . $e->getMessage());
            }
        }
    }
}

// Get pending refund requests from refunds table
$pending_stmt = $pdo->query("
    SELECT r.id as refund_id, r.*, p.id as purchase_id, p.package_id,
           u.full_name as user_name, 
           u.email, 
           u.phone,
           pk.name as package_name,
           pk.price
    FROM refunds r
    JOIN purchases p ON r.purchase_id = p.id
    JOIN users u ON r.user_id = u.id
    JOIN packages pk ON p.package_id = pk.id
    WHERE r.status = 'pending'
    ORDER BY r.request_date DESC
");
$pending_requests = $pending_stmt->fetchAll();

// Get approved/processed/rejected refunds from refunds table
$approved_stmt = $pdo->query("
    SELECT r.id as refund_id, r.*, p.id as purchase_id,
           u.full_name as user_name, 
           u.email, 
           pk.name as package_name,
           pk.price,
           a.full_name as approved_by_name
    FROM refunds r
    JOIN purchases p ON r.purchase_id = p.id
    JOIN users u ON r.user_id = u.id
    JOIN packages pk ON p.package_id = pk.id
    LEFT JOIN users a ON r.approved_by = a.id
    WHERE r.status IN ('approved', 'processed', 'rejected')
    ORDER BY r.approved_date DESC
    LIMIT 20
");
$approved_requests = $approved_stmt->fetchAll();

// Count pending
$pending_count = count($pending_requests);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Refund Requests - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .refund-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .pending-count {
            background: #f39c12;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
        }
        
        .refund-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            border-left: 5px solid #f39c12;
            transition: transform 0.3s;
        }
        
        .refund-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        
        .refund-card.approved {
            border-left-color: #27ae60;
        }
        
        .refund-card.rejected {
            border-left-color: #e74c3c;
        }
        
        .refund-card.processed {
            border-left-color: #3498db;
        }
        
        .refund-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .user-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .user-email {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .user-phone {
            background: #f0f0f0;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .refund-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .package-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
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
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .refund-reason {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #f39c12;
        }
        
        .refund-reason strong {
            color: #856404;
            display: block;
            margin-bottom: 5px;
        }
        
        .refund-reason p {
            color: #856404;
            margin: 0;
            white-space: pre-wrap;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn-approve {
            background: #27ae60;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-approve:hover {
            background: #229954;
            transform: translateY(-2px);
        }
        
        .btn-reject {
            background: #e74c3c;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-reject:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .btn-process {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-process:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            color: #2c3e50;
            margin: 0;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #f39c12;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
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
    
    <div class="refund-container">
        <div class="page-header">
            <h1>💰 Refund Requests</h1>
            <div class="pending-count">
                <?php echo $pending_count; ?> Pending Request<?php echo $pending_count != 1 ? 's' : ''; ?>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <?php
            // Get statistics from refunds table
            $stats = $pdo->query("
                SELECT 
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'processed' THEN 1 ELSE 0 END) as processed,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status IN ('approved', 'processed') THEN amount ELSE 0 END) as total_refund_amount
                FROM refunds
            ")->fetch();
            ?>
            
            <div class="stat-card">
                <div>⏳ Pending</div>
                <div class="stat-number"><?php echo $stats['pending'] ?? 0; ?></div>
            </div>
            
            <div class="stat-card">
                <div>✅ Approved</div>
                <div class="stat-number"><?php echo $stats['approved'] ?? 0; ?></div>
            </div>
            
            <div class="stat-card">
                <div>💰 Processed</div>
                <div class="stat-number"><?php echo $stats['processed'] ?? 0; ?></div>
            </div>
            
            <div class="stat-card">
                <div>❌ Rejected</div>
                <div class="stat-number"><?php echo $stats['rejected'] ?? 0; ?></div>
            </div>
            
            <div class="stat-card">
                <div>💵 Total Refunded</div>
                <div class="stat-number">Rs. <?php echo number_format($stats['total_refund_amount'] ?? 0, 2); ?></div>
            </div>
        </div>
        
        <!-- Pending Requests -->
        <h2 style="margin-bottom: 20px;">⏳ Pending Refund Requests</h2>
        
        <?php if (empty($pending_requests)): ?>
            <div class="empty-state">
                <p>No pending refund requests</p>
                <p style="font-size: 0.9rem; margin-top: 10px;">When users request refunds, they'll appear here</p>
            </div>
        <?php else: ?>
            <?php foreach ($pending_requests as $request): ?>
            <div class="refund-card">
                <div class="refund-header">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($request['user_name']); ?></span>
                        <span class="user-email"><?php echo htmlspecialchars($request['email']); ?></span>
                        <span class="user-phone">📱 <?php echo htmlspecialchars($request['phone']); ?></span>
                    </div>
                    <div class="refund-date">
                        Requested: <?php echo date('M d, Y h:i A', strtotime($request['refund_request_date'])); ?>
                    </div>
                </div>
                
                <div class="package-details">
                    <div class="detail-item">
                        <span class="detail-label">Package</span>
                        <span class="detail-value"><?php echo htmlspecialchars($request['package_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Purchase ID</span>
                        <span class="detail-value">#<?php echo $request['id']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Purchase Date</span>
                        <span class="detail-value"><?php echo date('M d, Y', strtotime($request['purchase_date'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Amount Paid</span>
                        <span class="detail-value">Rs. <?php echo number_format($request['price'], 2); ?></span>
                    </div>
                </div>
                
                <div class="refund-reason">
                    <strong>📝 Refund Reason:</strong>
                    <p><?php echo nl2br(htmlspecialchars($request['notes'] ?? 'No reason provided')); ?></p>
                </div>
                
                <div class="action-buttons">
                    <button class="btn-approve" onclick="openApproveModal(<?php echo $request['refund_id']; ?>, <?php echo $request['purchase_id']; ?>, '<?php echo htmlspecialchars($request['package_name']); ?>', <?php echo $request['price']; ?>)">
                        ✅ Approve Refund
                    </button>
                    
                    <button class="btn-reject" onclick="openRejectModal(<?php echo $request['refund_id']; ?>, '<?php echo htmlspecialchars($request['package_name']); ?>')">
                        ❌ Reject Refund
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Processed/Approved Requests -->
        <?php if (!empty($approved_requests)): ?>
            <h2 style="margin: 40px 0 20px;">📋 Recent Refund Activity</h2>
            
            <?php foreach ($approved_requests as $request): 
                $status_class = '';
                $status_text = '';
                
                switch($request['refund_status']) {
                    case 'approved':
                        $status_class = 'approved';
                        $status_text = '✅ Approved';
                        break;
                    case 'processed':
                        $status_class = 'processed';
                        $status_text = '💰 Processed';
                        break;
                    case 'rejected':
                        $status_class = 'rejected';
                        $status_text = '❌ Rejected';
                        break;
                }
            ?>
            <div class="refund-card <?php echo $status_class; ?>">
                <div class="refund-header">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($request['user_name']); ?></span>
                        <span class="user-email"><?php echo htmlspecialchars($request['email']); ?></span>
                    </div>
                    <div>
                        <span class="status-badge" style="margin-right: 15px; background: <?php 
                            echo $request['refund_status'] == 'approved' ? '#27ae60' : 
                                ($request['refund_status'] == 'processed' ? '#3498db' : '#e74c3c'); 
                        ?>; color: white; padding: 5px 10px; border-radius: 20px;">
                            <?php echo $status_text; ?>
                        </span>
                        <span class="refund-date">
                            <?php echo $request['status'] == 'approved' ? 'Approved' : 'Updated'; ?>: 
                            <?php echo date('M d, Y', strtotime($request['approved_date'] ?? $request['request_date'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="package-details">
                    <div class="detail-item">
                        <span class="detail-label">Package</span>
                        <span class="detail-value"><?php echo htmlspecialchars($request['package_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Amount</span>
                        <span class="detail-value">Rs. <?php echo number_format($request['price'], 2); ?></span>
                    </div>
                    <?php if ($request['refund_method']): ?>
                    <div class="detail-item">
                        <span class="detail-label">Refund Method</span>
                        <span class="detail-value"><?php echo ucfirst($request['refund_method']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($request['refund_transaction_id']): ?>
                    <div class="detail-item">
                        <span class="detail-label">Transaction ID</span>
                        <span class="detail-value"><?php echo htmlspecialchars($request['refund_transaction_id']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($request['status'] == 'approved'): ?>
                <div class="action-buttons">
                    <button class="btn-process" onclick="openProcessModal(<?php echo $request['refund_id']; ?>, <?php echo $request['purchase_id']; ?>, '<?php echo htmlspecialchars($request['package_name']); ?>', <?php echo $request['price']; ?>)">
                        💰 Process Refund
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="margin-top: 40px; text-align: center;">
            <a href="dashboard.php" class="btn-cancel">← Back to Dashboard</a>
        </div>
    </div>
    
    <!-- Approve Modal -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✅ Approve Refund Request</h3>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="refund_id" id="approve_refund_id">
                <input type="hidden" name="purchase_id" id="approve_purchase_id">
                <input type="hidden" name="action" value="approve">
                
                <div class="modal-body">
                    <p>Are you sure you want to approve this refund request?</p>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <p><strong>Package:</strong> <span id="approve_package_name"></span></p>
                        <p><strong>Refund Amount:</strong> Rs. <span id="approve_amount"></span></p>
                    </div>
                    <p style="color: #27ae60; margin-top: 15px;">
                        ✅ Once approved, you can process the refund later.
                    </p>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn-approve">Yes, Approve</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('approveModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>❌ Reject Refund Request</h3>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="refund_id" id="reject_refund_id">
                <input type="hidden" name="action" value="reject">
                
                <div class="modal-body">
                    <p>Please provide a reason for rejection:</p>
                    <div class="form-group">
                        <textarea name="reject_reason" required minlength="10" maxlength="500" placeholder="Explain why the refund is being rejected..."></textarea>
                    </div>
                    <div style="background: #fef5f5; padding: 10px; border-radius: 5px; margin-top: 10px;">
                        <p style="color: #e74c3c; font-size: 0.9rem;">
                            ⚠️ The user will be notified of this decision.
                        </p>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn-reject">Reject Refund</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('rejectModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Process Modal -->
    <div id="processModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>💰 Process Refund</h3>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="refund_id" id="process_refund_id">
                <input type="hidden" name="purchase_id" id="process_purchase_id">
                <input type="hidden" name="action" value="process">
                
                <div class="modal-body">
                    <p>Enter refund processing details:</p>
                    
                    <div class="form-group">
                        <label>Refund Amount</label>
                        <input type="text" id="process_amount" readonly style="background: #f0f0f0; font-weight: bold;">
                    </div>
                    
                    <div class="form-group">
                        <label for="refund_method">Refund Method</label>
                        <select name="refund_method" id="refund_method" required>
                            <option value="original">Original Payment Method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="khalti">Khalti</option>
                            <option value="esewa">eSewa</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="refund_transaction">Transaction ID / Reference</label>
                        <input type="text" name="refund_transaction" id="refund_transaction" 
                               placeholder="Enter transaction ID or reference number" required>
                    </div>
                    
                    <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 10px;">
                        <p style="color: #856404; font-size: 0.9rem;">
                            ⚠️ This will permanently remove the package from the user's account.
                        </p>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn-process">Confirm Refund</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('processModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openApproveModal(refundId, purchaseId, packageName, amount) {
            document.getElementById('approve_refund_id').value = refundId;
            document.getElementById('approve_purchase_id').value = purchaseId;
            document.getElementById('approve_package_name').textContent = packageName;
            document.getElementById('approve_amount').textContent = amount;
            document.getElementById('approveModal').classList.add('active');
        }
        
        function openRejectModal(refundId, packageName) {
            document.getElementById('reject_refund_id').value = refundId;
            document.getElementById('rejectModal').classList.add('active');
        }
        
        function openProcessModal(refundId, purchaseId, packageName, amount) {
            document.getElementById('process_refund_id').value = refundId;
            document.getElementById('process_purchase_id').value = purchaseId;
            document.getElementById('process_amount').value = 'Rs. ' + amount;
            document.getElementById('processModal').classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>