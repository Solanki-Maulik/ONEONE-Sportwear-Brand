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
        /* Hero Banner Slider Styles */
        .hero-slider-container {
            position: relative;
            width: 100%;
            height: 85vh;
            min-height: 520px;
            overflow: hidden;
            background: var(--ink);
        }

        .hero-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 1.2s ease-in-out, transform 1.2s ease-in-out;
            transform: scale(1.05);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
        }

        .hero-slide.active {
            opacity: 1;
            transform: scale(1);
            z-index: 1;
        }

        .hero-slide::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(16,19,26,0.92) 0%, rgba(16,19,26,0.55) 50%, rgba(16,19,26,0.2) 100%);
        }

        .hero-content-wrap {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
        }

        /* Banner Navigation Controls */
        .hero-ctrl-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition);
        }

        .hero-ctrl-btn:hover {
            background: var(--accent);
            border-color: var(--accent);
            transform: translateY(-50%) scale(1.1);
        }

        .hero-ctrl-prev { left: 24px; }
        .hero-ctrl-next { right: 24px; }

        .hero-dots {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 10px;
        }

        .hero-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            border: none;
            cursor: pointer;
            transition: all var(--transition);
        }

        .hero-dot.active {
            background: var(--accent);
            width: 32px;
            border-radius: 12px;
        }

        @media (max-width: 768px) {
            .hero-slider-container { height: 70vh; }
            .hero-content-wrap { padding: 0 20px; }
            .hero-ctrl-btn { display: none; }
        }
    </style>
</head>
<body>
    <main>
        <header></header>

        <section class="main" id="home">
            <?php include 'includes/nav.php'; ?>

            <!-- ============ DYNAMIC MOVING HERO SLIDER ============ -->
            <div class="hero-slider-container">
                
                <!-- Slide 1 -->
                <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=1600&q=80');">
                    <div class="hero-content-wrap">
                        <div class="hero-copy">
                            <span class="hero-eyebrow">// Performance sportswear</span>
                            <h1>Move like<br>you mean it.</h1>
                            <p>Footwear and jerseys engineered for people who train hard and don't stop at good enough. Built for the run, the court, and everything after.</p>
                            <div class="hero-ctas" style="margin-top: 24px;">
                                <a href="#shop-all" class="btn-primary">Shop Collection</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?w=1600&q=80');">
                    <div class="hero-content-wrap">
                        <div class="hero-copy">
                            <span class="hero-eyebrow">// Pro Footwear Lineup</span>
                            <h1>Precision<br>In Every Stride.</h1>
                            <p>Lightweight cushioning paired with high-traction soles. Built for maximum speed, stability, and endurance on any terrain.</p>
                            <div class="hero-ctas" style="margin-top: 24px;">
                                <a href="man.php" class="btn-primary">Explore Footwear</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1518611012118-696072aa579a?w=1600&q=80');">
                    <div class="hero-content-wrap">
                        <div class="hero-copy">
                            <span class="hero-eyebrow">// Match-Day Apparel</span>
                            <h1>Sweat-Wicking<br>Comfort.</h1>
                            <p>Official match jerseys and training gear crafted with climate-adapting breathable mesh to keep you cool under pressure.</p>
                            <div class="hero-ctas" style="margin-top: 24px;">
                                <a href="woman.php" class="btn-primary">Shop Apparel</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controls -->
                <button class="hero-ctrl-btn hero-ctrl-prev" onclick="changeSlide(-1)" aria-label="Previous Slide">&#10094;</button>
                <button class="hero-ctrl-btn hero-ctrl-next" onclick="changeSlide(1)" aria-label="Next Slide">&#10095;</button>

                <!-- Indicator Dots -->
                <div class="hero-dots">
                    <button class="hero-dot active" onclick="goToSlide(0)"></button>
                    <button class="hero-dot" onclick="goToSlide(1)"></button>
                    <button class="hero-dot" onclick="goToSlide(2)"></button>
                </div>

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
                    <!-- Virat Kohli Image -->
                    <img src="./img/Gemini_Generated_Image_69gp3h69gp3h69gp.png">
                    <div class="category-tile-label">
                        <span class="mono">Engineered For</span>
                        <h3>Men</h3>
                    </div>
                    <span class="category-tile-arrow">&#8594;</span>
                </a>
                <a href="woman.php" class="category-tile">
                    <!-- Smriti Mandhana Image -->
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
                <span>&copy; <?php echo date('Y'); ?> STRIDE Sportswear</span>
                <span>Made for people who don't stop at good enough.</span>
            </div>
        </footer>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script>
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

        // Hero Background Carousel (Switches every 4000ms = 4 seconds)
        let currentSlide = 0;
        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.hero-dot');
        const slideIntervalTime = 4000;
        let slideTimer;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                dots[i].classList.remove('active');
            });

            currentSlide = (index + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function changeSlide(direction) {
            showSlide(currentSlide + direction);
            resetSlideTimer();
        }

        function goToSlide(index) {
            showSlide(index);
            resetSlideTimer();
        }

        function startSlideTimer() {
            slideTimer = setInterval(() => {
                showSlide(currentSlide + 1);
            }, slideIntervalTime);
        }

        function resetSlideTimer() {
            clearInterval(slideTimer);
            startSlideTimer();
        }

        // Initialize Auto Slide
        startSlideTimer();
    </script>
</body>
</html>