<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require '../db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $original_price = (float)$_POST['original_price']; // NEW: Strikethrough price
    $category_id = (int)$_POST['category_id'];
    $base_stock = (int)$_POST['stock_quantity'];
    
    // Calculate total stock if sizes are provided
    $total_stock = $base_stock;
    $has_sizes = false;
    
    if (isset($_POST['sizes']) && is_array($_POST['sizes'])) {
        $size_total = 0;
        foreach ($_POST['sizes'] as $size => $stock) {
            if ((int)$stock > 0) {
                $size_total += (int)$stock;
                $has_sizes = true;
            }
        }
        if ($has_sizes) {
            $total_stock = $size_total;
        }
    }

    // NEW: Handle Multiple Image Uploads
    $uploaded_images = [];
    $target_dir = "../uploads/";
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $file_count = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['images']['error'][$i] == 0) {
                $file_extension = pathinfo($_FILES["images"]["name"][$i], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '_' . $i . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["images"]["tmp_name"][$i], $target_file)) {
                    $uploaded_images[] = "uploads/" . $new_filename;
                }
            }
        }
    }
    
    // Join all image paths with a comma (e.g., "uploads/img1.jpg,uploads/img2.jpg")
    $image_urls_string = implode(',', $uploaded_images);

    // Insert Product into Database (Now includes original_price)
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, original_price, stock_quantity, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddiis", $name, $description, $price, $original_price, $total_stock, $category_id, $image_urls_string);
    
    if ($stmt->execute()) {
        $new_product_id = $stmt->insert_id;
        
        // Insert Sizes if they exist
        if ($has_sizes) {
            foreach ($_POST['sizes'] as $size => $stock) {
                $stock = (int)$stock;
                if ($stock > 0) {
                    $size_name = urldecode($size);
                    $size_stmt = $conn->prepare("INSERT INTO product_sizes (product_id, size, stock) VALUES (?, ?, ?)");
                    $size_stmt->bind_param("isi", $new_product_id, $size_name, $stock);
                    $size_stmt->execute();
                    $size_stmt->close();
                }
            }
        }
        $message = "<div class='alert alert-green'>Product added successfully!</div>";
    } else {
        $message = "<div class='alert alert-red'>Failed to add product. Did you add the original_price column to your database?</div>";
    }
    $stmt->close();
}

$categories = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - ONEONE Elite</title>
    <style>
        /* ADMIN PANEL BASE STYLES */
        body { font-family: 'Outfit', Tahoma, Geneva, Verdana, sans-serif; display: flex; margin: 0; background: #f0f2f5; color: #333; }
        
        .sidebar { width: 260px; background: #1a1d21; color: white; min-height: 100vh; padding: 25px; box-sizing: border-box;}
        .sidebar h2 { font-size: 20px; text-transform: uppercase; letter-spacing: 1px; color: #FF4D23; margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 15px;}
        .sidebar a { color: #cbd5e0; text-decoration: none; display: flex; align-items: center; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; transition: background 0.2s; font-size: 15px;}
        .sidebar a:hover, .sidebar a.active { background: #2d3238; color: white;}
        
        .main-content { padding: 40px; flex-grow: 1; max-width: 900px; }
        .main-content h1 { font-size: 28px; margin-top: 0; color: #1a1d21; margin-bottom: 25px; }
        
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.03); border: 1px solid #e1e4e8; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #1a1d21; font-size: 14px; }
        input[type="text"], input[type="number"], textarea, select, input[type="file"] { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: 'Outfit', sans-serif; font-size: 15px; transition: border 0.3s; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #FF4D23; }
        textarea { resize: vertical; min-height: 100px; }
        
        .size-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 15px; background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #edf2f7; margin-bottom: 20px;}
        .size-item { display: flex; flex-direction: column; gap: 5px; }
        .size-item label { font-size: 13px; color: #6c757d; margin: 0; }
        
        .btn-submit { background: #FF4D23; color: white; border: none; padding: 14px 24px; font-size: 16px; font-weight: 600; cursor: pointer; border-radius: 8px; width: 100%; margin-top: 10px; transition: all 0.3s; }
        .btn-submit:hover { background: #D93E14; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(255, 77, 35, 0.2); }
        
        .alert { padding: 12px 20px; border-radius: 6px; margin-bottom: 25px; font-size: 15px;}
        .alert-green { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;}
        .alert-red { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;}
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>ONEONE Elite</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="add_product.php" class="active" style="background: #2d3238; color: white;">➕ Add Product</a>
        <a href="manage_products.php">📦 Manage Products</a>
        <a href="manage_orders.php">🛒 Manage Orders</a>
        <a href="coupons.php">🎟️ Coupons</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h1>Add New Product</h1>
        
        <?php echo $message; ?>
        
        <div class="form-card">
            <!-- Notice enctype="multipart/form-data" is required for file uploads -->
            <form method="POST" action="" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" required placeholder="e.g., Elite Performance Tee">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required placeholder="Describe the product..."></textarea>
                </div>
                
                <!-- NEW: Dual Price Layout -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Selling Price (₹)</label>
                        <input type="number" step="0.01" name="price" required placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label>Original Price (MRP) (₹)</label>
                        <input type="number" step="0.01" name="original_price" placeholder="Optional strikethrough price">
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" required>
                            <option value="" disabled selected>Select a category</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- NEW: Multiple Images -->
                <div class="form-group">
                    <label>Product Images (Hold CTRL / CMD to select multiple)</label>
                    <input type="file" name="images[]" accept="image/*" multiple required>
                </div>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

                <div class="form-group">
                    <label style="font-size: 18px; color: #FF4D23;">Inventory Details</label>
                    <p style="font-size: 13px; color: #6c757d; margin-top: 0;">If this product has sizes, fill them out below. The Base Stock will update automatically.</p>
                </div>

                <div class="form-group" style="width: 50%;">
                    <label>Base Stock (Leave at 0 if using sizes below)</label>
                    <input type="number" name="stock_quantity" value="0" min="0">
                </div>

                <label>Clothing Sizes (Enter stock amount)</label>
                <div class="size-grid">
                    <?php 
                    $clothing_sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                    foreach ($clothing_sizes as $size): 
                    ?>
                    <div class="size-item">
                        <label><?php echo $size; ?></label>
                        <input type="number" name="sizes[<?php echo $size; ?>]" placeholder="0" min="0">
                    </div>
                    <?php endforeach; ?>
                </div>

                <label>Shoe / Boot Sizes (Enter stock amount)</label>
                <div class="size-grid">
                    <?php 
                    $shoe_sizes = ['6', '7', '8', '9', '10', '11', '12'];
                    foreach ($shoe_sizes as $size): 
                    ?>
                    <div class="size-item">
                        <label>Size <?php echo $size; ?></label>
                        <input type="number" name="sizes[<?php echo $size; ?>]" placeholder="0" min="0">
                    </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn-submit">Add Product to Store</button>
            </form>
        </div>
    </div>
</body>
</html>