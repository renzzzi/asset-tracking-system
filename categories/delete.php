<?php
// categories/delete.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Category.php';
require_once '../includes/flash.php';

requireAdmin();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/categories/index.php');
    exit;
}

$id       = (int)($_POST['id'] ?? 0);
$category = Category::getById($id);

if (!$category) {
    setFlash('danger', 'Category not found.');
    header('Location: ' . BASE_URL . '/categories/index.php');
    exit;
}

// Category::delete() returns false if assets are using this category
$deleted = Category::delete($id);

if ($deleted) {
    setFlash('success', 'Category "' . htmlspecialchars($category['name']) . '" deleted successfully.');
} else {
    setFlash('danger', 'Cannot delete "' . htmlspecialchars($category['name']) . '": assets are using this category.');
}

header('Location: ' . BASE_URL . '/categories/index.php');
exit;