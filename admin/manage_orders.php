<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require '../db.php';

// Handle Status Update
$valid_statuses = ['placed', 'packed', 'shipped', 'delivered', 'cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id']) && isset($_POST['status'])) {
    $update_id = (int)$_POST['update_id'];
    $status = in_array($_POST['status'], $valid_statuses, true) ? $_POST['status'] : 'placed';
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $update_id);
    $stmt->execute();
    header("Location: manage_orders.php");
    exit();
}

$orders = $conn->query("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; margin: 0; background: #f4f7f6; }
        .sidebar { width: 250px; background: #111; color: white; min-height: 100vh; padding: 20px; }
        .sidebar a { color: #aaa; text-decoration: none; display: block; margin-bottom: 15px; }
        .main-content { padding: 40px; flex-grow: 1; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #111; color: white; }
        .status-pill { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; letter-spacing: 0.5px; }
        .status-pill.placed { background: #fff3cd; color: #92720c; }
        .status-pill.packed { background: #dbeafe; color: #1e4ed8; }
        .status-pill.shipped { background: #ede9fe; color: #6d28d9; }
        .status-pill.delivered { background: #e5f7e0; color: #1f8a2e; }
        .status-pill.cancelled { background: #fde2de; color: #c0392b; }
        .status-form { display: flex; gap: 6px; align-items: center; }
        .status-form select { padding: 7px 10px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; }
        .status-form button { padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; background: #111; color: white; }
        .status-form button:hover { background: #333; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Sportswear Elite</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="manage_products.php">📦 Manage Products</a>
        <a href="manage_orders.php" style="color: #f39c12;">🛒 Manage Orders</a>
        <a href="coupons.php">🎟️ Coupons</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h1>Customer Orders</h1>
        <table>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while($row = $orders->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?><br><small><?php echo htmlspecialchars($row['email']); ?></small>
                    <?php if (!empty($row['ship_address'])): ?>
                        <br><small style="color:#888;"><?php echo htmlspecialchars($row['ship_address'] . ', ' . $row['ship_city'] . ' ' . $row['ship_pincode']); ?><br>📞 <?php echo htmlspecialchars($row['ship_phone']); ?></small>
                    <?php endif; ?>
                </td>
                <td>₹<?php echo $row['total_amount']; ?></td>
                <td><?php echo htmlspecialchars($row['payment_detail'] ?? ucfirst($row['payment_method'])); ?></td>
                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                <td><span class="status-pill <?php echo htmlspecialchars($row['order_status']); ?>"><?php echo strtoupper($row['order_status']); ?></span></td>
                <td>
                    <form action="manage_orders.php" method="POST" class="status-form">
                        <input type="hidden" name="update_id" value="<?php echo $row['id']; ?>">
                        <select name="status">
                            <?php foreach ($valid_statuses as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $row['order_status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>