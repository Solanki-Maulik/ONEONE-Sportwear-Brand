<?php
require 'includes/session.php';
require 'db.php';
require 'includes/helpers.php';

// Fetch products for 3D Rotating Cylinder Ring (up to 8 items with images)
$ring_query = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.image_url IS NOT NULL AND p.image_url != '' ORDER BY p.id DESC LIMIT 8");
$ring_products = [];
while ($row = $ring_query->fetch_assoc()) {
    $ring_products[] = $row;
}
// If fewer than 8, duplicate to maintain a full 360-degree cylindrical circle
if (count($ring_products) > 0 && count($ring_products) < 8) {
    $orig_count = count($ring_products);
    for ($i = $orig_count; $i < 8; $i++) {
        $ring_products[] = $ring_products[$i % $orig_count];
    }
}

// Fetch Men's Wardrobe Capsule products
$men_wardrobe_res = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE (p.gender = 'Men' OR p.gender = 'Unisex') AND p.image_url IS NOT NULL AND p.image_url != '' ORDER BY p.id DESC LIMIT 6");

// Fetch Women's Wardrobe Capsule products
$women_wardrobe_res = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE (p.gender = 'Women' OR p.gender = 'Unisex') AND p.image_url IS NOT NULL AND p.image_url != '' ORDER BY p.id DESC LIMIT 6");

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
    <title>ELEVENX — High-Performance Athletic Footwear & Apparel</title>
    <meta name="description" content="ElevenX designs high-performance athletic footwear, running gear, and performance apparel engineered for speed, endurance, and elite athletes.">
    <meta name="keywords" content="ElevenX, ElevenX athletics, running shoes, sneaker lab, athletic apparel, performance sportswear">
    <meta property="og:title" content="ELEVENX — Next-Gen Performance Sportswear">
    <meta property="og:description" content="Engineered for athletes who never settle. Explore 3D sneaker lab and high-performance gear at ElevenX.">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <!-- NAVIGATION -->
        <?php include 'includes/nav.php'; ?>

        <!-- HERO BANNER -->
        <section class="hero-section">
            <div class="hero-slider">
                <div class="slide active" style="background-image: url('img/slider1.jpg');"></div>
                <div class="slide" style="background-image: url('img/slider2.jpg');"></div>
                <div class="slide" style="background-image: url('img/slider3.jpg');"></div>
            </div>

            <div class="hero-overlay">
                <div class="hero-content-inner">
                    <span class="hero-eyebrow-tag">// ELEVENX ATHLETICS · 2026 VELOCITY LAB</span>
                    <h1 class="hero-main-title">ENGINEERED<br>FOR SPEED.</h1>
                    <p class="hero-tagline">Precision technical footwear and breathable athletic apparel crafted for athletes who refuse to settle for ordinary.</p>
                    <div class="hero-cta-group">
                        <a href="man.php" class="btn-hero-pri">Shop Men &rarr;</a>
                        <a href="woman.php" class="btn-hero-sec">Shop Women &rarr;</a>
                        <a href="#cylinderLab" class="btn-hero-sec">&#9881; 3D Sneaker Lab</a>
                    </div>
                </div>
            </div>
        </section>
        
        <?php include 'includes/ticker.php'; ?>

        <!-- ============ SIGNATURE 3D SNEAKER LAB (SPACIOUS, UN-SQUEEZED 60FPS) ============ -->
        <?php if (!empty($ring_products)): ?>
        <section class="ring-3d-section" id="cylinderLab">
            <div class="ring-3d-head">
                <span class="ring-eyebrow">// 360° SNEAKER LAB · 3D ROTATING CYLINDER</span>
                <h2 class="ring-title">FLAGSHIP 3D ROTATION</h2>
                <p class="ring-subtitle">Scroll the page or drag horizontally to rotate our signature sneaker lineup in real-time 3D space.</p>
            </div>

            <div class="ring-3d-viewport" id="ring3DViewport">
                <div class="ring-pedestal"></div>
                <div class="ring-3d-stage" id="ring3DStage">
                    <?php 
                    $total_ring = count($ring_products);
                    $angle_step = 360 / max(1, $total_ring);
                    $radius = 520; // Expanded radius eliminates squishing completely!
                    foreach ($ring_products as $idx => $r_item): 
                        $angle = round($idx * $angle_step, 2);
                        $images = explode(',', $r_item['image_url']);
                        $first_image = trim($images[0]);
                        $r_price = (float)$r_item['price'];
                        $r_mrp = ($r_item['mrp'] !== null && $r_item['mrp'] !== '') ? (float)$r_item['mrp'] : null;
                    ?>
                        <div class="ring-3d-card" style="transform: rotateY(<?php echo $angle; ?>deg) translateZ(<?php echo $radius; ?>px);">
                            <span class="ring-3d-badge">360° LAB</span>
                            <div class="ring-card-img-wrap">
                                <img src="<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($r_item['name']); ?>">
                            </div>
                            <div class="ring-card-info">
                                <span class="ring-card-cat"><?php echo htmlspecialchars($r_item['category_name']); ?></span>
                                <div class="ring-card-title"><?php echo htmlspecialchars($r_item['name']); ?></div>
                                <div class="price-row">
                                    <span class="price-sell">₹<?php echo number_format($r_price, 2); ?></span>
                                    <?php if ($r_mrp && $r_mrp > $r_price): ?>
                                        <span class="price-mrp">₹<?php echo number_format($r_mrp, 2); ?></span>
                                        <span class="price-off"><?php echo round((($r_mrp - $r_price) / $r_mrp) * 100); ?>% off</span>
                                    <?php endif; ?>
                                </div>
                                <a href="product.php?id=<?php echo $r_item['id']; ?>" class="ring-card-btn">View Sneaker Lab &rarr;</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="ring-controls">
                <button type="button" class="ring-btn" id="ringPrev" aria-label="Previous">&larr;</button>
                <span class="ring-status">DRAG OR SCROLL TO ROTATE · 360° VIEW</span>
                <button type="button" class="ring-btn" id="ringNext" aria-label="Next">&rarr;</button>
            </div>
        </section>
        <?php endif; ?>

        <!-- ============ THE ATHLETIC WARDROBE (CAPSULE CAROUSEL & LOOKBOOK) ============ -->
        <section class="wardrobe-section" id="wardrobeSection">
            <div class="section-head" style="margin-bottom: 25px;">
                <div>
                    <span class="eyebrow">// Curated Wardrobe Capsules · Season 2026</span>
                    <h2>The Athletic Wardrobe</h2>
                </div>
                <p>Complete head-to-toe performance wardrobes engineered for high-intensity training, court agility, and everyday comfort.</p>
            </div>

            <!-- Wardrobe Switcher Tabs -->
            <div class="wardrobe-tabs-container">
                <button type="button" class="wardrobe-tab-btn active" id="tabMen" onclick="switchWardrobe('men')">
                    🏃 Men's Wardrobe
                </button>
                <button type="button" class="wardrobe-tab-btn" id="tabWomen" onclick="switchWardrobe('women')">
                    🏃‍♀️ Women's Wardrobe
                </button>
            </div>

            <!-- MEN'S WARDROBE PANEL -->
            <div class="wardrobe-panel active" id="wardrobeMen">
                <!-- Master Editorial Lookbook Card -->
                <div class="wardrobe-hero-card">
                    <img src="./img/Gemini_Generated_Image_69gp3h69gp3h69gp.png" alt="Men's Wardrobe Lookbook">
                    <div class="wardrobe-hero-content">
                        <span class="wardrobe-hero-badge">MEN'S CAPSULE · 2026</span>
                        <h3>THE MEN'S<br>UNIFORM</h3>
                        <p>High-velocity technical tops, breathable track bottoms, and sprint-ready footwear designed for relentless training.</p>
                        <a href="man.php" class="wardrobe-hero-btn">Explore Full Men's Wardrobe &rarr;</a>
                    </div>
                </div>

                <!-- Horizontal Sliding Wardrobe Carousel -->
                <div class="wardrobe-carousel-wrap">
                    <button type="button" class="wardrobe-arrow prev" onclick="document.getElementById('menWardrobeTrack').scrollBy({left:-300,behavior:'smooth'});" aria-label="Previous">&#8592;</button>
                    <div class="wardrobe-track" id="menWardrobeTrack">
                        <?php while ($m_item = $men_wardrobe_res->fetch_assoc()): ?>
                            <section class="card">
                                <a href="product.php?id=<?php echo $m_item['id']; ?>" class="card-link">
                                    <div class="card-img-wrap">
                                        <?php 
                                        if (!empty($m_item['image_url'])): 
                                            $m_images = explode(',', $m_item['image_url']);
                                            $m_first = trim($m_images[0]); 
                                        ?>
                                            <img src="<?php echo htmlspecialchars($m_first); ?>" alt="<?php echo htmlspecialchars($m_item['name']); ?>">
                                        <?php else: ?>
                                            <img src="img/favicon.png" alt="No image">
                                        <?php endif; ?>
                                    </div>
                                    <span class="cat-label"><?php echo htmlspecialchars($m_item['category_name']); ?></span>
                                    <h5><?php echo htmlspecialchars($m_item['name']); ?></h5>
                                    <?php render_rating_stars(4.8); ?>
                                    <?php render_delivery_badge(); ?>
                                    <?php render_price($m_item['price'], $m_item['mrp']); ?>
                                </a>
                                <?php render_quick_add($conn, $m_item); ?>
                            </section>
                        <?php endwhile; ?>
                    </div>
                    <button type="button" class="wardrobe-arrow next" onclick="document.getElementById('menWardrobeTrack').scrollBy({left:300,behavior:'smooth'});" aria-label="Next">&#8594;</button>
                </div>
            </div>

            <!-- WOMEN'S WARDROBE PANEL -->
            <div class="wardrobe-panel" id="wardrobeWomen">
                <!-- Master Editorial Lookbook Card -->
                <div class="wardrobe-hero-card">
                    <img src="./img/Gemini_Generated_Image_o07obdo07obdo07o.png" alt="Women's Wardrobe Lookbook" style="object-position: top;">
                    <div class="wardrobe-hero-content">
                        <span class="wardrobe-hero-badge">WOMEN'S CAPSULE · 2026</span>
                        <h3>THE WOMEN'S<br>UNIFORM</h3>
                        <p>Precision-tailored active tops, moisture-wicking tennis apparel, and lightweight cushioning built for sculpted agility.</p>
                        <a href="woman.php" class="wardrobe-hero-btn">Explore Full Women's Wardrobe &rarr;</a>
                    </div>
                </div>

                <!-- Horizontal Sliding Wardrobe Carousel -->
                <div class="wardrobe-carousel-wrap">
                    <button type="button" class="wardrobe-arrow prev" onclick="document.getElementById('womenWardrobeTrack').scrollBy({left:-300,behavior:'smooth'});" aria-label="Previous">&#8592;</button>
                    <div class="wardrobe-track" id="womenWardrobeTrack">
                        <?php while ($w_item = $women_wardrobe_res->fetch_assoc()): ?>
                            <section class="card">
                                <a href="product.php?id=<?php echo $w_item['id']; ?>" class="card-link">
                                    <div class="card-img-wrap">
                                        <?php 
                                        if (!empty($w_item['image_url'])): 
                                            $w_images = explode(',', $w_item['image_url']);
                                            $w_first = trim($w_images[0]); 
                                        ?>
                                            <img src="<?php echo htmlspecialchars($w_first); ?>" alt="<?php echo htmlspecialchars($w_item['name']); ?>">
                                        <?php else: ?>
                                            <img src="img/favicon.png" alt="No image">
                                        <?php endif; ?>
                                    </div>
                                    <span class="cat-label"><?php echo htmlspecialchars($w_item['category_name']); ?></span>
                                    <h5><?php echo htmlspecialchars($w_item['name']); ?></h5>
                                    <?php render_rating_stars(4.9); ?>
                                    <?php render_delivery_badge(); ?>
                                    <?php render_price($w_item['price'], $w_item['mrp']); ?>
                                </a>
                                <?php render_quick_add($conn, $w_item); ?>
                            </section>
                        <?php endwhile; ?>
                    </div>
                    <button type="button" class="wardrobe-arrow next" onclick="document.getElementById('womenWardrobeTrack').scrollBy({left:300,behavior:'smooth'});" aria-label="Next">&#8594;</button>
                </div>
            </div>
        </section>

        <!-- ============ NEW COLLECTION ============ -->
        <?php if ($new_result->num_rows > 0): ?>
        <section class="section">
            <div class="section-head">
                <div>
                    <span class="eyebrow">// Just landed</span>
                    <h2>New collection</h2>
                </div>
                <p>The latest additions to the lineup, fresh in this week.</p>
            </div>
            <div class="new-collection-wrap">
                <a href="#" class="slider-arrow prev" onclick="document.getElementById('newCollectionTrack').scrollBy({left:-440,behavior:'smooth'});return false;" aria-label="Previous">&#8592;</a>
                <div class="new-collection" id="newCollectionTrack">
                    <?php while ($item = $new_result->fetch_assoc()): ?>
                        <section class="card">
                            <span class="ribbon">New</span>
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
                                <span class="cat-label"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                <?php render_rating_stars(4.9); ?>
                                <?php render_delivery_badge(); ?>
                                <?php render_price($item['price'], $item['mrp']); ?>
                            </a>
                        </section>
                    <?php endwhile; ?>
                </div>
                <a href="#" class="slider-arrow next" onclick="document.getElementById('newCollectionTrack').scrollBy({left:440,behavior:'smooth'});return false;" aria-label="Next">&#8594;</a>
            </div>
        </section>
        <?php endif; ?>

        <!-- ============ SHOP ALL PRODUCTS (AMAZON/FLIPKART GRADE CARDS) ============ -->
        <section class="section section-alt" id="shop-all">
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
                            <span class="cat-label"><?php echo htmlspecialchars($item['category_name']); ?></span>
                            <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                            <?php render_rating_stars(4.8); ?>
                            <?php render_delivery_badge(); ?>
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

    <!-- SCRIPTS -->
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
                }, 5000);
            }
        });

        // Interactive Wardrobe Switcher
        function switchWardrobe(gender) {
            document.querySelectorAll('.wardrobe-tab-btn').forEach(function(b) { b.classList.remove('active'); });
            document.querySelectorAll('.wardrobe-panel').forEach(function(p) { p.classList.remove('active'); });
            if (gender === 'men') {
                document.getElementById('tabMen').classList.add('active');
                document.getElementById('wardrobeMen').classList.add('active');
            } else {
                document.getElementById('tabWomen').classList.add('active');
                document.getElementById('wardrobeWomen').classList.add('active');
            }
        }

        // 3D Rotating Ring Cylinder Engine (Spacious, Un-Squeezed 60FPS GPU Accelerated)
        (function() {
            const stage = document.getElementById('ring3DStage');
            const viewport = document.getElementById('ring3DViewport');
            const prevBtn = document.getElementById('ringPrev');
            const nextBtn = document.getElementById('ringNext');
            if (!stage || !viewport) return;

            let currentAngle = 0;
            let targetAngle = 0;
            let isDragging = false;
            let startX = 0;
            let dragStartAngle = 0;
            let isHovered = false;
            let lastScrollY = window.pageYOffset;

            // Pause auto-spin on hover so user can inspect or click
            viewport.addEventListener('mouseenter', function() { isHovered = true; });
            viewport.addEventListener('mouseleave', function() { isHovered = false; });

            // 60FPS GPU Transform Render Loop with lerp smoothing
            function updateRing() {
                if (!isDragging && !isHovered) {
                    targetAngle += 0.22; // Smooth idle rotation
                }
                currentAngle += (targetAngle - currentAngle) * 0.085;
                stage.style.transform = 'rotateY(' + currentAngle + 'deg)';
                requestAnimationFrame(updateRing);
            }
            requestAnimationFrame(updateRing);

            // Scroll-driven rotation: spins the cylinder smoothly as user scrolls
            window.addEventListener('scroll', function() {
                const currentScrollY = window.pageYOffset;
                const delta = currentScrollY - lastScrollY;
                lastScrollY = currentScrollY;
                targetAngle += delta * 0.16;
            }, { passive: true });

            // Mouse Drag interaction
            viewport.addEventListener('mousedown', function(e) {
                if (e.target.closest('a') || e.target.closest('button')) return;
                isDragging = true;
                startX = e.clientX;
                dragStartAngle = targetAngle;
                viewport.style.cursor = 'grabbing';
            });
            window.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                const dx = e.clientX - startX;
                targetAngle = dragStartAngle + (dx * 0.4);
            });
            window.addEventListener('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    viewport.style.cursor = 'grab';
                }
            });

            // Touch Swipe interaction
            viewport.addEventListener('touchstart', function(e) {
                if (e.touches.length === 1) {
                    isDragging = true;
                    startX = e.touches[0].clientX;
                    dragStartAngle = targetAngle;
                }
            }, { passive: true });
            viewport.addEventListener('touchmove', function(e) {
                if (!isDragging || e.touches.length !== 1) return;
                const dx = e.touches[0].clientX - startX;
                targetAngle = dragStartAngle + (dx * 0.4);
            }, { passive: true });
            viewport.addEventListener('touchend', function() {
                isDragging = false;
            });

            // Snap Buttons (45 degrees per click)
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    targetAngle -= 45;
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    targetAngle += 45;
                });
            }
        })();
    </script>
</body>
</html>