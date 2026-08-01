<?php
// Shared on every page: starts the session (used for login state and the
// coupon being applied at checkout - the cart itself now lives in the
// database, tied to the account, see includes/helpers.php cart_* functions).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
