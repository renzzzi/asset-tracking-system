<?php
// assets/view.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Asset.php';
require_once '../models/AuditLog.php';
require_once '../includes/flash.php';

$id    = (int)($_GET['id'] ?? 0);
$asset = Asset::getById($id);

if (!$asset) {
    setFlash('danger', 'Asset not found.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

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