<?php
require 'includes/session.php';
require 'db.php';
require 'includes/helpers.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$product_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}
$product = $result->fetch_assoc();
$stmt->close();

// The unused $gallery query was removed from here.

$sizes = get_product_sizes($conn, $product_id);
$total_stock = 0;
foreach ($sizes as $s) { $total_stock += $s['stock']; }
if (empty($sizes)) { $total_stock = $product['stock_quantity']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> — ONEONE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
    <section class="main">
        <?php include 'includes/nav.php'; ?>
    </section>
    <?php include 'includes/ticker.php'; ?>

    <section class="panel product-detail-wrap">
        <!-- LEFT COLUMN: IMAGES -->
        <div>
            <?php 
            // 1. Split the images into an array
            $images = [];
            if (!empty($product['image_url'])) {
                $images = explode(',', $product['image_url']);
            }
            // 2. Define the main starting image
            $main_image = !empty($images) ? trim($images[0]) : 'img/favicon.png';
            ?>

            <!-- The Main Large Image -->
            <div class="product-main-image-container">
                <img id="mainProductImage" src="<?php echo htmlspecialchars($main_image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; border-radius: 12px; object-fit: cover;">
            </div>

            <!-- The Clickable Thumbnails -->
            <?php if (count($images) > 1): ?>
                <div class="product-thumbnails" style="display: flex; gap: 10px; margin-top: 15px; overflow-x: auto;">
                    <?php foreach ($images as $img): 
                        $clean_img = trim($img);
                    ?>
                        <img src="<?php echo htmlspecialchars($clean_img); ?>" 
                             alt="Thumbnail" 
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid transparent; transition: 0.2s;"
                             onclick="document.getElementById('mainProductImage').src='<?php echo htmlspecialchars($clean_img); ?>'"
                             onmouseover="this.style.border='2px solid #FF4D23'"
                             onmouseout="this.style.border='2px solid transparent'">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT COLUMN: PRODUCT INFO -->
        <div class="info">
            <span class="cat-label"><?php echo htmlspecialchars($product['category_name']); ?></span>
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <?php render_price($product['price'], $product['mrp']); ?>
            
            <p style="color:var(--slate); line-height:1.6; margin-top:14px; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>
            
            <p class="mono" style="margin-top:14px; font-size:13px; color: <?php echo $total_stock > 0 ? '#1F8A2E' : '#D93E14'; ?>;" id="stockLine">
                <?php echo $total_stock > 0 ? "In stock — " . $total_stock . " available" : "Out of stock"; ?>
            </p>

            <form action="cart.php" method="POST" class="qty-form" id="addToCartForm" style="flex-wrap:wrap;">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <?php if (!empty($sizes)): ?>
                    <input type="hidden" name="size" id="selectedSize" value="">
                    <div style="width:100%; margin-bottom:4px;">
                        <label class="mono" style="font-size:12px; letter-spacing:1px; text-transform:uppercase; color:var(--slate); display:block; margin-bottom:8px;">Select size</label>
                        <div class="size-picker">
                            <?php foreach ($sizes as $s): ?>
                                <button type="button" class="size-chip <?php echo $s['stock'] <= 0 ? 'disabled' : ''; ?>"
                                        <?php echo $s['stock'] <= 0 ? 'disabled' : ''; ?>
                                        data-size="<?php echo htmlspecialchars($s['size']); ?>"
                                        data-stock="<?php echo $s['stock']; ?>"
                                        onclick="selectSize(this)"><?php echo htmlspecialchars($s['size']); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <p id="sizeError" style="color:var(--accent-deep); font-size:12px; margin-top:6px; display:none;">Please select a size.</p>
                    </div>
                    <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="1" disabled>
                    <button type="submit" name="add_to_cart" class="btn-dark" id="addToCartBtn" <?php echo $total_stock <= 0 ? 'disabled' : ''; ?>>Add to cart</button>
                <?php else: ?>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $total_stock; ?>" <?php echo $total_stock <= 0 ? 'disabled' : ''; ?>>
                    <button type="submit" name="add_to_cart" class="btn-dark" <?php echo $total_stock <= 0 ? 'disabled' : ''; ?>>Add to cart</button>
                <?php endif; ?>
            </form>
            
            <?php if (!empty($sizes)): ?>
            <script>
                function selectSize(btn){
                    if (btn.disabled) return;
                    document.querySelectorAll('.size-chip').forEach(function(el){ el.classList.remove('active'); });
                    btn.classList.add('active');
                    document.getElementById('selectedSize').value = btn.dataset.size;
                    document.getElementById('sizeError').style.display = 'none';

                    var stock = parseInt(btn.dataset.stock, 10);
                    var qty = document.getElementById('qtyInput');
                    qty.disabled = false;
                    qty.max = stock;
                    qty.value = 1;
                    document.getElementById('addToCartBtn').disabled = false;
                    document.getElementById('stockLine').textContent = 'In stock — ' + stock + ' available in this size';
                    document.getElementById('stockLine').style.color = '#1F8A2E';
                }
                
                document.getElementById('addToCartForm').addEventListener('submit', function(e){
                    if (!document.getElementById('selectedSize').value) {
                        e.preventDefault();
                        document.getElementById('sizeError').style.display = 'block';
                    }
                });
            </script>
            <?php endif; ?>
        </div> 
    </section>

    <footer id="fotter">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="index.php" class="brand">ONEONE</a>
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
            <span>&copy; <?php echo date('Y'); ?> ONEONE</span>
            <span>Made for people who don't stop at good enough.</span>
        </div>
    </footer>
    </main>
</body>
</html>