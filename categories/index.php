<?php
// categories/index.php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Category.php';

requireLogin();

$categories = Category::getWithAssetCount();

$pageTitle = 'Categories';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold">Categories</h1>
        <p class="text-muted small mb-0">Organize assets by type or department</p>
    </div>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/categories/add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Category
        </a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/flash.php'; ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="card-body">
                <p class="text-muted small mb-1">Total Categories</p>
                <h3 class="fw-bold mb-0"><?= count($categories) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="card-body">
                <p class="text-muted small mb-1">Total Assets</p>
                <h3 class="fw-bold mb-0"><?= array_sum(array_column($categories, 'asset_count')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="card-body">
                <p class="text-muted small mb-1">Empty Categories</p>
                <h3 class="fw-bold mb-0">
                    <?= count(array_filter($categories, fn($c) => $c['asset_count'] == 0)) ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Category Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="text-center">Assets</th>
                    <th>Date Added</th>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <th class="text-end pe-4">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-folder2-open fs-2 d-block mb-2"></i>
                            No categories found.
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <a href="<?= BASE_URL ?>/categories/add.php">Add the first one.</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $index => $cat): ?>
                        <tr>
                            <td class="ps-4 text-muted small"><?= $index + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center"
                                         style="width:36px; height:36px; flex-shrink:0;">
                                        <i class="bi bi-tag-fill text-primary small"></i>
                                    </div>
                                    <span class="fw-semibold"><?= htmlspecialchars($cat['name']) ?></span>
                                </div>
                            </td>
                            <td class="text-muted">
                                <?= $cat['description']
                                    ? htmlspecialchars($cat['description'])
                                    : '<span class="text-muted fst-italic small">No description</span>' ?>
                            </td>
                            <td class="text-center">
                                <?php if ($cat['asset_count'] > 0): ?>
                                    <a href="<?= BASE_URL ?>/assets/index.php?category_id=<?= $cat['id'] ?>"
                                       class="badge bg-primary-subtle text-primary-emphasis rounded-pill text-decoration-none px-3">
                                        <?= $cat['asset_count'] ?> asset<?= $cat['asset_count'] !== 1 ? 's' : '' ?>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3">
                                        0 assets
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= date('M j, Y', strtotime($cat['created_at'])) ?>
                            </td>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <td class="text-end pe-4">
                                    <a href="<?= BASE_URL ?>/categories/edit.php?id=<?= $cat['id'] ?>"
                                       class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            <?= $cat['asset_count'] > 0 ? 'disabled' : '' ?>
                                            onclick="confirmDelete(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($categories) && $_SESSION['user_role'] === 'admin'): ?>
        <div class="card-footer bg-white border-top-0 py-3 px-4">
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Categories with linked assets cannot be deleted. Remove or reassign their assets first.
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0">
                    Are you sure you want to delete <strong id="deleteCategoryName"></strong>?
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="<?= BASE_URL ?>/categories/delete.php">
                    <input type="hidden" name="id" id="deleteCategoryId">
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteCategoryId').value = id;
    document.getElementById('deleteCategoryName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>