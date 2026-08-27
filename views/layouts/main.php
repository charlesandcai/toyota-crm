<?php
$activePage = $activePage ?? 'dashboard';
$sidebarCollapsed = $_COOKIE['sidebar_collapsed'] ?? '0';
$faviconVer = Url::FAVICON_KEY;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toyota CRM</title>

    <!-- Apple touch icons -->
    <link rel="apple-touch-icon" sizes="57x57" href="<?= Url::asset('images/apple-icon-57x57.png') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" sizes="60x60" href="<?= Url::asset('images/apple-icon-60x60.png') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= Url::asset('images/apple-icon-72x72.png') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= Url::asset('images/apple-icon-76x76.png') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" sizes="114x114" href="<?= Url::asset('images/apple-icon-114x114.png') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?= Url::asset('images/apple-icon-120x120.png') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?= Url::asset('images/apple-icon-144x144.png') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= Url::asset('images/apple-icon-152x152.png') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= Url::asset('images/apple-icon-180x180.png') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon-precomposed" href="<?= Url::asset('images/apple-icon-precomposed.png') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" href="<?= Url::asset('images/apple-icon.png') ?>?v=<?= $faviconVer ?>">

    <!-- Standard favicons -->
    <link rel="icon" type="image/x-icon" href="<?= Url::base() ?>/favicon.ico?v=<?= $faviconVer ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= Url::asset('images/android-icon-192x192.png') ?>?v=<?= $faviconVer ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= Url::asset('images/favicon-96x96.png') ?>?v=<?= $faviconVer ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= Url::asset('images/favicon-32x32.png') ?>?v=<?= $faviconVer ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= Url::asset('images/favicon-16x16.png') ?>?v=<?= $faviconVer ?>">
    <link rel="shortcut icon" href="<?= Url::asset('images/favicon.ico') ?>?v=<?= $faviconVer ?>">

    <!-- Web app / MS tiles -->
    <link rel="manifest" href="<?= Url::base() ?>/manifest.json?v=<?= $faviconVer ?>">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?= Url::asset('images/ms-icon-144x144.png') ?>?v=<?= $faviconVer ?>">
    <meta name="msapplication-config" content="<?= Url::base() ?>/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

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
                <img src="<?= Url::asset('images/favicon-32x32.png') ?>?v=<?= $faviconVer ?>" alt="TSC" width="28" height="28" class="me-2 brand-logo">
                <span class="brand-text">Toyota CRM</span>
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
                        <li><span class="dropdown-item-text text-muted small">
                            <?= Security::escape($_SESSION['full_name'] ?? '') ?>
                            <?php if (Security::isAdmin()): ?>
                                <span class="badge bg-danger ms-1">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-1">User</span>
                            <?php endif; ?>
                        </span></li>
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
            <h5 class="offcanvas-title text-white"><img src="<?= Url::asset('images/favicon-32x32.png') ?>?v=<?= $faviconVer ?>" alt="TSC" width="24" height="24" class="me-2 brand-logo"> Toyota CRM</h5>
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
