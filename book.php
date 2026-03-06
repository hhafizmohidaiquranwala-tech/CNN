<?php
require_once 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$car_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch car details
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if (!$car) {
    header("Location: cars.php");
    exit();
}

$success = false;
$error = "";
$total_price = 0;
$days = 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    $pickup_location = $_POST['pickup_location'];
    $start_date = $_POST['start_date'];
    $return_date = $_POST['return_date'];
    
    // Calculate total price
    $date1 = new DateTime($start_date);
    $date2 = new DateTime($return_date);
    $interval = $date1->diff($date2);
    $days = $interval->days;
    if ($days <= 0) $days = 1;
    
    $total_price = $days * $car['price_per_day'];

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, car_id, pickup_location, start_date, return_date, total_price, status) VALUES (?, ?, ?, ?, ?, ?, 'Confirmed')");
    $stmt->bind_param("iisssd", $user_id, $car_id, $pickup_location, $start_date, $return_date, $total_price);
    
    if ($stmt->execute()) {
        $success = true;
    } else {
        $error = "Booking failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo $car['name']; ?> | RentACar</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .booking-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            padding: 5rem 10%;
        }
        .car-preview {
            position: sticky;
            top: 120px;
        }
        .car-preview img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0, 242, 255, 0.2);
            margin-bottom: 2rem;
        }
        .summary-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            padding: 2rem;
            border-radius: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--glass-border);
        }
        .total-price {
            font-size: 2rem;
            color: var(--neon-blue);
            font-weight: 700;
        }
        .success-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }
        .success-card {
            background: var(--glass-bg);
            border: 1px solid var(--neon-blue);
            padding: 4rem;
            border-radius: 30px;
            box-shadow: 0 0 50px rgba(0, 242, 255, 0.3);
            max-width: 500px;
        }
        .check-mark {
            font-size: 5rem;
            color: var(--neon-blue);
            margin-bottom: 1rem;
            display: block;
        }
        @media (max-width: 768px) {
            .booking-layout { grid-template-columns: 1fr; }
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

    <?php if ($success): ?>
        <div class="success-overlay">
            <div class="success-card">
                <span class="check-mark">✓</span>
                <h2>Booking Confirmed!</h2>
                <p style="margin: 1.5rem 0; color: var(--text-dim);">Your reservation for <strong><?php echo $car['name']; ?></strong> has been secured. Get ready for an electrifying ride!</p>
                <a href="mybookings.php" class="btn-search" style="text-decoration: none; display: inline-block;">View My Bookings</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="booking-layout">
        <div class="car-preview">
            <img src="<?php echo $car['image']; ?>" alt="<?php echo $car['name']; ?>">
            <h2><?php echo $car['brand']; ?> <?php echo $car['name']; ?></h2>
            <p style="color: var(--text-dim); margin-top: 1rem;">
                Experience unrivaled performance with our <?php echo strtolower($car['type']); ?> fleet. 
                Equipped with <?php echo strtolower($car['fuel_type']); ?> power and futuristic controls.
            </p>
            <div class="car-specs" style="margin-top: 2rem;">
                <span style="background: var(--glass-bg); padding: 0.5rem 1rem; border-radius: 50px; border: 1px solid var(--glass-border);">⭐ <?php echo $car['rating']; ?> Rating</span>
                <span style="background: var(--glass-bg); padding: 0.5rem 1rem; border-radius: 50px; border: 1px solid var(--glass-border);">💰 $<?php echo $car['price_per_day']; ?> / day</span>
            </div>
        </div>

        <div class="booking-form-container">
            <div class="auth-card" style="max-width: 100%;">
                <h2>Finalize Reservation</h2>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="book.php?id=<?php echo $car_id; ?>" method="POST" id="bookingForm">
                    <div class="form-group">
                        <label>Pickup Location</label>
                        <select name="pickup_location" required>
                            <option value="New York">New York Downtown</option>
                            <option value="Los Angeles">LA International Airport</option>
                            <option value="Miami">Miami Beach Terminal</option>
                            <option value="Chicago">Chicago Loop</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" id="startDate" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Return Date</label>
                        <input type="date" name="return_date" id="returnDate" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>

                    <div class="summary-card" style="margin-top: 2rem;">
                        <div class="summary-row">
                            <span>Daily Rate</span>
                            <span>$<?php echo number_format($car['price_per_day'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Duration</span>
                            <span id="durationDays">1 Day(s)</span>
                        </div>
                        <div class="summary-row" style="border: none;">
                            <strong>Total Estimated</strong>
                            <span class="total-price" id="totalPriceDisplay">$<?php echo number_format($car['price_per_day'], 2); ?></span>
                        </div>
                    </div>

                    <button type="submit" name="confirm_booking" class="btn-search" style="width: 100%; margin-top: 2rem;">Confirm & Pay at Pickup</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const startDateInput = document.getElementById('startDate');
        const returnDateInput = document.getElementById('returnDate');
        const durationDisplay = document.getElementById('durationDays');
        const totalDisplay = document.getElementById('totalPriceDisplay');
        const dailyRate = <?php echo $car['price_per_day']; ?>;

        function updatePrice() {
            if (startDateInput.value && returnDateInput.value) {
                const start = new Date(startDateInput.value);
                const end = new Date(returnDateInput.value);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                const finalDays = diffDays > 0 ? diffDays : 1;
                durationDisplay.innerText = finalDays + ' Day(s)';
                totalDisplay.innerText = '$' + (finalDays * dailyRate).toLocaleString(undefined, {minimumFractionDigits: 2});
            }
        }

        startDateInput.addEventListener('change', updatePrice);
        returnDateInput.addEventListener('change', updatePrice);
    </script>
</body>
</html>
