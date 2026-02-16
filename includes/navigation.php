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

<style>
.notification-container {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 350px;
}

.notification {
    background: white;
    border-radius: 10px;
    padding: 15px 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 15px;
    transform: translateX(120%);
    opacity: 0;
    transition: all 0.5s ease;
    border-left: 4px solid;
}

.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.notification.success {
    border-left-color: #27ae60;
    background: #f0fff4;
}

.notification.error {
    border-left-color: #e74c3c;
    background: #fef5f5;
}

.notification.info {
    border-left-color: #3498db;
    background: #f0f8ff;
}

.notification-icon {
    font-size: 24px;
}

.notification-message {
    flex: 1;
    font-size: 14px;
    color: #2c3e50;
}

.notification-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #95a5a6;
    padding: 0 5px;
}

.notification-close:hover {
    color: #e74c3c;
}

/* Auto-hide animation */
@keyframes slideOut {
    0% { transform: translateX(0); opacity: 1; }
    100% { transform: translateX(120%); opacity: 0; }
}

.notification.hide {
    animation: slideOut 0.5s ease forwards;
}
</style>

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
    <div class="logo">FitLife Gym</div>
    <div class="nav-links">
        <a href="<?php echo $base_path; ?>index.php">Home</a>
        <a href="<?php echo $base_path; ?>public/packages.php">Packages</a>
        
        <?php if (!isLoggedIn()): ?>
            <!-- NOT LOGGED IN - Show About Us -->
            <a href="<?php echo $base_path; ?>public/about.php">About Us</a>
            <a href="<?php echo $base_path; ?>login.php">Login</a>
            <a href="<?php echo $base_path; ?>user/register.php">Register</a>
            
        <?php elseif (isAdmin()): ?>
            <!-- LOGGED IN AS ADMIN -->
            <a href="<?php echo $base_path; ?>admin/dashboard.php">Dashboard</a>
            <a href="<?php echo $base_path; ?>admin/manage-packages.php">Manage Packages</a>
            <a href="<?php echo $base_path; ?>user/logout.php" class="logout-btn">Logout</a>
            
        <?php else: ?>
            <!-- LOGGED IN AS REGULAR USER - My Packages comes first -->
            <a href="<?php echo $base_path; ?>user/my-packages.php">My Packages</a>
            <a href="<?php echo $base_path; ?>user/dashboard.php">Dashboard</a>
            <a href="<?php echo $base_path; ?>user/logout.php" class="logout-btn">Logout</a>
        <?php endif; ?>
    </div>
</nav>