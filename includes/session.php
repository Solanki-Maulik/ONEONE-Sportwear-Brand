<?php
// Shared on every page: starts the session (used for login state and the
// coupon being applied at checkout - the cart itself now lives in the
// database, tied to the account, see includes/helpers.php cart_* functions).
if (session_status() === PHP_SESSION_NONE) {
    // Enforce secure cookie settings (HttpOnly and SameSite)
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Generate CSRF token if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper to get CSRF token
function csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

// Helper to verify CSRF token
function verify_csrf_token($token) {
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
?>
