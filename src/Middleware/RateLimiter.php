<?php

namespace App\Middleware;

use App\Models\ClickLog;

class RateLimiter
{
    private const MAX_REQUESTS = 5;
    private const WINDOW_SECONDS = 60;

    public static function check(string $ip): bool
    {
        $count = ClickLog::countRecentByIp($ip, self::WINDOW_SECONDS);

        if ($count >= self::MAX_REQUESTS) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests, please try again later']);
            return false;
        }

        return true;
    }
}
