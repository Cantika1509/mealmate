<?php
require_once __DIR__ . '/../models/UserModel.php';

requireRole('Pelanggan');

$user = findUserById(currentUser()['user_id']);

if (!$user) {
    session_unset();
    session_destroy();
    session_start();
    setFlash('warning', 'Akun Anda tidak ditemukan. Silakan login kembali.');
    redirect('index.php?page=login');
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
