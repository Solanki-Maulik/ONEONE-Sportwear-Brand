<?php
require 'includes/session.php';
require 'db.php';
require 'includes/helpers.php';

// --- 1. THE FILTERING ENGINE (Parameterized & Secure) ---
$where_clauses = ["(p.gender = 'Women' OR p.gender = 'Unisex')"];
$params = [];
$types = "";

// Check if a Sport filter is active
$active_sport = isset($_GET['sport']) ? trim($_GET['sport']) : '';
if (!empty($active_sport)) {
    $where_clauses[] = "p.sport_type = ?";
    $params[] = $active_sport;
    $types .= "s";
}

// Check if a Category filter is active
$active_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
if ($active_category > 0) {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $active_category;
    $types .= "i";
}

// Combine rules and fetch products securely with prepared statements
$where_sql = implode(' AND ', $where_clauses);
$query = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $where_sql ORDER BY p.id DESC";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch categories for the filter buttons (excluding Accessories)
$categories = $conn->query("SELECT * FROM categories WHERE name != 'Accessories' ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women's Performance Gear | ELEVENX</title>
    <meta name="description" content="Shop ELEVENX Women's high-performance running shoes, activewear tops, and engineered sportswear.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <section class="main" style="min-height: auto;">
            <?php include 'includes/nav.php'; ?>
            <?php include 'includes/ticker.php'; ?>
            <div style="text-align: center; padding: 50px 20px 20px;">
                <span class="mono" style="font-size: 11.5px; letter-spacing: 2.5px; text-transform: uppercase; color: var(--accent); font-weight: 700;">// Women's Performance Division</span>
                <h1 style="font-size: clamp(36px, 5vw, 56px); text-transform: uppercase; letter-spacing: 1px; margin: 10px 0; color: #FFFFFF;">Women's Collection</h1>
                <p style="color: var(--slate); font-size: 15px; max-width: 520px; margin: 0 auto; line-height: 1.6;">Precision tailored for agility, sculpted breathability, and elite athletic performance.</p>
            </div>
        </section>

        <!-- THE PRO FILTER BAR -->
        <div class="filter-section">
            <?php 
                $current_cat = !empty($active_category) ? '&category=' . $active_category : '';
                $current_sport = !empty($active_sport) ? '&sport=' . urlencode($active_sport) : '';
            ?>
            <div class="filter-title">Filter by Sport</div>
            <div class="pill-container">
                <a href="woman.php?<?php echo ltrim($current_cat, '&'); ?>" class="filter-pill <?php echo empty($active_sport) ? 'active' : ''; ?>">All Sports</a>
                <a href="woman.php?sport=Lifestyle<?php echo $current_cat; ?>" class="filter-pill <?php echo ($active_sport == 'Lifestyle') ? 'active' : ''; ?>">Lifestyle</a>
                <a href="woman.php?sport=Running<?php echo $current_cat; ?>" class="filter-pill <?php echo ($active_sport == 'Running') ? 'active' : ''; ?>">Running</a>
            </div>

            <div class="filter-title" style="margin-top: 18px;">Filter by Type</div>
            <div class="pill-container">
                <a href="woman.php?<?php echo ltrim($current_sport, '&'); ?>" class="filter-pill <?php echo empty($active_category) ? 'active' : ''; ?>">All Types</a>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <a href="woman.php?category=<?php echo $cat['id']; ?><?php echo $current_sport; ?>" class="filter-pill <?php echo ($active_category == $cat['id']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- PRODUCT GRID -->
        <section class="section" id="shop" style="padding-top: 20px;">
            <div class="second_page grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($item = $result->fetch_assoc()): ?>
                        <section class="card">
                            <a href="product.php?id=<?php echo $item['id']; ?>" class="card-link">
                                <div class="card-img-wrap">
                                    <?php 
                                    if (!empty($item['image_url'])): 
                                        $images = explode(',', $item['image_url']);
                                        $first_image = trim($images[0]); 
                                    ?>
                                        <img src="<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php else: ?>
                                        <img src="img/favicon.png" alt="No image">
                                    <?php endif; ?>
                                </div>
                                <span class="cat-label"><?php echo htmlspecialchars($item['sport_type'] . ' • ' . $item['category_name']); ?></span>
                                <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                <?php render_rating_stars(4.9); ?>
                                <?php render_delivery_badge(); ?>
                                <?php render_price($item['price'], $item['mrp']); ?>
                            </a>
                            <?php render_quick_add($conn, $item); ?>
                        </section>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 90px 20px;">
                        <h3 style="color: #FFFFFF; margin-bottom: 12px; font-size: 24px;">No gear found for this filter.</h3>
                        <p style="color: var(--slate); margin-bottom: 24px;">Try selecting a different sport or product category.</p>
                        <a href="woman.php" class="btn-dark">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- FOOTER -->
        <footer id="fotter">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="brand"><span class="brand-word">ELEVENX</span><span class="brand-badge">ATHLETICS</span></a>
                    <p>Performance sportswear built for the run, the court, and everything after. Designed and tested by athletes in motion.</p>
                </div>
                <div class="footer-col">
                    <h5>Shop</h5>
                    <a href="man.php">Men</a>
                    <a href="woman.php">Women</a>
                    <a href="index.php">All products</a>
                </div>
                <div class="footer-col">
                    <h5>Account</h5>
                    <a href="cart.php">Your cart</a>
                    <a href="login.php">Login</a>
                    <a href="login.php?register=1">Create account</a>
                    <a href="about.php">About</a>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?php echo date('Y'); ?> ELEVENX Athletics. All rights reserved.</span>
                <span>Engineered for athletes who never settle.</span>
            </div>
        </footer>
    </main>
</body>
</html>