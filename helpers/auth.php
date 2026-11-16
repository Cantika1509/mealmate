<?php
require_once __DIR__ . '/flash.php';

const SESSION_TIMEOUT_SECONDS = 1800; 

function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['user_id'], $_SESSION['last_activity'])) {
        if ((time() - (int) $_SESSION['last_activity']) > SESSION_TIMEOUT_SECONDS) {
            session_unset();
            session_destroy();
            session_start();
            setFlash('warning', 'Sesi Anda telah berakhir karena tidak aktif terlalu lama. Silakan login kembali.');
            redirect('index.php?page=login');
        }
    }

    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'user_id' => (int) $_SESSION['user_id'],
        'name'    => (string) ($_SESSION['name'] ?? ''),
        'email'   => (string) ($_SESSION['email'] ?? ''),
        'role'    => (string) ($_SESSION['role'] ?? 'Pelanggan'),
    ];
}

function requireLogin(?string $returnUrl = null): void {
    if (!isLoggedIn()) {
        if ($returnUrl !== null && $returnUrl !== '') {
            $_SESSION['redirect_after_login'] = $returnUrl;
        } elseif (!empty($_SERVER['QUERY_STRING'])) {
            $_SESSION['redirect_after_login'] = 'index.php?' . $_SERVER['QUERY_STRING'];
        }
        setFlash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.');
        redirect('index.php?page=login');
    }
}

function requireRole(string $role): void {
    requireLogin();
    $currentRole = $_SESSION['role'] ?? '';
    if ($currentRole !== $role) {
        setFlash('danger', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect($currentRole === 'Admin' ? 'index.php?page=admin-dashboard' : 'index.php?page=home');
    }
}

function requireGuest(): void {
    if (isLoggedIn()) {
        redirect(($_SESSION['role'] ?? '') === 'Admin' ? 'index.php?page=admin-dashboard' : 'index.php?page=home');
    }
}
