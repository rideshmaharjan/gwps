<nav class="user-nav">
    <div class="logo">FitLife Gym</div>
    <div class="nav-links">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        
        // Define isLoggedIn function
        function isLoggedIn() {
            return isset($_SESSION['user_id']);
        }
        
        // Check if user is admin
        function isAdmin() {
            return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
        }
        ?>
        
        <a href="index.php">Home</a>
        <a href="public/packages.php">Packages</a>
        <a href="/public/about.php">About Us</a>
        
        <?php if (!isLoggedIn()): ?>
            <!-- NOT LOGGED IN - Show Login -->
            <a href="login.php">Login</a>
            
        <?php elseif (isAdmin()): ?>
            <!-- LOGGED IN AS ADMIN - Show Admin Dashboard -->
            <a href="admin/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Admin Dashboard</a>
            <a href="user/logout.php" class="logout-btn">Logout</a>
            
        <?php else: ?>
            <!-- LOGGED IN AS REGULAR USER - Show User Dashboard -->
            <a href="user/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">My Dashboard</a>
            <a href="user/logout.php" class="logout-btn">Logout</a>
        <?php endif; ?>
        
    </div>
</nav>