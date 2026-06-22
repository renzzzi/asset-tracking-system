<?php
// users/delete.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/User.php';
require_once '../includes/flash.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/users/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

// Prevent self-deletion
if ($id === currentUser()['id']) {
    setFlash('danger', 'You cannot delete your own account.');
    header('Location: ' . BASE_URL . '/users/index.php');
    exit;
}

$user = User::getById($id);
if (!$user) {
    setFlash('danger', 'User not found.');
    header('Location: ' . BASE_URL . '/users/index.php');
    exit;
}

$ok = User::delete($id);
if ($ok) {
    setFlash('success', 'User "' . htmlspecialchars($user['full_name']) . '" deleted successfully.');
} else {
    setFlash('danger', 'Failed to delete user.');
}

header('Location: ' . BASE_URL . '/users/index.php');
exit;