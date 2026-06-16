<?php
// assets/edit.php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/AuditLog.php';

requireAdmin();

$id    = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$asset = Asset::getById($id);

if (!$asset) {
    $_SESSION['flash_error'] = 'Asset not found.';
    header("Location: " . BASE_URL . "/assets/index.php");
    exit;
}

$categories = Category::getAll();
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    if (empty(trim($_POST['name'] ?? ''))) {
        $errors['name'] = 'Asset name is required.';
    }
    if (empty($_POST['category_id'])) {
        $errors['category_id'] = 'Please select a category.';
    }
    if (!empty($_POST['purchase_cost']) && !is_numeric($_POST['purchase_cost'])) {
        $errors['purchase_cost'] = 'Purchase cost must be a number.';
    }

    if (empty($errors)) {
        $oldData = $asset;

        $data = [
            'name'          => trim($_POST['name']),
            'description'   => trim($_POST['description'] ?? ''),
            'category_id'   => (int) $_POST['category_id'],
            'serial_number' => trim($_POST['serial_number'] ?? '') ?: null,
            'location'      => trim($_POST['location'] ?? '') ?: null,
            'status'        => $_POST['status'] ?? 'active',
            'purchase_date' => $_POST['purchase_date'] ?: null,
            'purchase_cost' => $_POST['purchase_cost'] !== '' ? $_POST['purchase_cost'] : null,
            'assigned_to'   => trim($_POST['assigned_to'] ?? '') ?: null,
            'notes'         => trim($_POST['notes'] ?? '') ?: null,
        ];

        $updated = Asset::update($id, $data);

        if ($updated) {
            // Determine if status changed for a more specific log action
            $action = ($oldData['status'] !== $data['status']) ? 'status_changed' : 'updated';
            AuditLog::log($id, $_SESSION['user_id'], $action, $oldData, $data, 'Asset updated');

            $_SESSION['flash_success'] = 'Asset updated successfully.';
            header("Location: " . BASE_URL . "/assets/view.php?id=" . $id);
            exit;
        } else {
            $_SESSION['flash_error'] = 'Failed to update asset. Please try again.';
        }
    }

    // On validation error, use POST data
    $asset = array_merge($asset, $_POST);
}

$pageTitle = 'Edit Asset';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>/assets/view.php?id=<?= $id ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h1 class="h3 mb-0 fw-bold">Edit Asset</h1>
        <p class="text-muted small mb-0">
            <span class="font-monospace"><?= htmlspecialchars($asset['asset_tag']) ?></span>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/flash.php'; ?>

<form method="POST" action="" novalidate>
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="row g-4">

        <!-- Left Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Basic Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($asset['name']) ?>">
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= $errors['name'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>">
                                <option value="">Select category…</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= $asset['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                                <div class="invalid-feedback"><?= $errors['category_id'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (ASSET_STATUSES as $s): ?>
                                    <option value="<?= $s ?>" <?= $asset['status'] === $s ? 'selected' : '' ?>>
                                        <?= ucfirst(str_replace('_', ' ', $s)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($asset['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Location & Assignment</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location</label>
                            <input type="text" name="location" class="form-control"
                                   value="<?= htmlspecialchars($asset['location'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assigned To</label>
                            <input type="text" name="assigned_to" class="form-control"
                                   value="<?= htmlspecialchars($asset['assigned_to'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Notes</h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($asset['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Purchase Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Asset Tag</label>
                            <input type="text" class="form-control font-monospace bg-light"
                                   value="<?= htmlspecialchars($asset['asset_tag']) ?>" disabled>
                            <div class="form-text">Auto-generated, cannot be changed.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control font-monospace"
                                   value="<?= htmlspecialchars($asset['serial_number'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control"
                                   value="<?= htmlspecialchars($asset['purchase_date'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Purchase Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="purchase_cost" step="0.01" min="0"
                                       class="form-control <?= isset($errors['purchase_cost']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($asset['purchase_cost'] ?? '') ?>">
                                <?php if (isset($errors['purchase_cost'])): ?>
                                    <div class="invalid-feedback"><?= $errors['purchase_cost'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                        <a href="<?= BASE_URL ?>/assets/view.php?id=<?= $id ?>" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>