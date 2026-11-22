<?php
require_once __DIR__ . '/../models/UserModel.php';

requireRole('Pelanggan');

$userId = currentUser()['user_id'];
$user = findUserById($userId);

if (!$user) {
    session_unset();
    session_destroy();
    session_start();
    setFlash('warning', 'Akun Anda tidak ditemukan. Silakan login kembali.');
    redirect('index.php?page=login');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $emailChanged = $email !== strtolower($user['email']);
    $errors = [];

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
    } elseif ($emailChanged && isEmailTakenByOther($email, $userId)) {
        $errors['email'] = 'Email sudah terdaftar';
    }

    if ($phone === '') {
        $errors['phone_number'] = 'Nomor telepon wajib diisi.';
    } elseif (!preg_match('/^[0-9+\-\s]{9,15}$/', $phone)) {
        $errors['phone_number'] = 'Format nomor telepon tidak valid (9-15 digit angka).';
    }

    if (mb_strlen($address) > 255) {
        $errors['address'] = 'Alamat maksimal 255 karakter.';
    }

    if ($emailChanged && empty($errors['email'])) {
        if ($currentPassword === '') {
            $errors['current_password'] = 'Password wajib diisi untuk mengubah email.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors['current_password'] = 'Password salah.';
        }
    }

    $old = [
        'name' => $name,
        'email' => $email,
        'phone_number' => $phone,
        'address' => $address,
    ];

    if (!empty($errors)) {
        redirectWithErrors('index.php?page=profile', $errors, $old);
    }

    $saved = false;

    try {
        $saved = updateCustomerProfile(
            $userId,
            $name,
            $emailChanged ? $email : $user['email'],
            $phone,
            $address !== '' ? $address : null
        );
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            redirectWithErrors('index.php?page=profile', ['email' => 'Email sudah terdaftar'], $old);
        }
        error_log($e->getMessage());
    }

    if (!$saved) {
        setFlash('danger', 'Profil gagal diperbarui. Silakan coba lagi.');
        redirect('index.php?page=profile');
    }

    if ($emailChanged) {
        session_unset();
        session_destroy();
        session_start();
        setFlash('success', 'Email berhasil diubah. Silakan login kembali dengan email baru.');
        redirect('index.php?page=login');
    }

    $_SESSION['name'] = $name;
    setFlash('success', 'Profil berhasil diperbarui.');
    redirect('index.php?page=profile');
}

$profile = [
    'name' => $user['name'],
    'email' => $user['email'],
    'phone_number' => $user['phone_number'] ?? '',
    'address' => $user['address'] ?? '',
];

render('customer/profile', [
    'title' => 'Profil Saya - MealMate',
    'hideFooter' => true,
    'profile' => $profile,
    'form' => array_merge($profile, getOldInput()),
    'errors' => getValidationErrors(),
]);