<nav class="admin-nav">
    <div class="logo">FitLife Gym Admin</div>
    <div class="nav-links">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="view-purchases.php" class="<?php echo $current_page == 'view-purchases.php' ? 'active' : ''; ?>">View Purchases</a>
        <a href="../index.php">View Site</a>
        <a href="manage-users.php" class="<?php echo $current_page == 'manage-users.php' ? 'active' : ''; ?>">Manage Users</a>
        <!-- ADD THIS LINE -->
        <a href="delete-requests.php" class="<?php echo $current_page == 'delete-requests.php' ? 'active' : ''; ?>" style="background: #f39c12; color: white;">📝 Delete Requests</a>
        <a href="../user/logout.php" class="logout-btn">Logout</a>
    </div>
</nav>