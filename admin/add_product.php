<?php
// 1. Start the session and check admin
require_once '../includes/session.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. Connect to database
require '../db.php';

$success_msg = "";
$error_msg = "";

// 3. Process the form when submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    // CSRF verification
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = "Security token mismatch. Please reload and try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $sport_type = trim($_POST['sport_type'] ?? 'Lifestyle');
        $price = (float)($_POST['price'] ?? 0);
        $mrp = (isset($_POST['mrp']) && $_POST['mrp'] !== '') ? (float)$_POST['mrp'] : NULL;
        $description = trim($_POST['description'] ?? '');

        // Allowed image extensions and MIME types to prevent Web Shell / RCE
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'];
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        // Handle Multiple Images
        $image_paths = [];
        $upload_error = false;

        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            foreach ($_FILES['images']['name'] as $key => $image_name) {
                if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $tmp_name = $_FILES['images']['tmp_name'][$key];
                $file_size = $_FILES['images']['size'][$key];
                $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

                // Validate extension
                if (!in_array($ext, $allowed_extensions, true)) {
                    $error_msg = "Invalid file type for '$image_name'. Only JPG, PNG, WEBP, and AVIF images are permitted.";
                    $upload_error = true;
                    break;
                }

                // Validate file size
                if ($file_size > $max_file_size) {
                    $error_msg = "File '$image_name' exceeds the maximum allowed size of 5MB.";
                    $upload_error = true;
                    break;
                }

                // Validate actual MIME type
                $mime = finfo_file($finfo, $tmp_name);
                if (!in_array($mime, $allowed_mimes, true)) {
                    $error_msg = "Invalid image content detected in '$image_name'.";
                    $upload_error = true;
                    break;
                }

                $new_name = bin2hex(random_bytes(16)) . '.' . $ext;
                $destination = $upload_dir . $new_name;

                if (move_uploaded_file($tmp_name, $destination)) {
                    $image_paths[] = 'uploads/' . $new_name; 
                }
            }

            finfo_close($finfo);
        }

        if (!$upload_error) {
            $image_url_string = implode(',', $image_paths);

            // Insert into database INCLUDING the new sport_type
            $stmt = $conn->prepare("INSERT INTO products (name, gender, category_id, sport_type, price, mrp, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisddss", $name, $gender, $category_id, $sport_type, $price, $mrp, $description, $image_url_string);
            
            if ($stmt->execute()) {
                $success_msg = "Product added successfully!";
            } else {
                $error_msg = "Error adding product. Please verify your inputs.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Admin</title>
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
        
        /* MAIN CONTENT */
        .main-content { padding: 40px; flex-grow: 1; box-sizing: border-box; max-width: 1000px; margin: 0 auto; }
        .main-content h1 { margin-top: 0; color: #1a1d21; font-size: 28px; margin-bottom: 5px; }
        .subtitle { color: #6c757d; font-size: 15px; margin-top: 0; margin-bottom: 30px; }
        
        /* FORM STYLES */
        .form-panel { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.03); border: 1px solid #e1e4e8; }
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
        .form-group label { font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 600; }
        
        input[type="text"], input[type="number"], select, textarea, input[type="file"] {
            padding: 12px 15px; border: 1px solid #ced4da; border-radius: 6px; font-size: 15px; transition: border-color 0.2s; width: 100%; box-sizing: border-box;
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #f39c12; }
        
        .btn-submit { background: #1a1d21; color: white; border: none; padding: 14px 24px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.2s; width: 100%; }
        .btn-submit:hover { background: #f39c12; }
        
        /* ALERTS */
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <h2>ElevenX HQ</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="add_product.php" class="active">➕ Add Product</a>
        <a href="manage_products.php">📦 Manage Products</a>
        <a href="manage_orders.php">🛒 Manage Orders</a>
        <a href="coupons.php">🎟️ Coupons</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <h1>Add New Product</h1>
        <p class="subtitle">Upload a new item to the store catalog.</p>
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="form-panel">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" required placeholder="Enter product name">
                </div>

                <!-- 3-TIER FILTERING SYSTEM -->
                <div class="form-row">
                    <div class="form-group">
                        <label>1. Gender</label>
                        <select name="gender" required>
                            <option value="">-- Select Gender --</option>
                            <option value="Men">Men</option>
                            <option value="Women">Women</option>
                            <option value="Unisex">Unisex</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>2. Product Type</label>
                        <select name="category_id" required>
                            <option value="">-- Select Type --</option>
                            <?php
                            // Exclude Accessories from the dropdown
                            $cat_query = $conn->query("SELECT * FROM categories WHERE name != 'Accessories' ORDER BY name ASC");
                            while($cat = $cat_query->fetch_assoc()) {
                                echo '<option value="'.$cat['id'].'">'.htmlspecialchars($cat['name']).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>3. Sport / Activity</label>
                        <select name="sport_type" required>
                            <option value="">-- Select Sport --</option>
                            <option value="Lifestyle">Lifestyle / Casual</option>
                            <option value="Cricket">Cricket</option>
                            <option value="Running">Running</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Selling Price (₹)</label>
                        <input type="number" name="price" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Original MRP (₹) - Optional</label>
                        <input type="number" name="mrp" placeholder="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="5" required placeholder="Enter product details..."></textarea>
                </div>

                <div class="form-group">
                    <label>Product Images (Select multiple)</label>
                    <input type="file" name="images[]" multiple required accept="image/*">
                </div>

                <button type="submit" name="add_product" class="btn-submit">Add Product</button>
            </form>
        </div>
    </div>

</body>
</html>