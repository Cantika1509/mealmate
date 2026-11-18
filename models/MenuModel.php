<?php
require_once __DIR__ . '/../config/database.php';

function getMenuByDate(string $date): array {
    $grouped = getMenuByDateRange($date, $date);
    return $grouped[$date] ?? [];
}

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
        $row['rating'] = (float) $row['rating'];
        $row['reviews'] = (int) $row['reviews'];
        $grouped[$date][] = $row;
    }

    return $grouped;
}

function getAllMenus(): array {
    $pdo = getDBConnection();
    return $pdo->query("SELECT menu_id, menu_name, description, image FROM menus ORDER BY menu_name")->fetchAll();
}

function buildMenuSearchFilter(string $search): array {
    if (trim($search) === '') {
        return ['', []];
    }
    $like = '%' . trim($search) . '%';
    return ["WHERE menu_name LIKE :q1 OR description LIKE :q2", ['q1' => $like, 'q2' => $like]];
}

function searchMenus(string $search = '', int $limit = 6, int $offset = 0): array {
    $pdo = getDBConnection();
    [$where, $params] = buildMenuSearchFilter($search);
    $stmt = $pdo->prepare(
        "SELECT menu_id, menu_name, description, image
         FROM menus {$where}
         ORDER BY menu_id DESC LIMIT :limit OFFSET :offset"
    );
    foreach ($params as $key => $val) {
        $stmt->bindValue(":{$key}", $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function countMenus(string $search = ''): int {
    $pdo = getDBConnection();
    [$where, $params] = buildMenuSearchFilter($search);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM menus {$where}");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
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
        'name' => trim($name),
        'description' => $description !== null ? trim($description) : null,
        'image' => $image,
    ]);
    return (int) $pdo->lastInsertId();
}

function updateMenu(int $menuId, string $name, ?string $description, ?string $image): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE menus SET menu_name = :name, description = :description, image = :image WHERE menu_id = :id");
    return $stmt->execute([
        'id' => $menuId,
        'name' => trim($name),
        'description' => $description !== null ? trim($description) : null,
        'image' => $image,
    ]);
}

function isMenuInActiveOrders(int $menuId): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM daily_menus dm
         JOIN orders o ON o.slot = dm.slot
         JOIN packages p ON p.package_id = o.package_id
         WHERE dm.menu_id = :menu_id
           AND o.status IN ('Menunggu Verifikasi', 'Aktif')
           AND dm.`date` >= o.start_date
           AND dm.`date` <= DATE_ADD(o.start_date, INTERVAL (p.duration_days * 2) DAY)"
    );
    $stmt->execute(['menu_id' => $menuId]);
    return (int) $stmt->fetchColumn() > 0;
}

function deleteMenu(int $menuId): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM menus WHERE menu_id = :id");
    return $stmt->execute(['id' => $menuId]);
}

function isValidSlot(string $slot): bool {
    return in_array($slot, ['Pagi', 'Siang', 'Malam'], true);
}

function countScheduledDates(string $search = ''): int {
    $pdo = getDBConnection();
    if (trim($search) === '') {
        return (int) $pdo->query("SELECT COUNT(DISTINCT `date`) FROM daily_menus")->fetchColumn();
    }
    $stmt = $pdo->prepare(
        "SELECT COUNT(DISTINCT dm.`date`)
         FROM daily_menus dm
         JOIN menus m ON m.menu_id = dm.menu_id
         WHERE m.menu_name LIKE :q1 OR dm.`date` LIKE :q2"
    );
    $like = '%' . trim($search) . '%';
    $stmt->execute(['q1' => $like, 'q2' => $like]);
    return (int) $stmt->fetchColumn();
}

function getScheduledDatesPaginated(string $search = '', int $limit = 4, int $offset = 0): array {
    $pdo = getDBConnection();
    $where = '';
    $params = [];
    if (trim($search) !== '') {
        $where = "WHERE m.menu_name LIKE :q1 OR dm.`date` LIKE :q2";
        $like = '%' . trim($search) . '%';
        $params = ['q1' => $like, 'q2' => $like];
    }
    $stmt = $pdo->prepare(
        "SELECT DISTINCT dm.`date`
         FROM daily_menus dm
         JOIN menus m ON m.menu_id = dm.menu_id
         {$where}
         ORDER BY dm.`date` DESC
         LIMIT :limit OFFSET :offset"
    );
    foreach ($params as $key => $val) {
        $stmt->bindValue(":{$key}", $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getSchedulesByDates(array $dates): array {
    if (empty($dates)) {
        return [];
    }
    $pdo = getDBConnection();
    $placeholders = implode(',', array_fill(0, count($dates), '?'));
    $stmt = $pdo->prepare(
        "SELECT dm.daily_menu_id, dm.`date`, dm.slot, dm.menu_id,
                m.menu_name, m.description, m.image
         FROM daily_menus dm
         JOIN menus m ON m.menu_id = dm.menu_id
         WHERE dm.`date` IN ({$placeholders})
         ORDER BY dm.`date` DESC, FIELD(dm.slot, 'Pagi', 'Siang', 'Malam')"
    );
    $stmt->execute(array_values($dates));
    $grouped = [];
    foreach ($stmt->fetchAll() as $row) {
        $grouped[$row['date']][] = $row;
    }
    return $grouped;
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
        'date' => $date,
        'slot' => $slot,
    ]);
}

function isScheduleInActiveOrders(string $date, string $slot): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM orders o
         JOIN packages p ON p.package_id = o.package_id
         WHERE o.slot = :slot
           AND o.status IN ('Menunggu Verifikasi', 'Aktif')
           AND :d1 >= o.start_date
           AND :d2 <= DATE_ADD(o.start_date, INTERVAL (p.duration_days * 2) DAY)"
    );
    $stmt->execute(['slot' => $slot, 'd1' => $date, 'd2' => $date]);
    return (int) $stmt->fetchColumn() > 0;
}

function deleteDailyMenu(string $date, string $slot): bool {
    if (!isValidSlot($slot)) {
        return false;
    }
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM daily_menus WHERE `date` = :date AND slot = :slot");
    return $stmt->execute(['date' => $date, 'slot' => $slot]);
}
