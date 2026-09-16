<?php

namespace App\Middleware;

use App\Services\JwtService;

class AuthMiddleware
{
    public static function authenticate(): ?array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'You are not authrorized to perform this action,please login.']);
            return null;
        }

        $claims = JwtService::verify($matches[1]);

        if (!$claims) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            return null;
        }

        return $claims;
    }
}
