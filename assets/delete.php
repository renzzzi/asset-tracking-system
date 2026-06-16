<?php
// assets/delete.php
require_once '../auth/session.php';
requireLogin();
require_once '../config/constants.php';
require_once '../config/db.php';
require_once '../models/Asset.php';
require_once '../models/AuditLog.php';
require_once '../includes/flash.php';

requireAdmin();

// Only allow POST — never delete on GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

$id    = (int)($_POST['id'] ?? 0);
$asset = Asset::getById($id);

if (!$asset) {
    setFlash('danger', 'Asset not found.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

Asset::delete($id);
AuditLog::log($id, currentUser()['id'], 'deleted', $asset, null);
setFlash('success', 'Asset "' . htmlspecialchars($asset['name']) . '" deleted successfully.');
header('Location: ' . BASE_URL . '/assets/index.php');
exit;