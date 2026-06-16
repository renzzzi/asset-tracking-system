<?php
// assets/add.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Asset.php';
require_once '../models/Category.php';
require_once '../models/AuditLog.php';
require_once '../includes/flash.php';

requireAdmin();

$categories = Category::getAll();
$errors     = [];
$old        = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim all inputs
    $old = [
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
    if ($old['name'] === '') {
        $errors['name'] = 'Asset name is required.';
    }
    if ($old['category_id'] === '') {
        $errors['category_id'] = 'Category is required.';
    }

    if (empty($errors)) {
        $data = [
            'name'          => $old['name'],
            'description'   => null,
            'category_id'   => (int) $old['category_id'],
            'serial_number' => $old['serial_number'] ?: null,
            'location'      => $old['location'] ?: null,
            'status'        => $old['status'],
            'purchase_date' => $old['purchase_date'] ?: null,
            'purchase_cost' => $old['purchase_cost'] !== '' ? (float) $old['purchase_cost'] : null,
            'assigned_to'   => $old['assigned_to'] ?: null,
            'notes'         => $old['notes'] ?: null,
            'created_by'    => currentUser()['id'],
        ];

        $newId = Asset::create($data);

        if ($newId) {
            AuditLog::log($newId, currentUser()['id'], 'created', null, $data);
            setFlash('success', 'Asset added successfully.');
            header('Location: ' . BASE_URL . '/assets/index.php');
            exit;
        } else {
            setFlash('danger', 'Failed to create asset. Please try again.');
        }
    }
}

$pageTitle  = 'Add Asset';
$activePage = 'assets';
require_once '../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/assets/index.php" class="btn btn-outline-secondary btn-sm">
        &larr; Back
    </a>
    <h1 class="h3 fw-bold mb-0">Add Asset</h1>
</div>

<?= showFlash() ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="" novalidate>

            <!-- Name -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                <input type="text" name="name"
                       class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($old['name'] ?? '') ?>">
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
                            <?= ($old['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
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
                       value="<?= htmlspecialchars($old['serial_number'] ?? '') ?>">
            </div>

            <!-- Location -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Location</label>
                <input type="text" name="location" class="form-control"
                       value="<?= htmlspecialchars($old['location'] ?? '') ?>">
            </div>

            <!-- Status -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <?php foreach (ASSET_STATUSES as $s): ?>
                        <option value="<?= $s ?>"
                            <?= ($old['status'] ?? 'active') === $s ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Purchase Date -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Purchase Date</label>
                <input type="date" name="purchase_date" class="form-control"
                       value="<?= htmlspecialchars($old['purchase_date'] ?? '') ?>">
            </div>

            <!-- Purchase Cost -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Purchase Cost</label>
                <input type="number" name="purchase_cost" class="form-control"
                       step="0.01" min="0"
                       value="<?= htmlspecialchars($old['purchase_cost'] ?? '') ?>">
            </div>

            <!-- Assigned To -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Assigned To</label>
                <input type="text" name="assigned_to" class="form-control"
                       value="<?= htmlspecialchars($old['assigned_to'] ?? '') ?>">
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Asset</button>
                <a href="<?= BASE_URL ?>/assets/index.php" class="btn btn-outline-secondary">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>