<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/database.php';
$user_id = $_SESSION['user_id'];

// Get user's full name and email from database
$stmt = $pdo->prepare("SELECT full_name, email, phone, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get active packages count
$active_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM purchases WHERE user_id = ? AND is_active = 1 AND (status != 'completed' OR status IS NULL)");
$active_stmt->execute([$user_id]);
$active_count = $active_stmt->fetch()['count'];

// Get completed programs count
$completed_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM purchases WHERE user_id = ? AND is_active = 1 AND status = 'completed'");
$completed_stmt->execute([$user_id]);
$completed_count = $completed_stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Dashboard - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Main container */
        .dashboard-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 15px;
        }
        
        /* Welcome header */
        .dashboard-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .dashboard-header h1 {
            font-size: 1.8rem;
            margin: 0 0 5px 0;
            font-weight: 600;
        }
        
        .dashboard-header p {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
        }
        
        /* User info card */
        .user-info-card {
            background: white;
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            border: 1px solid #eee;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-icon {
            font-size: 1.5rem;
        }
        
        .info-content {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.75rem;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 500;
        }
        
        /* Stats row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: #3498db;
            line-height: 1.2;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        /* Dashboard sections grid */
        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        /* Dashboard cards */
        .dashboard-card {
            background: white;
            border-radius: 8px;
            padding: 18px 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }
        
        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .dashboard-card h3 {
            font-size: 1.2rem;
            color: #2c3e50;
            margin: 0 0 8px 0;
            font-weight: 600;
        }
        
        .dashboard-card p {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin: 0 0 15px 0;
            line-height: 1.4;
            flex-grow: 1;
        }
        
        .dashboard-card .btn-primary {
            display: inline-block;
            padding: 8px 16px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.85rem;
            text-align: center;
            transition: background 0.2s;
            align-self: flex-start;
            border: none;
            cursor: pointer;
        }
        
        .dashboard-card .btn-primary:hover {
            background: #2980b9;
        }
        
        .dashboard-card .btn-settings {
            background: #f39c12;
        }
        
        .dashboard-card .btn-settings:hover {
            background: #e67e22;
        }
        
        /* Footer */
        footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            font-size: 0.9rem;
        }
        
        footer a {
            color: #3498db;
            text-decoration: none;
        }
        
        footer a:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
            
            .user-info-card {
                flex-direction: column;
                gap: 10px;
            }
            
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-container {
                padding: 0 10px;
            }
            
            .dashboard-header {
                padding: 15px;
            }
            
            .stat-number {
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

    <div class="dashboard-container">
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?>! 🚀</h1>
            <p>Here's what's happening with your fitness journey</p>
        </div>
        
        <!-- User Info Card -->
        <div class="user-info-card">
            <div class="info-item">
                <span class="info-icon">📧</span>
                <div class="info-content">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
            
            <div class="info-item">
                <span class="info-icon">📅</span>
                <div class="info-content">
                    <span class="info-label">Member Since</span>
                    <span class="info-value"><?php echo date('F Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
            
            <div class="info-item">
                <span class="info-icon">📱</span>
                <div class="info-content">
                    <span class="info-label">Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-icon">📦</div>
                <div class="stat-number"><?php echo $active_count; ?></div>
                <div class="stat-label">Active Packages</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $completed_count; ?></div>
                <div class="stat-label">Completed Programs</div>
            </div>
        </div>
        
        <!-- Main Dashboard Sections -->
        <div class="dashboard-sections">
            <!-- My Packages Card -->
            <div class="dashboard-card">
                <div class="card-icon">📦</div>
                <h3>My Packages</h3>
                <p>View your purchased workout packages</p>
                <a href="my-packages.php" class="btn-primary">View Packages →</a>
            </div>
            
            <!-- Buy New Package Card -->
            <div class="dashboard-card">
                <div class="card-icon">🛒</div>
                <h3>Buy New Package</h3>
                <p>Browse and purchase available workout packages</p>
                <a href="../public/packages.php" class="btn-primary">Browse Packages →</a>
            </div>
            
            <!-- Account Settings Card -->
            <div class="dashboard-card">
                <div class="card-icon">⚙️</div>
                <h3>Account Settings</h3>
                <p>Update your profile, change password, and manage preferences</p>
                <a href="profile-settings.php" class="btn-primary btn-settings">Settings →</a>
            </div>
        </div>
    </div>

    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>