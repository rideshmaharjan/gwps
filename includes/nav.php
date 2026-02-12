<?php
/**
 * Reusable navigation component
 * Include this in all public pages
 */

// Don't start session here - it should already be started in the parent file
?>
<nav>
    <div class="logo">FitLife Gym</div>
    <div class="nav-links">
        <a href="<?php echo $base_path ?? ''; ?>index.php" 
           class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
        <a href="<?php echo $base_path ?? ''; ?>public/packages.php" 
           class="<?php echo strpos($_SERVER['PHP_SELF'], 'packages.php') !== false ? 'active' : ''; ?>">Packages</a>
        <a href="<?php echo $base_path ?? ''; ?>public/about.php" 
           class="<?php echo strpos($_SERVER['PHP_SELF'], 'about.php') !== false ? 'active' : ''; ?>">About Us</a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- User is logged in -->
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="<?php echo $base_path ?? ''; ?>admin/dashboard.php">Admin Dashboard</a>
            <?php else: ?>
                <a href="<?php echo $base_path ?? ''; ?>user/dashboard.php">Dashboard</a>
            <?php endif; ?>
            <a href="<?php echo $base_path ?? ''; ?>user/logout.php" class="logout-btn">Logout</a>
        <?php else: ?>
            <!-- User is not logged in -->
            <a href="<?php echo $base_path ?? ''; ?>login.php">Login</a>
            <a href="<?php echo $base_path ?? ''; ?>user/register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>