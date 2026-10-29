<?php

$conn = new mysqli("localhost", "prfvymog_oneone", "KspckmgxKAUMGQDZswfB", "prfvymog_oneone");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>


