<?php
require 'includes/session.php';
require 'db.php';
require 'includes/helpers.php';

// --- 1. THE FILTERING ENGINE (Parameterized & Secure) ---
$where_clauses = ["(p.gender = 'Men' OR p.gender = 'Unisex')"];
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
    <title>Men's Performance Gear | ONEONE</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* --- PRO FILTER UI --- */
        .filter-section {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            border-bottom: 1px solid #eaeaea;
            margin-bottom: 30px;
        }
        
        .filter-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .pill-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
            -ms-overflow-style: none;  /* Hide scrollbar for IE/Edge */
            scrollbar-width: none;  /* Hide scrollbar for Firefox */
        }
        .pill-container::-webkit-scrollbar {
            display: none; /* Hide scrollbar for Chrome/Safari */
        }

        .filter-pill {
            background: #f5f5f5;
            color: #333;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .filter-pill:hover {
            background: #e0e0e0;
        }

        .filter-pill.active {
            background: #FF4D23;
            color: white;
            box-shadow: 0 4px 10px rgba(255, 77, 35, 0.3);
        }
    </style>
</head>
<body>
    <main>
        <section class="main" style="min-height: auto; padding-bottom: 40px;">
            <?php include 'includes/nav.php'; ?>
            <div style="text-align: center; padding: 60px 20px 20px;">
                <h1 style="font-size: 3rem; text-transform: uppercase; letter-spacing: -1px; margin-bottom: 10px;">Men's Gear</h1>
                <p style="color: #666;">Engineered for peak performance, no matter the arena.</p>
            </div>
        </section>

       <!-- THE NEW FILTER BAR -->
        <div class="filter-section">
            <?php 
                // Preserve existing filters for combined URL building
                $current_cat = !empty($active_category) ? '&category=' . $active_category : '';
                $current_sport = !empty($active_sport) ? '&sport=' . urlencode($active_sport) : '';
            ?>
            <div class="filter-title">Filter by Sport</div>
            <div class="pill-container">
                <a href="man.php?<?php echo ltrim($current_cat, '&'); ?>" class="filter-pill <?php echo empty($active_sport) ? 'active' : ''; ?>">All Sports</a>
                <a href="man.php?sport=Lifestyle<?php echo $current_cat; ?>" class="filter-pill <?php echo ($active_sport == 'Lifestyle') ? 'active' : ''; ?>">Lifestyle</a>
                <a href="man.php?sport=Running<?php echo $current_cat; ?>" class="filter-pill <?php echo ($active_sport == 'Running') ? 'active' : ''; ?>">Running</a>
            </div>

            <div class="filter-title" style="margin-top: 15px;">Filter by Type</div>
            <div class="pill-container">
                <a href="man.php?<?php echo ltrim($current_sport, '&'); ?>" class="filter-pill <?php echo empty($active_category) ? 'active' : ''; ?>">All Types</a>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <a href="man.php?category=<?php echo $cat['id']; ?><?php echo $current_sport; ?>" class="filter-pill <?php echo ($active_category == $cat['id']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- PRODUCT GRID -->
        <section class="section" id="shop">
            <div class="second_page grid" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($item = $result->fetch_assoc()): ?>
                        <section class="card">
                            <a href="product.php?id=<?php echo $item['id']; ?>" class="card-link">
                                <?php 
                                // PRO IMAGE FIX 
                                if (!empty($item['image_url'])): 
                                    $images = explode(',', $item['image_url']);
                                    $first_image = trim($images[0]); 
                                ?>
                                    <img src="<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; aspect-ratio: 4/5; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <img src="img/favicon.png" alt="No image" style="width: 100%; aspect-ratio: 4/5; object-fit: cover; border-radius: 8px;">
                                <?php endif; ?>
                            </a>
                            
                            <a href="product.php?id=<?php echo $item['id']; ?>" class="card-link" style="display: block; margin-top: 15px; text-decoration: none;">
                                <span class="cat-label" style="font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px;"><?php echo htmlspecialchars($item['sport_type'] . ' • ' . $item['category_name']); ?></span>
                                <h5 style="font-size: 16px; margin: 5px 0; color: #000; font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <?php render_price($item['price'], $item['mrp']); ?>
                            </a>
                            <div style="margin-top: 15px;">
                                <?php render_quick_add($conn, $item); ?>
                            </div>
                        </section>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 80px 20px;">
                        <h3 style="color: #333; margin-bottom: 10px;">No gear found for this filter.</h3>
                        <p style="color: #666; margin-bottom: 20px;">Try selecting a different sport or product type.</p>
                        <a href="man.php" class="btn-dark" style="padding: 10px 20px; background: #000; color: #fff; text-decoration: none; border-radius: 5px;">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- FOOTER -->
        <footer id="fotter" style="margin-top: 60px;">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="brand">ONEONE</a>
                    <p>Performance sportswear built for the run, the court, and everything after.</p>
                </div>
                <div class="footer-col">
                    <h5>Shop</h5>
                    <a href="man.php">Men</a>
                    <a href="woman.php">Women</a>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?php echo date('Y'); ?> ONEONE</span>
            </div>
        </footer>
    </main>
</body>
</html>