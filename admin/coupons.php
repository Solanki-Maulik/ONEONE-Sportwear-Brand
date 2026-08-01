<?php
session_start();

// BOUNCER: Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../db.php';

$message = "";

// --- HANDLE COUPON CREATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'])); 
    $discount = (int)$_POST['discount_percent'];
    $expiry = $_POST['expiry_date'];
    
    $stmt = $conn->prepare("INSERT INTO coupons (code, discount_percent, expiry_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $code, $discount, $expiry);
    
    // Use try-catch to prevent Fatal Errors on duplicate entries
    try {
        $stmt->execute();
        $message = "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px;'>✅ Coupon <b>$code</b> added successfully!</div>";
    } catch (mysqli_sql_exception $e) {
        // Error code 1062 means "Duplicate Entry" in MySQL
        if ($e->getCode() == 1062) {
            $message = "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;'>❌ Error: The coupon code <b>$code</b> already exists! Try a different name.</div>";
        } else {
            $message = "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;'>❌ Database Error: " . $e->getMessage() . "</div>";
        }
    }
    $stmt->close();
}

// --- HANDLE COUPON DISABLE ---
if (isset($_GET['disable'])) {
    $id = (int)$_GET['disable'];
    $conn->query("UPDATE coupons SET is_active = FALSE WHERE id = $id");
    header("Location: coupons.php");
    exit();
}

// Fetch all coupons to display in the table
$coupons = $conn->query("SELECT * FROM coupons ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Coupons - Admin</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; margin: 0; background: #f4f7f6; color: #333; }
        
        /* Sidebar Styles */
        .sidebar { width: 250px; background: #111; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { color: #fff; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; font-size: 20px; text-transform: uppercase; }
        .sidebar a { color: #aaa; text-decoration: none; display: block; padding: 12px 10px; margin-bottom: 5px; border-radius: 4px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #333; color: #f39c12; }
        .sidebar .logout { color: #e74c3c; margin-top: 30px; }
        .sidebar .logout:hover { background: #e74c3c; color: white; }
        
        /* Main Content Styles */
        .main-content { padding: 40px; flex-grow: 1; box-sizing: border-box; }
        .main-content h1 { margin-top: 0; color: #111; }
        
        /* Form Styles */
        .coupon-form { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-width: 500px; margin-bottom: 40px; }
        .coupon-form label { font-weight: bold; color: #555; }
        .coupon-form input { width: 100%; padding: 10px; margin: 8px 0 20px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        .coupon-form button { background: #28a745; color: white; border: none; padding: 12px 20px; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; width: 100%; }
        .coupon-form button:hover { background: #218838; }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #111; color: white; font-weight: normal; text-transform: uppercase; font-size: 14px; }
        tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <h2>Sportswear Elite</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="manage_products.php">📦 Manage Products</a>
        <a href="manage_orders.php">🛒 Manage Orders</a>
        <a href="coupons.php" class="active">🎟️ Coupons</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main Dashboard Area -->
    <div class="main-content">
        <h1>Create Promo Code</h1>
        
        <?php echo $message; ?>
        
        <form action="coupons.php" method="POST" class="coupon-form">
            <label>Code Name (e.g., BCA50):</label>
            <input type="text" name="code" placeholder="Enter code name" required>
            
            <label>Discount Percentage (%):</label>
            <input type="number" name="discount_percent" min="1" max="100" placeholder="e.g., 20" required>
            
            <label>Expiry Date:</label>
            <input type="date" name="expiry_date" required>
            
            <button type="submit">Generate Code</button>
        </form>

        <h2>Active Coupons</h2>
        <table>
            <tr>
                <th>Code</th>
                <th>Discount</th>
                <th>Expires On</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if ($coupons->num_rows > 0): ?>
                <?php while($row = $coupons->fetch_assoc()): ?>
                <tr>
                    <td><strong style="font-size: 18px; color: #111;"><?php echo htmlspecialchars($row['code']); ?></strong></td>
                    <td><span style="background: #f39c12; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold;"><?php echo $row['discount_percent']; ?>% OFF</span></td>
                    <td><?php echo date('M d, Y', strtotime($row['expiry_date'])); ?></td>
                    <td>
                        <?php if($row['is_active'] && strtotime($row['expiry_date']) >= strtotime(date('Y-m-d'))): ?>
                            <span style="color: #28a745; font-weight: bold;">Active</span>
                        <?php else: ?>
                            <span style="color: #dc3545; font-weight: bold;">Expired / Disabled</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['is_active']): ?>
                            <a href="coupons.php?disable=<?php echo $row['id']; ?>" style="color: #dc3545; text-decoration: none; font-weight: bold;" onclick="return confirm('Are you sure you want to disable this coupon?');">Disable</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #888;">No coupons created yet.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

</body>
</html>