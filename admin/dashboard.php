<?php
require_once '../includes/session.php';

// 2. THE BOUNCER: Kick out anyone who isn't logged in OR isn't an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 3. Connect to the database
require '../db.php';

// 4. Fetch Quick Stats for the Dashboard
$user_query = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'user'");
$user_count = $user_query->fetch_assoc()['count'];

$product_query = $conn->query("SELECT COUNT(*) AS count FROM products");
$product_count = $product_query->fetch_assoc()['count'];

$order_query = $conn->query("SELECT COUNT(*) AS count FROM orders");
$order_count = $order_query->fetch_assoc()['count'];

$revenue_query = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE order_status = 'delivered'");
$revenue_result = $revenue_query->fetch_assoc()['total'];
$total_revenue = $revenue_result ? $revenue_result : 0; 

// 5. Fetch 5 most recent orders for the quick-view table
$recent_orders = $conn->query("SELECT id, total_amount, order_status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - STRIDE Elite</title>
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
        .main-content { padding: 40px; flex-grow: 1; box-sizing: border-box; max-width: 1400px; margin: 0 auto; }
        .main-content h1 { margin-top: 0; color: #1a1d21; font-size: 28px; margin-bottom: 5px; }
        .subtitle { color: #6c757d; font-size: 15px; margin-top: 0; margin-bottom: 30px; }
        
        /* STAT CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.03); border: 1px solid #e1e4e8; display: flex; flex-direction: column; position: relative; overflow: hidden;}
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: #1a1d21; }
        .stat-card.highlight::before { background: #2ecc71; }
        
        .stat-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .stat-title { color: #6c757d; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin: 0; }
        .stat-icon { font-size: 20px; opacity: 0.7; }
        .stat-value { margin: 0; font-size: 36px; color: #1a1d21; font-weight: 700; }
        
        /* RECENT ORDERS WIDGET */
        .widget-panel { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.03); border: 1px solid #e1e4e8; }
        .widget-panel h3 { margin-top: 0; margin-bottom: 20px; font-size: 18px; color: #1a1d21; border-bottom: 1px solid #edf2f7; padding-bottom: 15px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 10px; text-align: left; border-bottom: 1px solid #edf2f7; font-size: 14px; }
        th { color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        
        /* BADGES */
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .status-placed { background: #e1f5fe; color: #0288d1; }
        .status-packed { background: #fff3e0; color: #f57c00; }
        .status-shipped { background: #f3e5f5; color: #7b1fa2; }
        .status-delivered { background: #e8f5e9; color: #2e7d32; }
        .status-cancelled { background: #ffebee; color: #c62828; }
        
        .view-all { display: inline-block; margin-top: 15px; font-size: 14px; color: #f39c12; text-decoration: none; font-weight: 600; }
        .view-all:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <h2>STRIDE Elite</h2>
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="manage_products.php">📦 Manage Products</a>
        <a href="manage_orders.php">🛒 Manage Orders</a>
        <a href="coupons.php">🎟️ Coupons</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main Dashboard Area -->
    <div class="main-content">
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
        <p class="subtitle">Here is what is happening in your store today.</p>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card highlight">
                <div class="stat-header">
                    <h3 class="stat-title">Total Revenue</h3>
                    <span class="stat-icon">💰</span>
                </div>
                <h2 class="stat-value">₹<?php echo number_format($total_revenue, 2); ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">Total Orders</h3>
                    <span class="stat-icon">📦</span>
                </div>
                <h2 class="stat-value"><?php echo $order_count; ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">Products Live</h3>
                    <span class="stat-icon">🏷️</span>
                </div>
                <h2 class="stat-value"><?php echo $product_count; ?></h2>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">Registered Users</h3>
                    <span class="stat-icon">👥</span>
                </div>
                <h2 class="stat-value"><?php echo $user_count; ?></h2>
            </div>
        </div>
        
        <!-- Recent Orders Widget -->
        <div class="widget-panel">
            <h3>Recent Orders</h3>
            <?php if ($recent_orders->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                    </tr>
                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight: 600; color: #1a1d21;">#<?php echo $order['id']; ?></td>
                            <td style="color: #6c757d;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td style="font-weight: 600;">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <a href="manage_orders.php" class="view-all">View all orders →</a>
            <?php else: ?>
                <p style="color: #6c757d; font-size: 14px;">No orders have been placed yet.</p>
            <?php endif; ?>
        </div>
        
    </div>

</body>
</html>