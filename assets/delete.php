<?php
// assets/delete.php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/AuditLog.php';

requireAdmin();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/assets/index.php");
    exit;
}

$id    = (int)($_POST['id'] ?? 0);
$asset = Asset::getById($id);

if (!$asset) {
    $_SESSION['flash_error'] = 'Asset not found.';
    header("Location: " . BASE_URL . "/assets/index.php");
    exit;
}

// Log before deletion (asset row will be gone after)
AuditLog::log($id, $_SESSION['user_id'], 'deleted', $asset, null, 'Asset deleted: ' . $asset['asset_tag']);

$deleted = Asset::delete($id);

if ($deleted) {
    $_SESSION['flash_success'] = 'Asset "' . htmlspecialchars($asset['name']) . '" has been deleted.';
} else {
    $_SESSION['flash_error'] = 'Failed to delete asset. It may have dependent records.';
}

header("Location: " . BASE_URL . "/assets/index.php");
exit;