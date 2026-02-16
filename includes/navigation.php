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

<style>
.user-nav {
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

.user-nav .logo {
    color: white;
    font-size: 1.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #3498db, #2980b9);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.user-nav .nav-links {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.user-nav .nav-links a {
    color: white;
    text-decoration: none;
    padding: 0.7rem 1.2rem;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
}

.user-nav .nav-links a:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.user-nav .nav-links a.active {
    background: rgba(52, 152, 219, 0.3);
    border-bottom: 3px solid #3498db;
}

.user-nav .nav-links .logout-btn {
    background: #e74c3c;
    color: white;
}

.user-nav .nav-links .logout-btn:hover {
    background: #c0392b;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .user-nav {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
    }
    
    .user-nav .nav-links {
        flex-direction: column;
        width: 100%;
    }
    
    .user-nav .nav-links a {
        width: 100%;
        text-align: center;
    }
}
</style>