<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLife Gym</title>
    <link rel="stylesheet" href="css/homepage.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">FitLife Gym</div>
            <ul class="nav-links">
                <li><a href="homepage.php">Home</a></li>
                <li><a href="public/about.php">About</a></li>
                <li><a href="public/packages.php">Packages</a></li>
                <li><a href="user/login.php">Login/Register</a></li>
            </ul>
        </nav>
    </header>

    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Transform Your Body, Transform Your Life</h1>
            <p>Join the best gym in town with state-of-the-art facilities</p>
            <a href="user/register.php" class="cta-button">Get Started</a>
        </div>
    </section>

    <section id="packages" class="packages">
        <h2>Our Packages</h2>
        <div class="package-grid">
            <div class="package-card">
                <h3>Basic</h3>
                <p class="price">999/month</p>
                <ul>
                    <li>Gym Access</li>
                    <li>Cardio Area</li>
                    <li>Locker Room</li>
                </ul>
            </div>
            <div class="package-card popular">
                <h3>Premium</h3>
                <p class="price">1999/month</p>
                <ul>
                    <li>All Basic Features</li>
                    <li>Personal Trainer</li>
                    <li>Diet Plan</li>
                </ul>
            </div>
            <div class="package-card">
                <h3>Ultimate</h3>
                <p class="price">2999/month</p>
                <ul>
                    <li>All Premium Features</li>
                    <li>Yoga Classes</li>
                    <li>Massage Therapy</li>
                </ul>
            </div>
        </div>
    </section>
</body>
</html>