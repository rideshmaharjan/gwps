<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// PREVENT ADMIN FROM REQUESTING REFUNDS
if ($_SESSION['role'] == 'admin') {
    $_SESSION['error'] = 'Admins cannot request refunds';
    header('Location: ../admin/dashboard.php');
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

// Get purchase details
$stmt = $pdo->prepare("
    SELECT pur.*, p.name as package_name, p.price
    FROM purchases pur
    JOIN packages p ON pur.package_id = p.id
    WHERE pur.id = ? AND pur.user_id = ?
");
$stmt->execute([$purchase_id, $user_id]);
$purchase = $stmt->fetch();

if (!$purchase) {
    $_SESSION['error'] = 'Package not found';
    header('Location: my-packages.php');
    exit();
}

// Check if already has a pending refund request
$ref_check = $pdo->prepare("
    SELECT id FROM refunds WHERE purchase_id = ? AND status IN ('pending', 'approved')
");
$ref_check->execute([$purchase_id]);
if ($ref_check->fetch()) {
    $_SESSION['error'] = 'You have already requested a refund for this package';
    header('Location: my-packages.php');
    exit();
}

// Check if package is active
if (!$purchase['is_active']) {
    $_SESSION['error'] = 'This package is no longer active';
    header('Location: my-packages.php');
    exit();
}

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reason = trim($_POST['reason'] ?? '');
    
    if (empty($reason)) {
        $errors['reason'] = 'Please provide a reason for refund';
    } elseif (strlen($reason) < 10) {
        $errors['reason'] = 'Reason must be at least 10 characters';
    } elseif (strlen($reason) > 500) {
        $errors['reason'] = 'Reason must not exceed 500 characters';
    }
    
    if (empty($errors)) {
        try {
            // Insert into refunds table
            $stmt = $pdo->prepare("
                INSERT INTO refunds (purchase_id, user_id, amount, status, notes, request_date)
                VALUES (?, ?, ?, 'pending', ?, NOW())
            ");
            $stmt->execute([$purchase_id, $user_id, $purchase['amount'], $reason]);
            
            $_SESSION['success'] = 'Refund request submitted successfully! Admin will review your request.';
            header('Location: my-packages.php');
            exit();
            
        } catch (PDOException $e) {
            $errors['database'] = 'Failed to submit refund request. Please try again.';
            error_log("Refund request error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Refund - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .refund-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .refund-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        
        .package-summary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .package-summary h3 {
            margin: 0 0 10px 0;
            font-size: 1.3rem;
        }
        
        .package-summary .amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffd700;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .warning-box ul {
            margin: 10px 0 0 20px;
        }
        
        .reason-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            display: block;
        }
        
        .reason-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            resize: vertical;
            min-height: 150px;
            font-family: inherit;
        }
        
        .reason-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        
        .reason-input.error {
            border-color: #e74c3c;
        }
        
        .char-count {
            text-align: right;
            font-size: 0.85rem;
            color: #95a5a6;
            margin-top: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn-submit {
            background: #f39c12;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            flex: 2;
        }
        
        .btn-submit:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: #95a5a6;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            flex: 1;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="refund-container">
        <div class="refund-card">
            <h1>💰 Request Refund</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="package-summary">
                <h3><?php echo htmlspecialchars($purchase['package_name']); ?></h3>
                <p>Purchase Date: <?php echo date('M d, Y', strtotime($purchase['purchase_date'])); ?></p>
                <div class="amount">Refund Amount: Rs. <?php echo number_format($purchase['price'], 2); ?></div>
            </div>
            
            <div class="warning-box">
                <strong>⚠️ Important Information:</strong>
                <ul>
                    <li>Refunds are processed within 3-5 business days</li>
                    <li>Amount will be credited back to your original payment method</li>
                    <li>Once the refund is approved and processed, you will immediately lose access to this package.</li>
                    <li>The package will be permanently removed from your account after 1 day.</li>
                    <li>This action cannot be undone</li>
                </ul>
            </div>
            
            <form method="POST" action="">
                <label class="reason-label">Reason for Refund:</label>
                <textarea 
                    name="reason" 
                    class="reason-input <?php echo isset($errors['reason']) ? 'error' : ''; ?>"
                    placeholder="Please explain why you want a refund..."
                    maxlength="500"
                ><?php echo htmlspecialchars($_POST['reason'] ?? ''); ?></textarea>
                <div class="char-count">
                    <span id="charCount">0</span>/500 characters
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn-submit">Submit Refund Request</button>
                    <a href="my-packages.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Character counter
        const textarea = document.querySelector('.reason-input');
        const charCount = document.getElementById('charCount');
        
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    </script>
    
    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>