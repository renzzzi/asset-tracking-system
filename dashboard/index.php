<?php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../models/TransactionLog.php';
require_once __DIR__ . '/../includes/flash.php';

$allAssets      = Asset::getAll();
$statusCounts   = Asset::getCountByStatus();
$categoryCounts = Asset::getCountByCategory();
$recentActivity = AuditLog::getRecent(10);
$overdueList    = TransactionLog::getOverdue();
$overdueCount   = count($overdueList);

$statusTotals = [];
foreach ($statusCounts as $row) {
    $statusTotals[$row['status']] = (int)$row['count'];
}

$totalAssets      = count($allAssets);
$activeCount      = (int)($statusTotals['active']       ?? 0);
$underRepairCount = (int)($statusTotals['under_repair'] ?? 0);
$disposedCount    = (int)($statusTotals['disposed']     ?? 0);
$lostCount        = (int)($statusTotals['lost']         ?? 0);
$disposedLostCount = $disposedCount + $lostCount;

$chartStatusLabels = [];
$chartStatusData   = [];
foreach ($statusCounts as $row) {
    $chartStatusLabels[] = ucfirst(str_replace('_', ' ', $row['status']));
    $chartStatusData[]   = (int)$row['count'];
}

$chartCategoryLabels = [];
$chartCategoryData   = [];
foreach ($categoryCounts as $row) {
    $chartCategoryLabels[] = $row['category_name'];
    $chartCategoryData[]   = (int)$row['count'];
}

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Dashboard</h1>
        <p class="text-muted mb-0">Overview of your asset tracking system</p>
    </div>
    <?php if (currentUser() && currentUser()['role'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/assets/add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Asset
        </a>
    <?php endif; ?>
</div>

<?= showFlash() ?>

<!-- ── Stat Cards ─────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-1">Total Assets</h6>
                    <h2 class="mb-0 fw-bold"><?= $totalAssets ?></h2>
                </div>
                <i class="bi bi-boxes" style="font-size:2.5rem;opacity:.5"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-1">Active</h6>
                    <h2 class="mb-0 fw-bold"><?= $activeCount ?></h2>
                </div>
                <i class="bi bi-check-circle" style="font-size:2.5rem;opacity:.5"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-1">Under Repair</h6>
                    <h2 class="mb-0 fw-bold"><?= $underRepairCount ?></h2>
                </div>
                <i class="bi bi-tools" style="font-size:2.5rem;opacity:.5"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-1">Disposed/Lost</h6>
                    <h2 class="mb-0 fw-bold"><?= $disposedLostCount ?></h2>
                </div>
                <i class="bi bi-x-circle" style="font-size:2.5rem;opacity:.5"></i>
            </div>
        </div>
    </div>
</div>

<!-- ── Overdue Widget ──────────────────────────────────────── -->
<?php if ($overdueCount > 0): ?>
<div class="card border-danger shadow-sm mb-4">
    <div class="card-header bg-danger text-white d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <h5 class="mb-0">Overdue Assets</h5>
        </div>
        <span class="badge bg-white text-danger fw-bold"><?= $overdueCount ?> overdue</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Asset</th>
                        <th>Borrowed By</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($overdueList as $row): ?>
                    <tr class="table-danger">
                        <td class="ps-3">
                            <div class="fw-semibold"><?= htmlspecialchars($row['asset_name']) ?></div>
                            <div class="text-muted small font-monospace"><?= htmlspecialchars($row['asset_tag']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($row['user_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars(date('M d, Y', strtotime($row['due_date']))) ?></td>
                        <td>
                            <span class="badge bg-danger">
                                <?= $row['days_overdue'] ?> day<?= $row['days_overdue'] != 1 ? 's' : '' ?>
                            </span>
                        </td>
                        <td class="pe-3">
                            <a href="<?= BASE_URL ?>/assets/view.php?id=<?= (int)$row['asset_id'] ?>"
                               class="btn btn-sm btn-outline-danger">
                                View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Charts ─────────────────────────────────────────────── -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Asset Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Assets by Category</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ── Recent Activity ────────────────────────────────────── -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="mb-0">Recent Activity</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Asset</th>
                        <th>Action</th>
                        <th>Changed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentActivity as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($row['created_at']))) ?></td>
                        <td><?= htmlspecialchars($row['asset_name'] ?? 'Unknown Asset') ?></td>
                        <td>
                            <?php
                            $badgeMap = [
                                'created'        => 'bg-success',
                                'updated'        => 'bg-primary',
                                'deleted'        => 'bg-danger',
                                'status_changed' => 'bg-warning text-dark',
                            ];
                            $badge = $badgeMap[$row['action']] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?= $badge ?>">
                                <?= htmlspecialchars($row['action']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['user_name'] ?? 'System') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx && typeof Chart !== 'undefined') {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chartStatusLabels) ?>,
                datasets: [{
                    data: <?= json_encode($chartStatusData) ?>,
                    backgroundColor: ['#198754','#ffc107','#dc3545','#6c757d']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const catCtx = document.getElementById('categoryChart');
    if (catCtx && typeof Chart !== 'undefined') {
        new Chart(catCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartCategoryLabels) ?>,
                datasets: [{
                    label: 'Assets',
                    data: <?= json_encode($chartCategoryData) ?>,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>