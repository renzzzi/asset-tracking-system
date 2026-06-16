<?php
// includes/flash.php

/**
 * Saves a flash message into the session.
 * @param string $type    'success' | 'danger' | 'info'
 * @param string $message The message to display
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Reads, unsets, and returns a Bootstrap alert HTML string.
 * Returns empty string if no flash message exists.
 * @return string
 */
function showFlash() {
    if (empty($_SESSION['flash'])) {
        return '';
    }

    $type    = $_SESSION['flash']['type'];
    $message = $_SESSION['flash']['message'];

    unset($_SESSION['flash']);

    $alertClass = match($type) {
        'success' => 'alert-success',
        'danger'  => 'alert-danger',
        'info'    => 'alert-info',
        default   => 'alert-secondary',
    };

    return '
    <div class="alert ' . $alertClass . ' alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <span>' . htmlspecialchars($message) . '</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}