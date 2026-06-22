<?php
// categories/index.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Category.php';
require_once '../includes/flash.php';

$categories = Category::getWithAssetCount();
$isAdmin    = currentUser() && currentUser()['role'] === 'admin';

$pageTitle  = 'Categories';
$activePage = 'categories';
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0">Categories</h1>
    <?php if ($isAdmin): ?>
        <a href="<?= BASE_URL ?>/categories/add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Category
        </a>
    <?php endif; ?>
</div>

<?= showFlash() ?>

<?php if (empty($categories)): ?>
    <div class="alert alert-info">No categories found.</div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Description</th>
                        <th class="text-center">Asset Count</th>
                        <?php if ($isAdmin): ?>
                            <th class="text-end pe-4">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="ps-4 fw-semibold">
                                <?= htmlspecialchars($cat['name']) ?>
                            </td>
                            <td class="text-muted">
                                <?= $cat['description'] ? htmlspecialchars($cat['description']) : '—' ?>
                            </td>
                            <td class="text-center">
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
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script>
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.forEach(function (el) {
    new bootstrap.Tooltip(el);
});
</script>

<?php require_once '../includes/footer.php'; ?>