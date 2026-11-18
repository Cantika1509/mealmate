<?php
require_once __DIR__ . '/../models/UserModel.php';

$page = $_GET['page'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($page) {
    case 'login':
        requireGuest();

        if ($method === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $errors = [];

            if ($email === '') {
                $errors['email'] = 'Email wajib diisi.';
            }
            if ($password === '') {
                $errors['password'] = 'Password wajib diisi.';
            }
            if (!empty($errors)) {
                redirectWithErrors('index.php?page=login', $errors, ['email' => $email]);
            }

            $user = findUserByEmail($email);
            if (!$user || !password_verify($password, $user['password'])) {
                redirectWithErrors('index.php?page=login', ['general' => 'Email atau password salah'], ['email' => $email]);
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            $redirectUrl = ($user['role'] === 'Admin')
                ? 'index.php?page=admin-dashboard'
                : ($_SESSION['redirect_after_login'] ?? 'index.php?page=home');
            unset($_SESSION['redirect_after_login']);
            redirect($redirectUrl);
        }

        render('auth/login', [
            'title' => 'Login - MealMate',
            'errors' => getValidationErrors(),
            'old' => getOldInput(),
        ], 'auth');
        break;

    case 'signup':
        requireGuest();

        if ($method === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone_number'] ?? '');
            $password = $_POST['password'] ?? '';
            $errors = [];

            if ($name === '') {
                $errors['name'] = 'Nama lengkap wajib diisi.';
            } elseif (mb_strlen($name) > 100) {
                $errors['name'] = 'Nama lengkap maksimal 100 karakter.';
            }

            if ($email === '') {
                $errors['email'] = 'Email wajib diisi.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Format email tidak valid.';
            } elseif (isEmailTaken($email)) {
                $errors['email'] = 'Email sudah terdaftar';
            }

            if ($phone === '') {
                $errors['phone_number'] = 'Nomor telepon wajib diisi.';
            } elseif (!preg_match('/^[0-9+\-\s]{9,15}$/', $phone)) {
                $errors['phone_number'] = 'Format nomor telepon tidak valid (9-15 digit angka).';
            }

            if ($password === '') {
                $errors['password'] = 'Password wajib diisi.';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password tidak memenuhi ketentuan: minimal 8 karakter.';
            }

            if (!empty($errors)) {
                redirectWithErrors('index.php?page=signup', $errors, [
                    'name' => $name,
                    'email' => $email,
                    'phone_number' => $phone,
                ]);
            }

            createCustomerUser($name, $email, $phone, $password);
            setFlash('success', 'Pendaftaran akun berhasil! Silakan masuk menggunakan email dan password Anda.');
            redirect('index.php?page=login');
        }

        render('auth/signup', [
            'title' => 'Daftar Akun - MealMate',
            'errors' => getValidationErrors(),
            'old' => getOldInput(),
        ], 'auth');
        break;

    case 'logout':
        session_unset();
        session_destroy();
        session_start();
        setFlash('success', 'Anda telah berhasil keluar dari sesi.');
        redirect('index.php?page=login');

    case 'forgot-password':
        requireGuest();

        if ($method === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $errors = [];

            if ($email === '') {
                $errors['email'] = 'Email wajib diisi.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Format email tidak valid.';
            } else {
                $user = findUserByEmail($email);
                if (!$user) {
                    $errors['email'] = 'Email tidak ditemukan pada sistem.';
                }
            }

            if (!empty($errors)) {
                redirectWithErrors('index.php?page=forgot-password', $errors, ['email' => $email]);
            }

            $token = bin2hex(random_bytes(16));
            $_SESSION['password_reset'] = [
                'user_id' => (int) $user['user_id'],
                'email' => $user['email'],
                'token' => $token,
                'expires' => time() + 900, // 15 menit
            ];

            redirect('index.php?page=reset-password&token=' . urlencode($token));
        }

        render('auth/forgot_password', [
            'title' => 'Reset Password - MealMate',
            'errors' => getValidationErrors(),
            'old' => getOldInput(),
        ], 'auth');
        break;


    case 'reset-password':
        requireGuest();

        $token = $_GET['token'] ?? ($_POST['token'] ?? '');
        $resetSession = $_SESSION['password_reset'] ?? null;

        if (
            !$resetSession ||
            empty($token) ||
            !hash_equals($resetSession['token'], $token) ||
            time() > (int) $resetSession['expires']
        ) {
            unset($_SESSION['password_reset']);
            setFlash('warning', 'Tautan pemulihan kata sandi tidak valid atau telah kedaluwarsa. Silakan masukkan kembali email Anda.');
            redirect('index.php?page=forgot-password');
        }

        if ($method === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $errors = [];

            $user = findUserById((int) $resetSession['user_id']);
            if (!$user) {
                unset($_SESSION['password_reset']);
                setFlash('danger', 'Akun pengguna tidak ditemukan.');
                redirect('index.php?page=forgot-password');
            }

            if ($password === '') {
                $errors['password'] = 'Password baru wajib diisi.';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password baru minimal 8 karakter.';
            } elseif (password_verify($password, $user['password'])) {
                $errors['password'] = 'Password baru harus berbeda dari password yang digunakan sebelumnya.';
            }

            if ($confirmPassword === '') {
                $errors['confirm_password'] = 'Konfirmasi password wajib diisi.';
            } elseif ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Konfirmasi password tidak cocok.';
            }

            if (!empty($errors)) {
                redirectWithErrors('index.php?page=reset-password&token=' . urlencode($token), $errors);
            }

            updateUserPassword((int) $user['user_id'], $password);
            unset($_SESSION['password_reset']);
            setFlash('success', 'Kata sandi Anda berhasil diperbarui! Silakan login dengan kata sandi baru.');
            redirect('index.php?page=login');
        }

        render('auth/reset_password', [
            'title' => 'Buat Kata Sandi Baru - MealMate',
            'token' => $token,
            'email' => $resetSession['email'],
            'errors' => getValidationErrors(),
        ], 'auth');
        break;
}
