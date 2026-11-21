<?php
$currentPage = $_GET['page'] ?? 'admin-dashboard';

$navGroups = [
    'UTAMA' => [
        ['page' => 'admin-dashboard', 'icon' => 'bi-house-door', 'label' => 'Dashboard'],
    ],
    'MASTER DATA' => [
        ['page' => 'admin-menus', 'icon' => 'bi-fork-knife', 'label' => 'Menu'],
        ['page' => 'admin-packages', 'icon' => 'bi-box-seam', 'label' => 'Kelola Paket'],
    ],
    'OPERASIONAL & TRANSAKSI' => [
        ['page' => 'admin-schedules', 'icon' => 'bi-calendar3', 'label' => 'Jadwal Menu', 'match' => ['admin-schedules']],
        ['page' => 'admin-orders', 'icon' => 'bi-credit-card', 'label' => 'Pesanan & Pembayaran', 'match' => ['admin-orders', 'admin-order-detail']],
        ['page' => 'admin-customers', 'icon' => 'bi-people', 'label' => 'Data Pelanggan', 'match' => ['admin-customers']],
    ],
    'ANALISIS' => [
        ['page' => 'admin-reports', 'icon' => 'bi-bar-chart', 'label' => 'Laporan'],
        ['page' => 'admin-reviews', 'icon' => 'bi-star', 'label' => 'Ulasan Pelanggan'],
    ],
];
?>

<aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebarOffcanvas" aria-labelledby="adminSidebarLabel">
    <div class="offcanvas-body d-flex flex-column justify-content-between py-4 px-3 w-100 h-100">
        <div>
            <div class="d-flex align-items-center justify-content-between px-2 mb-4">
                <a href="index.php?page=admin-dashboard" class="logo d-flex align-items-center gap-2 text-decoration-none text-dark" id="adminSidebarLabel">
                    <img src="./assets/img/logo.png" alt="MealMate Logo" width="44" height="44">
                    <h2 class="mb-0" style="font-family: 'Kaushan Script', cursive; font-size: 24px;">Meal Mate</h2>
                </a>
                <button type="button" class="btn-close d-lg-none shadow-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebarOffcanvas" aria-label="Close"></button>
            </div>

            <?php foreach ($navGroups as $groupTitle => $items): ?>
                <div class="admin-nav-group mb-3">
                    <p class="admin-nav-section-title px-3 mb-1"><?= htmlspecialchars($groupTitle) ?></p>
                    <ul class="nav flex-column gap-1">
                        <?php foreach ($items as $item):
                            $matchPages = $item['match'] ?? [$item['page']];
                            $isActive = in_array($currentPage, $matchPages, true);
                        ?>
                            <li class="nav-item">
                                <a href="index.php?page=<?= htmlspecialchars($item['page']) ?>"
                                   class="admin-nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-3 text-decoration-none <?= $isActive ? 'active' : '' ?>">
                                    <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i>
                                    <span><?= htmlspecialchars($item['label']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pt-3 border-top mt-3">
            <a href="index.php?page=logout" class="admin-nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-3 text-decoration-none text-dark">
                <i class="bi bi-box-arrow-right"></i>
                <span>Log Out</span>
            </a>
        </div>
    </div>
</aside>
