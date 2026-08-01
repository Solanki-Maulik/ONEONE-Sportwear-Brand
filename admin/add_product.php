<?php
session_start();

// THE BOUNCER: Kick out non-admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../db.php';

$message = "";

// Fetch categories from the database for the dropdown menu
$categories = $conn->query("SELECT id, name FROM categories");

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $mrp = (isset($_POST['mrp']) && trim($_POST['mrp']) !== '') ? $_POST['mrp'] : null;
    $description = $_POST['description'];
    $gender = $_POST['gender'];

    // Collect whichever sizes got a checkbox + stock number filled in
    $size_stocks = [];
    if (!empty($_POST['size_stock']) && is_array($_POST['size_stock'])) {
        foreach ($_POST['size_stock'] as $size => $stock) {
            if (isset($_POST['sizes']) && in_array($size, $_POST['sizes'], true) && trim($stock) !== '') {
                $size_stocks[$size] = (int)$stock;
            }
        }
    }
    // If sizes were used, the product's overall stock is the sum of all sizes.
    // Otherwise fall back to the plain "no sizes" stock field.
    $stock_quantity = !empty($size_stocks) ? array_sum($size_stocks) : (int)($_POST['stock_quantity'] ?? 0);

    // 1. Insert the text data first (leave image_url blank for a second)
    $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, mrp, stock_quantity, gender, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, '')");
    
    // 'issddis' = Integer, String, String, Double, Double, Integer, String
    $stmt->bind_param("issddis", $category_id, $name, $description, $price, $mrp, $stock_quantity, $gender);

    if ($stmt->execute()) {
        $new_product_id = $conn->insert_id; // Get the ID of the product we just created

        // Save each checked size along with its own stock count
        if (!empty($size_stocks)) {
            $size_stmt = $conn->prepare("INSERT INTO product_sizes (product_id, size, stock) VALUES (?, ?, ?)");
            foreach ($size_stocks as $size => $stock) {
                $size_stmt->bind_param("isi", $new_product_id, $size, $stock);
                $size_stmt->execute();
            }
            $size_stmt->close();
        }

        $target_dir = "../uploads/";
        $upload_success = false;
        
        // 2. Loop through every image the user selected
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $total_files = count($_FILES['images']['name']);
            
            for ($i = 0; $i < $total_files; $i++) {
                // Create unique file name
                $file_name = time() . "_" . $i . "_" . basename($_FILES['images']['name'][$i]);
                $target_file = $target_dir . $file_name;
                $db_image_path = "uploads/" . $file_name; // Path saved to DB
                
                // Move file to uploads folder
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                    
                    // Save to product_images gallery table
                    $img_stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                    $img_stmt->bind_param("is", $new_product_id, $db_image_path);
                    $img_stmt->execute();
                    
                    // If this is the FIRST image, make it the main thumbnail
                    if ($i === 0) {
                        $conn->query("UPDATE products SET image_url = '$db_image_path' WHERE id = $new_product_id");
                    }
                    $upload_success = true;
                }
            }
        }

        if ($upload_success) {
            $message = "<h3 style='color: green;'>✅ Product and images uploaded successfully!</h3>";
        } else {
            $message = "<h3 style='color: orange;'>⚠️ Product added, but images failed to upload. Check if your 'uploads' folder exists!</h3>";
        }

    } else {
        $message = "<h3 style='color: red;'>❌ Database Error: " . $conn->error . "</h3>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; margin: 0; background: #f4f7f6; }
        .sidebar { width: 250px; background: #111; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { color: #fff; border-bottom: 2px solid #333; padding-bottom: 10px; font-size: 20px; text-transform: uppercase; }
        .sidebar a { color: #aaa; text-decoration: none; display: block; margin: 15px 0; padding: 5px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #f39c12; }
        .main-content { padding: 40px; flex-grow: 1; }
        form { max-width: 600px; display: flex; flex-direction: column; gap: 15px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        input, select, textarea { padding: 10px; font-size: 16px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 12px; background: #28a745; color: white; border: none; font-size: 16px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        button:hover { background: #218838; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Sportswear Elite</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="add_product.php" class="active">➕ Add Product</a>
        <a href="manage_products.php">📦 Manage Products</a>
        <a href="manage_orders.php">🛒 Manage Orders</a>
        <a href="coupons.php">🎟️ Coupons</a>
        <a href="../logout.php" style="color: #e74c3c; margin-top: 20px;">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Add New Sportswear</h1>
        
        <?php echo $message; ?>

        <!-- IMPORTANT: enctype MUST be multipart/form-data for image uploads -->
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            
            <label>Product Name (e.g., Virat Kohli One8 Shoes):</label>
            <input type="text" name="name" required>

            <label>Category:</label>
            <select name="category_id" required>
                <?php while($row = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                <?php endwhile; ?>
            </select>

            <label>Gender:</label>
            <select name="gender" required>
                <option value="men">Men</option>
                <option value="women">Women</option>
                <option value="unisex">Unisex</option>
            </select>

            <label>Price (₹) — selling price:</label>
            <input type="number" step="0.01" name="price" required>

            <label>MRP (₹) — original price, optional (shows as a struck-through price with a discount badge):</label>
            <input type="number" step="0.01" name="mrp">

            <label>Sizes & stock — check a size and set how many are in stock. Leave everything unchecked for a product that doesn't need sizes (e.g. a single-size accessory).</label>

            <div style="border:1px solid #eee; border-radius:6px; padding:14px;">
                <p style="margin:0 0 10px; font-size:13px; color:#888; font-weight:600; text-transform:uppercase;">Footwear sizes (UK)</p>
                <div style="display:flex; gap:14px; flex-wrap:wrap;">
                    <?php foreach (['6','7','8','9','10'] as $sz): ?>
                        <label style="display:flex; align-items:center; gap:6px; font-weight:normal;">
                            <input type="checkbox" name="sizes[]" value="<?php echo $sz; ?>" style="width:auto;"> UK <?php echo $sz; ?>
                            <input type="number" name="size_stock[<?php echo $sz; ?>]" placeholder="stock" min="0" style="width:70px; padding:6px;">
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="border:1px solid #eee; border-radius:6px; padding:14px;">
                <p style="margin:0 0 10px; font-size:13px; color:#888; font-weight:600; text-transform:uppercase;">Clothing sizes (tops / bottoms)</p>
                <div style="display:flex; gap:14px; flex-wrap:wrap;">
                    <?php foreach (['XS','S','M','L','XL','XXL'] as $sz): ?>
                        <label style="display:flex; align-items:center; gap:6px; font-weight:normal;">
                            <input type="checkbox" name="sizes[]" value="<?php echo $sz; ?>" style="width:auto;"> <?php echo $sz; ?>
                            <input type="number" name="size_stock[<?php echo $sz; ?>]" placeholder="stock" min="0" style="width:70px; padding:6px;">
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <label>Stock Quantity — only used if this product has no sizes checked above. <strong>A product left at 0 will show as "Out of stock" and can't be added to cart.</strong></label>
            <input type="number" name="stock_quantity" min="0" value="0" required>

            <label>Description:</label>
            <textarea name="description" rows="4" required></textarea>

            <label>Upload Multiple Images (Hold CTRL to select many):</label>
            <!-- Notice images[] and the multiple attribute! -->
            <input type="file" name="images[]" accept="image/png, image/jpeg, image/webp" multiple required>

            <button type="submit">Upload Product</button>
        </form>
    </div>

</body>
</html>