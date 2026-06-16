<?php
// assets/edit.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Asset.php';
require_once '../models/Category.php';
require_once '../models/AuditLog.php';
require_once '../includes/flash.php';

requireAdmin();

$id    = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$asset = Asset::getById($id);

if (!$asset) {
    setFlash('danger', 'Asset not found.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

$categories = Category::getAll();
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim all inputs
    $trimmed = [
        'name'          => trim($_POST['name'] ?? ''),
        'category_id'   => trim($_POST['category_id'] ?? ''),
        'serial_number' => trim($_POST['serial_number'] ?? ''),
        'location'      => trim($_POST['location'] ?? ''),
        'status'        => trim($_POST['status'] ?? 'active'),
        'purchase_date' => trim($_POST['purchase_date'] ?? ''),
        'purchase_cost' => trim($_POST['purchase_cost'] ?? ''),
        'assigned_to'   => trim($_POST['assigned_to'] ?? ''),
        'notes'         => trim($_POST['notes'] ?? ''),
    ];

    // Validate
    if ($trimmed['name'] === '') {
        $errors['name'] = 'Asset name is required.';
    }
    if ($trimmed['category_id'] === '') {
        $errors['category_id'] = 'Category is required.';
    }

    if (empty($errors)) {
        // Capture old values before updating
        $old = Asset::getById($id);

        $data = [
            'name'          => $trimmed['name'],
            'description'   => $asset['description'] ?? null,
            'category_id'   => (int) $trimmed['category_id'],
            'serial_number' => $trimmed['serial_number'] ?: null,
            'location'      => $trimmed['location'] ?: null,
            'status'        => $trimmed['status'],
            'purchase_date' => $trimmed['purchase_date'] ?: null,
            'purchase_cost' => $trimmed['purchase_cost'] !== '' ? (float) $trimmed['purchase_cost'] : null,
            'assigned_to'   => $trimmed['assigned_to'] ?: null,
            'notes'         => $trimmed['notes'] ?: null,
        ];

        Asset::update($id, $data);
        AuditLog::log($id, currentUser()['id'], 'updated', $old, $data);
        setFlash('success', 'Asset updated successfully.');
        header('Location: ' . BASE_URL . '/assets/view.php?id=' . $id);
        exit;
    }

    // Re-populate with submitted values on error
    $asset = array_merge($asset, $trimmed);
}

$pageTitle  = 'Edit Asset';
$activePage = 'assets';
require_once '../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/assets/view.php?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm">
        &larr; Back
    </a>
    <h1 class="h3 fw-bold mb-0">Edit Asset
        <small class="text-muted fs-6 font-monospace"><?= htmlspecialchars($asset['asset_tag']) ?></small>
    </h1>
</div>

<?= showFlash() ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="" novalidate>
            <input type="hidden" name="id" value="<?= $id ?>">

            <!-- Name -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                <input type="text" name="name"
                       class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($asset['name']) ?>">
                <?php if (isset($errors['name'])): ?>
                    <small class="text-danger"><?= $errors['name'] ?></small>
                <?php endif; ?>
            </div>

            <!-- Category -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                <select name="category_id"
                        class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>">
                    <option value="">Select category…</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= $asset['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                    <small class="text-danger"><?= $errors['category_id'] ?></small>
                <?php endif; ?>
            </div>

            <!-- Serial Number -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Serial Number</label>
                <input type="text" name="serial_number" class="form-control"
                       value="<?= htmlspecialchars($asset['serial_number'] ?? '') ?>">
            </div>

            <!-- Location -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Location</label>
                <input type="text" name="location" class="form-control"
                       value="<?= htmlspecialchars($asset['location'] ?? '') ?>">
            </div>

            <!-- Status -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <?php foreach (ASSET_STATUSES as $s): ?>
                        <option value="<?= $s ?>"
                            <?= $asset['status'] === $s ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Purchase Date -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Purchase Date</label>
                <input type="date" name="purchase_date" class="form-control"
                       value="<?= htmlspecialchars($asset['purchase_date'] ?? '') ?>">
            </div>

            <!-- Purchase Cost -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Purchase Cost</label>
                <input type="number" name="purchase_cost" class="form-control"
                       step="0.01" min="0"
                       value="<?= htmlspecialchars($asset['purchase_cost'] ?? '') ?>">
            </div>

            <!-- Assigned To -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Assigned To</label>
                <input type="text" name="assigned_to" class="form-control"
                       value="<?= htmlspecialchars($asset['assigned_to'] ?? '') ?>">
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($asset['notes'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= BASE_URL ?>/assets/view.php?id=<?= $id ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>