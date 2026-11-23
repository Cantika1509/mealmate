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
$rawPage = filter_var($_GET['p'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
$currentPageNum = max(1, min($totalPages, $rawPage !== false ? $rawPage : 1));
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
