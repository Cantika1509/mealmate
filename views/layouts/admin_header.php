<?php
$adminUser = currentUser();
$currentPage = $_GET['page'] ?? 'admin-dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin - MealMate') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Kaushan+Script&family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout d-flex min-vh-100 w-100">
        <!-- Sidebar -->
        <?php include __DIR__ . '/admin_sidebar.php'; ?>

        <!-- Main Content Wrapper -->
        <div class="admin-main flex-grow-1 d-flex flex-column min-w-0">
            <!-- Topbar -->
            <header class="admin-topbar d-flex align-items-center justify-content-between px-4 py-3">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <button class="btn d-lg-none border-0 p-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarOffcanvas" aria-controls="adminSidebarOffcanvas" aria-label="Toggle Sidebar">
                        <i class="bi bi-list fs-3"></i>
                    </button>
                    <form action="index.php" method="get" class="admin-global-search position-relative d-none d-sm-block w-100" style="max-width: 420px;">
                        <input type="hidden" name="page" value="<?= htmlspecialchars($currentPage) ?>">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="form-control ps-5 rounded-3 border-0 shadow-xs" placeholder="Cari menu, paket, atau pesanan...">
                    </form>
                </div>

                <div class="dropdown">
                    <button class="btn border-0 d-flex align-items-center gap-3 p-0 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="admin-avatar rounded-circle d-flex align-items-center justify-content-center fw-bold text-white">
                            <?= htmlspecialchars(getInitials($adminUser['name'] ?? 'Admin')) ?>
                        </div>
                        <div class="d-none d-sm-flex flex-column lh-sm">
                            <span class="fw-semibold text-dark" style="font-size: 14px;"><?= htmlspecialchars($adminUser['name'] ?? 'Admin') ?></span>
                            <span class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($adminUser['role'] ?? 'Admin') ?></span>
                        </div>
                        <i class="bi bi-chevron-down text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                        <li>
                            <a class="dropdown-item py-2" href="index.php?page=home">
                                <i class="bi bi-globe2 me-2 text-success"></i>Lihat Halaman Pelanggan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="index.php?page=logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Log Out
                            </a>
                        </li>
                    </ul>
                </div>
            </header>

            <!-- Content Area -->
            <main class="admin-content px-4 pb-5 pt-2 flex-grow-1">
                <?= renderFlash($flash, 'shadow-sm mb-4') ?>
