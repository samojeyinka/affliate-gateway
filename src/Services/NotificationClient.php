<?php

namespace App\Services;

class NotificationClient
{
    public static function notifyNewAffiliate(string $businessName, string $email): void
    {
        $url = $_ENV['NOTIFICATION_SERVICE_URL'] ?? 'http://localhost:4000/notify';

        $payload = json_encode([
            'event' => 'affiliate.registered',
            'business_name' => $businessName,
            'email' => $email,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 2,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
