<?php
require 'includes/session.php';
require 'db.php';
require 'includes/helpers.php'; // ADDED: This fixes the cart_count() error

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$orders = $conn->prepare("SELECT id, total_amount, payment_method, payment_detail, order_status, created_at, ship_name, ship_phone, ship_address, ship_city, ship_state, ship_pincode FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders->bind_param("i", $user_id);
$orders->execute();
$orders_result = $orders->get_result();

$payment_labels = ['cod' => 'Cash on Delivery', 'card' => 'Credit / Debit Card', 'upi' => 'UPI'];

function status_steps($status) {
    // Order status is now stored granularly, so this maps 1:1 to the tracker.
    $steps = ['Placed', 'Packed', 'Shipped', 'Delivered'];
    $progress = ['placed' => 1, 'packed' => 2, 'shipped' => 3, 'delivered' => 4];
    if ($status === 'cancelled') return ['labels' => $steps, 'done' => 0, 'cancelled' => true];
    $done = isset($progress[$status]) ? $progress[$status] : 1;
    return ['labels' => $steps, 'done' => $done, 'cancelled' => false];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders — ElevenX</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
    <section class="main">
        <?php include 'includes/nav.php'; ?>
    </section>
    <?php include 'includes/ticker.php'; ?>

    <section class="panel">
        <h1>Your Orders</h1>

        <?php if ($orders_result->num_rows === 0): ?>
            <div class="empty-state">
                <p>You haven't placed any orders yet.</p>
                <a href="index.php">Start shopping</a>
            </div>
        <?php else: ?>
            <?php while ($order = $orders_result->fetch_assoc()):
                $steps = status_steps($order['order_status']);
                $items = $conn->prepare("SELECT oi.quantity, oi.price_at_time, oi.size, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                $items->bind_param("i", $order['id']);
                $items->execute();
                $items_result = $items->get_result();
            ?>
            <div style="border:1px solid var(--line); border-radius:var(--radius-md); padding:22px; margin-bottom:20px;">
                <div class="order-row" style="border-bottom:1px solid var(--line); padding-top:0;">
                    <div>
                        <strong>Order #<?php echo $order['id']; ?></strong>
                        <div class="order-meta"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?> · <?php echo htmlspecialchars($order['payment_detail'] ?? ($payment_labels[$order['payment_method']] ?? 'Cash on Delivery')); ?></div>
                        <?php if (!empty($order['ship_address'])): ?>
                        <div class="order-meta" style="margin-top:4px;">Shipping to <?php echo htmlspecialchars($order['ship_name'] . ', ' . $order['ship_address'] . ', ' . $order['ship_city'] . ', ' . $order['ship_state'] . ' ' . $order['ship_pincode']); ?> · <?php echo htmlspecialchars($order['ship_phone']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align:right;">
                        <div class="order-total">₹<?php echo number_format($order['total_amount'], 2); ?></div>
                        <span class="status-badge status-<?php echo $order['order_status']; ?>"><?php echo htmlspecialchars($order['order_status']); ?></span>
                    </div>
                </div>

                <?php if (!$steps['cancelled']): ?>
                <div class="track-steps">
                    <?php foreach ($steps['labels'] as $i => $label): ?>
                        <div class="track-step <?php echo ($i < $steps['done']) ? 'done' : ''; ?>">
                            <div class="dot"></div>
                            <span><?php echo $label; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color:var(--accent-deep); font-size:13px; margin-top:14px;">This order was cancelled.</p>
                <?php endif; ?>

                <div style="margin-top:16px;">
                    <h4 class="mono" style="font-size:11px; letter-spacing:1px; text-transform:uppercase; color:var(--slate); margin-bottom:6px;">Items in this order</h4>
                    <?php while ($line = $items_result->fetch_assoc()): ?>
                        <div class="cart-line" style="padding:10px 0;">
                            
                            <!-- CORRECTED IMAGE FIX HERE -->
                            <?php 
                            if (!empty($line['image_url'])): 
                                $images = explode(',', $line['image_url']);
                                $first_image = trim($images[0]);
                            ?>
                                <img src="<?php echo htmlspecialchars($first_image); ?>" style="width:48px; height:48px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <img src="img/favicon.png" style="width:48px; height:48px; object-fit: cover; border-radius: 4px;" alt="No image">
                            <?php endif; ?>
                            
                            <div class="cart-line-info">
                                <h5 style="font-size:13px;"><?php echo htmlspecialchars($line['name']); ?></h5>
                                <div class="cart-line-qty">
                                    <?php if (!empty($line['size'])): ?>Size: <?php echo htmlspecialchars($line['size']); ?> · <?php endif; ?>
                                    Qty: <?php echo $line['quantity']; ?> · ₹<?php echo number_format($line['price_at_time'], 2); ?> each
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo $order['order_status']; ?>" style="align-self:center;"><?php echo htmlspecialchars($order['order_status']); ?></span>
                        </div>
                    <?php endwhile; $items->close(); ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </section>

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
                <a href="orders.php">Your orders</a>
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