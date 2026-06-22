<?php
// auth/session.php

// Ensure session is only started here
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if a user is currently logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Forces user to login, redirects if they are not authenticated
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/auth/login.php");
        exit;
    }
}

/**
 * Restricts access to admin role only
 * @return void
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        // Redirect with error as specified in Task 4
        $_SESSION['flash_error'] = "Access denied. Admins only.";
        header("Location: " . BASE_URL . "/dashboard/index.php");
        exit;
    }
}

/**
 * Returns current logged-in user data from session
 * @return array|null
 */
function currentUser() {
    if (isLoggedIn()) {
        return [
            'id'   => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }
    return null;
}