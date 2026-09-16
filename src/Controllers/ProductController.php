<?php

namespace App\Controllers;

use App\Models\Affiliate;
use App\Models\Product;
use App\Middleware\AuthMiddleware;

class ProductController
{
    public function create(string $slug): void
    {
        $affiliate = $this->authorize($slug);
        if ($affiliate === null) {
            return;
        }

        if (Product::findByAffiliateId($affiliate['id'])) {
            http_response_code(409);
            echo json_encode(['error' => 'This affiliate already has a product']);
            return;
        }

        $data = $this->validated();
        if ($data === null) {
            return;
        }

        $id = Product::create($affiliate['id'], $data);

        http_response_code(201);
        echo json_encode(['id' => $id, 'message' => 'Product created']);
    }

    public function update(string $slug): void
    {
        $affiliate = $this->authorize($slug);
        if ($affiliate === null) {
            return;
        }

        if (!Product::findByAffiliateId($affiliate['id'])) {
            http_response_code(404);
            echo json_encode(['error' => 'No product found for this affiliate']);
            return;
        }

        $data = $this->validated();
        if ($data === null) {
            return;
        }

        Product::update($affiliate['id'], $data);
        echo json_encode(['message' => 'Product updated']);
    }

    public function delete(string $slug): void
    {
        $affiliate = $this->authorize($slug);
        if ($affiliate === null) {
            return;
        }

        if (!Product::delete($affiliate['id'])) {
            http_response_code(404);
            echo json_encode(['error' => 'No product found for this affiliate']);
            return;
        }

        echo json_encode(['message' => 'Product deleted']);
    }

    private function authorize(string $slug): ?array
    {
        $claims = AuthMiddleware::authenticate();
        if (!$claims) {
            return null;
        }

        $affiliate = Affiliate::findBySlug($this->sanitizeSlug($slug));

        if (!$affiliate || $affiliate['id'] !== $claims['affiliate_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Not authorized to manage this product']);
            return null;
        }

        return $affiliate;
    }

    private function validated(): ?array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        foreach (['name', 'price', 'destination_url'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Field '{$field}' is required"]);
                return null;
            }
        }

        if (!is_numeric($data['price']) || (float) $data['price'] < 0) {
            http_response_code(422);
            echo json_encode(['error' => 'price must be a non-negative number']);
            return null;
        }

        if (!filter_var($data['destination_url'], FILTER_VALIDATE_URL)) {
            http_response_code(422);
            echo json_encode(['error' => 'destination_url must be a valid URL (e.g. https://example.com)']);
            return null;
        }

        return [
            'name' => htmlspecialchars(strip_tags($data['name'])),
            'description' => (isset($data['description']) && $data['description'] !== '')
                ? htmlspecialchars(strip_tags($data['description']))
                : null,
            'price' => (float) $data['price'],
            'destination_url' => $data['destination_url'],
        ];
    }

    private function sanitizeSlug(string $slug): string
    {
        return preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
    }
}