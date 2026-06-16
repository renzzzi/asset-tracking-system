<?php
// categories/delete.php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Category.php';

requireAdmin();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/categories/index.php");
    exit;
}

$id       = (int)($_POST['id'] ?? 0);
$category = Category::getById($id);

if (!$category) {
    $_SESSION['flash_error'] = 'Category not found.';
    header("Location: " . BASE_URL . "/categories/index.php");
    exit;
}

// Category::delete() returns false if assets are linked
$deleted = Category::delete($id);

if ($deleted) {
    $_SESSION['flash_success'] = 'Category "' . htmlspecialchars($category['name']) . '" has been deleted.';
} else {
    $_SESSION['flash_error'] = 'Cannot delete "' . htmlspecialchars($category['name']) . '". It still has assets linked to it. Reassign or remove those assets first.';
}

header("Location: " . BASE_URL . "/categories/index.php");
exit;