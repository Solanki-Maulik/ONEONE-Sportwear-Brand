<?php
require 'includes/session.php';
require 'db.php';
require 'includes/helpers.php';

$message = "";

// Add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $product_id = (int)$_POST['product_id'];
    $size = isset($_POST['size']) && $_POST['size'] !== '' ? $_POST['size'] : null;
    $quantity = (int)$_POST['quantity'];

    $available = stock_for($conn, $product_id, $size);
    $already_in_cart = cart_line_quantity($conn, $_SESSION['user_id'], $product_id, $size);
    $new_quantity = min($already_in_cart + $quantity, $available);
    if ($new_quantity > 0) {
        cart_set($conn, $_SESSION['user_id'], $product_id, $size, $new_quantity);
        header("Location: cart.php");
        exit();
    } else {
        // Redirect back to product page with stock error

        $back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        $sep = (strpos($back, '?') === false) ? '?' : '&';
        header("Location: " . $back . $sep . "stock_error=1");
        exit();
    }
}

// Remove from cart
if (isset($_GET['remove']) && isset($_SESSION['user_id'])) {
    $parsed = parse_cart_key($_GET['remove']);
    cart_remove_line($conn, $_SESSION['user_id'], $parsed['product_id'], $parsed['size']);
    header("Location: cart.php");
    exit();
}

// Apply coupon (requires login, since it's part of the checkout step)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon']) && isset($_SESSION['user_id'])) {
    $code = trim($_POST['coupon_code']);
    $stmt = $conn->prepare("SELECT id, discount_percent FROM coupons WHERE code = ? AND is_active = TRUE AND expiry_date >= CURDATE()");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $coupon = $result->fetch_assoc();
        $_SESSION['coupon_id'] = $coupon['id'];
        $_SESSION['discount_percent'] = $coupon['discount_percent'];
        $message = "<div class='alert-box alert-success'>Coupon applied! {$coupon['discount_percent']}% off.</div>";
    } else {
        $message = "<div class='alert-box alert-error'>Invalid or expired coupon.</div>";
        unset($_SESSION['coupon_id']);
        unset($_SESSION['discount_percent']);
    }
    $stmt->close();
}

$payment_methods = [
    'cod'  => 'Cash on Delivery',
    'card' => 'Credit / Debit Card',
    'upi'  => 'UPI',
];

// Pre-fetch every cart line (product + size) once, used for both display and totals
$cart_items = [];
$subtotal = 0;
$total_savings = 0;
$db_cart = isset($_SESSION['user_id']) ? cart_get_items($conn, $_SESSION['user_id']) : [];
foreach ($db_cart as $line) {
    $product_id = $line['product_id'];
    $size = $line['size'];
    $quantity = $line['quantity'];
    $stmt = $conn->prepare("SELECT id, name, price, mrp, image_url, stock_quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    if ($item) {
        $available = stock_for($conn, $product_id, $size);
        if ($quantity > $available) {
            $quantity = $available;
            if ($quantity > 0) {
                cart_set($conn, $_SESSION['user_id'], $product_id, $size, $quantity);
            } else {
                cart_remove_line($conn, $_SESSION['user_id'], $product_id, $size);
                $stmt->close();
                continue;
            }
        }
        $item['cart_key'] = cart_key($product_id, $size);
        $item['size'] = $size;
        $item['cart_quantity'] = $quantity;
        $line_total = $item['price'] * $quantity;
        $subtotal += $line_total;
        if (!empty($item['mrp']) && $item['mrp'] > $item['price']) {
            $total_savings += ($item['mrp'] - $item['price']) * $quantity;
        }
        $cart_items[] = $item;
    }
    $stmt->close();
}

$discount = 0;
$coupon_id = null;
if (isset($_SESSION['discount_percent']) && !empty($cart_items)) {
    $discount = ($subtotal * $_SESSION['discount_percent']) / 100;
    $coupon_id = $_SESSION['coupon_id'];
}
$final_total = $subtotal - $discount;

