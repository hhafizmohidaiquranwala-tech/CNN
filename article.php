<?php
session_start();
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$article_id = intval($_GET['id']);

// Fetch Article
$sql_article = "SELECT a.*, c.name as category_name 
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                WHERE a.id = ?";
$stmt = $conn->prepare($sql_article);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$article = $result->fetch_assoc();

// Fetch Related Articles
$cat_id = $article['category_id'];
$sql_related = "SELECT * FROM articles WHERE category_id = ? AND id != ? LIMIT 3";
$stmt_related = $conn->prepare($sql_related);
$stmt_related->bind_param("ii", $cat_id, $article_id);
$stmt_related->execute();
$result_related = $stmt_related->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $article['title']; ?> - NEON NEWS</title>
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
                <a href="#" class="nav-link">Categories <i class="fas fa-chevron-down"></i></a>
                <div class="dropdown-content" style="display: none; position: absolute; background: var(--bg-card); min-width: 160px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1; border: var(--glass-border); border-radius: 8px; backdrop-filter: blur(12px);">
                    <?php
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
        
        <div class="article-header">
            <div class="article-meta">
                <?php echo $article['category_name']; ?> • <?php echo date('F j, Y', strtotime($article['created_at'])); ?>
            </div>
            <h1 class="article-title"><?php echo $article['title']; ?></h1>
        </div>

        <?php if($article['image']): ?>
        <img src="<?php echo $article['image']; ?>" alt="Article Image" class="article-image">
        <?php endif; ?>

        <div class="article-content glass-panel" style="padding: 3rem; border-radius: 20px;">
            <?php echo nl2br($article['content']); ?>
        </div>

        <?php if ($result_related->num_rows > 0): ?>
        <h2 class="section-title" style="margin-top: 4rem;">Related Stories</h2>
        <div class="news-grid">
            <?php while($row = $result_related->fetch_assoc()): ?>
                <div class="news-card glass-panel" onclick="location.href='article.php?id=<?php echo $row['id']; ?>'">
                        <div class="card-img-container">
                            <img src="<?php echo $row['image']; ?>" alt="News" class="card-img">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><a href="article.php?id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></h3>
                            <div class="card-footer">
                                <span><i class="far fa-clock"></i> <?php echo date('M j, Y', strtotime($row['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        const dropdown = document.querySelector('.dropdown');
        const dropdownContent = document.querySelector('.dropdown-content');
        dropdown.addEventListener('mouseenter', () => dropdownContent.style.display = 'block');
        dropdown.addEventListener('mouseleave', () => dropdownContent.style.display = 'none');
    </script>
</body>
</html>
