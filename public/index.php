<?php
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/flash.php';
require_once __DIR__ . '/../app/helpers/view.php';

initSession();

$page = $_GET['page'] ?? 'home';
$controllersDir = __DIR__ . '/../app/controllers/';

$routeMap = [
    'home' => $controllersDir . 'home.php',
    'menu' => $controllersDir . 'menu.php',
    'about' => $controllersDir . 'about.php',
    'faq' => $controllersDir . 'faq.php',
    'profile' => $controllersDir . 'profile.php',
    'checkout' => $controllersDir . 'checkout.php',
    'orders' => $controllersDir . 'orders.php',
    'order-detail' => $controllersDir . 'order_detail.php',
    'payment' => $controllersDir . 'payment.php',
    'review' => $controllersDir . 'review.php',

    'login' => $controllersDir . 'auth.php',
    'signup' => $controllersDir . 'auth.php',
    'logout' => $controllersDir . 'auth.php',
    'forgot-password' => $controllersDir . 'auth.php',
    'reset-password' => $controllersDir . 'auth.php',
    'change-password' => $controllersDir . 'auth.php',

    'admin-dashboard' => $controllersDir . 'admin_dashboard.php',
    'admin-menus' => $controllersDir . 'admin_menus.php',
    'admin-schedules' => $controllersDir . 'admin_schedules.php',
    'admin-packages' => $controllersDir . 'admin_packages.php',
    'admin-orders' => $controllersDir . 'admin_orders.php',
    'admin-order-detail' => $controllersDir . 'admin_order_detail.php',
    'admin-customers' => $controllersDir . 'admin_customers.php',
    'admin-reports' => $controllersDir . 'admin_reports.php',
    'admin-reviews' => $controllersDir . 'admin_reviews.php',
    'admin-search' => $controllersDir . 'admin_search.php',
];

if (isset($routeMap[$page]) && file_exists($routeMap[$page])) {
    require_once $routeMap[$page];
} else {
    http_response_code(404);
    require_once __DIR__ . '/../app/views/layouts/header.php';
    echo "<main class='container my-5 text-center'>
            <div class='py-5'>
                <h1 class='display-1 fw-bold text-danger'>404</h1>
                <h3 class='fw-semibold'>Halaman Tidak Ditemukan</h3>
                <p class='text-muted'>Halaman '" . htmlspecialchars($page) . "' belum dibuat atau tidak valid.</p>
                <a href='index.php' class='btn btn-success mt-3'>Kembali ke Beranda</a>
            </div>
          </main>";
    require_once __DIR__ . '/../app/views/layouts/footer.php';
}
