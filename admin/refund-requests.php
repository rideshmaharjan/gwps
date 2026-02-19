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
        $refund_id = filter_input(INPUT_POST, 'refund_id', FILTER_VALIDATE_INT);
        $action = $_POST['action'] ?? '';
        
        if ($refund_id && in_array($action, ['approve', 'reject', 'process'])) {
            try {
                if ($action == 'approve') {
                    // Approve refund - update refunds table only
                    $stmt = $pdo->prepare("
                        UPDATE refunds 
                        SET status = 'approved', 
                            approved_date = NOW(), 
                            approved_by = ?
                        WHERE id = ? AND status = 'pending'
                    ");
                    $stmt->execute([$_SESSION['user_id'], $refund_id]);
                    
                    if ($stmt->rowCount() > 0) {
                        $message = "Refund request approved successfully!";
                    } else {
                        $error = "Refund request not found or already processed.";
                    }
                    
                } elseif ($action == 'reject') {
                    // Reject refund
                    $reject_reason = trim($_POST['reject_reason'] ?? '');
                    
                    $stmt = $pdo->prepare("
                        UPDATE refunds 
                        SET status = 'rejected',
                            notes = CONCAT(IFNULL(notes, ''), '\n\nRejection Reason: ', ?)
                        WHERE id = ? AND status = 'pending'
                    ");
                    $stmt->execute([$reject_reason, $refund_id]);
                    
                    if ($stmt->rowCount() > 0) {
                        $message = "Refund request rejected.";
                    } else {
                        $error = "Refund request not found or already processed.";
                    }
                    
                } elseif ($action == 'process') {
                    // Process refund - THIS IS THE KEY PART
                    $refund_method = $_POST['refund_method'] ?? 'original';
                    $refund_transaction = trim($_POST['refund_transaction'] ?? '');
                    
                    // Begin transaction
                    $pdo->beginTransaction();
                    
                    try {
                        // Get refund details with package information
                        $get_details = $pdo->prepare("
                            SELECT r.*, p.id as purchase_id, p.package_id, p.user_id,
                                   pk.name as package_name, pk.description as package_details,
                                   pk.price
                            FROM refunds r
                            JOIN purchases p ON r.purchase_id = p.id
                            JOIN packages pk ON p.package_id = pk.id
                            WHERE r.id = ? AND r.status = 'approved'
                        ");
                        $get_details->execute([$refund_id]);
                        $refund_data = $get_details->fetch();
                        
                        if ($refund_data) {
                            // 1. Update refund status to processed
                            $stmt = $pdo->prepare("
                                UPDATE refunds 
                                SET status = 'processed',
                                    refund_method = ?,
                                    refund_transaction_id = ?,
                                    processed_date = NOW()
                                WHERE id = ? AND status = 'approved'
                            ");
                            $stmt->execute([$refund_method, $refund_transaction, $refund_id]);
                            
                            // 2. Store package details in refund_logs before removing
                            $log_stmt = $pdo->prepare("
                                INSERT INTO refund_logs 
                                (refund_id, purchase_id, user_id, package_id, package_name, 
                                 package_details, amount_refunded, refund_method, transaction_id, processed_by)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            $log_stmt->execute([
                                $refund_id,
                                $refund_data['purchase_id'],
                                $refund_data['user_id'],
                                $refund_data['package_id'],
                                $refund_data['package_name'],
                                $refund_data['package_details'],
                                $refund_data['amount'],
                                $refund_method,
                                $refund_transaction,
                                $_SESSION['user_id']
                            ]);
                            
                            // 3. Deactivate the purchase
                            $update_purchase = $pdo->prepare("
                                UPDATE purchases 
                                SET is_active = 0,
                                    deleted_at = NOW()
                                WHERE id = ?
                            ");
                            $update_purchase->execute([$refund_data['purchase_id']]);
                            
                            $pdo->commit();
                            $message = "Refund processed successfully! Package has been removed from user's account. Details saved in refund logs.";
                        } else {
                            $pdo->rollBack();
                            $error = "Refund record not found or not in approved status.";
                        }
                        
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        throw $e;
                    }
                }
                
            } catch (PDOException $e) {
                $error = "Failed to process request: " . $e->getMessage();
                error_log("Refund admin error: " . $e->getMessage());
            }
        }
    }
}

// Get pending refund requests
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

// Get approved/processed/rejected refunds
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

// Get refund logs for history
$logs_stmt = $pdo->query("
    SELECT rl.*, u.full_name as user_name, a.full_name as processed_by_name
    FROM refund_logs rl
    LEFT JOIN users u ON rl.user_id = u.id
    LEFT JOIN users a ON rl.processed_by = a.id
    ORDER BY rl.refund_date DESC
    LIMIT 20
");
$refund_logs = $logs_stmt->fetchAll();

// Count pending
$pending_count = count($pending_requests);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Refund Requests - Admin</title>
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="refund-container">
        <div class="page-header">
            <h1>💰 Refund Management</h1>
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
        
        <!-- Tabs Navigation -->
        <div class="tabs">
            <div class="tab active" onclick="showTab('pending')">⏳ Pending Requests</div>
            <div class="tab" onclick="showTab('history')">📋 Refund History</div>
            <div class="tab" onclick="showTab('logs')">📦 Refund Logs (Package Details)</div>
        </div>
        
        <!-- Pending Requests Tab -->
        <div id="pending-tab" class="tab-content active">
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
                            Requested: <?php echo date('M d, Y h:i A', strtotime($request['request_date'])); ?>
                        </div>
                    </div>
                    
                    <div class="package-details">
                        <div class="detail-item">
                            <span class="detail-label">Package</span>
                            <span class="detail-value"><?php echo htmlspecialchars($request['package_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Purchase ID</span>
                            <span class="detail-value">#<?php echo $request['purchase_id']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Purchase Date</span>
                            <span class="detail-value"><?php echo date('M d, Y', strtotime($request['purchase_date'] ?? 'now')); ?></span>
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
                        <button class="btn-approve" onclick="openApproveModal(<?php echo $request['refund_id']; ?>, '<?php echo htmlspecialchars($request['package_name']); ?>', <?php echo $request['price']; ?>)">
                            ✅ Approve Refund
                        </button>
                        
                        <button class="btn-reject" onclick="openRejectModal(<?php echo $request['refund_id']; ?>, '<?php echo htmlspecialchars($request['package_name']); ?>')">
                            ❌ Reject Refund
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Refund History Tab -->
        <div id="history-tab" class="tab-content">
            <h2 style="margin-bottom: 20px;">📋 Refund History</h2>
            
            <?php if (empty($approved_requests)): ?>
                <div class="empty-state">
                    <p>No refund history yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($approved_requests as $request): 
                    $status_class = '';
                    $status_text = '';
                    
                    switch($request['status']) {
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
                                echo $request['status'] == 'approved' ? '#27ae60' : 
                                    ($request['status'] == 'processed' ? '#3498db' : '#e74c3c'); 
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
                        <button class="btn-process" onclick="openProcessModal(<?php echo $request['refund_id']; ?>, '<?php echo htmlspecialchars($request['package_name']); ?>', <?php echo $request['price']; ?>)">
                            💰 Process Refund
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Refund Logs Tab (Package Details) -->
        <div id="logs-tab" class="tab-content">
            <h2 style="margin-bottom: 20px;">📦 Refunded Package Details</h2>
            <p class="subtitle">Complete package information saved at the time of refund</p>
            
            <?php if (empty($refund_logs)): ?>
                <div class="empty-state">
                    <p>No refund logs yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($refund_logs as $log): ?>
                <div class="log-card">
                    <div class="log-header">
                        <span class="log-title"><?php echo htmlspecialchars($log['package_name']); ?></span>
                        <span class="log-date">Refunded: <?php echo date('M d, Y h:i A', strtotime($log['refund_date'])); ?></span>
                    </div>
                    
                    <div class="log-details">
                        <div><strong>User:</strong> <?php echo htmlspecialchars($log['user_name']); ?></div>
                        <div><strong>Amount:</strong> Rs. <?php echo number_format($log['amount_refunded'], 2); ?></div>
                        <div><strong>Method:</strong> <?php echo ucfirst($log['refund_method']); ?></div>
                        <div><strong>Transaction ID:</strong> <?php echo htmlspecialchars($log['transaction_id']); ?></div>
                        <div><strong>Processed By:</strong> <?php echo htmlspecialchars($log['processed_by_name']); ?></div>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <button class="btn-view-details" onclick="viewPackageDetails(<?php echo htmlspecialchars(json_encode($log)); ?>)">
                            🔍 View Full Package Details
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
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
                            ⚠️ This will permanently remove the package from the user's account.<br>
                            <strong>Note:</strong> Package details will be saved in Refund Logs.
                        </p>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn-process">Confirm Refund & Remove Package</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('processModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Package Details Modal -->
    <div id="packageDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📦 Package Details (Refunded)</h3>
            </div>
            <div class="modal-body" id="packageDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('packageDetailsModal')">Close</button>
            </div>
        </div>
    </div>
    
    <script>
        function openApproveModal(refundId, packageName, amount) {
            document.getElementById('approve_refund_id').value = refundId;
            document.getElementById('approve_package_name').textContent = packageName;
            document.getElementById('approve_amount').textContent = amount;
            document.getElementById('approveModal').classList.add('active');
        }
        
        function openRejectModal(refundId, packageName) {
            document.getElementById('reject_refund_id').value = refundId;
            document.getElementById('rejectModal').classList.add('active');
        }
        
        function openProcessModal(refundId, packageName, amount) {
            document.getElementById('process_refund_id').value = refundId;
            document.getElementById('process_amount').value = 'Rs. ' + amount;
            document.getElementById('processModal').classList.add('active');
        }
        
        function viewPackageDetails(log) {
            const content = document.getElementById('packageDetailsContent');
            
            // Parse the log data if it's a string
            const data = typeof log === 'string' ? JSON.parse(log) : log;
            
            // Format the package details nicely
            let detailsHtml = `
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                    <h4 style="color: #2c3e50; margin-bottom: 15px;">${data.package_name}</h4>
                    
                    <div style="margin-bottom: 20px;">
                        <strong>Amount Refunded:</strong> Rs. ${parseFloat(data.amount_refunded).toFixed(2)}<br>
                        <strong>Refund Method:</strong> ${data.refund_method}<br>
                        <strong>Transaction ID:</strong> ${data.transaction_id}<br>
                        <strong>Refund Date:</strong> ${new Date(data.refund_date).toLocaleString()}<br>
                        <strong>User:</strong> ${data.user_name}<br>
                        <strong>Processed By:</strong> ${data.processed_by_name}<br>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <strong>Package Details (Saved at Refund Time):</strong>
                        <div style="background: white; padding: 15px; border-radius: 5px; margin-top: 10px; border-left: 4px solid #9b59b6;">
                            ${data.package_details ? data.package_details.replace(/\n/g, '<br>') : 'No details available'}
                        </div>
                    </div>
                </div>
            `;
            
            content.innerHTML = detailsHtml;
            document.getElementById('packageDetailsModal').classList.add('active');
        }
        
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