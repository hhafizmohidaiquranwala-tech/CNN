<?php
require_once 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user bookings with car details
$sql = "SELECT b.*, c.name as car_name, c.brand, c.image, c.price_per_day 
        FROM bookings b 
        JOIN cars c ON b.car_id = c.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | RentACar</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .bookings-table-container {
            padding: 5rem 10%;
            overflow-x: auto;
        }
        .booking-row {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            transition: var(--transition);
        }
        .booking-row:hover {
            border-color: var(--neon-blue);
            transform: scale(1.01);
        }
        .booking-car-img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        .booking-details {
            flex: 1;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 1rem;
            align-items: center;
        }
        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            text-align: center;
        }
        .status-confirmed { background: rgba(0, 255, 0, 0.1); color: #00ff00; border: 1px solid #00ff00; }
        .status-pending { background: rgba(255, 165, 0, 0.1); color: #ffa500; border: 1px solid #ffa500; }
        .status-completed { background: rgba(0, 242, 255, 0.1); color: var(--neon-blue); border: 1px solid var(--neon-blue); }

        @media (max-width: 900px) {
            .booking-details { grid-template-columns: 1fr 1fr; }
            .booking-row { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <nav>
        <a href="index.php" class="logo">RentACar</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="cars.php">Fleet</a></li>
            <li><a href="mybookings.php">My Bookings</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="bookings-table-container">
        <div class="section-title">
            <h2>Your <span>Reservations</span></h2>
            <p>Manage and track your premium car rental history.</p>
        </div>

        <?php if ($bookings->num_rows > 0): ?>
            <?php while($booking = $bookings->fetch_assoc()): ?>
                <div class="booking-row slide-up">
                    <img src="<?php echo $booking['image']; ?>" alt="Car" class="booking-car-img">
                    <div class="booking-details">
                        <div>
                            <h4 style="color: var(--neon-blue);"><?php echo $booking['brand']; ?> <?php echo $booking['car_name']; ?></h4>
                            <p style="font-size: 0.9rem; color: var(--text-dim);">ID: #REC-<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></p>
                        </div>
                        <div>
                            <p style="font-size: 0.8rem; color: var(--text-dim);">RENTAL DATES</p>
                            <p><?php echo date('M d', strtotime($booking['start_date'])); ?> - <?php echo date('M d, Y', strtotime($booking['return_date'])); ?></p>
                        </div>
                        <div>
                            <p style="font-size: 0.8rem; color: var(--text-dim);">TOTAL PRICE</p>
                            <p style="font-weight: 700; color: var(--neon-purple);">$<?php echo number_format($booking['total_price'], 2); ?></p>
                        </div>
                        <div>
                            <div class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                <?php echo $booking['status']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 5rem;">
                <h3 style="color: var(--text-dim);">No bookings found.</h3>
                <p style="margin-bottom: 2rem;">Ready to hit the road in style?</p>
                <a href="cars.php" class="btn-search">Explore Fleet</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="script.js"></script>
</body>
</html>
