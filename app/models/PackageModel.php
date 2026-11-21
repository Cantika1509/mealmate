<?php
require_once __DIR__ . '/../config/database.php';

function getAllPackages(): array {
    $pdo = getDBConnection();
    return $pdo->query(
        "SELECT package_id, package_name, description, price, image, duration_days, applicable_days
         FROM packages
         ORDER BY price ASC"
    )->fetchAll();
}

// buat checkout
function getPackageById(int $packageId): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        "SELECT package_id, package_name, description, price, image, duration_days, applicable_days
         FROM packages
         WHERE package_id = :id"
    );
    $stmt->execute(['id' => $packageId]);
    return $stmt->fetch() ?: null;
}