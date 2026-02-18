<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>GWPS - Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
   <?php
   $base_path = '';
   include 'includes/navigation.php';
   ?>

    <!-- Hero Section with Background Image -->
    <section class="hero" style="background-image: linear-gradient(135deg, rgba(44, 62, 80, 0.85), rgba(231, 76, 60, 0.85)), 
              url('images/gym-hero.jpg');">
        <h1>Transform Your Body, Transform Your Life</h1>
        <p>Join the best gym in town with personalized workout packages</p>
        <div class="cta-buttons">
            <a href="public/packages.php" class="btn-primary">View Packages</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="user/register.php" class="btn-secondary">Join Now</a>
            <?php else: ?>
                <a href="user/dashboard.php" class="btn-secondary">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <p>GWPS &copy; 2025 | <a href="public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>