<?php
// assets/index.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Asset.php';
require_once '../models/Category.php';
<<<<<<< HEAD
require_once '../models/TransactionLog.php';
require_once '../models/User.php';
=======
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
require_once '../includes/flash.php';

// Build filters from GET
$filters = [
    'search'      => trim($_GET['search'] ?? ''),
    'category_id' => (int)($_GET['category_id'] ?? 0),
    'status'      => $_GET['status'] ?? '',
];

$assets     = Asset::getAll($filters);
$categories = Category::getAll();
<<<<<<< HEAD
$isAdmin    = currentUser() && currentUser()['role'] === 'admin';
$users      = $isAdmin ? User::getAll() : [];

// ── Capture modal HTML so footer.php can render it OUTSIDE the flex wrapper ──
ob_start();
?>

<!-- ══════════════════════════════════════════════════════
     VIEW ASSET MODAL  (rendered outside .d-flex wrapper)
══════════════════════════════════════════════════════ -->
<div class="modal fade" id="viewModal" tabindex="-1"
     aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="viewModalLabel">—</h5>
                    <div class="text-muted small font-monospace" id="vm-tag">—</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-4">

                    <!-- Left: Asset Info -->
                    <div class="col-md-6">
                        <h6 class="fw-semibold text-muted text-uppercase small mb-3">Asset Information</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted small">Category</div>
                                <div id="vm-category" class="fw-semibold">—</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Location</div>
                                <div id="vm-location" class="fw-semibold">—</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Status</div>
                                <div><span id="vm-status-badge" class="badge">—</span></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Serial Number</div>
                                <div id="vm-serial" class="fw-semibold">—</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Purchase Date</div>
                                <div id="vm-purchase-date" class="fw-semibold">—</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Purchase Cost</div>
                                <div id="vm-purchase-cost" class="fw-semibold">—</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Assigned To</div>
                                <div id="vm-assigned" class="fw-semibold">—</div>
                            </div>
                            <div class="col-12 d-none" id="vm-notes-wrap">
                                <div class="text-muted small">Notes</div>
                                <div id="vm-notes" class="fw-semibold">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Borrow Status + Form -->
                    <div class="col-md-6 border-start">
                        <h6 class="fw-semibold text-muted text-uppercase small mb-3">Borrow Status</h6>

                        <!-- Checked-out state -->
                        <div id="vm-active-tx" class="d-none">
                            <div class="rounded p-3 mb-3" style="background:#fff3cd; border:1px solid #ffc107;">
                                <i class="bi bi-person-check me-1 text-warning"></i>
                                Borrowed by <strong id="vm-borrower">—</strong><br>
                                <span class="small">Due: <strong id="vm-due">—</strong></span>
                            </div>
                            <?php if ($isAdmin): ?>
                            <form method="POST" action="<?= BASE_URL ?>/transactions/return.php">
                                <input type="hidden" name="asset_id"       id="vm-return-asset-id">
                                <input type="hidden" name="transaction_id" id="vm-return-tx-id">

                                <!-- Condition dropdown -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Asset Condition <span class="text-danger">*</span>
                                    </label>
                                    <select name="condition" class="form-select" required id="vm-return-condition">
                                        <option value="good">✅ Good — ready for reuse</option>
                                        <option value="damaged">⚠️ Damaged — needs repair</option>
                                        <option value="needs_repair">🔧 Needs Repair — send to maintenance</option>
                                    </select>
                                    <div class="form-text" id="vm-condition-hint">
                                        Asset will be set back to <strong>Active</strong>.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Return Notes <span class="text-muted fw-normal">(optional)</span>
                                    </label>
                                    <textarea name="notes" class="form-control" rows="2"
                                              placeholder="Additional remarks…"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-1"></i> Confirm Return
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>

                        <!-- Available state -->
                        <div id="vm-available">
                            <p class="text-muted small mb-3">
                                This asset is currently available. No active checkout.
                            </p>
                            <?php if ($isAdmin): ?>
                            <form method="POST" action="<?= BASE_URL ?>/transactions/checkout.php">
                                <input type="hidden" name="asset_id"       id="vm-checkout-asset-id">
                                <input type="hidden" name="checked_out_by" value="<?= (int)currentUser()['id'] ?>">
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
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Notes <span class="text-muted fw-normal">(optional)</span>
                                    </label>
                                    <textarea name="notes" class="form-control" rows="2"
                                              placeholder="Purpose, remarks…"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-box-arrow-in-left me-1"></i> Confirm Checkout
                                </button>
                            </form>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">Only admins can checkout assets.</div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <?php if ($isAdmin): ?>
                    <a id="vm-edit-link" href="#" class="btn btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i> Edit Asset
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById('viewModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;

    const id           = btn.dataset.id;
    const tag          = btn.dataset.tag;
    const name         = btn.dataset.name;
    const category     = btn.dataset.category;
    const location     = btn.dataset.location;
    const status       = btn.dataset.status;
    const serial       = btn.dataset.serial;
    const purchaseDate = btn.dataset.purchaseDate;
    const purchaseCost = btn.dataset.purchaseCost;
    const assigned     = btn.dataset.assigned;
    const notes        = btn.dataset.notes;
    const borrower     = btn.dataset.borrower;
    const due          = btn.dataset.due;
    const txId         = btn.dataset.txId;
    const checkedOut   = btn.dataset.checkedOut === '1';

    document.getElementById('viewModalLabel').textContent  = name;
    document.getElementById('vm-tag').textContent          = 'Tag: ' + tag;
    document.getElementById('vm-category').textContent     = category;
    document.getElementById('vm-location').textContent     = location;
    document.getElementById('vm-serial').textContent       = serial;
    document.getElementById('vm-purchase-date').textContent = purchaseDate;
    document.getElementById('vm-purchase-cost').textContent = purchaseCost;
    document.getElementById('vm-assigned').textContent     = assigned || '—';

    const notesWrap = document.getElementById('vm-notes-wrap');
    if (notes) {
        document.getElementById('vm-notes').textContent = notes;
        notesWrap.classList.remove('d-none');
    } else {
        notesWrap.classList.add('d-none');
    }

    const badgeMap = {
        active:       'bg-success',
        under_repair: 'bg-warning text-dark',
        disposed:     'bg-danger',
        lost:         'bg-danger',
    };
    const badge = document.getElementById('vm-status-badge');
    badge.className   = 'badge ' + (badgeMap[status] ?? 'bg-secondary');
    badge.textContent = status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());

    const activeTxDiv  = document.getElementById('vm-active-tx');
    const availableDiv = document.getElementById('vm-available');

    if (checkedOut) {
        activeTxDiv.classList.remove('d-none');
        availableDiv.classList.add('d-none');
        document.getElementById('vm-borrower').textContent = borrower;
        document.getElementById('vm-due').textContent      = due;
        const retAsset = document.getElementById('vm-return-asset-id');
        const retTx    = document.getElementById('vm-return-tx-id');
        if (retAsset) retAsset.value = id;
        if (retTx)    retTx.value    = txId;
    } else {
        activeTxDiv.classList.add('d-none');
        availableDiv.classList.remove('d-none');
        const coAsset = document.getElementById('vm-checkout-asset-id');
        if (coAsset) coAsset.value = id;
    }

    const editLink = document.getElementById('vm-edit-link');
    if (editLink) editLink.href = '<?= BASE_URL ?>/assets/edit.php?id=' + id;

    // Condition hint text
    const conditionSelect = document.getElementById('vm-return-condition');
    const conditionHint   = document.getElementById('vm-condition-hint');
    if (conditionSelect && conditionHint) {
        conditionSelect.value = 'good'; // reset each time modal opens
        conditionSelect.addEventListener('change', function () {
            const hints = {
                good:         'Asset will be set back to <strong>Active</strong>.',
                damaged:      'Asset will be set to <strong>Under Repair</strong>.',
                needs_repair: 'Asset will be set to <strong>Under Repair</strong>.',
            };
            conditionHint.innerHTML = hints[this.value] ?? '';
        });
        conditionHint.innerHTML = 'Asset will be set back to <strong>Active</strong>.';
    }
});
</script>

