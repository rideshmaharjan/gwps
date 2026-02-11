<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>About Us - FitLife Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'public-nav.php'; ?>

    <section class="about-section">
        <h1>About FitLife Gym</h1>
        <p>FitLife Gym has been transforming lives through fitness...</p>
        
        <h2>Our Mission</h2>
        <p>To make fitness accessible and enjoyable for everyone...</p>
        
        <h2>Our Trainers</h2>
        <p>Meet our team of certified professionals...</p>
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
        <form class="contact-form">
            <input type="text" placeholder="Your Name" required>
            <input type="email" placeholder="Your Email" required>
            <textarea placeholder="Your Message" rows="5" required></textarea>
            <button type="submit">Send Message</button>
        </form>
    </section>

    <footer>
        <p>FitLife Gym &copy; 2025 | <a href="#contact">Contact</a></p>
    </footer>
</body>
</html>
