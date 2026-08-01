<?php
// 1. Start the session
session_start();

// 2. THE BOUNCER: Kick out anyone who isn't logged in OR isn't an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 3. Connect to the database (using ../ because this file is inside the admin folder)
require '../db.php';

// 4. Fetch Quick Stats for the Dashboard
// Count regular users
$user_query = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'user'");
$user_count = $user_query->fetch_assoc()['count'];

// Count products
$product_query = $conn->query("SELECT COUNT(*) AS count FROM products");
$product_count = $product_query->fetch_assoc()['count'];

// Count orders
$order_query = $conn->query("SELECT COUNT(*) AS count FROM orders");
$order_count = $order_query->fetch_assoc()['count'];

// Calculate total revenue (sum of delivered orders)
$revenue_query = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE order_status = 'delivered'");
$revenue_result = $revenue_query->fetch_assoc()['total'];
$total_revenue = $revenue_result ? $revenue_result : 0; // Default to 0 if there are no orders yet
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Sportswear Elite</title>
    <style>
        /* Premium Admin Styling */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; background: #f4f7f6; color: #333; }
        
        /* Sidebar */
        .sidebar { width: 250px; background: #111; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { color: #fff; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .sidebar a { color: #aaa; text-decoration: none; display: block; padding: 12px 10px; margin-bottom: 5px; border-radius: 4px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #333; color: #f39c12; }
        .sidebar .logout { color: #e74c3c; margin-top: 30px; }
        .sidebar .logout:hover { background: #e74c3c; color: white; }
        
        /* Main Content */
        .main-content { padding: 40px; flex-grow: 1; box-sizing: border-box; }
        .main-content h1 { margin-top: 0; color: #111; }
        
        /* Stat Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px; }
        .stat-card { background: white; padding: 30px 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; border-top: 4px solid #111; }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card h2 { margin: 0; font-size: 32px; color: #111; }
        .stat-card.highlight { border-top-color: #f39c12; }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <h2>Sportswear Elite</h2>
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="manage_products.php">📦 Manage Products</a>
        <a href="manage_orders.php">🛒 Manage Orders</a>
        <a href="coupons.php">🎟️ Coupons</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main Dashboard Area -->
    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p style="color: #666;">Here is the current overview of your store.</p>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <h2><?php echo $user_count; ?></h2>
            </div>
            
            <div class="stat-card">
                <h3>Products in Stock</h3>
                <h2><?php echo $product_count; ?></h2>
            </div>
            
            <div class="stat-card">
                <h3>Total Orders</h3>
                <h2><?php echo $order_count; ?></h2>
            </div>
            
            <div class="stat-card highlight">
                <h3>Total Revenue</h3>
                <h2>₹<?php echo number_format($total_revenue, 2); ?></h2>
            </div>
        </div>
        
    </div>

</body>
</html>