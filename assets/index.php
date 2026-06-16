<?php
// assets/index.php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/Category.php';

requireLogin();

// Collect filters from GET
$filters = [
    'search'      => trim($_GET['search'] ?? ''),
    'status'      => $_GET['status'] ?? '',
    'category_id' => $_GET['category_id'] ?? '',
];

$assets     = Asset::getAll($filters);
$categories = Category::getAll();

$pageTitle = 'Assets';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold">Assets</h1>
        <p class="text-muted small mb-0">Manage and track all organization equipment</p>
    </div>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/assets/add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Asset
        </a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/flash.php'; ?>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text"
                           name="search"
                           class="form-control border-start-0 ps-0"
                           placeholder="Name, tag, serial, location…"
                           value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php foreach (ASSET_STATUSES as $s): ?>
                        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="<?= BASE_URL ?>/assets/index.php" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Results count -->
<p class="text-muted small mb-2">
    Showing <strong><?= count($assets) ?></strong> asset<?= count($assets) !== 1 ? 's' : '' ?>
    <?php if ($filters['search'] || $filters['status'] || $filters['category_id']): ?>
        — <a href="<?= BASE_URL ?>/assets/index.php" class="text-decoration-none">Clear filters</a>
    <?php endif; ?>
</p>

<!-- Asset Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Asset Tag</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assets)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No assets found.
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <a href="<?= BASE_URL ?>/assets/add.php">Add the first one.</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assets as $asset): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="font-monospace fw-semibold text-primary">
                                    <?= htmlspecialchars($asset['asset_tag']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/assets/view.php?id=<?= $asset['id'] ?>"
                                   class="text-decoration-none fw-semibold text-dark">
                                    <?= htmlspecialchars($asset['name']) ?>
                                </a>
                                <?php if ($asset['serial_number']): ?>
                                    <div class="text-muted small"><?= htmlspecialchars($asset['serial_number']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                                    <?= htmlspecialchars($asset['category_name'] ?? '—') ?>
                                </span>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($asset['location'] ?? '—') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($asset['assigned_to'] ?? '—') ?></td>
                            <td>
                                <?php
                                $statusMap = [
                                    'active'       => 'success',
                                    'under_repair' => 'warning',
                                    'disposed'     => 'secondary',
                                    'lost'         => 'danger',
                                ];
                                $badge = $statusMap[$asset['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $badge ?>-subtle text-<?= $badge ?>-emphasis rounded-pill px-3">
                                    <?= ucfirst(str_replace('_', ' ', $asset['status'])) ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?= BASE_URL ?>/assets/view.php?id=<?= $asset['id'] ?>"
                                   class="btn btn-sm btn-outline-secondary me-1" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <a href="<?= BASE_URL ?>/assets/edit.php?id=<?= $asset['id'] ?>"
                                       class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            onclick="confirmDelete(<?= $asset['id'] ?>, '<?= htmlspecialchars(addslashes($asset['name'])) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Delete Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0">
                    Are you sure you want to delete <strong id="deleteAssetName"></strong>?
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="<?= BASE_URL ?>/assets/delete.php">
                    <input type="hidden" name="id" id="deleteAssetId">
                    <button type="submit" class="btn btn-danger">Delete Asset</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteAssetId').value = id;
    document.getElementById('deleteAssetName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>