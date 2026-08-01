    <nav>
        <a href="index.php" class="brand">STRIDE</a>
        <input type="checkbox" id="nav-toggle" class="nav-toggle">
        <label for="nav-toggle" class="nav-toggle-label">&#9776;</label>
        <div class="nav_con">
           <a href="index.php"><p>Home</p></a>
           <a href="man.php"><p>Men</p></a>
           <a href="woman.php"><p>Women</p></a>
           <a href="about.php"><p>About</p></a>
           <a href="cart.php"><p>Cart<?php if (isset($_SESSION['user_id']) && cart_count($conn, $_SESSION['user_id']) > 0) echo ' (' . cart_count($conn, $_SESSION['user_id']) . ')'; ?></p></a>
           <?php if (isset($_SESSION['user_id'])): ?>
                <a href="orders.php"><p>Orders</p></a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php"><p>Admin</p></a>
                <?php endif; ?>
                <a href="logout.php"><p>Logout</p></a>
           <?php else: ?>
                <a href="login.php"><p>Login</p></a>
           <?php endif; ?>
        </div>
    </nav>
    <?php if (isset($_GET['stock_error'])): ?>
        <div class="alert-box alert-error" style="max-width:1200px; margin:16px auto 0;">
            That item is currently out of stock, so it wasn't added to your cart.
        </div>
    <?php endif; ?>
