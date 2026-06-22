<?php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../includes/flash.php';

// Active tab
$activeTab = $_GET['tab'] ?? 'assets';

// ── Asset Filters ────────────────────────────────────────────
$assetFilters = [
    'category_id'        => (int)($_GET['category_id'] ?? 0),
    'status'             => $_GET['status'] ?? '',
    'purchase_date_from' => $_GET['purchase_date_from'] ?? '',
    'purchase_date_to'   => $_GET['purchase_date_to']   ?? '',
];

$categories = Category::getAll();
$allAssets  = Asset::getAll([
    'category_id' => $assetFilters['category_id'] ?: null,
    'status'      => $assetFilters['status']       ?: null,
]);

$assets = [];
foreach ($allAssets as $asset) {
    $pd           = $asset['purchase_date'] ?? '';
    $matchesFrom  = $assetFilters['purchase_date_from'] === '' || $pd === '' || $pd >= $assetFilters['purchase_date_from'];
    $matchesTo    = $assetFilters['purchase_date_to']   === '' || $pd === '' || $pd <= $assetFilters['purchase_date_to'];
    if ($matchesFrom && $matchesTo) $assets[] = $asset;
}

// ── Transaction Filters ──────────────────────────────────────
$txFilters = [
    'tx_status'  => $_GET['tx_status']  ?? '',
    'tx_search'  => trim($_GET['tx_search']  ?? ''),
    'tx_from'    => $_GET['tx_from']    ?? '',
    'tx_to'      => $_GET['tx_to']      ?? '',
];

try {
    $sql = "SELECT tl.*,
                   a.name      as asset_name,
                   a.asset_tag,
                   u.full_name as borrower_name,
                   ch.full_name as checked_out_by_name,
                   CASE WHEN tl.status = 'checked_out' AND tl.due_date < CURDATE()
                        THEN 1 ELSE 0 END as is_overdue,
                   CASE WHEN tl.returned_at IS NOT NULL
                        THEN DATEDIFF(tl.returned_at, tl.checked_out_at)
                        ELSE DATEDIFF(NOW(), tl.checked_out_at)
                   END as days_held
            FROM transaction_logs tl
            LEFT JOIN assets a  ON tl.asset_id       = a.id
            LEFT JOIN users  u  ON tl.user_id         = u.id
            LEFT JOIN users  ch ON tl.checked_out_by  = ch.id
            WHERE 1=1";
    $params = [];

    if ($txFilters['tx_status'] !== '') {
        $sql .= " AND tl.status = ?";
        $params[] = $txFilters['tx_status'];
    }
    if ($txFilters['tx_search'] !== '') {
        $sql .= " AND (a.name LIKE ? OR a.asset_tag LIKE ? OR u.full_name LIKE ?)";
        $kw = '%' . $txFilters['tx_search'] . '%';
        array_push($params, $kw, $kw, $kw);
    }
    if ($txFilters['tx_from'] !== '') {
        $sql .= " AND DATE(tl.checked_out_at) >= ?";
        $params[] = $txFilters['tx_from'];
    }
    if ($txFilters['tx_to'] !== '') {
        $sql .= " AND DATE(tl.checked_out_at) <= ?";
        $params[] = $txFilters['tx_to'];
    }

    $sql .= " ORDER BY tl.checked_out_at DESC";
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    $transactions = [];
}

$txTotal       = count($transactions);
$txCheckedOut  = count(array_filter($transactions, fn($t) => $t['status'] === 'checked_out'));
$txReturned    = count(array_filter($transactions, fn($t) => $t['status'] === 'returned'));
$txOverdue     = count(array_filter($transactions, fn($t) => $t['is_overdue']));

$pageTitle  = 'Reports';
$activePage = 'reports';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">Reports</h1>
        <p class="text-muted mb-0">Filter and review asset and transaction records</p>
    </div>
</div>

<?= showFlash() ?>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'assets' ? 'active' : '' ?>"
           href="?tab=assets">
            <i class="bi bi-box-seam me-1"></i> Assets
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'transactions' ? 'active' : '' ?>"
           href="?tab=transactions">
            <i class="bi bi-clock-history me-1"></i> Transactions
        </a>
    </li>
</ul>


<?php if ($activeTab === 'assets'): ?>
<!-- ══════════════════════════════════════════════════════
     ASSETS TAB
══════════════════════════════════════════════════════ -->

