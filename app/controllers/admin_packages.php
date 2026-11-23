<?php
require_once __DIR__ . '/../models/PackageModel.php';
require_once __DIR__ . '/../helpers/upload.php';

requireRole('Admin');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$redirectUrl = 'index.php?page=admin-packages';

if ($method === 'POST') {
    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        redirectWithErrors($redirectUrl, [
            'image' => 'Ukuran berkas terlalu besar melebihi batas unggahan server.',
        ], ['modal_mode' => 'create']);
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $rawPackageId = trim((string) ($_POST['package_id'] ?? '0'));
        $validPackageId = filter_var($rawPackageId, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 2147483647],
        ]);
        $packageId = $validPackageId !== false ? $validPackageId : 0;

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

        $normalizedDuration = ltrim($durationRaw, '0');
        $validDuration = ($durationRaw !== '' && ctype_digit($durationRaw) && $normalizedDuration !== '')
            ? filter_var($normalizedDuration, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 365]])
            : false;

        if ($durationRaw === '') {
            $errors['duration_days'] = 'Durasi hari wajib diisi.';
        } elseif (!ctype_digit($durationRaw) || $normalizedDuration === '') {
            $errors['duration_days'] = 'Durasi hari harus berupa bilangan bulat positif.';
        } elseif ($validDuration === false) {
            $errors['duration_days'] = 'Durasi hari tidak valid (maksimal 365 hari).';
        }

        if ($applicableDays === '') {
            $errors['applicable_days'] = 'Hari berlaku wajib dipilih.';
        } elseif (!isValidApplicableDays($applicableDays)) {
            $errors['applicable_days'] = 'Pilihan hari berlaku tidak valid.';
        }

        $cleanPrice = str_replace(['.', ',', ' '], '', $priceRaw);
        $normalizedPrice = ltrim($cleanPrice, '0');
        $validPrice = ($priceRaw !== '' && ctype_digit($cleanPrice) && $normalizedPrice !== '')
            ? filter_var($normalizedPrice, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2000000000]])
            : false;

        if ($priceRaw === '') {
            $errors['price'] = 'Harga paket wajib diisi.';
        } elseif (!ctype_digit($cleanPrice) || $normalizedPrice === '') {
            $errors['price'] = 'Harga tidak valid. Masukkan nominal angka lebih dari 0.';
        } elseif ($validPrice === false) {
            $errors['price'] = 'Harga paket terlalu besar (maksimal Rp 2.000.000.000).';
        }

        if ($description === '') {
            $errors['description'] = 'Deskripsi paket wajib diisi.';
        } elseif (mb_strlen($description) > 5000) {
            $errors['description'] = 'Deskripsi paket maksimal 5000 karakter.';
        }

        $existingPackage = ($action === 'update' && $packageId > 0) ? getPackageById($packageId) : null;
        if ($action === 'update' && !$existingPackage) {
            setFlash('danger', 'Data paket langganan tidak ditemukan.');
            redirect($redirectUrl);
        }

        $imageName = $existingPackage['image'] ?? 'nasi-lemak.webp';
        $hasFile = isset($_FILES['image']['error']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($hasFile) {
            $upload = validateAndUploadImage($_FILES['image'], __DIR__ . '/../../public/assets/img', 'package_');
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
            'package_id' => $packageId,
            'package_name' => $name,
            'description' => $description,
            'duration_days' => $durationRaw,
            'applicable_days' => $applicableDays,
            'price' => $priceRaw,
        ];

        if (!empty($errors)) {
            redirectWithErrors($redirectUrl, $errors, $oldInput);
        }

        $durationDays = $validDuration;
        $price = $validPrice;

        try {
            if ($action === 'create') {
                createPackage($name, $description, $price, $durationDays, $applicableDays, $imageName);
                setFlash('success', 'Paket langganan baru berhasil ditambahkan.');
            } else {
                updatePackage($packageId, $name, $description, $price, $durationDays, $applicableDays, $imageName);
                setFlash('success', 'Data paket langganan berhasil diperbarui.');
            }
        } catch (Throwable $e) {
            redirectWithErrors($redirectUrl, [
                'general' => 'Gagal menyimpan paket. Pastikan seluruh data dan nominal harga sudah valid.',
            ], $oldInput);
        }

        redirect($redirectUrl);
    }

    if ($action === 'delete') {
        $rawPackageId = trim((string) ($_POST['package_id'] ?? '0'));
        $validPackageId = filter_var($rawPackageId, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);
        $packageId = $validPackageId !== false ? $validPackageId : 0;
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
$rawPage = filter_var($_GET['p'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
$currentPageNum = max(1, min($totalPages, $rawPage !== false ? $rawPage : 1));
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
