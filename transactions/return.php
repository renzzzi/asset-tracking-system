<?php
// transactions/return.php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/TransactionLog.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../includes/flash.php';

if (currentUser()['role'] !== 'admin') {
    $_SESSION['flash_error'] = "Access denied. Admins only.";
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

$transactionId = (int)($_POST['transaction_id'] ?? 0);
$assetId       = (int)($_POST['asset_id']       ?? 0);
$condition     = trim($_POST['condition']        ?? 'good');
$notes         = trim($_POST['notes']            ?? '') ?: null;

// Validate condition
if (!in_array($condition, ['good', 'damaged', 'needs_repair'])) {
    $condition = 'good';
}

if ($assetId <= 0) {
    setFlash('danger', 'Invalid request. Missing asset.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

// Get active transaction
$active = TransactionLog::getActiveByAsset($assetId);
if (!$active) {
    setFlash('danger', 'No active checkout found for this asset.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

if ($transactionId <= 0) {
    $transactionId = (int)$active['id'];
}

// Mark returned with condition
$ok = TransactionLog::markReturned($transactionId, $condition, $notes);
if (!$ok) {
    setFlash('danger', 'Failed to mark asset as returned.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

// Auto-update asset status based on condition
$newAssetStatus = match($condition) {
    'damaged', 'needs_repair' => 'under_repair',
    default                   => 'active',
};

$oldAsset = Asset::getById($assetId);
Asset::update($assetId, array_merge($oldAsset, ['status' => $newAssetStatus]));

// Audit log
AuditLog::log(
    $assetId,
    currentUser()['id'],
    'returned',
    ['status' => $oldAsset['status']],
    ['status' => $newAssetStatus, 'condition' => $condition, 'return_notes' => $notes]
);

$conditionLabel = match($condition) {
    'damaged'      => 'Damaged — marked for repair.',
    'needs_repair' => 'Needs repair — marked for repair.',
    default        => 'Good condition.',
};

setFlash('success', 'Asset returned successfully. Condition: ' . $conditionLabel);
header('Location: ' . BASE_URL . '/assets/index.php');
exit;