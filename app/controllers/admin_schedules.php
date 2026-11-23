<?php
require_once __DIR__ . '/../models/MenuModel.php';

requireRole('Admin');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $date = trim($_POST['date'] ?? '');
        $slot = trim($_POST['slot'] ?? '');
        $validMenuId = filter_var(trim((string) ($_POST['menu_id'] ?? '0')), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);
        $menuId = $validMenuId !== false ? $validMenuId : 0;
        $origDate = trim($_POST['original_date'] ?? '');
        $origSlot = trim($_POST['original_slot'] ?? '');
        $errors = [];

        $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        $year = $parsedDate ? (int) $parsedDate->format('Y') : 0;
        if ($date === '' || !$parsedDate || $parsedDate->format('Y-m-d') !== $date || $year < 2000 || $year > 2100) {
            $errors['date'] = 'Tanggal penyajian wajib diisi dengan format yang valid.';
        }

        if (!isValidSlot($slot)) {
            $errors['slot'] = 'Slot waktu wajib dipilih (Pagi, Siang, atau Malam).';
        }

        if ($menuId <= 0 || !getMenuById($menuId)) {
            $errors['menu_id'] = 'Menu hidangan wajib dipilih.';
        }

        $oldInput = [
            'modal_open' => '1',
            'date' => $date,
            'slot' => $slot,
            'menu_id' => $menuId,
            'original_date' => $origDate,
            'original_slot' => $origSlot,
        ];

        if (!empty($errors)) {
            redirectWithErrors('index.php?page=admin-schedules', $errors, $oldInput);
        }

        $isMoved = $origDate !== '' && $origSlot !== '' && ($origDate !== $date || $origSlot !== $slot);
        if ($isMoved && isScheduleInActiveOrders($origDate, $origSlot)) {
            setFlash('danger', 'Jadwal lama tidak dapat diubah tanggal/slot-nya karena terkait pesanan langganan aktif.');
            redirect('index.php?page=admin-schedules');
        }

        $pdo = getDBConnection();
        try {
            $pdo->beginTransaction();
            if ($isMoved) {
                deleteDailyMenu($origDate, $origSlot);
            }
            setDailyMenu($menuId, $date, $slot);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            redirectWithErrors('index.php?page=admin-schedules', [
                'general' => 'Gagal menyimpan jadwal menu. Pastikan tanggal dan menu sudah valid.',
            ], $oldInput);
        }

        setFlash('success', 'Jadwal menu berhasil disimpan.');
        redirect('index.php?page=admin-schedules');
    }

    if ($action === 'delete') {
        $date = trim($_POST['date'] ?? '');
        $slot = trim($_POST['slot'] ?? '');

        $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if ($date === '' || !$parsedDate || $parsedDate->format('Y-m-d') !== $date || !isValidSlot($slot)) {
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
$rawPage = filter_var($_GET['p'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
$currentPageNum = max(1, min($totalPages, $rawPage !== false ? $rawPage : 1));
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
