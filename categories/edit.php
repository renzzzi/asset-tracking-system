<?php
// categories/edit.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Category.php';
require_once '../includes/flash.php';

requireAdmin();

$id       = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$category = Category::getById($id);

if (!$category) {
    setFlash('danger', 'Category not found.');
    header('Location: ' . BASE_URL . '/categories/index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim all inputs
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validate
    if ($name === '') {
        $errors['name'] = 'Category name is required.';
    }

    if (empty($errors)) {
        $updated = Category::update($id, [
            'name'        => $name,
            'description' => $description ?: null,
        ]);

        if ($updated) {
            setFlash('success', 'Category updated successfully.');
            header('Location: ' . BASE_URL . '/categories/index.php');
            exit;
        } else {
            $errors['name'] = 'A category with this name already exists, or no changes were made.';
        }
    }

    // Re-populate with submitted values on error
    $category['name']        = $name;
    $category['description'] = $description;
}

$pageTitle  = 'Edit Category';
$activePage = 'categories';
require_once '../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-outline-secondary btn-sm">
        &larr; Back
    </a>
    <h1 class="h3 fw-bold mb-0">Edit Category</h1>
</div>

<?= showFlash() ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="" novalidate>
            <input type="hidden" name="id" value="<?= $id ?>">

            <!-- Name -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name"
                       class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($category['name']) ?>">
                <?php if (isset($errors['name'])): ?>
                    <small class="text-danger"><?= $errors['name'] ?></small>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-outline-secondary">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>