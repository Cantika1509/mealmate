<?php
require_once __DIR__ . '/../models/MenuModel.php';

requireRole('Admin');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $date = trim($_POST['date'] ?? '');
        $slot = trim($_POST['slot'] ?? '');
        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $origDate = trim($_POST['original_date'] ?? '');
        $origSlot = trim($_POST['original_slot'] ?? '');
        $errors = [];

        $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if ($date === '' || !$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
            $errors['date'] = 'Tanggal penyajian wajib diisi dengan format yang valid.';
        }

        if (!isValidSlot($slot)) {
            $errors['slot'] = 'Slot waktu wajib dipilih (Pagi, Siang, atau Malam).';
        }

        if ($menuId <= 0 || !getMenuById($menuId)) {
            $errors['menu_id'] = 'Menu hidangan wajib dipilih.';
        }

        if (!empty($errors)) {
            redirectWithErrors('index.php?page=admin-schedules', $errors, [
                'modal_open' => '1',
                'date' => $date,
                'slot' => $slot,
                'menu_id' => $menuId,
                'original_date' => $origDate,
                'original_slot' => $origSlot,
            ]);
        }

        $isMoved = $origDate !== '' && $origSlot !== '' && ($origDate !== $date || $origSlot !== $slot);
        if ($isMoved && isScheduleInActiveOrders($origDate, $origSlot)) {
            setFlash('danger', 'Jadwal lama tidak dapat diubah tanggal/slot-nya karena terkait pesanan langganan aktif.');
            redirect('index.php?page=admin-schedules');
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        if ($isMoved) {
            deleteDailyMenu($origDate, $origSlot);
        }
        setDailyMenu($menuId, $date, $slot);
        $pdo->commit();

        setFlash('success', 'Jadwal menu berhasil disimpan.');
        redirect('index.php?page=admin-schedules');
    }

    if ($action === 'delete') {
        $date = trim($_POST['date'] ?? '');
        $slot = trim($_POST['slot'] ?? '');

        if ($date === '' || !isValidSlot($slot)) {
            setFlash('danger', 'Data jadwal tidak valid.');
            redirect('index.php?page=admin-schedules');
        }

        if (isScheduleInActiveOrders($date, $slot)) {
            setFlash('danger', 'Jadwal tidak dapat dihapus karena masih termasuk dalam paket langganan aktif.');
            redirect('index.php?page=admin-schedules');
        }

        deleteDailyMenu($date, $slot);
        setFlash('success', 'Jadwal menu berhasil dihapus.');
        redirect('index.php?page=admin-schedules');
    }
}

$search = trim($_GET['q'] ?? '');
$perPage = 4;
$totalDates = countScheduledDates($search);
$totalPages = max(1, (int) ceil($totalDates / $perPage));
$currentPageNum = max(1, min($totalPages, (int) ($_GET['p'] ?? 1)));
$offset = ($currentPageNum - 1) * $perPage;
$dates = getScheduledDatesPaginated($search, $perPage, $offset);
$schedulesByDate = getSchedulesByDates($dates);
$allMenus = getAllMenus();

render('admin/schedules', [
    'title' => 'Kelola Jadwal Menu - MealMate',
    'dates' => $dates,
    'schedulesByDate' => $schedulesByDate,
    'allMenus' => $allMenus,
    'search' => $search,
    'totalDates' => $totalDates,
    'totalPages' => $totalPages,
    'currentPageNum' => $currentPageNum,
    'errors' => getValidationErrors(),
    'old' => getOldInput(),
], 'admin');
