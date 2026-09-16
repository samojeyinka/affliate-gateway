<?php

namespace App\Controllers;

use App\Models\Affiliate;
use App\Models\ClickLog;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimiter;

class ClickController
{
    public function track(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!RateLimiter::check($ip)) {
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($data['slug'] ?? ''));

        $affiliate = Affiliate::findBySlug($slug);

        if (!$affiliate) {
            http_response_code(404);
            echo json_encode(['error' => 'Affiliate not found']);
            return;
        }

        ClickLog::record($affiliate['id'], $ip, $_SERVER['HTTP_USER_AGENT'] ?? '');
        echo json_encode(['message' => 'Click recorded']);
    }

    public function stats(string $slug): void
    {
        $claims = AuthMiddleware::authenticate();
        if (!$claims) {
            return;
        }

        $affiliate = Affiliate::findBySlug($this->sanitizeSlug($slug));

        if (!$affiliate || $affiliate['id'] !== $claims['affiliate_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Not authorized to view these stats']);
            return;
        }

        echo json_encode([
            'affiliate' => $affiliate['slug'],
            'stats' => ClickLog::statsByAffiliate($affiliate['id']),
        ]);
    }

    private function sanitizeSlug(string $slug): string
    {
        return preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
    }
}
