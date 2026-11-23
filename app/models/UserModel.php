<?php
require_once __DIR__ . '/../config/database.php';

const CUSTOMER_PROTECTED_ORDER_STATUSES = "'Menunggu Verifikasi', 'Aktif', 'Selesai', 'Ditolak'";

function normalizePhoneNumber(string $raw): ?string {
    $raw = trim($raw);
    if (!preg_match('/^\+?[0-9\s\-()]+$/', $raw)) {
        return null;
    }
    $digits = preg_replace('/\D/', '', $raw);
    $length = strlen($digits);
    return ($length >= 9 && $length <= 15) ? $digits : null;
}

function findUserByEmail(string $email): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
    $stmt->execute(['email' => trim($email)]);
    return $stmt->fetch() ?: null;
}

function findUserById(int $userId): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id LIMIT 1");
    $stmt->execute(['id' => $userId]);
    return $stmt->fetch() ?: null;
}

function findCustomerById(int $userId): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        "SELECT user_id, name, email, phone_number, address
         FROM users
         WHERE user_id = :id AND role = 'Pelanggan'
         LIMIT 1"
    );
    $stmt->execute(['id' => $userId]);
    return $stmt->fetch() ?: null;
}

function isEmailTaken(string $email): bool {
    return findUserByEmail($email) !== null;
}

function isEmailTakenByOther(string $email, int $excludeUserId): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM users WHERE LOWER(email) = LOWER(:email) AND user_id != :id"
    );
    $stmt->execute([
        'email' => trim($email),
        'id' => $excludeUserId,
    ]);
    return (int) $stmt->fetchColumn() > 0;
}

function createCustomerUser(string $name, string $email, string $phone, string $rawPassword, ?string $address = null): int {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email, password, phone_number, address, role)
         VALUES (:name, :email, :password, :phone, :address, 'Pelanggan')"
    );
    $stmt->execute([
        'name' => trim($name),
        'email' => strtolower(trim($email)),
        'password' => password_hash($rawPassword, PASSWORD_DEFAULT),
        'phone' => trim($phone),
        'address' => $address !== null ? trim($address) : null,
    ]);
    return (int) $pdo->lastInsertId();
}

function updateUserPassword(int $userId, string $newRawPassword): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :id");
    return $stmt->execute([
        'id' => $userId,
        'password' => password_hash($newRawPassword, PASSWORD_DEFAULT),
    ]);
}

function updateCustomerProfile(int $userId, string $name, string $email, string $phone, ?string $address): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        "UPDATE users
         SET name = :name, email = :email, phone_number = :phone, address = :address
         WHERE user_id = :id AND role = 'Pelanggan'"
    );
    return $stmt->execute([
        'id' => $userId,
        'name' => trim($name),
        'email' => strtolower(trim($email)),
        'phone' => trim($phone),
        'address' => $address !== null ? trim($address) : null,
    ]);
}

function buildCustomerSearchFilter(string $search): array {
    $where = "WHERE role = 'Pelanggan'";
    $params = [];
    if (trim($search) !== '') {
        $where .= " AND (name LIKE :q1 OR email LIKE :q2 OR phone_number LIKE :q3)";
        $like = '%' . trim($search) . '%';
        $params = ['q1' => $like, 'q2' => $like, 'q3' => $like];
    }
    return [$where, $params];
}

function getCustomers(string $search = '', int $limit = 8, int $offset = 0): array {
    $pdo = getDBConnection();
    [$where, $params] = buildCustomerSearchFilter($search);
    $stmt = $pdo->prepare(
        "SELECT user_id, name, email, phone_number, address
         FROM users {$where}
         ORDER BY user_id DESC LIMIT :limit OFFSET :offset"
    );
    foreach ($params as $key => $val) {
        $stmt->bindValue(":{$key}", $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function countCustomers(string $search = ''): int {
    $pdo = getDBConnection();
    [$where, $params] = buildCustomerSearchFilter($search);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users {$where}");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function customerHasProtectedOrders(int $userId): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM orders
         WHERE user_id = :user_id
           AND status IN (" . CUSTOMER_PROTECTED_ORDER_STATUSES . ")"
    );
    $stmt->execute(['user_id' => $userId]);
    return (int) $stmt->fetchColumn() > 0;
}

function deleteCustomerIfNoProtectedOrders(int $userId): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        "DELETE FROM users
         WHERE user_id = :id
           AND role = 'Pelanggan'
           AND NOT EXISTS (
               SELECT 1 FROM orders
               WHERE orders.user_id = :id2
                 AND orders.status IN (" . CUSTOMER_PROTECTED_ORDER_STATUSES . ")
           )"
    );
    $stmt->execute(['id' => $userId, 'id2' => $userId]);
    return $stmt->rowCount() > 0;
}
