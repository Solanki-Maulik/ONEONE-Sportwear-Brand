<?php
require 'includes/session.php';
require 'db.php';

$login_error = "";
$register_error = "";
$register_success = "";
$show_register = isset($_GET['register']);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: " . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
            exit();
        } else {
            $login_error = "Incorrect password.";
        }
    } else {
        $login_error = "No account found with that email.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'register') {
    $show_register = true;
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    try {
        $stmt->execute();
        $register_success = "Account created! You can log in now.";
        $show_register = false;
    } catch (mysqli_sql_exception $e) {
        $register_error = ($e->getCode() == 1062) ? "An account with that email already exists." : "Database error.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — STRIDE</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="auth-wrap">
        <input type="checkbox" id="flip-toggle" class="flip-toggle" <?php echo $show_register ? 'checked' : ''; ?>>

        <div class="flip-card">
            <!-- FRONT: Login -->
            <div class="flip-face flip-front">
                <a href="index.php" class="mini-brand">STRIDE</a>
                <h3>Login</h3>

                <?php if ($login_error): ?>
                    <div class="alert-box alert-error"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                <?php if ($register_success): ?>
                    <div class="alert-box alert-success"><?php echo htmlspecialchars($register_success); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input class="anm" type="email" name="email" placeholder="Enter email" required>
                    <input class="anm" type="password" name="password" placeholder="Enter password" required>
                    <button class="anm" type="submit">Login</button>
                </form>
                <label for="flip-toggle">New here? Create an account</label>
            </div>

            <!-- BACK: Register -->
            <div class="flip-face flip-back">
                <a href="index.php" class="mini-brand">STRIDE</a>
                <h3>Create Account</h3>

                <?php if ($register_error): ?>
                    <div class="alert-box alert-error"><?php echo htmlspecialchars($register_error); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    <input class="anm" type="text" name="username" placeholder="Full name" required>
                    <input class="anm" type="email" name="email" placeholder="Enter email" required>
                    <input class="anm" type="password" name="password" placeholder="Enter password" required>
                    <button class="anm" type="submit">Sign Up</button>
                </form>
                <label for="flip-toggle">Already have an account? Login</label>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        if (window.gsap) {
            function playAuthIntro(faceSelector) {
                gsap.set(faceSelector + ' .anm', { opacity: 0, scale: 0 });
                var tl = gsap.timeline();
                tl.from(faceSelector + ' form', { scale: 0, duration: 0.8 });
                tl.to(faceSelector + ' .anm', { opacity: 1, scale: 1, duration: 0.5, stagger: 0.15 });
            }
            var toggle = document.getElementById('flip-toggle');
            playAuthIntro(toggle.checked ? '.flip-back' : '.flip-front');
            toggle.addEventListener('change', function () {
                playAuthIntro(this.checked ? '.flip-back' : '.flip-front');
            });
        }
    </script>
</body>
</html>
