<?php
require_once 'db.php';

// Filter Logic
$where_clauses = [];
$params = [];
$types = "";

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where_clauses[] = "type = ?";
    $params[] = $_GET['type'];
    $types .= "s";
}

if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $where_clauses[] = "brand = ?";
    $params[] = $_GET['brand'];
    $types .= "s";
}

if (isset($_GET['price_max']) && !empty($_GET['price_max'])) {
    $where_clauses[] = "price_per_day <= ?";
    $params[] = $_GET['price_max'];
    $types .= "d";
}

$sql = "SELECT * FROM cars";
if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
switch($sort) {
    case 'price_low': $sql .= " ORDER BY price_per_day ASC"; break;
    case 'price_high': $sql .= " ORDER BY price_per_day DESC"; break;
    case 'best_rated': $sql .= " ORDER BY rating DESC"; break;
    default: $sql .= " ORDER BY created_at DESC";
}

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$cars = $stmt->get_result();

// Get unique types/brands for filters
$all_types = $conn->query("SELECT DISTINCT type FROM cars");
$all_brands = $conn->query("SELECT DISTINCT brand FROM cars");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet | RentACar</title>
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

    <div class="cars-container">
        <div class="section-title">
            <h2>Explore Our <span>Neon Fleet</span></h2>
            <p>Select from the most technologically advanced vehicles available for rent today.</p>
        </div>

        <form action="cars.php" method="GET" class="filters-bar">
            <div class="form-group" style="min-width: 150px;">
                <label>Car Type</label>
                <select name="type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php while($t = $all_types->fetch_assoc()): ?>
                        <option value="<?php echo $t['type']; ?>" <?php echo (isset($_GET['type']) && $_GET['type'] == $t['type']) ? 'selected' : ''; ?>>
                            <?php echo $t['type']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group" style="min-width: 150px;">
                <label>Brand</label>
                <select name="brand" onchange="this.form.submit()">
                    <option value="">All Brands</option>
                    <?php while($b = $all_brands->fetch_assoc()): ?>
                        <option value="<?php echo $b['brand']; ?>" <?php echo (isset($_GET['brand']) && $_GET['brand'] == $b['brand']) ? 'selected' : ''; ?>>
                            <?php echo $b['brand']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group" style="min-width: 150px;">
                <label>Sort By</label>
                <select name="sort" onchange="this.form.submit()">
                    <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>Newest Arrivals</option>
                    <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="best_rated" <?php echo ($sort == 'best_rated') ? 'selected' : ''; ?>>Best Rated</option>
                </select>
            </div>

            <div class="form-group">
                <label>Max Price ($/day)</label>
                <input type="number" name="price_max" placeholder="e.g. 500" value="<?php echo $_GET['price_max'] ?? ''; ?>" onchange="this.form.submit()">
            </div>
        </form>

        <div class="grid">
            <?php if ($cars->num_rows > 0): ?>
                <?php while($car = $cars->fetch_assoc()): ?>
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
                            <a href="book.php?id=<?php echo $car['id']; ?>" class="btn-book">Book This Car</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 5rem;">
                    <h3 style="color: var(--text-dim);">No cars found matching your criteria.</h3>
                    <a href="cars.php" class="btn-login" style="margin-top: 1rem; display: inline-block;">View All Cars</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
