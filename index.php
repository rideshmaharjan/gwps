<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>FitLife Gym - Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
   <?php include 'nav-index.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Transform Your Body, Transform Your Life</h1>
        <p>Join the best gym in town with personalized workout packages</p>
        <div class="cta-buttons">
            <a href="public/packages.php" class="btn-primary">View Packages</a>
            <a href="user/register.php" class="btn-secondary">Join Now</a>
        </div>
    </section>

    <!-- Features -->
    <section class="features">
        <h2>Why Choose FitLife?</h2>
        <div class="feature-grid">
            <div class="feature">
                <h3>Expert Trainers</h3>
                <p>Certified professionals to guide you</p>
            </div>
            <div class="feature">
                <h3>Flexible Packages</h3>
                <p>Choose plans that fit your schedule</p>
            </div>
            <div class="feature">
                <h3>24/7 Access</h3>
                <p>Workout anytime that suits you</p>
            </div>
        </div>
    </section>

    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="public/about.php#contact">Contact Us</a></p>
    </footer>
</body>
</html>