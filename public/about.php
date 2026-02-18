<?php
session_start();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>About Us - GWPS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    // Use the unified navigation file
    $base_path = '../';
    include '../includes/navigation.php';
    ?>

    <section class="about-section">
        <h1>About GWPS</h1>
        <p>GWPS has been transforming lives through fitness since 2020. We believe that fitness is not just about looking good, but about feeling great and living a healthier, happier life.</p>
        
        <h2>Our Mission</h2>
        <p>To make fitness accessible and enjoyable for everyone, regardless of their starting point. We provide personalized workout plans, expert guidance, and a supportive community to help you achieve your goals.</p>
        
        <h2>Our Trainers</h2>
        <p>Meet our team of certified professionals with years of experience in fitness training, nutrition, and wellness coaching. Every trainer at FitLife is committed to your success.</p>
    </section>

    <section id="contact" class="contact-section">
        <h2>Contact Us</h2>
        <div class="contact-info">
            <p><strong>Address:</strong> Kathmandu, Nepal</p>
            <p><strong>Phone:</strong> +977 9800000000</p>
            <p><strong>Email:</strong> info@fitlifegym.com</p>
            <p><strong>Hours:</strong> 5:00 AM - 10:00 PM (Daily)</p>
        </div>
        
        <h3>Send us a Message</h3>
        <form class="contact-form" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
            <button type="submit">Send Message</button>
        </form>
    </section>

    <footer>
        <p>GWPS &copy; 2025 | <a href="#contact">Contact</a></p>
    </footer>
</body>
</html>