<form method="GET" action="" class="card border-0 shadow-sm mb-4">
    <input type="hidden" name="tab" value="assets">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"
                            <?= (int)$assetFilters['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php foreach (ASSET_STATUSES as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>"
                            <?= $assetFilters['status'] === $s ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Purchase Date From</label>
                <input type="date" name="purchase_date_from" class="form-control"
                       value="<?= htmlspecialchars($assetFilters['purchase_date_from']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Purchase Date To</label>
                <input type="date" name="purchase_date_to" class="form-control"
                       value="<?= htmlspecialchars($assetFilters['purchase_date_to']) ?>">
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="?tab=assets" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<form method="POST" action="<?= BASE_URL ?>/reports/export_csv.php" class="mb-3">
    <input type="hidden" name="category_id"        value="<?= htmlspecialchars((string)$assetFilters['category_id']) ?>">
    <input type="hidden" name="status"             value="<?= htmlspecialchars($assetFilters['status']) ?>">
    <input type="hidden" name="purchase_date_from" value="<?= htmlspecialchars($assetFilters['purchase_date_from']) ?>">
    <input type="hidden" name="purchase_date_to"   value="<?= htmlspecialchars($assetFilters['purchase_date_to']) ?>">
    <button type="submit" class="btn btn-success">
        <i class="bi bi-download me-1"></i> Export CSV
    </button>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Asset Tag</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Purchase Date</th>
                        <th class="pe-3">Purchase Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No assets found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assets as $asset): ?>
                            <?php
                            $badgeClass = match($asset['status'] ?? '') {
                                'active'       => 'bg-success',
                                'under_repair' => 'bg-warning text-dark',
                                'disposed'     => 'bg-danger',
                                'lost'         => 'bg-danger',
                                default        => 'bg-secondary',
                            };
                            ?>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">
                                    <?= htmlspecialchars($asset['asset_tag'] ?? '') ?>
                                </td>
                                <td><?= htmlspecialchars($asset['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($asset['category_name'] ?? '') ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= ucfirst(str_replace('_', ' ', $asset['status'] ?? '')) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($asset['purchase_date'] ?? '—') ?></td>
                                <td class="pe-3"><?= htmlspecialchars($asset['purchase_cost'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php else: ?>
<!-- ══════════════════════════════════════════════════════
     TRANSACTIONS TAB
══════════════════════════════════════════════════════ -->

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h2 fw-bold text-primary mb-0"><?= $txTotal ?></div>
            <div class="text-muted small">Total Transactions</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h2 fw-bold text-warning mb-0"><?= $txCheckedOut ?></div>
            <div class="text-muted small">Currently Out</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h2 fw-bold text-success mb-0"><?= $txReturned ?></div>
            <div class="text-muted small">Returned</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h2 fw-bold text-danger mb-0"><?= $txOverdue ?></div>
            <div class="text-muted small">Overdue</div>
        </div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="" class="card border-0 shadow-sm mb-4">
    <input type="hidden" name="tab" value="transactions">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="tx_search" class="form-control"
                       placeholder="Asset name, tag, borrower…"
                       value="<?= htmlspecialchars($txFilters['tx_search']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="tx_status" class="form-select">
                    <option value="">All</option>
                    <option value="checked_out" <?= $txFilters['tx_status'] === 'checked_out' ? 'selected' : '' ?>>
                        Checked Out
                    </option>
                    <option value="returned" <?= $txFilters['tx_status'] === 'returned' ? 'selected' : '' ?>>
                        Returned
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Checkout Date From</label>
                <input type="date" name="tx_from" class="form-control"
                       value="<?= htmlspecialchars($txFilters['tx_from']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Checkout Date To</label>
                <input type="date" name="tx_to" class="form-control"
                       value="<?= htmlspecialchars($txFilters['tx_to']) ?>">
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="?tab=transactions" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-sm-headers">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Asset</th>
                        <th>Borrowed By</th>
                        <th>Processed By</th>
                        <th>Checkout Date</th>
                        <th>Due Date</th>
                        <th>Returned On</th>
                        <th>Days Held</th>
                        <th>Condition</th>
                        <th class="pe-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                No transactions found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $i => $tx): ?>
                            <?php
                            $isOverdue = (bool)$tx['is_overdue'];
                            $rowClass  = $isOverdue ? 'table-danger' : '';

                            $conditionBadge = match($tx['condition'] ?? '') {
                                'good'         => '<span class="badge bg-success">Good</span>',
                                'damaged'      => '<span class="badge bg-danger">Damaged</span>',
                                'needs_repair' => '<span class="badge bg-warning text-dark">Needs Repair</span>',
                                default        => '<span class="text-muted">—</span>',
                            };

                            $statusBadge = $isOverdue
                                ? '<span class="badge bg-danger">Overdue</span>'
                                : match($tx['status']) {
                                    'checked_out' => '<span class="badge bg-warning text-dark">Checked Out</span>',
                                    'returned'    => '<span class="badge bg-success">Returned</span>',
                                    default       => '<span class="badge bg-secondary">' . htmlspecialchars($tx['status']) . '</span>',
                                };

                            $daysOverdue = $isOverdue
                                ? (new DateTime())->diff(new DateTime($tx['due_date']))->days
                                : 0;
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td class="ps-3 text-muted small"><?= $i + 1 ?></td>
                                <td>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($tx['asset_name'] ?? '—') ?>
                                    </div>
                                    <div class="text-muted small font-monospace">
                                        <?= htmlspecialchars($tx['asset_tag'] ?? '') ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($tx['borrower_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($tx['checked_out_by_name'] ?? '—') ?></td>
                                <td><?= date('M d, Y', strtotime($tx['checked_out_at'])) ?></td>
                                <td>
                                    <?= date('M d, Y', strtotime($tx['due_date'])) ?>
                                    <?php if ($isOverdue): ?>
                                        <div class="text-danger small fw-semibold">
                                            <?= $daysOverdue ?> day<?= $daysOverdue != 1 ? 's' : '' ?> late
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $tx['returned_at']
                                        ? date('M d, Y', strtotime($tx['returned_at']))
                                        : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= (int)$tx['days_held'] ?> day<?= $tx['days_held'] != 1 ? 's' : '' ?>
                                    </span>
                                </td>
                                <td><?= $conditionBadge ?></td>
                                <td class="pe-3"><?= $statusBadge ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>