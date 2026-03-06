<?php
session_start();
require 'db.php';

// Check for category ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$category_id = intval($_GET['id']);

// Fetch Category Name
$sql_cat = "SELECT name FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql_cat);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result_cat = $stmt->get_result();

if ($result_cat->num_rows === 0) {
    header("Location: index.php");
    exit();
}
$category = $result_cat->fetch_assoc();
$category_name = $category['name'];

// Fetch Articles in Category
$sql_articles = "SELECT * FROM articles WHERE category_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql_articles);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result_articles = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category_name; ?> - NEON NEWS</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar glass-panel">
        <a href="index.php" class="brand-logo">NEON<span style="color:var(--neon-pink)">NEWS</span></a>
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <div class="dropdown" style="position: relative; display: inline-block;">
                <a href="#" class="nav-link active">Categories <i class="fas fa-chevron-down"></i></a>
                <div class="dropdown-content" style="display: none; position: absolute; background: var(--bg-card); min-width: 160px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1; border: var(--glass-border); border-radius: 8px; backdrop-filter: blur(12px);">
                    <?php
                    // Re-fetch categories for menu (could optimize this into a function but keeping it simple as requested)
                    $res_cats = $conn->query("SELECT * FROM categories");
                    while($c = $res_cats->fetch_assoc()): ?>
                        <a href="category.php?id=<?php echo $c['id']; ?>" style="color: white; padding: 12px 16px; text-decoration: none; display: block; transition: 0.3s;"><?php echo $c['name']; ?></a>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <span style="color: var(--neon-cyan);">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="nav-btn nav-btn-logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
                <a href="signup.php" class="nav-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <h1 class="section-title"><?php echo $category_name; ?> News</h1>
        
        <div class="news-grid">
            <?php if ($result_articles->num_rows > 0): ?>
                <?php while($row = $result_articles->fetch_assoc()): ?>
                    <div class="news-card glass-panel" onclick="location.href='article.php?id=<?php echo $row['id']; ?>'">
                        <div class="card-img-container">
                            <img src="<?php echo $row['image']; ?>" alt="News" class="card-img">
                        </div>
                        <div class="card-content">
                            <div class="card-category"><?php echo $category_name; ?></div>
                            <h3 class="card-title"><a href="article.php?id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></h3>
                            <p class="card-excerpt"><?php echo substr($row['content'], 0, 100) . '...'; ?></p>
                            <div class="card-footer">
                                <span><i class="far fa-clock"></i> <?php echo date('M j, Y', strtotime($row['created_at'])); ?></span>
                                <span style="color: var(--neon-purple);">Read More <i class="fas fa-arrow-right"></i></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No articles found in this category.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const dropdown = document.querySelector('.dropdown');
        const dropdownContent = document.querySelector('.dropdown-content');
        dropdown.addEventListener('mouseenter', () => dropdownContent.style.display = 'block');
        dropdown.addEventListener('mouseleave', () => dropdownContent.style.display = 'none');
    </script>
</body>
</html>
