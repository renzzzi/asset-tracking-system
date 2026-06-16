<?php
// categories/add.php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Category.php';

requireAdmin();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation
    if ($name === '') {
        $errors['name'] = 'Category name is required.';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Category name must not exceed 100 characters.';
    }

    if (empty($errors)) {
        $newId = Category::create([
            'name'        => $name,
            'description' => $description ?: null,
        ]);

        if ($newId) {
            $_SESSION['flash_success'] = 'Category "' . htmlspecialchars($name) . '" added successfully.';
            header("Location: " . BASE_URL . "/categories/index.php");
            exit;
        } else {
            // Likely a duplicate name (UNIQUE constraint)
            $errors['name'] = 'A category with this name already exists.';
        }
    }
}

$pageTitle = 'Add Category';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h1 class="h3 mb-0 fw-bold">Add Category</h1>
        <p class="text-muted small mb-0">Create a new asset category</p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/flash.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Category Details</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                               placeholder="e.g. Laptop, Furniture, Vehicle"
                               maxlength="100"
                               autofocus>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= $errors['name'] ?></div>
                        <?php endif; ?>
                        <div class="form-text">Must be unique. Max 100 characters.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Brief description of what this category covers…"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                        <div class="form-text">Optional.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Category
                        </button>
                        <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>