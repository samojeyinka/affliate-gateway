<?php

namespace App\Models;

use App\Config\Database;

class Affiliate
{
    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, business_name, logo_url, email, phone, slug FROM affiliates WHERE slug = :slug LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);

        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM affiliates WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO affiliates (business_name, logo_url, email, phone, slug, password_hash)
             VALUES (:business_name, :logo_url, :email, :phone, :slug, :password_hash)'
        );
        $stmt->execute([
            'business_name' => $data['business_name'],
            'logo_url' => $data['logo_url'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'slug' => $data['slug'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function updateContactInfo(int $id, array $data): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE affiliates SET business_name = :business_name, phone = :phone, email = :email WHERE id = :id'
        );

        return $stmt->execute([
            'business_name' => $data['business_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'id' => $id,
        ]);
    }
}
