<?php
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/flash.php';
require_once __DIR__ . '/helpers/view.php';

initSession();

$page = $_GET['page'] ?? 'home';

$routeMap = [
    'home' => './controllers/home.php',
    'menu' => './controllers/menu.php',
    'about' => './controllers/about.php',
    'faq' => './controllers/faq.php',
    'profile' => './controllers/profile.php',
    'checkout' => './controllers/checkout.php',
    'orders' => './controllers/orders.php',
    'order-detail' => './controllers/order_detail.php',
    'payment' => './controllers/payment.php',
    'review' => './controllers/review.php',

    // Autentikasi 
    'login' => './controllers/auth.php',
    'signup' => './controllers/auth.php',
    'logout' => './controllers/auth.php',
    'forgot-password' => './controllers/auth.php',
    'reset-password' => './controllers/auth.php',

    // Halaman Admin
    'admin-dashboard' => './controllers/admin_dashboard.php',
    'admin-menus' => './controllers/admin_menus.php',
    'admin-schedules' => './controllers/admin_schedules.php',
    'admin-packages' => './controllers/admin_packages.php',
    'admin-orders' => './controllers/admin_orders.php',
    'admin-order-detail' => './controllers/admin_order_detail.php',
    'admin-customers' => './controllers/admin_customers.php',
    'admin-reports' => './controllers/admin_reports.php',
    'admin-reviews' => './controllers/admin_reviews.php',
    'admin-search' => './controllers/admin_search.php',
];

if (isset($routeMap[$page]) && file_exists($routeMap[$page])) {
    require_once $routeMap[$page];
} else {
    http_response_code(404);
    require_once './views/layouts/header.php';
    echo "<main class='container my-5 text-center'>
            <div class='py-5'>
                <h1 class='display-1 fw-bold text-danger'>404</h1>
                <h3 class='fw-semibold'>Halaman Tidak Ditemukan</h3>
                <p class='text-muted'>Halaman '" . htmlspecialchars($page) . "' belum dibuat atau tidak valid.</p>
                <a href='index.php' class='btn btn-success mt-3'>Kembali ke Beranda</a>
            </div>
          </main>";
    require_once './views/layouts/footer.php';
}