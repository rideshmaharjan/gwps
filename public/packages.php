<!DOCTYPE html>
<html>
<head>
    <title>Workout Plans - FitLife Fitness</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav>
        <div class="logo">FitLife Fitness</div>
        <div class="nav-links">
            <a href="../homepage.php">Home</a>
            <a href="packages.php" class="active">Workout Plans</a>
            <a href="about.php">About Us</a>
            <a href="../login.php">Login</a>
        </div>
    </nav>

    <div class="container">
        <h1>Custom Workout Plans</h1>
        <p class="subtitle">Choose a workout routine tailored to your fitness goals</p>

        <div class="packages-grid">
            <!-- Package 1: Beginner's Foundation -->
            <div class="package-card">
                <div class="level-badge" style="background: #d4edda; color: #155724;">Beginner Level</div>
                <h3>Foundation Builder</h3>
                <p class="price">Rs. 1500/month</p>
                
                <div class="workout-details">
                    <p><strong>Duration:</strong> 8 weeks</p>
                    <p><strong>Focus:</strong> Full Body, Strength Basics</p>
                    
                    <div class="routine-day">
                        <div class="day-title">🏋️‍♂️ Monday - Full Body A</div>
                        <p>Squats: 3x12 | Push-ups: 3x10 | Rows: 3x12 | Plank: 3x30s</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">🚴‍♂️ Wednesday - Cardio & Core</div>
                        <p>Treadmill: 20min | Russian Twists: 3x15 | Leg Raises: 3x12</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">💪 Friday - Full Body B</div>
                        <p>Lunges: 3x12 | Dumbbell Press: 3x10 | Lat Pulldowns: 3x12</p>
                    </div>
                </div>
                
                <ul>
                    <li>3 workout days per week</li>
                    <li>Video exercise demonstrations</li>
                    <li>Weekly progress tracking</li>
                </ul>
                <div style="margin-top: 15px;">
                    <a href="workout-details.php?plan=foundation" class="btn-details">View Full Plan</a>
                    <a href="../user/register.php?plan=foundation" class="btn-book">Start Now</a>
                </div>
            </div>

            <!-- Package 2: Muscle Builder (Most Popular) -->
            <div class="package-card popular">
                <div class="badge">Most Popular</div>
                <div class="level-badge" style="background: #cce5ff; color: #004085;">Intermediate Level</div>
                <h3>Muscle Builder Pro</h3>
                <p class="price">Rs. 2500/month</p>
                
                <div class="workout-details">
                    <p><strong>Duration:</strong> 12 weeks</p>
                    <p><strong>Focus:</strong> Hypertrophy, Muscle Growth</p>
                    
                    <div class="routine-day">
                        <div class="day-title">💪 Monday - Chest & Triceps</div>
                        <p>Bench Press: 4x8 | Incline DB Press: 3x10 | Tricep Pushdowns: 3x12</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">🏋️‍♂️ Tuesday - Back & Biceps</div>
                        <p>Deadlifts: 4x6 | Pull-ups: 3x10 | Barbell Rows: 3x8</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">🦵 Thursday - Legs</div>
                        <p>Squats: 4x8 | Leg Press: 3x10 | Hamstring Curls: 3x12</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">🏃‍♂️ Saturday - Shoulders & Cardio</div>
                        <p>Military Press: 4x8 | Lateral Raises: 3x12 | Treadmill HIIT: 20min</p>
                    </div>
                </div>
                
                <ul>
                    <li>4-5 workout days per week</li>
                    <li>Personalized weight progression</li>
                    <li>Advanced exercise library</li>
                    <li>Macro-based nutrition plan</li>
                </ul>
                <div style="margin-top: 15px;">
                    <a href="workout-details.php?plan=muscle" class="btn-details">View Full Plan</a>
                    <a href="../user/register.php?plan=muscle" class="btn-book">Start Now</a>
                </div>
            </div>

            <!-- Package 3: Fat Loss Shred -->
            <div class="package-card">
                <div class="level-badge" style="background: #fff3cd; color: #856404;">All Levels</div>
                <h3>Fat Loss Shred</h3>
                <p class="price">Rs. 2000/month</p>
                
                <div class="workout-details">
                    <p><strong>Duration:</strong> 6 weeks</p>
                    <p><strong>Focus:</strong> Fat Burning, HIIT, Endurance</p>
                    
                    <div class="routine-day">
                        <div class="day-title">🔥 Monday - HIIT Circuit</div>
                        <p>Burpees: 40s | Mountain Climbers: 40s | Jump Squats: 40s | Rest: 20s</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">🏃‍♂️ Wednesday - Cardio & Core</div>
                        <p>Treadmill Intervals: 30min | Plank Variations: 15min</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">💪 Friday - Strength HIIT</div>
                        <p>Kettlebell Swings: 3x15 | Battle Ropes: 3x30s | Box Jumps: 3x10</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">🧘‍♀️ Sunday - Active Recovery</div>
                        <p>Yoga Flow: 30min | Foam Rolling: 15min | Walking: 45min</p>
                    </div>
                </div>
                
                <ul>
                    <li>4 workout days + active recovery</li>
                    <li>HIIT focused training</li>
                    <li>Calorie tracking integration</li>
                </ul>
                <div style="margin-top: 15px;">
                    <a href="workout-details.php?plan=shred" class="btn-details">View Full Plan</a>
                    <a href="../user/register.php?plan=shred" class="btn-book">Start Now</a>
                </div>
            </div>

            <!-- Package 4: Athlete Performance -->
            <div class="package-card">
                <div class="level-badge" style="background: #f8d7da; color: #721c24;">Advanced Level</div>
                <h3>Athlete Performance</h3>
                <p class="price">Rs. 3500/month</p>
                
                <div class="workout-details">
                    <p><strong>Duration:</strong> 16 weeks</p>
                    <p><strong>Focus:</strong> Sports Performance, Power, Agility</p>
                    
                    <div class="routine-day">
                        <div class="day-title">⚡ Monday - Power & Speed</div>
                        <p>Power Cleans: 5x3 | Box Jumps: 4x5 | Sprints: 10x40m</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">💪 Tuesday - Strength</div>
                        <p>Squats: 5x5 | Bench Press: 5x5 | Weighted Pull-ups: 4x6</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">🏃‍♂️ Thursday - Conditioning</div>
                        <p>Circuit Training: 45min | Agility Ladder Drills: 20min</p>
                    </div>
                    
                    <div class="routine-day">
                        <div class="day-title">🦵 Saturday - Sport-Specific</div>
                        <p>Plyometrics: 30min | Sport Drills: 30min | Recovery Work</p>
                    </div>
                </div>
                
                <ul>
                    <li>5-6 training days per week</li>
                    <li>1-on-1 coaching sessions</li>
                    <li>Video form analysis</li>
                </ul>
                <div style="margin-top: 15px;">
                    <a href="workout-details.php?plan=athlete" class="btn-details">View Full Plan</a>
                    <a href="../user/register.php?plan=athlete" class="btn-book">Start Now</a>
                </div>
            </div>
        </div>

        <!-- How It Works Section -->
        <div class="how-it-works">
            <h2 style="text-align: center; margin: 40px 0 20px 0;">How Our Workout Plans Work</h2>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <h4>Choose Your Plan</h4>
                    <p>Select a workout package that matches your fitness level and goals</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h4>Get Your Schedule</h4>
                    <p>Receive a detailed weekly workout schedule with exercise videos</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h4>Track Progress</h4>
                    <p>Use our app to log workouts, track weights, and monitor improvements</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h4>Adjust & Grow</h4>
                    <p>Plans evolve with you - automatic progression as you get stronger</p>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>FitLife Fitness &copy; 2025 | <a href="about.php#contact">Contact Us</a> | <a href="../faq.php">FAQ</a></p>
    </footer>
</body>
</html>