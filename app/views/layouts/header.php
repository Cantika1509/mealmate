<?php
$currentPage = $_GET['page'] ?? 'home';
$flash = $flash ?? getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'MealMate') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css"> 
    <link rel="stylesheet" href="./assets/css/footer.css">
    <link rel="stylesheet" href="./assets/css/navbar.css">
    <?php if ($currentPage === 'home'): ?>
        <link rel="stylesheet" href="./assets/css/home.css">
    <?php elseif ($currentPage === 'menu'): ?>
        <link rel="stylesheet" href="./assets/css/menu.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&family=Kaushan+Script&family=Poppins:wght@400;500;600;700&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include __DIR__ . '/navbar.php'; ?>

    <?php if (!empty($flash)): ?>
        <div class="container mt-3">
            <?= renderFlash($flash, 'shadow-sm mb-0') ?>
        </div>
    <?php endif; ?>
