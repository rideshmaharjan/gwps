<nav class="admin-nav">
    <div class="logo">FitLife Gym Admin</div>
    <div class="nav-links">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            📊 Dashboard
        </a>
        
        <a href="manage-packages.php" class="<?php echo $current_page == 'manage-packages.php' ? 'active' : ''; ?>">
            📦 Manage Packages
        </a>
        
        <a href="add-package.php" class="<?php echo $current_page == 'add-package.php' ? 'active' : ''; ?>">
            ➕ Add Package
        </a>
        
        <a href="view-purchases.php" class="<?php echo $current_page == 'view-purchases.php' ? 'active' : ''; ?>">
            💰 Purchases
        </a>
        
        <a href="manage-users.php" class="<?php echo $current_page == 'manage-users.php' ? 'active' : ''; ?>">
            👥 Manage Users
        </a>
        
        <a href="backup.php" class="<?php echo $current_page == 'backup.php' ? 'active' : ''; ?>">
            💾 Backup
        </a>
        
        <a href="../index.php" class="view-site">
            🌐 View Site
        </a>
        
        <a href="../user/logout.php" class="logout-btn">
            🚪 Logout
       
    </div>
</nav>