// Place order
$payment_error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order']) && isset($_SESSION['user_id']) && !empty($cart_items)) {
    $user_id = $_SESSION['user_id'];
    $payment_method = isset($_POST['payment_method']) && isset($payment_methods[$_POST['payment_method']]) ? $_POST['payment_method'] : 'cod';

    // Shipping address - required for every payment method, since it's about
    // where the order goes, not how it's paid for.
    $ship_name = trim($_POST['ship_name'] ?? '');
    $ship_phone = trim($_POST['ship_phone'] ?? '');
    $ship_address = trim($_POST['ship_address'] ?? '');
    $ship_city = trim($_POST['ship_city'] ?? '');
    $ship_state = trim($_POST['ship_state'] ?? '');
    $ship_pincode = trim($_POST['ship_pincode'] ?? '');

    if ($ship_name === '' || $ship_address === '' || $ship_city === '' || $ship_state === '') {
        $payment_error = "Please fill in your shipping address.";
    } elseif (!preg_match('/^[0-9]{10}$/', preg_replace('/\D/', '', $ship_phone))) {
        $payment_error = "Please enter a valid 10-digit phone number.";
    } elseif (!preg_match('/^[0-9]{4,8}$/', $ship_pincode)) {
        $payment_error = "Please enter a valid pincode.";
    }

    // Format payment details for display. We only store the last 4 digits for security.

    $payment_detail = null;
    if ($payment_error === '' && $payment_method === 'card') {
        $card_digits = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
        $card_name = trim($_POST['card_name'] ?? '');
        $expiry = trim($_POST['card_expiry'] ?? '');
        $cvv = trim($_POST['card_cvv'] ?? '');
        if (strlen($card_digits) < 13 || $card_name === '' || !preg_match('/^\d{2}\/\d{2}$/', $expiry) || !preg_match('/^\d{3,4}$/', $cvv)) {
            $payment_error = "Please enter valid card details.";
        } else {
            $payment_detail = "Card ending in " . substr($card_digits, -4);
        }
        // Do not insert $card_digits and $cvv into database

    } elseif ($payment_error === '' && $payment_method === 'upi') {
        $upi_id = trim($_POST['upi_id'] ?? '');
        if (!preg_match('/^[\w.\-]{2,}@[\w.\-]{2,}$/', $upi_id)) {
            $payment_error = "Please enter a valid UPI ID (e.g. name@bank).";
        } else {
            $payment_detail = "UPI — " . $upi_id;
        }
    } elseif ($payment_error === '') {
        $payment_detail = "Cash on Delivery";
    }
    if ($payment_error !== "") {
        $message = "<div class='alert-box alert-error'>" . htmlspecialchars($payment_error) . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order']) && isset($_SESSION['user_id']) && !empty($cart_items) && $payment_error === '') {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO orders (user_id, coupon_id, total_amount, payment_method, payment_detail, ship_name, ship_phone, ship_address, ship_city, ship_state, ship_pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iidssssssss", $user_id, $coupon_id, $final_total, $payment_method, $payment_detail, $ship_name, $ship_phone, $ship_address, $ship_city, $ship_state, $ship_pincode);
        $stmt->execute();
        $order_id = $conn->insert_id;
        $stmt->close();

        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time, size) VALUES (?, ?, ?, ?, ?)");
        $stmt_stock_product = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $stmt_stock_size = $conn->prepare("UPDATE product_sizes SET stock = stock - ? WHERE product_id = ? AND size = ?");
        foreach ($cart_items as $item) {
            $stmt_item->bind_param("iiids", $order_id, $item['id'], $item['cart_quantity'], $item['price'], $item['size']);
            $stmt_item->execute();
            if ($item['size'] !== null) {
                $stmt_stock_size->bind_param("iis", $item['cart_quantity'], $item['id'], $item['size']);
                $stmt_stock_size->execute();
            } else {
                $stmt_stock_product->bind_param("ii", $item['cart_quantity'], $item['id']);
                $stmt_stock_product->execute();
            }
        }
        $conn->commit();
        cart_clear($conn, $user_id); unset($_SESSION['coupon_id']); unset($_SESSION['discount_percent']);
        ?>
        <!DOCTYPE html><html><head><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Order Confirmed — ELEVENX</title><link rel="stylesheet" href="style.css"></head><body style="background:#050608; color:#FFFFFF;">
        <section class="panel" style="text-align:center; margin-top:80px; max-width:600px; background:var(--paper-card); border:1px solid var(--line); border-radius:var(--radius-lg); box-shadow:0 20px 45px rgba(0,0,0,0.8);">
            <span class="mono" style="color:var(--success-green); letter-spacing:2px; text-transform:uppercase; font-size:12px; font-weight:700;">✓ ORDER CONFIRMED</span>
            <h1 style="margin-top:14px; font-size:36px; color:#FFFFFF;">ORDER PLACED!</h1>
            <p class="mono" style="margin-top:10px; color:var(--slate);">Order #<?php echo $order_id; ?> · <?php echo htmlspecialchars($payment_detail); ?></p>
            <p class="mono" style="margin-top:4px; color:var(--slate); font-size:12px;">Shipping to <?php echo htmlspecialchars($ship_city . ', ' . $ship_state . ' ' . $ship_pincode); ?></p>
            <div style="margin-top:28px; display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
                <a href="orders.php" class="btn-dark">Track Your Order</a>
                <a href="index.php" class="btn-outline" style="color:#FFFFFF; border-color:rgba(255,255,255,0.25);">Continue Shopping</a>
            </div>
        </section>
        </body></html>
        <?php
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='alert-box alert-error'>Order failed. Please try again.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart & Checkout — ElevenX</title>
    <meta name="description" content="Review your cart and proceed to secure checkout on ElevenX Performance Sportswear.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
    <section class="main">
        <?php include 'includes/nav.php'; ?>
    </section>
    <?php include 'includes/ticker.php'; ?>

    <?php if (empty($cart_items)): ?>
        <section class="panel">
            <h1>Your Cart</h1>
            <div class="empty-state">
                <p>Your cart is empty.</p>
                <a href="index.php">Go back to shop</a>
            </div>
        </section>
    <?php else: ?>
        <div class="shop-layout">
            <div class="items-col">
                <h1>Cart & Checkout (<?php echo count($cart_items); ?> item<?php echo count($cart_items) > 1 ? 's' : ''; ?>)</h1>
                <?php foreach ($cart_items as $item):
                    $line_total = $item['price'] * $item['cart_quantity'];
                ?>
                <div class="cart-line">
                    <?php 
    if (!empty($item['image_url'])): 
        // Split the images and select the first one
        $images = explode(',', $item['image_url']);
        $first_image = trim($images[0]);
