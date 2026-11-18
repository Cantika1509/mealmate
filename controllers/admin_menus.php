<?php
require_once __DIR__ . '/../models/MenuModel.php';
require_once __DIR__ . '/../helpers/upload.php';

requireRole('Admin');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$viewMode = (($_REQUEST['view'] ?? 'kanban') === 'list') ? 'list' : 'kanban';

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    $redirectUrl = 'index.php?page=admin-menus&view=' . urlencode($viewMode);

    if ($action === 'create' || $action === 'update') {
        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $name = trim($_POST['menu_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $errors = [];

        if ($name === '') {
            $errors['menu_name'] = 'Nama menu wajib diisi.';
        } elseif (mb_strlen($name) > 100) {
            $errors['menu_name'] = 'Nama menu maksimal 100 karakter.';
        }

        if ($description === '') {
            $errors['description'] = 'Deskripsi menu wajib diisi.';
        }

        $existingMenu = ($action === 'update' && $menuId > 0) ? getMenuById($menuId) : null;
        if ($action === 'update' && !$existingMenu) {
            setFlash('danger', 'Data menu tidak ditemukan.');
            redirect($redirectUrl);
        }

        $imageName = $existingMenu['image'] ?? null;
        $hasFile = isset($_FILES['image']['error']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($hasFile && empty($errors)) {
            $upload = validateAndUploadImage($_FILES['image'], __DIR__ . '/../assets/img', 'menu_');
            if (!$upload['ok']) {
                $errors['image'] = $upload['error'];
            } else {
                $imageName = $upload['filename'];
            }
        }

        if (!empty($errors)) {
            redirectWithErrors($redirectUrl, $errors, [
                'modal_mode' => $action,
                'menu_id' => $menuId,
                'menu_name' => $name,
                'description' => $description,
            ]);
        }

        if ($action === 'create') {
            createMenu($name, $description, $imageName);
            setFlash('success', 'Menu baru berhasil ditambahkan.');
        } else {
            updateMenu($menuId, $name, $description, $imageName);
            setFlash('success', 'Data menu berhasil diperbarui.');
        }

        redirect($redirectUrl);
    }

    if ($action === 'delete') {
        $menuId = (int) ($_POST['menu_id'] ?? 0);
        if ($menuId <= 0 || !getMenuById($menuId)) {
            setFlash('danger', 'Menu tidak ditemukan.');
            redirect($redirectUrl);
        }

        if (isMenuInActiveOrders($menuId)) {
            setFlash('danger', 'Menu tidak dapat dihapus karena masih termasuk dalam paket langganan aktif.');
            redirect($redirectUrl);
        }

        deleteMenu($menuId);
        setFlash('success', 'Menu berhasil dihapus.');
        redirect($redirectUrl);
    }

    if ($action === 'bulk_delete') {
        $rawIds = $_POST['menu_ids'] ?? [];
        if (!is_array($rawIds) || empty($rawIds)) {
            setFlash('warning', 'Pilih minimal satu menu untuk dihapus.');
            redirect($redirectUrl);
        }

        $deletedCount = 0;
        $blockedCount = 0;

        foreach ($rawIds as $rawId) {
            $id = (int) $rawId;
            if ($id <= 0 || !getMenuById($id)) {
                continue;
            }
            if (isMenuInActiveOrders($id)) {
                $blockedCount++;
                continue;
            }
            deleteMenu($id);
            $deletedCount++;
        }

        if ($blockedCount > 0 && $deletedCount > 0) {
            setFlash('warning', "{$deletedCount} menu berhasil dihapus, namun {$blockedCount} menu ditolak karena masih termasuk dalam paket langganan aktif.");
        } elseif ($blockedCount > 0) {
            setFlash('danger', 'Menu yang dipilih tidak dapat dihapus karena masih termasuk dalam paket langganan aktif.');
        } else {
            setFlash('success', "{$deletedCount} menu berhasil dihapus.");
        }

        redirect($redirectUrl);
    }
}

$search = trim($_GET['q'] ?? '');
$perPage = ($viewMode === 'list') ? 15 : 6;
$totalMenus = countMenus($search);
$totalPages = max(1, (int) ceil($totalMenus / $perPage));
$currentPageNum = max(1, min($totalPages, (int) ($_GET['p'] ?? 1)));
$offset = ($currentPageNum - 1) * $perPage;
$menus = searchMenus($search, $perPage, $offset);

render('admin/menus', [
    'title' => 'Master Data Menu - MealMate',
    'menus' => $menus,
    'search' => $search,
    'viewMode' => $viewMode,
    'totalMenus' => $totalMenus,
    'totalPages' => $totalPages,
    'currentPageNum' => $currentPageNum,
    'perPage' => $perPage,
    'offset' => $offset,
    'errors' => getValidationErrors(),
    'old' => getOldInput(),
], 'admin');
