<?php
require 'includes/session.php';
require 'db.php';
require 'includes/helpers.php';

// Fetch the 4 newest products for the highlighted section
$new_result = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 4");

// Fetch up to 8 products for the main shop grid
$result = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ONEONE — Performance Sportswear</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Hero Container */
        .hero-section {
            position: relative;
            width: 100%;
            height: 80vh; 
            overflow: hidden;
            background-color: #000; 
        }

        /* Premium Slider Styles */
        .hero-slider {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            background-size: cover;
            background-position: center top; /* Focuses on the upper body/face for sportswear */
            transition: opacity 1.5s ease-in-out;
            z-index: 1;
        }

        /* The Dark Overlay for Premium Contrast */
        .slide::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.25); /* Darkens images slightly so navigation stays readable */
            z-index: 2;
        }

        /* The Active Slide + Slow Zoom Animation */
        .slide.active {
            opacity: 1;
            z-index: 3;
            animation: slowZoom 6s linear forwards;
        }

        @keyframes slowZoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.05); }
        }
    </style>
</head>
<body>
    <main>
        <!-- NAVIGATION FIXED HERE -->
        <?php include 'includes/nav.php'; ?>

        <section class="hero-section">
            <!-- Premium Image Slider -->
            <div class="hero-slider">
                <!-- Slide 1 -->
                <div class="slide active" style="background-image: url('img/slider1.jpg');"></div>
                <!-- Slide 2 -->
                <div class="slide" style="background-image: url('img/slider2.jpg');"></div>
                <!-- Slide 3 -->
                <div class="slide" style="background-image: url('img/slider3.jpg');"></div>
            </div>
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
                    <img src="./img/Gemini_Generated_Image_69gp3h69gp3h69gp.png">
                    <div class="category-tile-label">
                        <span class="mono">Engineered For</span>
                        <h3>Men</h3>
                    </div>
                    <span class="category-tile-arrow">&#8594;</span>
                </a>
                <a href="woman.php" class="category-tile">
                    <img src="./img/Gemini_Generated_Image_o07obdo07obdo07o.png"  style="object-position: top; object-fit: cover;">
                    <div class="category-tile-label">
                        <span class="mono">Engineered For</span>
                        <h3>Women</h3>
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
                              <?php 
                                if (!empty($item['image_url'])): 
                                    $images = explode(',', $item['image_url']);
                                    $first_image = trim($images[0]); 
                                ?>
                                    <img src="<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
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
        <section class="section" id="shop-all">
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
                            <?php 
                            if (!empty($item['image_url'])): 
                                $images = explode(',', $item['image_url']);
                                $first_image = trim($images[0]);
                            ?>
                                <img src="<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
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
                    <a href="index.php" class="brand">ONEONE</a>
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
                <span>&copy; <?php echo date('Y'); ?> ONEONE</span>
                <span>Made for people who don't stop at good enough.</span>
            </div>
        </footer>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script>
        // Hero Image Slider Automation
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.hero-slider .slide');
            let currentSlide = 0;
            
            if (slides.length > 1) {
                setInterval(function() {
                    slides[currentSlide].classList.remove('active');
                    currentSlide = (currentSlide + 1) % slides.length;
                    slides[currentSlide].classList.add('active');
                }, 5000); // Changes image every 5 seconds
            }
        });

        // GSAP Scroll Animations
        if (window.gsap && window.ScrollTrigger) {
            gsap.registerPlugin(ScrollTrigger);
            gsap.utils.toArray('.card').forEach(function (card) {
                gsap.from(card, {
                    scale: 0.85,
                    y: 40,
                    opacity: 0,
                    duration: 0.6,
                    ease: 'power2.out',
                    scrollTrigger: { trigger: card, start: 'top 92%' }
                });
            });
        }
    </script>
</body>
</html>