?>
    <img src="<?php echo htmlspecialchars($first_image); ?>" alt="Product Image">
<?php else: ?>
    <img src="img/favicon.png" alt="No image">
<?php endif; ?>
                    <div class="cart-line-info">
                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                        <?php render_price($item['price'], $item['mrp']); ?>
                        <div class="cart-line-qty">
                            <?php if ($item['size']): ?>Size: <?php echo htmlspecialchars($item['size']); ?> · <?php endif; ?>
                            Qty: <?php echo $item['cart_quantity']; ?>
                        </div>
                        <a href="cart.php?remove=<?php echo urlencode($item['cart_key']); ?>" class="remove-link">Remove</a>
                    </div>
                    <div class="cart-line-total">₹<?php echo number_format($line_total, 2); ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-col">
                <h3>Price details</h3>
                <?php echo $message; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="cart.php" method="POST" class="coupon-form" style="margin-top:0;">
                        <input type="text" name="coupon_code" placeholder="Promo code">
                        <button type="submit" name="apply_coupon" class="btn-dark">Apply</button>
                    </form>
                <?php endif; ?>

                <div class="summary-row"><span>Subtotal</span><span>₹<?php echo number_format($subtotal, 2); ?></span></div>
                <?php if ($total_savings > 0): ?>
                <div class="summary-row discount"><span>Product savings</span><span>-₹<?php echo number_format($total_savings, 2); ?></span></div>
                <?php endif; ?>
                <?php if ($discount > 0): ?>
                <div class="summary-row discount"><span>Coupon discount</span><span>-₹<?php echo number_format($discount, 2); ?></span></div>
                <?php endif; ?>
                <div class="summary-row grand"><span>Total</span><span>₹<?php echo number_format($final_total, 2); ?></span></div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="cart.php" method="POST" id="paymentForm">
                        <h3 style="margin-top:20px;">Shipping address</h3>
                        <div class="payment-detail-fields">
                            <input type="text" name="ship_name" placeholder="Full name" value="<?php echo htmlspecialchars($_POST['ship_name'] ?? ''); ?>" required>
                            <input type="tel" name="ship_phone" id="shipPhone" placeholder="Phone number (10 digits)" inputmode="numeric" maxlength="10" value="<?php echo htmlspecialchars($_POST['ship_phone'] ?? ''); ?>" required>
                            <input type="text" name="ship_address" placeholder="Address, house no., street" value="<?php echo htmlspecialchars($_POST['ship_address'] ?? ''); ?>" required>
                            <div class="payment-detail-row">
                                <input type="text" name="ship_city" placeholder="City" value="<?php echo htmlspecialchars($_POST['ship_city'] ?? ''); ?>" required>
                                <input type="text" name="ship_state" placeholder="State" value="<?php echo htmlspecialchars($_POST['ship_state'] ?? ''); ?>" required>
                                <input type="text" name="ship_pincode" placeholder="Pincode" inputmode="numeric" maxlength="8" value="<?php echo htmlspecialchars($_POST['ship_pincode'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <h3 style="margin-top:20px;">Payment method</h3>
                        <div class="payment-methods">
                            <?php $first = true; foreach ($payment_methods as $key => $label): ?>
                            <label class="payment-method-option">
                                <input type="radio" name="payment_method" value="<?php echo $key; ?>" onchange="togglePaymentFields()" <?php echo $first ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </label>
                            <?php $first = false; endforeach; ?>
                        </div>

                        <div id="cardFields" class="payment-detail-fields" style="display:none;">
                            <input type="text" name="card_name" placeholder="Name on card" autocomplete="cc-name">
                            <input type="text" name="card_number" id="cardNumber" placeholder="Card number" inputmode="numeric" maxlength="19" autocomplete="cc-number">
                            <div class="payment-detail-row">
                                <input type="text" name="card_expiry" id="cardExpiry" placeholder="MM/YY" inputmode="numeric" maxlength="5" autocomplete="cc-exp">
                                <input type="text" name="card_cvv" placeholder="CVV" inputmode="numeric" maxlength="4" autocomplete="cc-csc">
                            </div>
                        </div>

                        <div id="upiFields" class="payment-detail-fields" style="display:none;">
                            <input type="text" name="upi_id" id="upiId" placeholder="yourname@bank">
                        </div>

                        <button type="submit" name="place_order" class="btn-dark" id="placeOrderBtn">Place Order</button>
                        <p class="mono" style="font-size:11.5px; color:var(--slate); margin-top:12px; line-height:1.5; display:flex; align-items:center; gap:6px;">🔒 256-Bit SSL Encrypted Checkout · Guaranteed Safe & Direct Dispatch</p>
                    </form>
                    <script>
                        function togglePaymentFields(){
                            var method = document.querySelector('input[name="payment_method"]:checked').value;
                            document.getElementById('cardFields').style.display = (method === 'card') ? 'flex' : 'none';
                            document.getElementById('upiFields').style.display = (method === 'upi') ? 'flex' : 'none';
                        }
                        togglePaymentFields();

                        // Light formatting as you type - purely cosmetic, real validation happens server-side too
                        var cardNumberEl = document.getElementById('cardNumber');
                        if (cardNumberEl) {
                            cardNumberEl.addEventListener('input', function(){
                                this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim().slice(0, 19);
                            });
                        }
                        var cardExpiryEl = document.getElementById('cardExpiry');
                        if (cardExpiryEl) {
                            cardExpiryEl.addEventListener('input', function(){
                                var v = this.value.replace(/\D/g, '').slice(0, 4);
                                this.value = v.length > 2 ? v.slice(0,2) + '/' + v.slice(2) : v;
                            });
                        }
                    </script>
                <?php else: ?>
                    <a href="login.php" class="btn-dark">Login to Checkout</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <footer id="fotter">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="index.php" class="brand"><span class="brand-word">ELEVENX</span><span class="brand-badge">ATHLETICS</span></a>
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
            <span>&copy; <?php echo date('Y'); ?> ElevenX Athletics. All rights reserved.</span>
            <span>Made for athletes who don't stop at good enough.</span>
        </div>
    </footer>
    </main>
</body>
</html>
