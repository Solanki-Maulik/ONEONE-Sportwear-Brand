<?php
require 'includes/session.php';
require 'db.php';
$brand = 'ONEONE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About — <?php echo htmlspecialchars($brand); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
    <section class="main">
        <?php include 'includes/nav.php'; ?>
    </section>
    <?php include 'includes/ticker.php'; ?>

    <section class="hero" style="min-height:auto; padding-bottom:60px;">
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
        <div class="panel" style="margin:0 auto;">
            <p style="color:var(--slate); line-height:1.7;">
                For order questions, use the <a href="orders.php" style="color:var(--accent); font-weight:600;">Track Orders</a> page once you're logged in.
                For anything else, reach the team at <span class="mono"><?php echo htmlspecialchars('support@stride.example'); ?></span>.
            </p>
        </div>
    </section>

    <footer id="fotter">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="index.php" class="brand"><?php echo htmlspecialchars($brand); ?></a>
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
            <span>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($brand); ?> Sportswear</span>
            <span>Made for people who don't stop at good enough.</span>
        </div>
    </footer>
    </main>
</body>
</html>
