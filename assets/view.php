<?php
// assets/view.php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/AuditLog.php';

requireLogin();

$id    = (int)($_GET['id'] ?? 0);
$asset = Asset::getById($id);

if (!$asset) {
    $_SESSION['flash_error'] = 'Asset not found.';
    header("Location: " . BASE_URL . "/assets/index.php");
    exit;
}

$logs = AuditLog::getByAsset($id);

$statusMap = [
    'active'       => ['badge' => 'success',   'label' => 'Active'],
    'under_repair' => ['badge' => 'warning',    'label' => 'Under Repair'],
    'disposed'     => ['badge' => 'secondary',  'label' => 'Disposed'],
    'lost'         => ['badge' => 'danger',     'label' => 'Lost'],
];
$statusInfo = $statusMap[$asset['status']] ?? ['badge' => 'secondary', 'label' => ucfirst($asset['status'])];

$actionMap = [
    'created'        => ['icon' => 'bi-plus-circle-fill',  'color' => 'text-success'],
    'updated'        => ['icon' => 'bi-pencil-fill',        'color' => 'text-primary'],
    'status_changed' => ['icon' => 'bi-arrow-left-right',   'color' => 'text-warning'],
    'deleted'        => ['icon' => 'bi-trash-fill',         'color' => 'text-danger'],
];

$pageTitle = htmlspecialchars($asset['name']);
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= BASE_URL ?>/assets/index.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 mb-0 fw-bold"><?= htmlspecialchars($asset['name']) ?></h1>
                <span class="badge bg-<?= $statusInfo['badge'] ?>-subtle text-<?= $statusInfo['badge'] ?>-emphasis rounded-pill px-3">
                    <?= $statusInfo['label'] ?>
                </span>
            </div>
            <p class="text-muted small mb-0 font-monospace"><?= htmlspecialchars($asset['asset_tag']) ?></p>
        </div>
    </div>

    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/assets/edit.php?id=<?= $id ?>" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Asset
            </a>
            <button type="button" class="btn btn-outline-danger"
                    onclick="confirmDelete(<?= $id ?>, '<?= htmlspecialchars(addslashes($asset['name'])) ?>')">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/flash.php'; ?>

<div class="row g-4">

    <!-- Left Column: Details -->
    <div class="col-lg-8">

        <!-- Basic Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Asset Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">Category</p>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                            <?= htmlspecialchars($asset['category_name'] ?? '—') ?>
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">Serial Number</p>
                        <p class="fw-semibold font-monospace mb-0"><?= htmlspecialchars($asset['serial_number'] ?? '—') ?></p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">Location</p>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($asset['location'] ?? '—') ?></p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">Assigned To</p>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($asset['assigned_to'] ?? '—') ?></p>
                    </div>

                    <?php if (!empty($asset['description'])): ?>
                        <div class="col-12">
                            <hr class="my-1">
                            <p class="text-muted small mb-1 mt-2">Description</p>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($asset['description'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($asset['notes'])): ?>
                        <div class="col-12">
                            <p class="text-muted small mb-1">Notes</p>
                            <p class="mb-0 fst-italic text-muted"><?= nl2br(htmlspecialchars($asset['notes'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Audit History -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Activity History</h6>
                <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                    <?= count($logs) ?> event<?= count($logs) !== 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="card-body p-4">
                <?php if (empty($logs)): ?>
                    <p class="text-muted text-center py-3 mb-0">No activity recorded yet.</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($logs as $i => $log):
                            $act = $actionMap[$log['action']] ?? ['icon' => 'bi-circle-fill', 'color' => 'text-secondary'];
                        ?>
                            <div class="d-flex gap-3 <?= $i < count($logs) - 1 ? 'mb-4' : '' ?>">
                                <div class="flex-shrink-0 mt-1">
                                    <i class="bi <?= $act['icon'] ?> <?= $act['color'] ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="fw-semibold">
                                                <?= ucfirst(str_replace('_', ' ', $log['action'])) ?>
                                            </span>
                                            <span class="text-muted"> by </span>
                                            <span class="fw-semibold"><?= htmlspecialchars($log['user_name'] ?? 'Unknown') ?></span>
                                        </div>
                                        <span class="text-muted small text-nowrap ms-3">
                                            <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?>
                                        </span>
                                    </div>
                                    <?php if ($log['notes']): ?>
                                        <p class="text-muted small mb-1 mt-1"><?= htmlspecialchars($log['notes']) ?></p>
                                    <?php endif; ?>

                                    <?php if ($log['old_value'] || $log['new_value']): ?>
                                        <div class="mt-2">
                                            <?php
                                            $old = $log['old_value'] ? json_decode($log['old_value'], true) : null;
                                            $new = $log['new_value'] ? json_decode($log['new_value'], true) : null;

                                            // Show diff only for status changes
                                            if ($log['action'] === 'status_changed' && $old && $new):
                                                $oldStatus = $old['status'] ?? '—';
                                                $newStatus = $new['status'] ?? '—';
                                            ?>
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis me-1">
                                                    <?= ucfirst(str_replace('_', ' ', $oldStatus)) ?>
                                                </span>
                                                <i class="bi bi-arrow-right text-muted small"></i>
                                                <span class="badge bg-primary-subtle text-primary-emphasis ms-1">
                                                    <?= ucfirst(str_replace('_', ' ', $newStatus)) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Right Column: Purchase Info -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Purchase Information</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <p class="text-muted small mb-1">Purchase Date</p>
                        <p class="fw-semibold mb-0">
                            <?= $asset['purchase_date']
                                ? date('F j, Y', strtotime($asset['purchase_date']))
                                : '—' ?>
                        </p>
                    </div>
                    <div class="col-12">
                        <p class="text-muted small mb-1">Purchase Cost</p>
                        <p class="fw-semibold mb-0 fs-5">
                            <?= $asset['purchase_cost'] !== null
                                ? '$' . number_format($asset['purchase_cost'], 2)
                                : '—' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Record Info</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <p class="text-muted small mb-1">Date Added</p>
                        <p class="fw-semibold mb-0">
                            <?= date('M j, Y g:i A', strtotime($asset['created_at'])) ?>
                        </p>
                    </div>
                    <div class="col-12">
                        <p class="text-muted small mb-1">Last Updated</p>
                        <p class="fw-semibold mb-0">
                            <?= date('M j, Y g:i A', strtotime($asset['updated_at'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
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
                <form method="POST" action="<?= BASE_URL ?>/assets/delete.php">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <button type="submit" class="btn btn-danger">Delete Asset</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteAssetName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>