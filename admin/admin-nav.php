<!-- admin/admin-nav.php -->
<nav class="admin-nav">
    <div class="logo">FitLife Gym Admin</div>
    <div class="nav-links">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="manage-packages.php" class="<?php echo $current_page == 'manage-packages.php' ? 'active' : ''; ?>">Manage Packages</a>
        <a href="view-purchases.php" class="<?php echo $current_page == 'view-purchases.php' ? 'active' : ''; ?>">View Purchases</a>
        <a href="../index.php">View Site</a>
        <a href="../user/logout.php" class="logout-btn">Logout</a>
    </div>
</nav>