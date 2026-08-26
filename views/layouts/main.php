<?php
$activePage = $activePage ?? 'dashboard';
$sidebarCollapsed = $_COOKIE['sidebar_collapsed'] ?? '0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toyota Silang CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= Url::asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
    <!-- Top navbar -->
    <nav class="navbar navbar-dark bg-dark fixed-top top-nav">
        <div class="container-fluid">
            <button class="btn btn-dark sidebar-toggle d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center" href="<?= Url::route('dashboard') ?>">
                <i class="bi bi-car-front-fill me-2 text-danger"></i>
                <span class="brand-text">Toyota Silang CRM</span>
            </a>
            <div class="d-flex align-items-center">
                <a href="<?= Url::route('leads/create') ?>" class="btn btn-danger btn-sm me-3 d-none d-md-inline-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i> New Lead
                </a>
                <div class="dropdown">
                    <button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <span class="d-none d-md-inline"><?= Security::escape($_SESSION['full_name'] ?? 'User') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text text-muted small"><?= Security::escape($_SESSION['role'] ?? 'user') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= Url::route('auth/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile sidebar offcanvas -->
    <div class="offcanvas offcanvas-start sidebar-dark" tabindex="-1" id="sidebarOffcanvas">
        <div class="offcanvas-header bg-dark">
            <h5 class="offcanvas-title text-white"><i class="bi bi-car-front-fill me-2 text-danger"></i> Toyota CRM</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <?php require dirname(__DIR__) . '/layouts/_sidebar.php'; ?>
        </div>
    </div>

    <!-- Desktop sidebar -->
    <nav class="sidebar d-none d-md-flex">
        <?php require dirname(__DIR__) . '/layouts/_sidebar.php'; ?>
    </nav>

    <!-- Main content -->
    <main class="main-content">
        <div class="container-fluid py-3">
            <!-- Toast container -->
            <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
            
            <?= $content ?>
        </div>
    </main>

    <!-- Mobile FAB -->
    <a href="<?= Url::route('leads/create') ?>" class="btn btn-danger fab d-md-none">
        <i class="bi bi-plus-lg fs-4"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?= Url::asset('js/app.js') ?>"></script>
</body>
</html>
