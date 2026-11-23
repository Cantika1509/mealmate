<?php

function render(string $view, array $data = [], string $layout = 'customer'): void {
    $viewFile = __DIR__ . '/../views/' . $view . '.php';

    if (!file_exists($viewFile)) {
        http_response_code(500);
        die("View '{$view}' tidak ditemukan");
    }

    $flash = getFlash();
    extract($data);

    if ($layout === 'admin') {
        require_once __DIR__ . '/../views/layouts/admin_header.php';
        require $viewFile;
        require_once __DIR__ . '/../views/layouts/admin_footer.php';
    } elseif ($layout === 'auth') {
        require_once __DIR__ . '/../views/layouts/auth_header.php';
        require $viewFile;
        require_once __DIR__ . '/../views/layouts/auth_footer.php';
    } else {
        require_once __DIR__ . '/../views/layouts/header.php';
        require $viewFile;
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}

function renderFlash(?array $flash, string $extraClass = ''): string {
    if (empty($flash)) {
        return '';
    }
    $type = htmlspecialchars($flash['type']);
    $msg = htmlspecialchars($flash['message']);
    $cls = trim("alert alert-{$type} alert-dismissible fade show rounded-3 {$extraClass}");
    return "<div class=\"{$cls}\" role=\"alert\">{$msg}<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>";
}

function renderStars(float $rating, int $max = 5): string {
    $html = '';

    for ($i = 1; $i <= $max; $i++) {
        if ($rating >= $i) {
            $class = 'bi-star-fill';
        } elseif ($rating >= $i - 0.5) {
            $class = 'bi-star-half';
        } else {
            $class = 'bi-star';
        }
        $html .= '<i class="bi ' . $class . '"></i>';
    }

    return $html;
}

function getInitials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    if (!$parts || $parts[0] === '') {
        return 'MM';
    }
    if (count($parts) === 1) {
        return strtoupper(substr($parts[0], 0, 2));
    }
    return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
}

function formatRupiah(int|float $amount): string {
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

function formatDateIndo(string $dateStr, bool $shortMonth = false): string {
    $ts = strtotime($dateStr);
    if ($ts === false) {
        return $dateStr;
    }

    $monthsFull = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $monthsShort = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    $day = date('d', $ts);
    $monthIdx = (int) date('n', $ts);
    $year = date('Y', $ts);
    $monthName = $shortMonth ? ($monthsShort[$monthIdx] ?? '') : ($monthsFull[$monthIdx] ?? '');

    return "{$day} {$monthName} {$year}";
}

function escape(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
