<?php
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../models/MenuModel.php';
require_once __DIR__ . '/../models/PackageModel.php';

$hariIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$bulanIndo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

$formatTanggalIndo = static function (DateTimeInterface $dt, string $namaHari) use ($bulanIndo): string {
    return sprintf('%s, %d %s %s', $namaHari, (int) $dt->format('j'), $bulanIndo[(int) $dt->format('n')], $dt->format('Y'));
};

$now = new DateTimeImmutable('today');
$tanggalHariIni = $formatTanggalIndo($now, $hariIndo[$now->format('l')]);
$dailyMenus = getMenuByDate($now->format('Y-m-d'));

$startOfWeek = new DateTimeImmutable('monday this week');
$endOfWeek = $startOfWeek->modify('+6 days');
$menusByDate = getMenuByDateRange($startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d'));

$listHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$weeklyMenus = [];

foreach ($listHari as $index => $namaHari) {
    $dateCurrent = $startOfWeek->modify("+{$index} days");
    $weeklyMenus[$namaHari] = [
        'tanggal' => $formatTanggalIndo($dateCurrent, $namaHari),
        'menus' => $menusByDate[$dateCurrent->format('Y-m-d')] ?? [],
    ];
}

$packages = getAllPackages();

render('customer/menu', [
    'title' => 'Menu',
    'tanggalHariIni' => $tanggalHariIni,
    'dailyMenus' => $dailyMenus,
    'weeklyMenus' => $weeklyMenus,
    'packages' => $packages,
]);
