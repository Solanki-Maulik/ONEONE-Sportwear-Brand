<?php
require_once __DIR__ . '/helpers.php';
?>
    <nav>
        <a href="index.php" class="brand"><span class="brand-word">ELEVENX</span><span class="brand-badge">ATHLETICS</span></a>
        <input type="checkbox" id="nav-toggle" class="nav-toggle">
        <label for="nav-toggle" class="nav-toggle-label">&#9776;</label>
        <div class="nav_con">
           <a href="index.php"><p>Home</p></a>
           <a href="man.php"><p>Men</p></a>
           <a href="woman.php"><p>Women</p></a>
           <a href="about.php"><p>About</p></a>
           <a href="cart.php"><p>Cart<?php if (isset($_SESSION['user_id']) && cart_count($conn, $_SESSION['user_id']) > 0): ?><span class="cart-badge-pill"><?php echo cart_count($conn, $_SESSION['user_id']); ?></span><?php endif; ?></p></a>
           <?php if (isset($_SESSION['user_id'])): ?>
                <a href="orders.php"><p>Orders</p></a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php"><p>Admin</p></a>
                <?php endif; ?> 
                <a href="logout.php"><p>Logout</p></a>
           <?php else: ?>
                <a href="login.php"><p>Login</p></a>
           <?php endif; ?>
           <button type="button" id="themeToggleBtn" class="theme-toggle-btn" onclick="toggleTheme()" aria-label="Toggle Theme">
               <span id="themeToggleIcon">🌙</span> <span id="themeToggleText">Dark</span>
           </button>
        </div>
    </nav>
    <script>
        (function() {
            var savedTheme = localStorage.getItem('elevenx-theme');
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        })();
        function toggleTheme() {
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            if (isDark) {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('elevenx-theme', 'light');
                updateThemeButton('light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('elevenx-theme', 'dark');
                updateThemeButton('dark');
            }
        }
        function updateThemeButton(theme) {
            var icon = document.getElementById('themeToggleIcon');
            var text = document.getElementById('themeToggleText');
            if (icon && text) {
                if (theme === 'dark') {
                    icon.textContent = '☀️';
                    text.textContent = 'Light';
                } else {
                    icon.textContent = '🌙';
                    text.textContent = 'Dark';
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            updateThemeButton(current);
        });
    </script>
    <?php if (isset($_GET['stock_error'])): ?>
        <div class="alert-box alert-error" style="max-width:1200px; margin:16px auto 0;">
            That item is currently out of stock, so it wasn't added to your cart.
        </div>
    <?php endif; ?>
