<?php
// auth/logout.php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/constants.php';

// Destroy the session completely
session_destroy();

// Redirect back to login page
header("Location: " . BASE_URL . "/auth/login.php");
exit;