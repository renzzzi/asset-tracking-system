<?php
// assets/index.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Asset.php';
require_once '../models/Category.php';
require_once '../includes/flash.php';

// Build filters from GET
$filters = [
    'search'      => trim($_GET['search'] ?? ''),
    'category_id' => (int)($_GET['category_id'] ?? 0),
    'status'      => $_GET['status'] ?? '',
];

$assets     = Asset::getAll($filters);
$categories = Category::getAll();

$pageTitle  = 'Asset List';
$activePage = 'assets';
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0">Assets</h1>
    <a href="<?= BASE_URL ?>/assets/add.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Asset
    </a>
</div>

<?= showFlash() ?>

<!-- Filters -->
<form method="GET" action="" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text"
               name="search"
               class="form-control"
               placeholder="Search by name, tag, serial, location…"
               value="<?= htmlspecialchars($filters['search']) ?>">
    </div>
    <div class="col-md-3">
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

<!-- Count -->
<p class="text-muted small mb-2">Showing <strong><?= count($assets) ?></strong> asset<?= count($assets) !== 1 ? 's' : '' ?></p>

<!-- Table -->
<?php if (empty($assets)): ?>
    <div class="alert alert-info">No assets found.</div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Asset Tag</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $asset): ?>
                        <?php
                        $badgeClass = match($asset['status']) {
                            'active'       => 'bg-success',
                            'under_repair' => 'bg-warning text-dark',
                            'disposed'     => 'bg-danger',
                            'lost'         => 'bg-danger',
                            default        => 'bg-secondary',
                        };
                        ?>
                        <tr>
                            <td class="ps-4 font-monospace fw-semibold">
                                <?= htmlspecialchars($asset['asset_tag']) ?>
                            </td>
                            <td><?= htmlspecialchars($asset['name']) ?></td>
                            <td><?= htmlspecialchars($asset['category_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($asset['location'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= ucfirst(str_replace('_', ' ', $asset['status'])) ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?= BASE_URL ?>/assets/view.php?id=<?= $asset['id'] ?>"
                                   class="btn btn-sm btn-primary me-1">View</a>
                                <a href="<?= BASE_URL ?>/assets/edit.php?id=<?= $asset['id'] ?>"
                                   class="btn btn-sm btn-secondary me-1">Edit</a>
                                <form method="POST" action="<?= BASE_URL ?>/assets/delete.php"
                                      class="d-inline">
                                    <input type="hidden" name="id" value="<?= $asset['id'] ?>">
                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this asset?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>