<?php
// categories/edit.php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Category.php';

requireAdmin();

$id       = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$category = Category::getById($id);

if (!$category) {
    $_SESSION['flash_error'] = 'Category not found.';
    header("Location: " . BASE_URL . "/categories/index.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation
    if ($name === '') {
        $errors['name'] = 'Category name is required.';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Category name must not exceed 100 characters.';
    }

    if (empty($errors)) {
        $updated = Category::update($id, [
            'name'        => $name,
            'description' => $description ?: null,
        ]);

        if ($updated) {
            $_SESSION['flash_success'] = 'Category updated successfully.';
            header("Location: " . BASE_URL . "/categories/index.php");
            exit;
        } else {
            // Could be a duplicate name
            $errors['name'] = 'A category with this name already exists, or no changes were made.';
        }
    }

    // Keep POST values on error
    $category['name']        = $_POST['name'] ?? $category['name'];
    $category['description'] = $_POST['description'] ?? $category['description'];
}

$pageTitle = 'Edit Category';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h1 class="h3 mb-0 fw-bold">Edit Category</h1>
        <p class="text-muted small mb-0">Update category details</p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/flash.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Category Details</h6>
                <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                    ID #<?= $id ?>
                </span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="" novalidate>
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($category['name']) ?>"
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
                                  rows="4"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                        <div class="form-text">Optional.</div>
                    </div>

                    <div class="mb-4 p-3 bg-light rounded">
                        <p class="text-muted small mb-1">
                            <i class="bi bi-clock me-1"></i> Created
                        </p>
                        <p class="fw-semibold small mb-0">
                            <?= date('F j, Y \a\t g:i A', strtotime($category['created_at'])) ?>
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
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