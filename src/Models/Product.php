<?php

namespace App\Models;

use App\Config\Database;

class Product
{
    public static function findByAffiliateId(int $affiliateId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, name, description, price, destination_url FROM products WHERE affiliate_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $affiliateId]);

        return $stmt->fetch() ?: null;
    }

    public static function create(int $affiliateId, array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO products (affiliate_id, name, description, price, destination_url)
             VALUES (:affiliate_id, :name, :description, :price, :destination_url)'
        );
        $stmt->execute([
            'affiliate_id' => $affiliateId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'destination_url' => $data['destination_url'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $affiliateId, array $data): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE products
             SET name = :name, description = :description, price = :price, destination_url = :destination_url
             WHERE affiliate_id = :affiliate_id'
        );
        $stmt->execute([
            'affiliate_id' => $affiliateId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'destination_url' => $data['destination_url'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function delete(int $affiliateId): bool
    {
        $stmt = Database::connection()->prepare(
            'DELETE FROM products WHERE affiliate_id = :affiliate_id'
        );
        $stmt->execute(['affiliate_id' => $affiliateId]);

        return $stmt->rowCount() > 0;
    }
}
