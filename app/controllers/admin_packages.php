<?php
require_once __DIR__ . '/../models/PackageModel.php';
require_once __DIR__ . '/../helpers/upload.php';

requireRole('Admin');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$redirectUrl = 'index.php?page=admin-packages';

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $packageId = (int) ($_POST['package_id'] ?? 0);
        $name = trim($_POST['package_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $durationRaw = trim($_POST['duration_days'] ?? '');
        $applicableDays = trim($_POST['applicable_days'] ?? '');
        $priceRaw = trim($_POST['price'] ?? '');
        $errors = [];

        if ($name === '') {
            $errors['package_name'] = 'Nama paket wajib diisi.';
        } elseif (mb_strlen($name) > 100) {
            $errors['package_name'] = 'Nama paket maksimal 100 karakter.';
        } elseif (isPackageNameExists($name, $packageId)) {
            $errors['package_name'] = 'Nama paket sudah digunakan.';
        }

        if ($durationRaw === '') {
            $errors['duration_days'] = 'Durasi hari wajib diisi.';
        } elseif (!ctype_digit($durationRaw) || (int) $durationRaw <= 0) {
            $errors['duration_days'] = 'Durasi hari harus berupa bilangan bulat positif.';
        }

        if ($applicableDays === '') {
            $errors['applicable_days'] = 'Hari berlaku wajib dipilih.';
        } elseif (!isValidApplicableDays($applicableDays)) {
            $errors['applicable_days'] = 'Pilihan hari berlaku tidak valid.';
        }

        $cleanPrice = str_replace(['.', ',', ' '], '', $priceRaw);
        if ($priceRaw === '') {
            $errors['price'] = 'Harga paket wajib diisi.';
        } elseif (!ctype_digit($cleanPrice) || (int) $cleanPrice <= 0) {
            $errors['price'] = 'Harga tidak valid. Masukkan nominal angka lebih dari 0.';
        }

        if ($description === '') {
            $errors['description'] = 'Deskripsi paket wajib diisi.';
        }

        $existingPackage = ($action === 'update' && $packageId > 0) ? getPackageById($packageId) : null;
        if ($action === 'update' && !$existingPackage) {
            setFlash('danger', 'Data paket langganan tidak ditemukan.');
            redirect($redirectUrl);
        }

        $imageName = $existingPackage['image'] ?? 'nasi-lemak.webp';
        $hasFile = isset($_FILES['image']['error']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($hasFile && empty($errors)) {
            $upload = validateAndUploadImage($_FILES['image'], __DIR__ . '/../../public/assets/img', 'package_');
            if (!$upload['ok']) {
                $errors['image'] = $upload['error'];
            } else {
                $imageName = $upload['filename'];
            }
        }

        if (!empty($errors)) {
            redirectWithErrors($redirectUrl, $errors, [
                'modal_mode' => $action,
                'package_id' => $packageId,
                'package_name' => $name,
                'description' => $description,
                'duration_days' => $durationRaw,
                'applicable_days' => $applicableDays,
                'price' => $priceRaw,
            ]);
        }

        $durationDays = (int) $durationRaw;
        $price = (int) $cleanPrice;

        if ($action === 'create') {
            createPackage($name, $description, $price, $durationDays, $applicableDays, $imageName);
            setFlash('success', 'Paket langganan baru berhasil ditambahkan.');
        } else {
            updatePackage($packageId, $name, $description, $price, $durationDays, $applicableDays, $imageName);
            setFlash('success', 'Data paket langganan berhasil diperbarui.');
        }

        redirect($redirectUrl);
    }

    if ($action === 'delete') {
        $packageId = (int) ($_POST['package_id'] ?? 0);
        if ($packageId <= 0 || !getPackageById($packageId)) {
            setFlash('danger', 'Paket langganan tidak ditemukan.');
            redirect($redirectUrl);
        }

        if (isPackageInActiveOrders($packageId)) {
            setFlash('danger', 'Paket tidak dapat dihapus karena masih digunakan dalam langganan aktif.');
            redirect($redirectUrl);
        }

        deletePackage($packageId);
        setFlash('success', 'Paket langganan berhasil dihapus.');
        redirect($redirectUrl);
    }
}

$search = trim($_GET['q'] ?? '');
$perPage = 8;
$totalPackages = countPackages($search);
$totalPages = max(1, (int) ceil($totalPackages / $perPage));
$currentPageNum = max(1, min($totalPages, (int) ($_GET['p'] ?? 1)));
$offset = ($currentPageNum - 1) * $perPage;
$packages = searchPackages($search, $perPage, $offset);

render('admin/packages', [
    'title' => 'Master Data Paket - MealMate',
    'packages' => $packages,
    'search' => $search,
    'totalPackages' => $totalPackages,
    'totalPages' => $totalPages,
    'currentPageNum' => $currentPageNum,
    'perPage' => $perPage,
    'offset' => $offset,
    'errors' => getValidationErrors(),
    'old' => getOldInput(),
], 'admin');
