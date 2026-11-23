<?php
require_once __DIR__ . '/../models/UserModel.php';

requireRole('Admin');

$search = trim($_GET['q'] ?? '');
$perPage = 8;
$totalCustomers = countCustomers($search);
$totalAllCustomers = countCustomers('');
$totalPages = max(1, (int) ceil($totalCustomers / $perPage));
$currentPageNum = max(1, min($totalPages, (int) ($_GET['p'] ?? 1)));
$offset = ($currentPageNum - 1) * $perPage;
$customers = getCustomers($search, $perPage, $offset);

render('admin/customers', [
    'title' => 'Kelola Data Pelanggan - MealMate',
    'customers' => $customers,
    'search' => $search,
    'totalCustomers' => $totalCustomers,
    'totalAllCustomers' => $totalAllCustomers,
    'totalPages' => $totalPages,
    'currentPageNum' => $currentPageNum,
    'perPage' => $perPage,
    'offset' => $offset,
    'startRow' => $totalCustomers > 0 ? $offset + 1 : 0,
    'endRow' => min($offset + $perPage, $totalCustomers),
    'errors' => getValidationErrors(),
    'old' => getOldInput(),
], 'admin');
