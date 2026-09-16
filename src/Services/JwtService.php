<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtService
{
    public static function issue(array $payload, int $ttlSeconds = 3600): string
    {
        $now = time();
        $claims = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $ttlSeconds,
        ]);

        return JWT::encode($claims, $_ENV['JWT_SECRET'], 'HS256');
    }

    public static function verify(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));

            return (array) $decoded;
        } catch (Exception $e) {
            return null;
        }
    }
}