<?php
$pageModals = ob_get_clean();
// ── End modal capture ──
=======
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87

$pageTitle  = 'Asset List';
$activePage = 'assets';
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0">Assets</h1>
<<<<<<< HEAD
    <?php if ($isAdmin): ?>
        <a href="<?= BASE_URL ?>/assets/add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Asset
        </a>
    <?php endif; ?>
=======
    <a href="<?= BASE_URL ?>/assets/add.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Asset
    </a>
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
</div>

<?= showFlash() ?>

<!-- Filters -->
<form method="GET" action="" class="row g-2 mb-3">
    <div class="col-md-5">
<<<<<<< HEAD
        <input type="text" name="search" class="form-control"
=======
        <input type="text"
               name="search"
               class="form-control"
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
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

<<<<<<< HEAD
<p class="text-muted small mb-2">
    Showing <strong><?= count($assets) ?></strong> asset<?= count($assets) !== 1 ? 's' : '' ?>
</p>
=======
<!-- Count -->
<p class="text-muted small mb-2">Showing <strong><?= count($assets) ?></strong> asset<?= count($assets) !== 1 ? 's' : '' ?></p>
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87

<!-- Table -->
<?php if (empty($assets)): ?>
    <div class="alert alert-info">No assets found.</div>
<?php else: ?>
<<<<<<< HEAD
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
                    $activeTx = TransactionLog::getActiveByAsset($asset['id']);
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
                            <button type="button"
                                    class="btn btn-sm btn-primary me-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewModal"
                                    data-id="<?= $asset['id'] ?>"
                                    data-tag="<?= htmlspecialchars($asset['asset_tag']) ?>"
                                    data-name="<?= htmlspecialchars($asset['name']) ?>"
                                    data-category="<?= htmlspecialchars($asset['category_name'] ?? '—') ?>"
                                    data-location="<?= htmlspecialchars($asset['location'] ?? '—') ?>"
                                    data-status="<?= htmlspecialchars($asset['status']) ?>"
                                    data-serial="<?= htmlspecialchars($asset['serial_number'] ?? '—') ?>"
                                    data-purchase-date="<?= htmlspecialchars($asset['purchase_date'] ?? '—') ?>"
                                    data-purchase-cost="<?= htmlspecialchars((string)($asset['purchase_cost'] ?? '—')) ?>"
                                    data-assigned="<?= htmlspecialchars($asset['assigned_to'] ?? '—') ?>"
                                    data-notes="<?= htmlspecialchars($asset['notes'] ?? '') ?>"
                                    data-borrower="<?= htmlspecialchars($activeTx['user_name'] ?? '') ?>"
                                    data-due="<?= htmlspecialchars($activeTx['due_date'] ?? '') ?>"
                                    data-tx-id="<?= (int)($activeTx['id'] ?? 0) ?>"
                                    data-checked-out="<?= $activeTx ? '1' : '0' ?>">
                                View
                            </button>

                            <?php if ($isAdmin): ?>
=======
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
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
                                <a href="<?= BASE_URL ?>/assets/edit.php?id=<?= $asset['id'] ?>"
                                   class="btn btn-sm btn-secondary me-1">Edit</a>
                                <form method="POST" action="<?= BASE_URL ?>/assets/delete.php"
                                      class="d-inline">
                                    <input type="hidden" name="id" value="<?= $asset['id'] ?>">
<<<<<<< HEAD
                                    <button type="submit" class="btn btn-sm btn-danger"
=======
                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
                                            onclick="return confirm('Delete this asset?')">
                                        Delete
                                    </button>
                                </form>
<<<<<<< HEAD
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
=======
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
>>>>>>> 26081a8d5c02234edb451de9aa63bcef126bfc87
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>