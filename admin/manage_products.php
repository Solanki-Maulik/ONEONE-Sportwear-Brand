<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require '../db.php';

$message = "";

// Handle Product Deletion
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $message = "<p style='color: green;'>Product deleted successfully.</p>";
    } catch (Exception $e) {
        $message = "<p style='color: red;'>Cannot delete product. It is already linked to customer orders. Update stock to 0 instead.</p>";
    }
}

// Handle removing all sizes from a product (switches it back to a plain,
// sizeless "Add to cart" button on the storefront)
if (isset($_GET['remove_sizes'])) {
    $rs_id = (int)$_GET['remove_sizes'];
    $stmt = $conn->prepare("SELECT COALESCE(SUM(stock), 0) AS total FROM product_sizes WHERE product_id = ?");
    $stmt->bind_param("i", $rs_id);
    $stmt->execute();
    $total_stock = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $del = $conn->prepare("DELETE FROM product_sizes WHERE product_id = ?");
    $del->bind_param("i", $rs_id);
    $del->execute();
    $del->close();

    // Carry the sizes' combined stock over to the plain stock_quantity field
    // so the product doesn't suddenly show as out of stock.
    $upd = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
    $upd->bind_param("ii", $total_stock, $rs_id);
    $upd->execute();
    $upd->close();

    $message = "<p style='color: green;'>Sizes removed - this product now shows a plain Add to Cart button.</p>";
}

$products = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; margin: 0; background: #f4f7f6; }
        .sidebar { width: 250px; background: #111; color: white; min-height: 100vh; padding: 20px; }
        .sidebar a { color: #aaa; text-decoration: none; display: block; margin-bottom: 15px; }
        .main-content { padding: 40px; flex-grow: 1; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #111; color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Sportswear Elite</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="manage_products.php" style="color: #f39c12;">📦 Manage Products</a>
        <a href="manage_orders.php">🛒 Manage Orders</a>
        <a href="coupons.php">🎟️ Coupons</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h1>Manage Products</h1>
        <?php echo $message; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Sizes</th>
                <th>Action</th>
            </tr>
            <?php while($row = $products->fetch_assoc()):
                $size_stmt = $conn->prepare("SELECT size, stock FROM product_sizes WHERE product_id = ? ORDER BY FIELD(size,'XS','S','M','L','XL','XXL'), size + 0");
                $size_stmt->bind_param("i", $row['id']);
                $size_stmt->execute();
                $size_rows = $size_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $size_stmt->close();
            ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><img src="../<?php echo htmlspecialchars($row['image_url']); ?>" width="50" height="50" style="object-fit:cover;"></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                <td>₹<?php echo $row['price']; ?></td>
                <td><?php echo $row['stock_quantity']; ?></td>
                <td>
                    <?php if (empty($size_rows)): ?>
                        <em style="color:#999;">No sizes</em>
                    <?php else: ?>
                        <?php foreach ($size_rows as $sr): ?>
                            <span style="display:inline-block; background:#eee; border-radius:4px; padding:2px 6px; margin:1px; font-size:12px;"><?php echo htmlspecialchars($sr['size']); ?>:<?php echo $sr['stock']; ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="manage_products.php?delete=<?php echo $row['id']; ?>" style="color: red; text-decoration: none;" onclick="return confirm('Delete this product?');">Delete</a>
                    <?php if (!empty($size_rows)): ?>
                        <br><a href="manage_products.php?remove_sizes=<?php echo $row['id']; ?>" style="color: #d98607; text-decoration: none; font-size: 13px;" onclick="return confirm('Remove all sizes from this product? It will switch to a plain Add to Cart button.');">Remove sizes</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>