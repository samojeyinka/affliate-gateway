<?php

namespace App\Controllers;

use App\Models\Affiliate;
use App\Models\Product;
use App\Middleware\AuthMiddleware;

class AffiliateController
{
    public function show(string $slug): void
    {
        $affiliate = Affiliate::findBySlug($this->sanitizeSlug($slug));

        if (!$affiliate) {
            http_response_code(404);
            echo json_encode(['error' => 'Affiliate not found']);
            return;
        }

        echo json_encode([
            'affiliate' => $affiliate,
            'product' => Product::findByAffiliateId($affiliate['id']),
        ]);
    }

    public function update(string $slug): void
    {
        $claims = AuthMiddleware::authenticate();
        if (!$claims) {
            return;
        }

        $affiliate = Affiliate::findBySlug($this->sanitizeSlug($slug));

        if (!$affiliate || $affiliate['id'] !== $claims['affiliate_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Not authorized to update this affiliate']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $sanitized = [
            'business_name' => htmlspecialchars(strip_tags($data['business_name'] ?? $affiliate['business_name'])),
            'phone' => htmlspecialchars(strip_tags($data['phone'] ?? $affiliate['phone'])),
            'email' => filter_var($data['email'] ?? $affiliate['email'], FILTER_SANITIZE_EMAIL),
        ];

        Affiliate::updateContactInfo($affiliate['id'], $sanitized);
        echo json_encode(['message' => 'Contact info updated']);
    }

    private function sanitizeSlug(string $slug): string
    {
        return preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
    }
}
