<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user bookings from database (you'll add this later)
require_once '../includes/database.php';
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Dashboard - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav>
        <div class="logo">FitLife Gym</div>
        <div class="nav-links">
            <a href="../index.php">Home</a>
            <a href="../public/packages.php">Packages</a>
            <a href="../public/about.php">About Us</a>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        
        <div class="user-info">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
        </div>
        
        <div class="dashboard-sections">
            <div class="dashboard-card">
                <h3>My Bookings</h3>
                <p>View and manage your gym package bookings</p>
                <a href="my-bookings.php" class="btn-primary">View Bookings</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Book New Package</h3>
                <p>Browse and book available gym packages</p>
                <a href="../public/packages.php" class="btn-primary">View Packages</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Profile Settings</h3>
                <p>Update your personal information</p>
                <a href="profile.php" class="btn-primary">Edit Profile</a>
            </div>
        </div>
    </div>

    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="../public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>