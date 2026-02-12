<?php
// includes/navigation.php
// Single navigation file for all pages

// Functions
function isLoggedIn() { 
    return isset($_SESSION['user_id']); 
}

function isAdmin() { 
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin'; 
}

// Current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Base path - should be set in the including file
if (!isset($base_path)) {
    $base_path = '';
}
?>

<nav class="user-nav">
    <div class="logo">FitLife Gym</div>
    <div class="nav-links">
        <a href="<?php echo $base_path; ?>index.php">Home</a>
        <a href="<?php echo $base_path; ?>public/packages.php">Packages</a>
        <a href="<?php echo $base_path; ?>public/about.php">About Us</a>
        
        <?php if (!isLoggedIn()): ?>
            <!-- NOT LOGGED IN -->
            <a href="<?php echo $base_path; ?>login.php">Login</a>
            <a href="<?php echo $base_path; ?>user/register.php">Register</a>
            
        <?php elseif (isAdmin()): ?>
            <!-- LOGGED IN AS ADMIN - THIS SECTION IS CRITICAL -->
            <a href="<?php echo $base_path; ?>admin/dashboard.php">Admin Dashboard</a>
            <a href="<?php echo $base_path; ?>user/logout.php" class="logout-btn">Logout</a>
            
        <?php else: ?>
            <!-- LOGGED IN AS REGULAR USER -->
            <a href="<?php echo $base_path; ?>user/dashboard.php">My Dashboard</a>
            <a href="<?php echo $base_path; ?>user/logout.php" class="logout-btn">Logout</a>
        <?php endif; ?>
    </div>
</nav>