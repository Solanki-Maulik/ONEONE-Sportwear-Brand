<?php
require 'includes/session.php';
require 'db.php';
$brand = 'ElevenX';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About ElevenX — Performance Engineered Athletics</title>
    <meta name="description" content="Learn about ElevenX: engineered performance sportswear, footwear, and apparel built for athletes who push boundaries.">
    <link rel="stylesheet" href="style.css">
    <style>
        .about-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('img/about_hero.jpg') center/cover no-repeat;
            color: white;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: left;
            padding: 80px 20px;
        }
        .about-hero h1 {
            color: white;
            margin-bottom: 20px;
        }
        .about-hero p {
            color: #f1f1f1;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .about-hero .hero-eyebrow {
            color: var(--accent);
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 2px;
            display: block;
            margin-bottom: 15px;
            text-shadow: 0 0 10px rgba(255, 77, 35, 0.5);
        }
    </style>
</head>
<body>
    <main>
    <section class="main">
        <?php include 'includes/nav.php'; ?>
    </section>
    <?php include 'includes/ticker.php'; ?>

    <section class="hero about-hero">
        <div class="hero-copy" style="max-width:700px;">
            <span class="hero-eyebrow">// About <?php echo htmlspecialchars($brand); ?></span>
            <h1>Built by people<br>who train.</h1>
            <p><?php echo htmlspecialchars("Performance sportswear built for the run, the court, and everything after. Designed and tested by people who actually train."); ?></p>
        </div>
    </section>

    <section class="section">
        <div class="value-strip">
            <div class="value-item">
                <span class="mono">Our story</span>
                <h4>Why <?php echo htmlspecialchars($brand); ?> exists</h4>
                <p>We got tired of choosing between gear that looks good and gear that actually holds up to real training. So we started building our own — footwear and jerseys tested by the people who wear them.</p>
            </div>
            <div class="value-item">
                <span class="mono">What we make</span>
                <h4>Footwear & jerseys</h4>
                <p>A focused lineup for men and women, built around fit, durability, and everyday performance rather than chasing every trend.</p>
            </div>
            <div class="value-item">
                <span class="mono">How we ship</span>
                <h4>Fast, honest, simple</h4>
                <p>Same-day dispatch, transparent pricing, and a 30-day return window — no fine print, no surprises at checkout.</p>
            </div>
        </div>
    </section>

    <section class="section section-alt">
        <div class="section-head">
            <div>
                <span class="eyebrow">// Get in touch</span>
                <h2>Questions? Reach out.</h2>
            </div>
            <p>We read every message.</p>
        </div>
        <div class="value-strip" style="margin-top: 40px; padding: 0;">
            <div class="value-item">
                <span class="mono">General Enquiries</span>
                <h4>Email Us</h4>
                <p><a href="mailto:support@<?php echo strtolower($brand); ?>.com" style="color:var(--accent); text-decoration:none; font-weight:600;">support@<?php echo strtolower($brand); ?>.com</a><br>We aim to reply within 24 hours.</p>
            </div>
            <div class="value-item">
                <span class="mono">Headquarters</span>
                <h4>Visit Us</h4>
                <p>123 Athletics Boulevard<br>Suite 400, New York, NY 10001<br>United States</p>
            </div>
            <div class="value-item">
                <span class="mono">Phone Support</span>
                <h4>Call Us</h4>
                <p><a href="tel:+18005550199" style="color:var(--accent); text-decoration:none; font-weight:600;">+1 (800) 555-0199</a><br>Mon-Fri, 9am - 6pm EST</p>
            </div>
        </div>
    </section>

    <footer id="fotter">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="index.php" class="brand"><span class="brand-word">ELEVENX</span><span class="brand-badge">ATHLETICS</span></a>
                <p><?php echo htmlspecialchars('Performance sportswear built for the run, the court, and everything after.'); ?></p>
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
            <span>&copy; <?php echo date('Y'); ?> ElevenX Athletics. All rights reserved.</span>
            <span>Made for athletes who don't stop at good enough.</span>
        </div>
    </footer>
    </main>
</body>
</html>
