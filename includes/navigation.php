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

// Display login success message
if (isset($_SESSION['login_success'])) {
    $login_message = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
}

// Display logout message
if (isset($_SESSION['logout_message'])) {
    $logout_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

// Display general success/error messages
$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!-- Notification Container -->
<?php if (isset($login_message) || isset($logout_message) || $success_message || $error_message): ?>
<div class="notification-container">
    <?php if (isset($login_message)): ?>
        <div class="notification success show">
            <div class="notification-icon">👋</div>
            <div class="notification-message"><?php echo htmlspecialchars($login_message); ?></div>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($logout_message)): ?>
        <div class="notification info show">
            <div class="notification-icon">👋</div>
            <div class="notification-message"><?php echo htmlspecialchars($logout_message); ?></div>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="notification success show">
            <div class="notification-icon">✅</div>
            <div class="notification-message"><?php echo htmlspecialchars($success_message); ?></div>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="notification error show">
            <div class="notification-icon">⚠️</div>
            <div class="notification-message"><?php echo htmlspecialchars($error_message); ?></div>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        </div>
    <?php endif; ?>
</div>



<script>
// Auto-hide notifications after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(function(notification) {
        setTimeout(function() {
            notification.classList.add('hide');
            setTimeout(function() {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 500);
        }, 5000);
    });
});
</script>
<?php endif; ?>

<nav class="user-nav">
    <div class="logo">GWPS</div>
    <div class="nav-links">
        <a href="<?php echo $base_path; ?>index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a>
        <a href="<?php echo $base_path; ?>public/packages.php" class="<?php echo $current_page == 'packages.php' ? 'active' : ''; ?>">Packages</a>
        
        <?php if (!isLoggedIn()): ?>
            <!-- NOT LOGGED IN -->
            <a href="<?php echo $base_path; ?>public/about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">About Us</a>
            <a href="<?php echo $base_path; ?>login.php" class="<?php echo $current_page == 'login.php' ? 'active' : ''; ?>">Login</a>
            <a href="<?php echo $base_path; ?>user/register.php" class="<?php echo $current_page == 'register.php' ? 'active' : ''; ?>">Register</a>
            
        <?php elseif (isAdmin()): ?>
            <!-- LOGGED IN AS ADMIN -->
            <a href="<?php echo $base_path; ?>admin/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
            <a href="<?php echo $base_path; ?>user/logout.php" class="logout-btn">Logout</a>
            
        <?php else: ?>
            <!-- LOGGED IN AS REGULAR USER -->
            <a href="<?php echo $base_path; ?>user/my-packages.php" class="<?php echo $current_page == 'my-packages.php' ? 'active' : ''; ?>">My Packages</a>
            <a href="<?php echo $base_path; ?>user/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
            <a href="<?php echo $base_path; ?>user/logout.php" class="logout-btn">Logout</a>
        <?php endif; ?>
    </div>
</nav>

