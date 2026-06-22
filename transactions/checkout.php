<?php
// transactions/checkout.php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/TransactionLog.php';
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

$assetId        = (int)($_POST['asset_id']         ?? 0);
$borrowerUserId = (int)($_POST['borrower_user_id'] ?? 0);
$checkedOutBy   = (int)($_POST['checked_out_by']   ?? currentUser()['id']);
$dueDate        = trim($_POST['due_date'] ?? '');
$notes          = trim($_POST['notes']    ?? '');

if ($assetId <= 0 || $borrowerUserId <= 0) {
    setFlash('danger', 'Invalid request. Missing asset or borrower.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

if ($dueDate === '') {
    setFlash('danger', 'Due date is required.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

$asset = Asset::getById($assetId);
if (!$asset) {
    setFlash('danger', 'Asset not found.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

// Block checkout if asset is not active
if ($asset['status'] !== 'active') {
    setFlash('danger', 'Only active assets can be checked out. Current status: ' . ucfirst(str_replace('_', ' ', $asset['status'])) . '.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

$borrower = User::getById($borrowerUserId);
if (!$borrower) {
    setFlash('danger', 'Borrower user not found.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

// Prevent duplicate active checkout
$active = TransactionLog::getActiveByAsset($assetId);
if ($active) {
    setFlash('danger', 'This asset is already checked out. Return it before checking out again.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

$ok = TransactionLog::create([
    'asset_id'       => $assetId,
    'user_id'        => $borrowerUserId,
    'checked_out_by' => $checkedOutBy,
    'due_date'       => $dueDate,
    'status'         => 'checked_out',
    'notes'          => $notes !== '' ? $notes : null,
]);

if (!$ok) {
    setFlash('danger', 'Failed to checkout asset.');
    header('Location: ' . BASE_URL . '/assets/index.php');
    exit;
}

// Update asset status to reflect it's in use
Asset::update($assetId, array_merge($asset, ['status' => 'active'])); // keep active; just logged

$transaction   = TransactionLog::getActiveByAsset($assetId);
$createdUserId = currentUser()['id'];

AuditLog::log($assetId, $createdUserId, 'checked_out', null, [
    'borrower_user_id' => $borrowerUserId,
    'checked_out_by'   => $checkedOutBy,
    'due_date'         => $dueDate,
    'notes'            => $notes,
    'transaction_id'   => $transaction['id'] ?? null,
]);

setFlash('success', 'Asset "' . htmlspecialchars($asset['name']) . '" checked out successfully.');
header('Location: ' . BASE_URL . '/assets/index.php');
exit;