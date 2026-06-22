<?php
// assets/view.php
<<<<<<< HEAD
require_once __DIR__ . '/../auth/session.php';
requireLogin();
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/TransactionLog.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../includes/flash.php';
=======
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Asset.php';
require_once '../models/AuditLog.php';
require_once '../includes/flash.php';
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87

$id    = (int)($_GET['id'] ?? 0);
$asset = Asset::getById($id);

if (!$asset) {
    setFlash('danger', 'Asset not found.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

<<<<<<< HEAD
$activeTx = TransactionLog::getActiveByAsset($id);
$isAdmin  = currentUser() && currentUser()['role'] === 'admin';
$users    = $isAdmin ? User::getAll() : [];

$pageTitle  = 'Asset Details';
$activePage = 'assets';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/assets/index.php" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
    <div>
        <h1 class="h3 fw-bold mb-0"><?= htmlspecialchars($asset['name']) ?></h1>
        <div class="text-muted small font-monospace">Tag: <?= htmlspecialchars($asset['asset_tag']) ?></div>
    </div>
</div>

<?= showFlash() ?>

<div class="row g-4">

    <!-- Asset Info Card -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Asset Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Category</div>
                        <div><?= htmlspecialchars($asset['category_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Location</div>
                        <div><?= htmlspecialchars($asset['location'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Status</div>
                        <div>
                            <?php
                            $badgeClass = match($asset['status']) {
                                'active'       => 'bg-success',
                                'under_repair' => 'bg-warning text-dark',
                                'disposed'     => 'bg-danger',
                                'lost'         => 'bg-danger',
                                default        => 'bg-secondary',
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>">
                                <?= ucfirst(str_replace('_', ' ', $asset['status'])) ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Serial Number</div>
                        <div><?= htmlspecialchars($asset['serial_number'] ?? '—') ?></div>
                    </div>
                </div>

                <?php if (!empty($asset['purchase_date']) || $asset['purchase_cost'] !== null): ?>
                    <hr class="my-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Purchase Date</div>
                            <div><?= htmlspecialchars($asset['purchase_date'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Purchase Cost</div>
                            <div><?= htmlspecialchars((string)($asset['purchase_cost'] ?? '—')) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($asset['notes'])): ?>
                    <hr class="my-4">
                    <div class="text-muted small mb-1">Notes</div>
                    <div><?= nl2br(htmlspecialchars($asset['notes'])) ?></div>
                <?php endif; ?>

                <div class="mt-4 d-flex gap-2">
                    <?php if ($isAdmin): ?>
                        <a href="<?= BASE_URL ?>/assets/edit.php?id=<?= $id ?>"
                           class="btn btn-secondary">Edit</a>

                        <?php if (!$activeTx): ?>
                            <button type="button" class="btn btn-primary"
                                    data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                <i class="bi bi-box-arrow-in-left me-1"></i> Checkout
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-success"
                                    data-bs-toggle="modal" data-bs-target="#returnModal">
                                <i class="bi bi-check-lg me-1"></i> Return Asset
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Borrow Status Card -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Borrow Status</h5>
                <span class="badge <?= $activeTx ? 'bg-danger' : 'bg-success' ?>">
                    <?= $activeTx ? 'Checked Out' : 'Available' ?>
                </span>
            </div>
            <div class="card-body">
                <?php if ($activeTx): ?>
                    <div class="mb-3">
                        <div class="text-muted small">Borrower</div>
                        <div class="fw-semibold"><?= htmlspecialchars($activeTx['user_name'] ?? 'Unknown') ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Checked out by</div>
                        <div class="fw-semibold"><?= htmlspecialchars($activeTx['checked_out_by_name'] ?? 'Unknown') ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Due Date</div>
                        <div class="fw-semibold"><?= htmlspecialchars($activeTx['due_date'] ?? '—') ?></div>
                    </div>
                    <?php if (!empty($activeTx['notes'])): ?>
                        <div class="text-muted small mb-1">Notes</div>
                        <div class="mb-3"><?= nl2br(htmlspecialchars($activeTx['notes'])) ?></div>
                    <?php endif; ?>

                    <?php if ($isAdmin): ?>
                        <button type="button" class="btn btn-success w-100 mt-2"
                                data-bs-toggle="modal" data-bs-target="#returnModal">
                            <i class="bi bi-check-lg me-1"></i> Return Asset
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mb-3">This asset is currently available. No active checkout.</p>
                    <?php if ($isAdmin): ?>
                        <button type="button" class="btn btn-primary w-100"
                                data-bs-toggle="modal" data-bs-target="#checkoutModal">
                            <i class="bi bi-box-arrow-in-left me-1"></i> Checkout / Borrow
                        </button>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Only admins can checkout assets.</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- end .row -->


<?php if ($isAdmin): ?>

<!-- CHECKOUT MODAL -->
<div class="modal fade" id="checkoutModal" tabindex="-1"
     aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalLabel">
                    <i class="bi bi-box-arrow-in-left me-2"></i>Checkout Asset
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/transactions/checkout.php">
                <input type="hidden" name="asset_id"       value="<?= $id ?>">
                <input type="hidden" name="checked_out_by" value="<?= (int)currentUser()['id'] ?>">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Asset</label>
                        <input type="text" class="form-control bg-light" readonly
                               value="<?= htmlspecialchars($asset['name']) ?> (<?= htmlspecialchars($asset['asset_tag']) ?>)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Borrower <span class="text-danger">*</span>
                        </label>
                        <select name="borrower_user_id" class="form-select" required>
                            <option value="">Select borrower…</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= (int)$u['id'] ?>">
                                    <?= htmlspecialchars($u['full_name']) ?>
                                    (<?= htmlspecialchars($u['role']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Due Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="due_date" class="form-control"
                               required min="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold">
                            Notes <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Purpose, remarks…"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-left me-1"></i> Confirm Checkout
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- RETURN MODAL -->
<?php if ($activeTx): ?>
<div class="modal fade" id="returnModal" tabindex="-1"
     aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title" id="returnModalLabel">
                    <i class="bi bi-check-lg me-2"></i>Return Asset
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/transactions/return.php">
                <input type="hidden" name="transaction_id" value="<?= (int)$activeTx['id'] ?>">
                <input type="hidden" name="asset_id"       value="<?= $id ?>">

                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <strong><?= htmlspecialchars($asset['name']) ?></strong>
                        is borrowed by
                        <strong><?= htmlspecialchars($activeTx['user_name'] ?? 'Unknown') ?></strong>
                        — due <strong><?= htmlspecialchars($activeTx['due_date'] ?? '—') ?></strong>.
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold">
                            Return Notes <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Condition on return, damage notes…"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Confirm Return
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; // isAdmin ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
=======
$logs = AuditLog::getByAsset($id);

$pageTitle  = htmlspecialchars($asset['name']);
$activePage = 'assets';
require_once '../includes/header.php';
?>

<?= showFlash() ?>

<!-- Top Buttons -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0"><?= htmlspecialchars($asset['name']) ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/assets/index.php" class="btn btn-outline-secondary">
            &larr; Back
        </a>
        <?php if (currentUser()['role'] === 'admin'): ?>
            <a href="<?= BASE_URL ?>/assets/edit.php?id=<?= $id ?>" class="btn btn-primary">
                Edit Asset
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Asset Details -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-semibold">Asset Details</h6>
    </div>
    <div class="card-body p-4">
        <?php
        $statusBadge = match($asset['status']) {
            'active'       => 'bg-success',
            'under_repair' => 'bg-warning text-dark',
            'disposed'     => 'bg-danger',
            'lost'         => 'bg-danger',
            default        => 'bg-secondary',
        };

        $fields = [
            'Asset Tag'     => '<span class="font-monospace">' . htmlspecialchars($asset['asset_tag']) . '</span>',
            'Name'          => htmlspecialchars($asset['name']),
            'Category'      => htmlspecialchars($asset['category_name'] ?? '—'),
            'Serial Number' => htmlspecialchars($asset['serial_number'] ?? '—'),
            'Location'      => htmlspecialchars($asset['location'] ?? '—'),
            'Assigned To'   => htmlspecialchars($asset['assigned_to'] ?? '—'),
            'Status'        => '<span class="badge ' . $statusBadge . '">' . ucfirst(str_replace('_', ' ', $asset['status'])) . '</span>',
            'Purchase Date' => $asset['purchase_date'] ? date('F j, Y', strtotime($asset['purchase_date'])) : '—',
            'Purchase Cost' => $asset['purchase_cost'] !== null ? '₱' . number_format((float)$asset['purchase_cost'], 2) : '—',
            'Notes'         => $asset['notes'] ? nl2br(htmlspecialchars($asset['notes'])) : '—',
            'Date Added'    => date('M j, Y g:i A', strtotime($asset['created_at'])),
            'Last Updated'  => date('M j, Y g:i A', strtotime($asset['updated_at'])),
        ];
        ?>
        <div class="row">
            <?php foreach ($fields as $label => $value): ?>
                <div class="col-md-6 mb-3">
                    <div class="row">
                        <div class="col-5 text-muted fw-semibold small"><?= $label ?></div>
                        <div class="col-7"><?= $value ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Audit History -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Audit History</h6>
        <span class="badge bg-secondary"><?= count($logs) ?> record<?= count($logs) !== 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($logs)): ?>
            <div class="p-4 text-muted">No audit records found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date / Time</th>
                            <th>Action</th>
                            <th>Changed By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log):
                            $actionBadge = match($log['action']) {
                                'created'        => 'bg-success',
                                'updated'        => 'bg-primary',
                                'status_changed' => 'bg-warning text-dark',
                                'deleted'        => 'bg-danger',
                                default          => 'bg-secondary',
                            };
                        ?>
                            <tr>
                                <td class="ps-4 text-muted small">
                                    <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $actionBadge ?>">
                                        <?= ucfirst(str_replace('_', ' ', $log['action'])) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['user_name'] ?? '—') ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($log['notes'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
