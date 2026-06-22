<?php
// categories/index.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Category.php';
require_once '../includes/flash.php';

$categories = Category::getWithAssetCount();
<<<<<<< HEAD
$isAdmin    = currentUser() && currentUser()['role'] === 'admin';
=======
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87

$pageTitle  = 'Categories';
$activePage = 'categories';
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0">Categories</h1>
<<<<<<< HEAD
    <?php if ($isAdmin): ?>
        <a href="<?= BASE_URL ?>/categories/add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Category
        </a>
    <?php endif; ?>
=======
    <a href="<?= BASE_URL ?>/categories/add.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Category
    </a>
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
</div>

<?= showFlash() ?>

<?php if (empty($categories)): ?>
    <div class="alert alert-info">No categories found.</div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
<<<<<<< HEAD
            <table class="table align-middle mb-0">
=======
            <table class="table table-hover align-middle mb-0">
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Description</th>
                        <th class="text-center">Asset Count</th>
<<<<<<< HEAD
                        <?php if ($isAdmin): ?>
                            <th class="text-end pe-4">Actions</th>
                        <?php endif; ?>
=======
                        <th class="text-end pe-4">Actions</th>
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
<<<<<<< HEAD
                            <td class="ps-4 fw-semibold">
                                <?= htmlspecialchars($cat['name']) ?>
                            </td>
=======
                            <td class="ps-4 fw-semibold"><?= htmlspecialchars($cat['name']) ?></td>
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
                            <td class="text-muted">
                                <?= $cat['description'] ? htmlspecialchars($cat['description']) : '—' ?>
                            </td>
                            <td class="text-center">
<<<<<<< HEAD
                                <span class="badge bg-primary">
                                    <?= $cat['asset_count'] ?>
                                </span>
                            </td>
                            <?php if ($isAdmin): ?>
                                <td class="text-end pe-4">
                                    <a href="<?= BASE_URL ?>/categories/edit.php?id=<?= $cat['id'] ?>"
                                       class="btn btn-sm btn-secondary me-1">
                                        Edit
                                    </a>

                                    <?php if ($cat['asset_count'] > 0): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-danger disabled"
                                                disabled
                                                data-bs-toggle="tooltip"
                                                title="Cannot delete: assets are using this category.">
                                            Delete
                                        </button>
                                    <?php else: ?>
                                        <form method="POST"
                                              action="<?= BASE_URL ?>/categories/delete.php"
                                              class="d-inline">
                                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Delete this category?')">
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
=======
                                <span class="badge bg-secondary"><?= $cat['asset_count'] ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?= BASE_URL ?>/categories/edit.php?id=<?= $cat['id'] ?>"
                                   class="btn btn-sm btn-secondary me-1">Edit</a>

                                <?php if ($cat['asset_count'] > 0): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-secondary disabled"
                                            disabled
                                            data-bs-toggle="tooltip"
                                            title="Cannot delete: assets are using this category.">
                                        Delete
                                    </button>
                                <?php else: ?>
                                    <form method="POST" action="<?= BASE_URL ?>/categories/delete.php" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this category?')">
                                            Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script>
<<<<<<< HEAD
=======
// Enable Bootstrap tooltips
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.forEach(function (el) {
    new bootstrap.Tooltip(el);
});
</script>

<?php require_once '../includes/footer.php'; ?>