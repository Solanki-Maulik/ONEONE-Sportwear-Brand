<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require '../db.php';

$message = "";

// --- Handlers for GET Actions remain here ---

// Handle Product Deletion
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $message = "<div class='alert alert-green'>Product deleted successfully.</div>";
    } catch (Exception $e) {
        $message = "<div class='alert alert-red'>Cannot delete product. It is already linked to customer orders. Update stock to 0 instead.</div>";
    }
}

// Handle removing all sizes
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

    // Carry stock back to the main products table
    $upd = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
    $upd->bind_param("ii", $total_stock, $rs_id);
    $upd->execute();
    $upd->close();

    $message = "<div class='alert alert-green'>Sizes removed - product now shows a plain Add to Cart button.</div>";
}

$products = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products - Admin Sportswear</title>
    <style>
        /* ADMIN PANEL BASE STYLES */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; margin: 0; background: #f0f2f5; color: #333; }
        
        /* Sidebar Styling */
        .sidebar { width: 260px; background: #1a1d21; color: white; min-height: 100vh; padding: 25px; box-sizing: border-box;}
        .sidebar h2 { font-size: 20px; text-transform: uppercase; letter-spacing: 1px; color: #f39c12; margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 15px;}
        .sidebar a { color: #cbd5e0; text-decoration: none; display: flex; align-items: center; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; transition: background 0.2s; font-size: 15px;}
        .sidebar a:hover { background: #2d3238; color: white;}
        
        /* Main Content Styling */
        .main-content { padding: 40px; flex-grow: 1; max-width: 1400px; margin: 0 auto;}
        .main-content h1 { font-size: 28px; margin-top: 0; color: #1a1d21; }
        
        /* TABLE DESIGN IMPROVEMENTS */
        .table-container { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.03); border: 1px solid #e1e4e8;}
        table { width: 100%; border-collapse: collapse; margin-top: 10px;}
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid #edf2f7; vertical-align: middle;}
        th { background: #f8f9fa; color: #6c757d; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;}
        tr:hover { background-color: #fcfcfd;}
        
        /* ALERTS */
        .alert { padding: 12px 20px; border-radius: 6px; margin-bottom: 25px; font-size: 15px;}
        .alert-green { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;}
        .alert-red { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;}
        
        /* PRODUCT ELEMENTS */
        .prod-img { width: 55px; height: 55px; border-radius: 8px; object-fit: cover; border: 1px solid #ddd; background: #fff;}
        .prod-name { font-weight: 600; color: #1a1d21; font-size: 15px;}
        .prod-cat { font-size: 13px; background: #e2e8f0; color: #4a5568; padding: 3px 8px; border-radius: 4px;}
        .prod-price { font-weight: bold; color: #2ecc71; font-size: 16px;}
        
        /* STOCK BADGES */
        .stock-count { font-size: 15px; font-weight: bold; color: #1a1d21;}
        .size-badge { display: inline-block; background: #edf2f7; color: #2d3748; border-radius: 4px; padding: 4px 8px; margin: 2px; font-size: 12px; border: 1px solid #e1e4e8;}
        .out-of-stock { color: #e74c3c; font-weight: bold;}
        
        /* ACTION BUTTONS DESIGN */
        .btn-group { display: flex; flex-direction: column; gap: 8px; width: 140px; }
        .btn { text-decoration: none; border: none; padding: 8px 12px; cursor: pointer; border-radius: 6px; font-size: 13px; font-weight: 600; text-align: center; transition: all 0.2s; box-sizing: border-box; width: 100%;}
        
        .btn-edit { background-color: #2ecc71; color: white; border: 1px solid #27ae60;}
        .btn-edit:hover { background-color: #27ae60; box-shadow: 0 2px 4px rgba(46,204,113,0.3);}
        
        .btn-delete { background-color: #e74c3c; color: white; border: 1px solid #c0392b;}
        .btn-delete:hover { background-color: #c0392b; box-shadow: 0 2px 4px rgba(231,76,60,0.3);}
        
        .btn-sizes { background-color: #f39c12; color: white; border: 1px solid #d35400;}
        .btn-sizes:hover { background-color: #d35400; box-shadow: 0 2px 4px rgba(243,156,18,0.3);}
    
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>STRIDE Elite</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="manage_products.php" style="background: #2d3238; color: white;">📦 Manage Products</a>
        <a href="manage_orders.php">🛒 Manage Orders</a>
        <a href="coupons.php">🎟️ Coupons</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h1>Manage Product Inventory</h1>
        <?php echo $message; ?>
        
        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Product Details</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Base Stock</th> <!-- Form Removed, just a display -->
                    <th>Size breakdown & Stock</th>
                    <th>Actions</th>
                </tr>
                <?php while($row = $products->fetch_assoc()): 
                    $size_stmt = $conn->prepare("SELECT size, stock FROM product_sizes WHERE product_id = ? ORDER BY FIELD(size,'XS','S','M','L','XL','XXL'), size + 0");
                    $size_stmt->bind_param("i", $row['id']);
                    $size_stmt->execute();
                    $size_rows = $size_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $size_stmt->close();
                ?>
                <tr>
                    <td style="color: #888; font-size: 13px;">#<?php echo $row['id']; ?></td>
                    <td><img src="../<?php echo htmlspecialchars($row['image_url']); ?>" class="prod-img"></td>
                    <td class="prod-name"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><span class="prod-cat"><?php echo htmlspecialchars($row['category_name']); ?></span></td>
                    <td class="prod-price">₹<?php echo $row['price']; ?></td>
                    
                    <!-- Base Stock (No Form) -->
                    <td>
                        <?php if ($row['stock_quantity'] > 0): ?>
                            <span class="stock-count"><?php echo $row['stock_quantity']; ?></span>
                        <?php else: ?>
                            <span class="out-of-stock">Out</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if (empty($size_rows)): ?>
                            <em style="color:#999; font-size: 13px;">No sizes</em>
                        <?php else: ?>
                            <?php foreach ($size_rows as $sr): ?>
                                <span class="size-badge">
                                    <?php echo htmlspecialchars($sr['size']); ?>: 
                                    <strong style="color:<?php echo ($sr['stock'] > 0) ? '#111' : '#e74c3c'; ?>">
                                        <?php echo $sr['stock']; ?>
                                    </strong>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Styled Action Buttons -->
                    <td>
                        <div class="btn-group">
                            <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Edit Inventory</a>
                            
                            <a href="manage_products.php?delete=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this product? It will be removed from the store forever.');">Delete Product</a>
                            
                            <?php if (!empty($size_rows)): ?>
                                <a href="manage_products.php?remove_sizes=<?php echo $row['id']; ?>" class="btn btn-sizes" onclick="return confirm('WARNING: This will delete all sizes (S, M, L, etc.) for this product. Stock will be merged back to base stock.');">Remove Sizes</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>