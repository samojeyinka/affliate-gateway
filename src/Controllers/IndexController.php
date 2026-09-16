<?php

namespace App\Controllers;

class IndexController
{
    public function home(): void
    {
        echo json_encode([
            'service' => 'Ditco Affiliate Gateway',
            'status' => 'running',
            'endpoints' => [
                [
                    'method' => 'GET',
                    'path' => '/',
                    'description' => 'Service info',
                    'auth' => false,
                ],
                [
                    'method' => 'GET',
                    'path' => '/affiliate/{slug}',
                    'description' => 'Fetch affiliate + product info for the landing page',
                    'auth' => false,
                ],
                [
                    'method' => 'POST',
                    'path' => '/auth/register',
                    'description' => 'Register a new affiliate (triggers notification)',
                    'auth' => false,
                ],
                [
                    'method' => 'PUT',
                    'path' => '/affiliate/{slug}',
                    'description' => 'Update affiliate contact info',
                    'auth' => 'JWT',
                ],
                [
                    'method' => 'POST',
                    'path' => '/auth/login',
                    'description' => 'Log in with email/password, returns a JWT',
                    'auth' => false,
                ],
                [
                    'method' => 'POST',
                    'path' => '/click-track',
                    'description' => 'Record a click event (rate limited)',
                    'auth' => false,
                ],
                [
                    'method' => 'GET',
                    'path' => '/affiliate/{slug}/stats',
                    'description' => 'View the affiliate\'s click counts',
                    'auth' => 'JWT',
                ],
                [
                    'method' => 'POST',
                    'path' => '/affiliate/{slug}/product',
                    'description' => 'Create a product for the affiliate',
                    'auth' => 'JWT',
                ],
                [
                    'method' => 'PUT',
                    'path' => '/affiliate/{slug}/product',
                    'description' => 'Update the affiliate\'s product',
                    'auth' => 'JWT',
                ],
                [
                    'method' => 'DELETE',
                    'path' => '/affiliate/{slug}/product',
                    'description' => 'Delete the affiliate\'s product',
                    'auth' => 'JWT',
                ],
            ],
        ]);
    }
}