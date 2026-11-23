<?php
require_once __DIR__ . '/../models/UserModel.php';

requireRole('Admin');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$redirectUrl = 'index.php?page=admin-customers';

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phoneRaw = trim($_POST['phone_number'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if ($action === 'update' && ($userId <= 0 || !findCustomerById($userId))) {
            setFlash('danger', 'Data pelanggan tidak ditemukan.');
            redirect($redirectUrl);
        }

        if ($name === '') {
            $errors['name'] = 'Nama lengkap wajib diisi.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Nama lengkap maksimal 100 karakter.';
        }

        if ($email === '') {
            $errors['email'] = 'Email wajib diisi.';
        } elseif (mb_strlen($email) > 100) {
            $errors['email'] = 'Email maksimal 100 karakter.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif ($action === 'create' ? isEmailTaken($email) : isEmailTakenByOther($email, $userId)) {
            $errors['email'] = 'Email sudah terdaftar';
        }

        $phone = normalizePhoneNumber($phoneRaw);
        if ($phoneRaw === '') {
            $errors['phone_number'] = 'Nomor telepon wajib diisi.';
        } elseif ($phone === null) {
            $errors['phone_number'] = 'Format nomor telepon tidak valid (9-15 digit angka).';
        }

        if (mb_strlen($address) > 255) {
            $errors['address'] = 'Alamat maksimal 255 karakter.';
        }

        if ($action === 'create' && $password === '') {
            $errors['password'] = 'Password wajib diisi.';
        } elseif ($password !== '' && strlen($password) < 8) {
            $errors['password'] = 'Password minimal 8 karakter.';
        } elseif (strlen($password) > 72) {
            $errors['password'] = 'Password maksimal 72 karakter.';
        }

        $old = [
            'modal_mode' => $action,
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone_number' => $phoneRaw,
            'address' => $address,
        ];

        if (!empty($errors)) {
            redirectWithErrors($redirectUrl, $errors, $old);
        }

        $pdo = getDBConnection();

        try {
            $pdo->beginTransaction();

            if ($action === 'create') {
                createCustomerUser($name, $email, $phone, $password, $address !== '' ? $address : null);
            } else {
                updateCustomerProfile($userId, $name, $email, $phone, $address !== '' ? $address : null);
                if ($password !== '') {
                    updateUserPassword($userId, $password);
                }
            }

            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() === '23000') {
                redirectWithErrors($redirectUrl, ['email' => 'Email sudah terdaftar'], $old);
            }
            error_log($e->getMessage());
            setFlash('danger', 'Data pelanggan gagal disimpan. Silakan coba lagi.');
            redirect($redirectUrl);
        }

        setFlash('success', $action === 'create' ? 'Pelanggan baru berhasil ditambahkan.' : 'Data pelanggan berhasil diperbarui.');
        redirect($redirectUrl);
    }

    if ($action === 'delete') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $customer = $userId > 0 ? findCustomerById($userId) : null;

        if (!$customer) {
            setFlash('danger', 'Pelanggan tidak ditemukan.');
            redirect($redirectUrl);
        }

        if (customerHasProtectedOrders($userId)) {
            setFlash('danger', "Akun {$customer['name']} tidak dapat dihapus karena memiliki pesanan yang sudah dibayar atau sedang diproses.");
            redirect($redirectUrl);
        }

        if (!deleteCustomerIfNoProtectedOrders($userId)) {
            setFlash('danger', 'Pelanggan gagal dihapus. Silakan coba lagi.');
            redirect($redirectUrl);
        }

        setFlash('success', "Akun pelanggan {$customer['name']} berhasil dihapus.");
        redirect($redirectUrl);
    }
}

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
