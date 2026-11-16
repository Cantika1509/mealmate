<?php
require_once __DIR__ . '/../config/database.php';

function getMenuByDate(string $date): array {
    $grouped = getMenuByDateRange($date, $date);
    return $grouped[$date] ?? [];
}

/
function getMenuByDateRange(string $startDate, string $endDate): array {
    $pdo = getDBConnection();

    $query = "SELECT dm.`date` AS menu_date, dm.slot, m.menu_id, m.menu_name,
                     m.description, m.image,
                     COALESCE(rv.rating, 0)  AS rating,
                     COALESCE(rv.reviews, 0) AS reviews
              FROM daily_menus dm
              JOIN menus m ON m.menu_id = dm.menu_id
              LEFT JOIN (
                  SELECT menu_id,
                         ROUND(AVG(rating) * 2) / 2 AS rating,
                         COUNT(*)                   AS reviews
                  FROM reviews
                  GROUP BY menu_id
              ) rv ON rv.menu_id = m.menu_id
              WHERE dm.`date` BETWEEN :start AND :end
              ORDER BY dm.`date`, FIELD(dm.slot, 'Pagi', 'Siang', 'Malam')";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['start' => $startDate, 'end' => $endDate]);

    $grouped = [];
    foreach ($stmt->fetchAll() as $row) {
        $date = $row['menu_date'];
        unset($row['menu_date']);
        $row['rating']  = (float) $row['rating'];
        $row['reviews'] = (int) $row['reviews'];
        $grouped[$date][] = $row;
    }

    return $grouped;
}


function getAllMenus(): array {
    $pdo = getDBConnection();
    return $pdo->query("SELECT menu_id, menu_name, description, image FROM menus ORDER BY menu_name")->fetchAll();
}

function getMenuById(int $menuId): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT menu_id, menu_name, description, image FROM menus WHERE menu_id = :id");
    $stmt->execute(['id' => $menuId]);
    return $stmt->fetch() ?: null;
}

function createMenu(string $name, ?string $description, ?string $image): int {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO menus (menu_name, description, image) VALUES (:name, :description, :image)");
    $stmt->execute([
        'name'        => $name,
        'description' => $description,
        'image'       => $image,
    ]);
    return (int) $pdo->lastInsertId();
}

function updateMenu(int $menuId, string $name, ?string $description, ?string $image): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE menus SET menu_name = :name, description = :description, image = :image WHERE menu_id = :id");
    return $stmt->execute([
        'id'          => $menuId,
        'name'        => $name,
        'description' => $description,
        'image'       => $image,
    ]);
}

function deleteMenu(int $menuId): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM menus WHERE menu_id = :id");
    return $stmt->execute(['id' => $menuId]);
}


function isValidSlot(string $slot): bool {
    return in_array($slot, ['Pagi', 'Siang', 'Malam'], true);
}

function setDailyMenu(int $menuId, string $date, string $slot): bool {
    if (!isValidSlot($slot)) {
        return false;
    }
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        "INSERT INTO daily_menus (menu_id, `date`, slot)
         VALUES (:menu_id, :date, :slot)
         ON DUPLICATE KEY UPDATE menu_id = VALUES(menu_id)"
    );
    return $stmt->execute([
        'menu_id' => $menuId,
        'date'    => $date,
        'slot'    => $slot,
    ]);
}

function deleteDailyMenu(string $date, string $slot): bool {
    if (!isValidSlot($slot)) {
        return false;
    }
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM daily_menus WHERE `date` = :date AND slot = :slot");
    return $stmt->execute(['date' => $date, 'slot' => $slot]);
}
