<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to purchase a package';
    header('Location: ../login.php');
    exit();
}

// PREVENT ADMIN FROM BUYING PACKAGES
if ($_SESSION['role'] == 'admin') {
    $_SESSION['error'] = 'Admins have automatic access to all packages';
    header('Location: ../admin/dashboard.php');
    exit();
}

require_once '../includes/database.php';

$package_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$package_id || $package_id <= 0) {
    $_SESSION['error'] = 'Invalid package';
    header('Location: ../public/packages.php');
    exit();
}

// Get package details
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch();

if (!$package) {
    $_SESSION['error'] = 'Package not found';
    header('Location: ../public/packages.php');
    exit();
}

// Check ownership
$check = $pdo->prepare("SELECT id FROM purchases WHERE user_id = ? AND package_id = ? AND is_active = 1");
$check->execute([$_SESSION['user_id'], $package_id]);

if ($check->fetch()) {
    $_SESSION['error'] = 'You already own this package. View it in My Packages.';
    header('Location: my-packages.php');
    exit();
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buy Package - <?php echo htmlspecialchars($package['name']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .purchase-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .package-summary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .package-summary h2 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }
        
        .package-summary .price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ffd700;
            margin: 10px 0;
        }
        
        .payment-options {
            display: flex;
            justify-content: center;
            margin: 40px 0;
        }
        
        .payment-option {
            width: 100%;
            max-width: 350px;
            cursor: pointer;
            padding: 30px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s ease;
            background: white;
        }
        
        .payment-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: #3498db;
        }
        
        .payment-option.selected {
            border-color: #3498db;
            background: #f0f8ff;
            box-shadow: 0 5px 20px rgba(52,152,219,0.2);
        }
        
        .payment-option img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 15px;
        }
        
        .payment-option h3 {
            margin: 10px 0;
            color: #2c3e50;
            font-size: 1.5rem;
        }
        
        .payment-option p {
            color: #7f8c8d;
            font-size: 0.95rem;
            margin: 5px 0;
        }
        
        .payment-option small {
            color: #e74c3c;
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        
        .pay-btn {
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            max-width: 300px;
            margin: 20px auto;
            display: block;
        }
        
        .pay-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .pay-btn:not(:disabled):hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(39,174,96,0.3);
        }
        
        .btn-cancel {
            display: inline-block;
            padding: 12px 25px;
            background: #95a5a6;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        
        .error {
            background: #fde8e8;
            color: #e74c3c;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php
    $base_path = '../';
    include '../includes/navigation.php';
    ?>
    
    <div class="purchase-container">
        <h1>Complete Your Purchase</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Package Summary -->
        <div class="package-summary">
            <h2><?php echo htmlspecialchars($package['name']); ?></h2>
            <div class="price">Rs. <?php echo number_format($package['price'], 2); ?></div>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></p>
            <p><?php echo htmlspecialchars($package['short_description'] ?? $package['description']); ?></p>
        </div>
        
        <h2 style="text-align: center; margin-bottom: 20px;">Select Payment Method</h2>
        
        <!-- Payment Options -->
        <div class="payment-options">
            <!-- Mock/Test Payment Option -->
            <div class="payment-option" id="mock-payment">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='%233498db'/%3E%3Ctext x='50' y='70' font-size='30' text-anchor='middle' fill='white' font-family='Arial'%3ET%3C/text%3E%3C/svg%3E" 
                     alt="Test Mode">
                <h3>Test Payment</h3>
                <p>For development and testing</p>
                <p style="color: #666;">No real money will be charged</p>
                <small>Development Mode Only</small>
            </div>
        </div>
        
        <!-- Payment Form -->
        <form method="POST" action="mock-payment.php" id="payment-form">
            <input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
            <button type="submit" class="pay-btn" id="pay-btn" disabled>
                Proceed to Payment
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="../public/package-details.php?id=<?php echo $package_id; ?>" class="btn-cancel">
                ← Back to Package Details
            </a>
        </div>
    </div>
    
    <script>
        // Get elements
        const mockPayment = document.getElementById('mock-payment');
        const payBtn = document.getElementById('pay-btn');
        
        // Add click event to payment option
        mockPayment.addEventListener('click', function() {
            // Remove selected class from any other option (though there's only one)
            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Enable pay button
            payBtn.disabled = false;
        });
        
        // Prevent form submission if no payment method selected
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            if (payBtn.disabled) {
                e.preventDefault();
                alert('Please select a payment method first');
            }
        });
    </script>
</body>
</html>