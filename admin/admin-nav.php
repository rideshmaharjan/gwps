<nav class="admin-nav">
    <div class="logo">GWPS Admin</div>
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
        </a>
        <a href="refund-requests.php" class="<?php echo $current_page == 'refund-requests.php' ? 'active' : ''; ?>">
    💰 Refund Requests
</a>
    </div>
</nav>

<style>
.admin-nav {
    background: linear-gradient(135deg, #2c3e50, #1a252f);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.admin-nav .logo {
    color: white;
    font-size: 1.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #3498db, #2980b9);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.admin-nav .nav-links {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.admin-nav .nav-links a {
    color: white;
    text-decoration: none;
    padding: 0.7rem 1.2rem;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
}

.admin-nav .nav-links a:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.admin-nav .nav-links a.active {
    background: rgba(52, 152, 219, 0.3);
    border-bottom: 3px solid #3498db;
}

.admin-nav .nav-links .view-site {
    background: #27ae60;
    color: white;
}

.admin-nav .nav-links .view-site:hover {
    background: #229954;
}

.admin-nav .nav-links .logout-btn {
    background: #e74c3c;
    color: white;
}

.admin-nav .nav-links .logout-btn:hover {
    background: #c0392b;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .admin-nav {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
    }
    
    .admin-nav .nav-links {
        flex-direction: column;
        width: 100%;
    }
    
    .admin-nav .nav-links a {
        width: 100%;
        text-align: center;
    }
}
</style>