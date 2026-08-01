<?php
require 'includes/session.php';
require 'db.php';
require 'includes/helpers.php';

$result = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.gender IN ('women','unisex') ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women — STRIDE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
    <section class="main">
        <?php include 'includes/nav.php'; ?>
    </section>
    <?php include 'includes/ticker.php'; ?>

    <section class="section" style="padding-bottom:0;">
        <div class="section-head">
            <div>
                <h2 class="page-heading" style="margin:0;">Women</h2>
            </div>
            <p>Footwear and jerseys built for training days and everything after.</p>
        </div>
    </section>

    <section class="section">
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
            <div class="empty-state"><p>No women's products yet.</p><a href="index.php">Back to shop</a></div>
        <?php endif; ?>
        </div>
    </section>

    <footer id="fotter">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="index.php" class="brand">STRIDE</a>
                <p>Performance sportswear built for the run, the court, and everything after.</p>
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
