<?php
require 'includes/session.php';
require 'db.php';
require 'includes/helpers.php';

$new_result = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 4");
$result = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 8");
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STRIDE — Performance Sportswear</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <header></header>

  <section class="main" id="home">
    <?php include 'includes/nav.php'; ?>

    <section class="hero">
        <div class="hero-copy">
            <span class="hero-eyebrow">// Performance sportswear</span>
            <h1>Move like<br>you mean it.</h1>
            <p>Footwear and jerseys engineered for people who train hard and don't stop at good enough. Built for the run, the court, and everything after.</p>
        </div>
    </section>
 </section>

    <?php include 'includes/ticker.php'; ?>

    <!-- ============ CATEGORIES ============ -->
    <section class="section">
        <div class="section-head">
            <div>
                <span class="eyebrow">// Shop by category</span>
                <h2>Find your fit</h2>
            </div>
            <p>Two collections, one standard: gear that keeps up when things get hard.</p>
        </div>
        <div class="category-strip">
            <a href="man.php" class="category-tile">
                <img src="<?php echo htmlspecialchars('img/test_main.jpg'); ?>" alt="Men's collection">
                <div class="category-tile-label">
                    <h3><?php echo htmlspecialchars('Men'); ?></h3>
                </div>
                <span class="category-tile-arrow">&#8594;</span>
            </a>
            <a href="woman.php" class="category-tile">
                <img src="<?php echo htmlspecialchars('img/test.jpg'); ?>" alt="Women's collection">
                <div class="category-tile-label">
                    <h3><?php echo htmlspecialchars('Women'); ?></h3>
                </div>
                <span class="category-tile-arrow">&#8594;</span>
            </a>
        </div>
    </section>

    <!-- ============ NEW COLLECTION ============ -->
    <?php if ($new_result->num_rows > 0): ?>
    <section class="section section-alt">
        <div class="section-head">
            <div>
                <span class="eyebrow">// Just landed</span>
                <h2>New collection</h2>
            </div>
            <p>The latest additions to the lineup, fresh in this week.</p>
        </div>
        <div class="new-collection-wrap">
            <a href="#" class="slider-arrow prev" onclick="document.getElementById('newCollectionTrack').scrollBy({left:-440,behavior:'smooth'});return false;">&#8592;</a>
            <div class="new-collection" id="newCollectionTrack">
                <?php while ($item = $new_result->fetch_assoc()): ?>
                    <section class="card">
                        <span class="ribbon">New</span>
                        <a href="product.php?id=<?php echo $item['id']; ?>" class="card-link">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <img src="img/favicon.png" alt="No image">
                            <?php endif; ?>
                            <span class="cat-label"><?php echo htmlspecialchars($item['category_name']); ?></span>
                            <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                            <?php render_price($item['price'], $item['mrp']); ?>
                        </a>
                    </section>
                <?php endwhile; ?>
            </div>
            <a href="#" class="slider-arrow next" onclick="document.getElementById('newCollectionTrack').scrollBy({left:440,behavior:'smooth'});return false;">&#8594;</a>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============ SHOP ============ -->
    <section class="section">
        <div class="section-head">
            <div>
                <span class="eyebrow">// Shop</span>
                <h2><?php echo $result->num_rows > 0 ? "All products" : "No products yet"; ?></h2>
            </div>
            <p>Fresh stock, straight from the warehouse to your door.</p>
        </div>

        <div class="second_page grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($item = $result->fetch_assoc()): ?>
                <section class="card">
                    <a href="product.php?id=<?php echo $item['id']; ?>" class="card-link">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            <img src="img/favicon.png" alt="No image">
                        <?php endif; ?>
                    </a>
                    <a href="product.php?id=<?php echo $item['id']; ?>" class="card-link">
                        <span class="cat-label"><?php echo htmlspecialchars($item['category_name']); ?></span>
                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                        <?php render_price($item['price'], $item['mrp']); ?>
                    </a>
                    <?php render_quick_add($conn, $item); ?>
                </section>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>No sportswear products have been uploaded yet.</p>
            </div>
        <?php endif; ?>
        </div>
    </section>

    <!-- ============ VALUE STRIP ============ -->
    <section class="section">
        <div class="value-strip">
            <div class="value-item">
                <span class="mono">Dispatch</span>
                <h4>Fast dispatch</h4>
                <p>Orders placed before 5pm ship the same day from our warehouse.</p>
            </div>
            <div class="value-item">
                <span class="mono">Returns</span>
                <h4>Easy 30-day returns</h4>
                <p>Didn't work out? Send it back within 30 days, no questions asked.</p>
            </div>
            <div class="value-item">
                <span class="mono">Checkout</span>
                <h4>Secure checkout</h4>
                <p>Your order and account details are handled safely, every time.</p>
            </div>
        </div>
    </section>

  <footer id="fotter">
    <div class="footer-grid">
        <div class="footer-brand">
            <a href="index.php" class="brand">STRIDE</a>
            <p>Performance sportswear built for the run, the court, and everything after. Designed and tested by people who actually train.</p>
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
        <span>&copy; <?php echo date('Y'); ?> STRIDE Sportswear</span>
        <span>Made for people who don't stop at good enough.</span>
    </div>
  </footer>
   </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script>
        if (window.gsap && window.ScrollTrigger) {
            gsap.registerPlugin(ScrollTrigger);
            gsap.utils.toArray('.card').forEach(function (card) {
                gsap.from(card, {
                    scale: 0.4,
                    y: 80,
                    opacity: 0,
                    duration: 0.8,
                    ease: 'back.out(1.7)',
                    scrollTrigger: { trigger: card, start: 'top 90%' }
                });
            });
        }
    </script>
</body>
</html>
