<?php
require_once __DIR__ . '/../models/OrderModel.php';

requireRole('Admin');

$statusFilter = trim($_GET['status'] ?? 'Semua');
if ($statusFilter !== 'Semua' && !in_array($statusFilter, getValidOrderStatuses(), true)) {
    $statusFilter = 'Semua';
}

$search = trim($_GET['q'] ?? '');
$perPage = 10;
$totalOrders = countAdminOrders($statusFilter, $search);
$totalAllOrders = countAdminOrders('Semua', '');
$totalPages = max(1, (int) ceil($totalOrders / $perPage));
$currentPageNum = max(1, min($totalPages, (int) ($_GET['p'] ?? 1)));
$offset = ($currentPageNum - 1) * $perPage;
$orders = getAdminOrders($statusFilter, $search, $perPage, $offset);

render('admin/orders', [
    'title' => 'Daftar Pesanan - MealMate',
    'orders' => $orders,
    'statusFilter' => $statusFilter,
    'search' => $search,
    'totalOrders' => $totalOrders,
    'totalAllOrders' => $totalAllOrders,
    'totalPages' => $totalPages,
    'currentPageNum' => $currentPageNum,
    'perPage' => $perPage,
    'offset' => $offset,
], 'admin');
