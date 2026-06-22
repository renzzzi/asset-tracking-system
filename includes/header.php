<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/custom.css" rel="stylesheet">

    <title><?= htmlspecialchars($pageTitle) ?> — AssetTrack</title>

    <style>
        html, body {
            height: 100%;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        .app-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        .app-sidebar {
            width: 260px;
            height: 100vh;
            flex-shrink: 0;
            overflow-y: auto;
        }
        .app-main {
            flex: 1;
            height: 100vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <aside class="app-sidebar sidebar-wrap d-flex flex-column justify-content-between">
        <div>
            <div class="sidebar-brand px-3 py-4">
                <a class="d-flex align-items-center gap-2 fw-bold text-decoration-none"
                   href="<?= BASE_URL ?>/dashboard/index.php">
                    <span class="sidebar-brand-icon">
                        <i class="bi bi-boxes"></i>
                    </span>
                    <span class="sidebar-brand-text">AssetTrack</span>
                </a>
            </div>

            <nav class="nav flex-column px-3 pb-3">
                <a class="sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>"
                   href="<?= BASE_URL ?>/dashboard/index.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a class="sidebar-link <?= $activePage === 'assets' ? 'active' : '' ?>"
                   href="<?= BASE_URL ?>/assets/index.php">
                    <i class="bi bi-box-seam me-2"></i> Assets
                </a>
                <a class="sidebar-link <?= $activePage === 'categories' ? 'active' : '' ?>"
                   href="<?= BASE_URL ?>/categories/index.php">
                    <i class="bi bi-tags me-2"></i> Categories
                </a>
                <a class="sidebar-link <?= $activePage === 'reports' ? 'active' : '' ?>"
                   href="<?= BASE_URL ?>/reports/index.php">
                    <i class="bi bi-bar-chart me-2"></i> Reports
                </a>
            </nav>
        </div>

        <div class="sidebar-footer px-3 py-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-person-circle"></i>
                <span><?= htmlspecialchars(currentUser()['name'] ?? '') ?></span>
            </div>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-sm w-100 logout-btn">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </aside>

    <main class="app-main py-4">
        <div class="container-fluid px-4">
            <?php require_once __DIR__ . '/flash.php'; echo showFlash(); ?>