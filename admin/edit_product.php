<?php
require_once '../includes/session.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require '../db.php';

$message = "";

// Check if a product ID was passed in the URL
if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}
$product_id = (int)$_GET['id'];

// Handle form submission to update stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inventory'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "<div style='background: #e74c3c; color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px;'>Security token mismatch. Please try again.</div>";
    } else {
        // 1. Update Base Stock
        $base_stock = (int)$_POST['stock_quantity'];
        $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $base_stock, $product_id);
        $stmt->execute();
        $stmt->close();

    // 2. Update Existing Sizes Stock
    if (isset($_POST['sizes']) && is_array($_POST['sizes'])) {
        foreach ($_POST['sizes'] as $size => $stock) {
            $stock = (int)$stock;
            // Decode the size in case it has spaces or special characters
            $size = urldecode($size); 
            $stmt = $conn->prepare("UPDATE product_sizes SET stock = ? WHERE product_id = ? AND size = ?");
            $stmt->bind_param("iis", $stock, $product_id, $size);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 3. Add a Brand New Size (if filled out)
    if (!empty(trim($_POST['new_size'])) && isset($_POST['new_size_stock'])) {
        $new_size = trim($_POST['new_size']);
        $new_stock = (int)$_POST['new_size_stock'];
        
        // Check if size already exists to prevent duplicates
        $check = $conn->prepare("SELECT stock FROM product_sizes WHERE product_id = ? AND size = ?");
        $check->bind_param("is", $product_id, $new_size);
        $check->execute();
        if ($check->get_result()->num_rows == 0) {
            $insert = $conn->prepare("INSERT INTO product_sizes (product_id, size, stock) VALUES (?, ?, ?)");
            $insert->bind_param("isi", $product_id, $new_size, $new_stock);
            $insert->execute();
            $insert->close();
        }
        $check->close();
    }

        $message = "<div style='background: #2ecc71; color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px;'>Inventory updated successfully!</div>";
    }
}

// Fetch the product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch the sizes associated with this product
$size_stmt = $conn->prepare("SELECT size, stock FROM product_sizes WHERE product_id = ? ORDER BY FIELD(size,'XS','S','M','L','XL','XXL'), size + 0");
$size_stmt->bind_param("i", $product_id);
$size_stmt->execute();
$sizes_result = $size_stmt->get_result();
$size_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Inventory - Admin</title>
    <style>
        /* BASE STYLES */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; margin: 0; background: #f0f2f5; color: #333; }
        
        /* UNIFIED SIDEBAR STYLES */
        .sidebar { width: 260px; background: #1a1d21; color: white; min-height: 100vh; padding: 25px; box-sizing: border-box; }
        .sidebar h2 { font-size: 20px; text-transform: uppercase; letter-spacing: 1px; color: #f39c12; margin-top: 0; margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 15px; }
        .sidebar a { color: #cbd5e0; text-decoration: none; display: flex; align-items: center; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; transition: background 0.2s; font-size: 15px; }
        .sidebar a:hover, .sidebar a.active { background: #2d3238; color: white; }
        .sidebar .logout { color: #e74c3c; margin-top: 30px; }
        .sidebar .logout:hover { background: #c0392b; color: white; }

        /* MAIN CONTENT STYLES */
        .main-content { padding: 40px; flex-grow: 1; max-width: 800px; }
        .edit-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
        input[type="number"], input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .size-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 10px;}
        .btn-submit { background: #111; color: white; border: none; padding: 12px 24px; font-size: 16px; cursor: pointer; border-radius: 4px; width: 100%; margin-top: 20px; }
        .btn-submit:hover { background: #333; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #111; text-decoration: none; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>ElevenX HQ</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="manage_products.php" class="active">📦 Manage Products</a>
        <a href="manage_orders.php">🛒 Manage Orders</a>
        <a href="coupons.php">🎟️ Coupons</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <div class="main-content">
        <a href="manage_products.php" class="back-link">← Back to Products</a>
        
        <div class="edit-card">
            <h1 style="margin-top:0;">Edit Inventory: <?php echo htmlspecialchars($product['name']); ?></h1>
            <?php echo $message; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                <!-- BASE STOCK -->
                <div class="form-group">
                    <label>Base Stock (For products without sizes)</label>
                    <input type="number" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>" min="0">
                </div>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">
                
                <h3>Specific Sizes</h3>
                <p style="color: #666; font-size: 14px; margin-top: -10px;">Update the stock for existing sizes below.</p>
                
                <!-- EXISTING SIZES LOOP -->
                <?php if ($sizes_result->num_rows > 0): ?>
                    <div class="size-grid">
                        <?php while ($size_row = $sizes_result->fetch_assoc()): ?>
                            <div>
                                <label>Size: <?php echo htmlspecialchars($size_row['size']); ?></label>
                                <!-- URL encode the size in the name attribute so PHP handles sizes with spaces properly -->
                                <input type="number" name="sizes[<?php echo urlencode($size_row['size']); ?>]" value="<?php echo $size_row['stock']; ?>" min="0">
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #d98607;"><em>This product currently has no specific sizes assigned.</em></p>
                <?php endif; ?>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

                <!-- ADD NEW SIZE -->
                <h3>Add a New Size</h3>
                <p style="color: #666; font-size: 14px; margin-top: -10px;">Leave blank if you don't need to add one.</p>
                <div class="size-grid" style="background: #e8f4fd;">
                    <div>
                        <label>New Size (e.g., XL, 10, 10.5)</label>
                        <input type="text" name="new_size" placeholder="Enter size name">
                    </div>
                    <div>
                        <label>Initial Stock</label>
                        <input type="number" name="new_size_stock" value="0" min="0">
                    </div>
                </div>

                <button type="submit" name="update_inventory" class="btn-submit">Save All Inventory Changes</button>
            </form>
        </div>
    </div>
</body>
</html>