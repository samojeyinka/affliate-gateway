<?php

namespace App\Controllers;

use App\Models\Affiliate;
use App\Services\JwtService;
use App\Services\NotificationClient;
use App\Support\PasswordPolicy;
use App\Support\SlugGenerator;
use App\Support\UrlValidator;

class AuthController
{
    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        foreach (['business_name', 'email', 'phone', 'slug', 'password'] as $field) {
            if (empty($data[$field])) {
                http_response_code(422);
                echo json_encode(['error' => "Field '{$field}' is required"]);
                return;
            }
        }

        $passwordErrors = PasswordPolicy::errors((string) $data['password']);

        if ($passwordErrors) {
            http_response_code(422);
            echo json_encode(['error' => 'Password must contain ' . implode(', ', $passwordErrors)]);
            return;
        }

        if (!empty($data['logo_url']) && !UrlValidator::isValid($data['logo_url'])) {
            http_response_code(422);
            echo json_encode(['error' => 'logo_url must be a valid URL (e.g. https://example.com/logo.png)']);
            return;
        }

        if (Affiliate::findByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'That email address is already registered']);
            return;
        }

        $data['slug'] = SlugGenerator::make(
            $this->sanitizeSlug($data['slug']),
            fn (string $slug): bool => Affiliate::findBySlug($slug) !== null
        );

        $data['business_name'] = htmlspecialchars(strip_tags($data['business_name']));

        try {
            $id = Affiliate::create($data);
        } catch (\PDOException $e) {
            if ($e->getCode() === 1062) {
                if (Affiliate::findByEmail($data['email'])) {
                    http_response_code(409);
                    echo json_encode(['error' => 'That email address is already registered']);
                    return;
                }

                $data['slug'] = SlugGenerator::make(
                    $this->sanitizeSlug($data['slug']),
                    fn (string $slug): bool => Affiliate::findBySlug($slug) !== null
                );
                $id = Affiliate::create($data);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to register affiliate, please try again']);
                return;
            }
        }

        NotificationClient::notifyNewAffiliate($data['business_name'], $data['email']);

        http_response_code(201);
        echo json_encode(['id' => $id, 'slug' => $data['slug'], 'message' => 'Affiliate registered']);
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $affiliate = Affiliate::findByEmail($data['email']);

        if (!$affiliate || !password_verify($data['password'], $affiliate['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $token = JwtService::issue([
            'affiliate_id' => $affiliate['id'],
            'slug' => $affiliate['slug'],
        ]);

        echo json_encode(['token' => $token]);
    }

    private function sanitizeSlug(string $slug): string
    {
        return preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
    }
}