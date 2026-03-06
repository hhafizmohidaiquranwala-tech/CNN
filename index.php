<?php
require_once 'db.php';

// Fetch featured cars (top 3 by rating)
$featured_cars = $conn->query("SELECT * FROM cars ORDER BY rating DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentACar | Premium Neon Car Rentals</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="logo">RentACar</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="cars.php">Fleet</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="mybookings.php">My Bookings</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php" class="btn-login">Get Started</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <section class="hero">
        <h1>Experience the <span>Next Generation</span> of Travel</h1>
        <p>Premium cars, neon vibes, and seamless booking. Your journey starts here with the most advanced fleet on the planet.</p>
        
        <form action="cars.php" method="GET" class="search-form">
            <div class="form-group">
                <label>Pickup Location</label>
                <select name="location" required>
                    <option value="">Select city...</option>
                    <option value="New York">New York</option>
                    <option value="Los Angeles">Los Angeles</option>
                    <option value="Miami">Miami</option>
                    <option value="Chicago">Chicago</option>
                </select>
            </div>
            <div class="form-group">
                <label>Pickup Date</label>
                <input type="date" name="start_date" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Return Date</label>
                <input type="date" name="return_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
            </div>
            <button type="submit" class="btn-search">Search Cars</button>
        </form>
    </section>

    <section class="cars-container">
        <div class="section-title">
            <h2>Featured <span>Elite Fleet</span></h2>
            <p>Our top rated vehicles hand-picked for your premium experience.</p>
        </div>

        <div class="grid">
            <?php while($car = $featured_cars->fetch_assoc()): ?>
                <div class="car-card">
                    <img src="<?php echo $car['image']; ?>" alt="<?php echo $car['name']; ?>" class="car-image">
                    <div class="car-info">
                        <div class="car-header">
                            <div>
                                <p class="car-brand"><?php echo $car['brand']; ?></p>
                                <h3><?php echo $car['name']; ?></h3>
                            </div>
                            <div class="car-price">
                                $<?php echo number_format($car['price_per_day'], 0); ?><span>/day</span>
                            </div>
                        </div>
                        <div class="car-specs">
                            <span>⚡ <?php echo $car['fuel_type']; ?></span>
                            <span>🏎️ <?php echo $car['type']; ?></span>
                            <span>⭐ <?php echo $car['rating']; ?></span>
                        </div>
                        <a href="cars.php?id=<?php echo $car['id']; ?>" class="btn-book">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <footer style="padding: 3rem 10%; text-align: center; border-top: 1px solid var(--glass-border); margin-top: 5rem; color: var(--text-dim);">
        <p>&copy; 2026 RentACar Neon Edition. All rights reserved.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
