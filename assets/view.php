<?php
// assets/view.php
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

$id    = (int)($_GET['id'] ?? 0);
$asset = Asset::getById($id);

if (!$asset) {
    setFlash('danger', 'Asset not found.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

$activeTx = TransactionLog::getActiveByAsset($id);
$isAdmin  = currentUser() && currentUser()['role'] === 'admin';
$users    = $isAdmin ? User::getAll() : [];

// ── Capture modals outside the flex wrapper ──────────────────
ob_start();
?>
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
                    <div class="rounded p-3 mb-3" style="background:#fff3cd; border:1px solid #ffc107;">
                        <i class="bi bi-person-check me-1 text-warning"></i>
                        Borrowed by <strong><?= htmlspecialchars($activeTx['user_name'] ?? 'Unknown') ?></strong><br>
                        <span class="small">Due: <strong><?= htmlspecialchars($activeTx['due_date'] ?? '—') ?></strong></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Asset Condition <span class="text-danger">*</span>
                        </label>
                        <select name="condition" class="form-select" required id="view-return-condition">
                            <option value="good">✅ Good — ready for reuse</option>
                            <option value="damaged">⚠️ Damaged — needs repair</option>
                            <option value="needs_repair">🔧 Needs Repair — send to maintenance</option>
                        </select>
                        <div class="form-text" id="view-condition-hint">
                            Asset will be set back to <strong>Active</strong>.
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold">
                            Return Notes <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Additional remarks…"></textarea>
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

<script>
const conditionSelect = document.getElementById('view-return-condition');
const conditionHint   = document.getElementById('view-condition-hint');
if (conditionSelect && conditionHint) {
    conditionSelect.addEventListener('change', function () {
        const hints = {
            good:         'Asset will be set back to <strong>Active</strong>.',
            damaged:      'Asset will be set to <strong>Under Repair</strong>.',
            needs_repair: 'Asset will be set to <strong>Under Repair</strong>.',
        };
        conditionHint.innerHTML = hints[this.value] ?? '';
    });
}
</script>
<?php endif; ?>

<?php endif; // isAdmin ?>
<?php
$pageModals = ob_get_clean();
// ── End modal capture ────────────────────────────────────────

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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>