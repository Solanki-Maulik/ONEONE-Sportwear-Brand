<?php
require 'includes/session.php';
require 'db.php';
ini_set('display_errors', 0);
error_reporting(E_ALL);

$login_error = "";
$register_error = "";
$register_success = "";
$show_register = isset($_GET['register']);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Prevent Session Fixation attack
                session_regenerate_id(true);

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
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'register') {
    $show_register = true;
    $username = trim($_POST['username'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($username) || strlen($username) < 2) {
        $register_error = "Please enter a valid name (at least 2 characters).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $register_error = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        try {
            $stmt->execute();
            $register_success = "Account created! You can log in now.";
            $show_register = false;
        } catch (mysqli_sql_exception $e) {
            $register_error = ($e->getCode() == 1062) ? "An account with that email already exists." : "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Access — ElevenX</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="auth-wrap">
        <input type="checkbox" id="flip-toggle" class="flip-toggle" <?php echo $show_register ? 'checked' : ''; ?>>

        <div class="flip-card">
            <!-- FRONT: Login -->
            <div class="flip-face flip-front">
                <div class="header-row">
                    <a href="index.php" class="back-link">← Store</a>
                    <div class="mini-brand"><span class="brand-word">ELEVENX</span><span class="brand-badge">ATHLETICS</span></div>
                </div>
                <h3>Welcome Back</h3>

                <?php if ($login_error): ?>
                    <div class="alert-box alert-error"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                <?php if ($register_success): ?>
                    <div class="alert-box alert-success"><?php echo htmlspecialchars($register_success); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input class="anm" type="email" name="email" placeholder="Email address" required>
                    <input class="anm" type="password" name="password" placeholder="Password" required>
                    <button class="anm" type="submit">Log in</button>
                </form>
                <label for="flip-toggle" class="toggle-link">New here? <span>Create an account</span></label>
            </div>

            <!-- BACK: Register -->
            <div class="flip-face flip-back">
                <div class="header-row">
                    <a href="index.php" class="back-link">← Store</a>
                    <div class="mini-brand"><span class="brand-word">ELEVENX</span><span class="brand-badge">ATHLETICS</span></div>
                </div>
                <h3>Create Account</h3>

                <?php if ($register_error): ?>
                    <div class="alert-box alert-error"><?php echo htmlspecialchars($register_error); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    <input class="anm" type="text" name="username" placeholder="Full name" required>
                    <input class="anm" type="email" name="email" placeholder="Email address" required>
                    <input class="anm" type="password" name="password" placeholder="Password" required>
                    <button class="anm" type="submit">Sign Up</button>
                </form>
                <label for="flip-toggle" class="toggle-link">Already have an account? <span>Log in</span></label>
            </div>
        </div>
    </div>

    <!-- GSAP for Lightning Fast, Smooth Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        if (window.gsap) {
            function playAuthIntro(faceSelector) {
                // Reset elements for animation
                gsap.set(faceSelector + ' .anm', { opacity: 0, y: 15 });

                gsap.to(faceSelector + ' .anm', { 
                    opacity: 1, 
                    y: 0, 
                    duration: 0.35, 
                    stagger: 0.08, 
                    ease: "power2.out",
                    delay: 0.1
                });
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