<?php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Asset.php';

$filters = [
    'category_id' => (int)($_POST['category_id'] ?? 0),
    'status' => $_POST['status'] ?? '',
    'search' => '',
];

$assets = Asset::getAll([
    'category_id' => $filters['category_id'] ?: null,
    'status' => $filters['status'] ?: null,
]);

$from = $_POST['purchase_date_from'] ?? '';
$to = $_POST['purchase_date_to'] ?? '';

if ($from !== '' || $to !== '') {
    $filteredAssets = [];

    foreach ($assets as $asset) {
        $purchaseDate = $asset['purchase_date'] ?? '';

        $matchesFrom = true;
        if ($from !== '' && $purchaseDate !== '') {
            $matchesFrom = $purchaseDate >= $from;
        }

        $matchesTo = true;
        if ($to !== '' && $purchaseDate !== '') {
            $matchesTo = $purchaseDate <= $to;
        }

        if ($matchesFrom && $matchesTo) {
            $filteredAssets[] = $asset;
        }
    }

    $assets = $filteredAssets;
}

$date = date('Ymd');
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="assets_' . $date . '.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Asset Tag',
    'Name',
    'Category',
    'Serial No',
    'Location',
    'Status',
    'Purchase Date',
    'Cost',
    'Assigned To'
]);

foreach ($assets as $asset) {
    fputcsv($output, [
        $asset['asset_tag'] ?? '',
        $asset['name'] ?? '',
        $asset['category_name'] ?? '',
        $asset['serial_number'] ?? '',
        $asset['location'] ?? '',
        $asset['status'] ?? '',
        $asset['purchase_date'] ?? '',
        $asset['purchase_cost'] ?? '',
        $asset['assigned_to'] ?? ''
    ]);
}

fclose($output);
exit;
