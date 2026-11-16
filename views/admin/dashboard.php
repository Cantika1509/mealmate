<?php
$adminUser = currentUser();
?>
<div class="mb-4">
    <h1 class="admin-page-title mb-1">Selamat datang, <span style="color: #D96B38;"><?= htmlspecialchars($adminUser['name'] ?? 'Admin') ?></span></h1>
    <p class="text-muted">Ringkasan katering MealMate</p>
</div>

<div class="admin-card p-4">
    <p class="mb-0 text-muted">Sesi Admin aktif. Modul metrik operasional dan grafik penjualan akan dilengkapi pada Plan 11 (MF-6: F012).</p>
</div>
