<?php
// index.php (project root)
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/auth/session.php';

if (isLoggedIn()) {
    header("Location: " . BASE_URL . "/dashboard/index.php");
} else {
    header("Location: " . BASE_URL . "/auth/login.php");
}
exit;