<?php
// Renders the price block used on every product card/detail page:
// selling price, plus a struck-through MRP and a discount % badge
// whenever an MRP higher than the selling price is set.
function render_price($price, $mrp) {
    $price = (float)$price;
    $mrp = ($mrp !== null && $mrp !== '') ? (float)$mrp : null;
    echo '<span class="price-row">';
    echo '<span class="price-sell">₹' . number_format($price, 2) . '</span>';
    if ($mrp !== null && $mrp > $price) {
        $off = round((($mrp - $price) / $mrp) * 100);
        echo '<span class="price-mrp">₹' . number_format($mrp, 2) . '</span>';
        echo '<span class="price-off">' . $off . '% off</span>';
    }
    echo '</span>';
}

// Cart entries are keyed "productId|size" (size is '' when a product has no
// sizes) so the same product in two different sizes are separate cart lines.
function cart_key($product_id, $size) {
    return $product_id . '|' . ($size !== null ? $size : '');
}
function parse_cart_key($key) {
    $parts = explode('|', $key, 2);
    return ['product_id' => (int)$parts[0], 'size' => isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null];
}

// The cart lives in the `cart_items` table, tied to the account, not the
// browser session - so it's still there after logging out and back in.

// Sets the exact quantity for one product+size line (creating it if needed).
function cart_set($conn, $user_id, $product_id, $size, $quantity) {
    $size_key = $size !== null ? $size : '';
    $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, size, quantity) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)");
    $stmt->bind_param("iisi", $user_id, $product_id, $size_key, $quantity);
    $stmt->execute();
    $stmt->close();
}

// Current quantity already in the cart for one product+size line (0 if none).
function cart_line_quantity($conn, $user_id, $product_id, $size) {
    $size_key = $size !== null ? $size : '';
    $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ? AND size = ?");
    $stmt->bind_param("iis", $user_id, $product_id, $size_key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int)$row['quantity'] : 0;
}

// Every line in a user's cart: [['product_id'=>.., 'size'=>.. or null, 'quantity'=>..], ...]
function cart_get_items($conn, $user_id) {
    $stmt = $conn->prepare("SELECT product_id, size, quantity FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'product_id' => (int)$row['product_id'],
            'size' => $row['size'] !== '' ? $row['size'] : null,
            'quantity' => (int)$row['quantity'],
        ];
    }
    $stmt->close();
    return $items;
}

function cart_remove_line($conn, $user_id, $product_id, $size) {
    $size_key = $size !== null ? $size : '';
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ? AND size = ?");
    $stmt->bind_param("iis", $user_id, $product_id, $size_key);
    $stmt->execute();
    $stmt->close();
}

function cart_clear($conn, $user_id) {
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Total item count for the nav badge. Safe to call with no user (returns 0).
function cart_count($conn, $user_id) {
    if (empty($user_id)) return 0;
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)$row['total'];
}

// Returns [['size' => '8', 'stock' => 3], ...] ordered so shoe sizes (numeric)
// sort numerically and clothing sizes (S/M/L/XL/XXL) sort by garment order.
function get_product_sizes($conn, $product_id) {
    $order = "FIELD(size,'XS','S','M','L','XL','XXL'), size + 0";
    $stmt = $conn->prepare("SELECT size, stock FROM product_sizes WHERE product_id = ? ORDER BY $order");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sizes = [];
    while ($row = $result->fetch_assoc()) { $sizes[] = $row; }
    $stmt->close();
    return $sizes;
}

function product_has_sizes($conn, $product_id) {
    return count(get_product_sizes($conn, $product_id)) > 0;
}

// Stock available for a specific product+size combination. Falls back to the
// product's own stock_quantity when the product has no sizes at all.
function stock_for($conn, $product_id, $size) {
    if ($size === null) {
        $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? (int)$row['stock_quantity'] : 0;
    }
    $stmt = $conn->prepare("SELECT stock FROM product_sizes WHERE product_id = ? AND size = ?");
    $stmt->bind_param("is", $product_id, $size);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int)$row['stock'] : 0;
}

// Renders the add-to-cart control for a grid card. Sizeless products get a
// plain "Add to cart" button. Sized products get inline size chips plus an
// "Add to cart" button right on the card - no navigating away needed. Chips
// for sizes that are out of stock are shown disabled rather than hidden, so
// the shopper can see the size exists but is unavailable.
function render_quick_add($conn, $item) {
    $sizes = get_product_sizes($conn, $item['id']);

    if (empty($sizes)) {
        if ($item['stock_quantity'] <= 0) {
            echo '<button type="button" disabled>Out of stock</button>';
            return;
        }
        ?>
        <form action="cart.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" name="add_to_cart">Add to cart</button>
        </form>
        <?php
        return;
    }

    // Sized products pick their size on the product page - no chips on the card.
    $any_in_stock = false;
    foreach ($sizes as $s) { if ($s['stock'] > 0) { $any_in_stock = true; break; } }

    if (!$any_in_stock) {
        echo '<button type="button" disabled>Out of stock</button>';
        return;
    }
    ?>
    <a href="product.php?id=<?php echo $item['id']; ?>" class="btn-dark" style="display:block; text-align:center; width:100%; box-sizing:border-box;">Add to cart</a>
    <?php
}
?>
