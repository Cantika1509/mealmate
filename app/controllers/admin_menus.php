<?php
require_once __DIR__ . '/../models/MenuModel.php';
require_once __DIR__ . '/../helpers/upload.php';

requireRole('Admin');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$viewMode = (($_REQUEST['view'] ?? 'kanban') === 'list') ? 'list' : 'kanban';

if ($method === 'POST') {
    $redirectUrl = 'index.php?page=admin-menus&view=' . urlencode($viewMode);

    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        redirectWithErrors($redirectUrl, [
            'image' => 'Ukuran berkas terlalu besar melebihi batas unggahan server.',
        ], ['modal_mode' => 'create']);
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $validMenuId = filter_var(trim((string) ($_POST['menu_id'] ?? '0')), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 2147483647],
        ]);
        $menuId = $validMenuId !== false ? $validMenuId : 0;
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
        } elseif (mb_strlen($description) > 5000) {
            $errors['description'] = 'Deskripsi menu maksimal 5000 karakter.';
        }

        $existingMenu = ($action === 'update' && $menuId > 0) ? getMenuById($menuId) : null;
        if ($action === 'update' && !$existingMenu) {
            setFlash('danger', 'Data menu tidak ditemukan.');
            redirect($redirectUrl);
        }

        $imageName = $existingMenu['image'] ?? null;
        $hasFile = isset($_FILES['image']['error']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($hasFile) {
            $upload = validateAndUploadImage($_FILES['image'], __DIR__ . '/../../public/assets/img', 'menu_');
            if (!$upload['ok']) {
                $errors['image'] = $upload['error'];
            } elseif (empty($errors)) {
                $imageName = $upload['filename'];
            } else {
                @unlink(__DIR__ . '/../../public/assets/img/' . $upload['filename']);
            }
        }

        $oldInput = [
            'modal_mode' => $action,
            'menu_id' => $menuId,
            'menu_name' => $name,
            'description' => $description,
        ];

        if (!empty($errors)) {
            redirectWithErrors($redirectUrl, $errors, $oldInput);
        }

        try {
            if ($action === 'create') {
                createMenu($name, $description, $imageName);
                setFlash('success', 'Menu baru berhasil ditambahkan.');
            } else {
                updateMenu($menuId, $name, $description, $imageName);
                setFlash('success', 'Data menu berhasil diperbarui.');
            }
        } catch (Throwable $e) {
            redirectWithErrors($redirectUrl, [
                'general' => 'Gagal menyimpan data menu. Silakan periksa kembali masukan Anda.',
            ], $oldInput);
        }

        redirect($redirectUrl);
    }

    if ($action === 'delete') {
        $validMenuId = filter_var(trim((string) ($_POST['menu_id'] ?? '0')), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);
        $menuId = $validMenuId !== false ? $validMenuId : 0;
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
            $validId = filter_var(trim((string) $rawId), FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 2147483647],
            ]);
            $id = $validId !== false ? $validId : 0;
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
$perPage = ($viewMode === 'list') ? 15 : 8;
$totalMenus = countMenus($search);
$totalPages = max(1, (int) ceil($totalMenus / $perPage));
$rawPage = filter_var($_GET['p'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
$currentPageNum = max(1, min($totalPages, $rawPage !== false ? $rawPage : 1));
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
