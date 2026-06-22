<?php
// categories/add.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Category.php';
require_once '../includes/flash.php';

requireAdmin();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim all inputs
    $old = [
        'name'        => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
    ];

    // Validate
    if ($old['name'] === '') {
        $errors['name'] = 'Category name is required.';
    }

    if (empty($errors)) {
        $newId = Category::create([
            'name'        => $old['name'],
            'description' => $old['description'] ?: null,
        ]);

        if ($newId) {
            setFlash('success', 'Category added successfully.');
            header('Location: ' . BASE_URL . '/categories/index.php');
            exit;
        } else {
            $errors['name'] = 'A category with this name already exists.';
        }
    }
}

$pageTitle  = 'Add Category';
$activePage = 'categories';
require_once '../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-outline-secondary btn-sm">
        &larr; Back
    </a>
    <h1 class="h3 fw-bold mb-0">Add Category</h1>
</div>

<?= showFlash() ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="" novalidate>

            <!-- Name -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name"
                       class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                <?php if (isset($errors['name'])): ?>
                    <small class="text-danger"><?= $errors['name'] ?></small>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Category</button>
                <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-outline-secondary">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>