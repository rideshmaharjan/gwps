<!-- user/user-nav.php -->
<nav class="user-nav">
    <div class="logo">FitLife Gym</div>
    <div class="nav-links">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <a href="../index.php">Home</a>
        <a href="../public/packages.php">Packages</a>
        <a href="../public/about.php">About Us</a>
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>