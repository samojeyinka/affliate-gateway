<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class ClickLog
{
    public static function record(int $affiliateId, string $ip, string $userAgent): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO click_logs (affiliate_id, ip_address, user_agent, clicked_at)
             VALUES (:affiliate_id, :ip, :user_agent, NOW())'
        );
        $stmt->execute([
            'affiliate_id' => $affiliateId,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    public static function countRecentByIp(string $ip, int $seconds): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) AS total FROM click_logs
             WHERE ip_address = :ip AND clicked_at >= (NOW() - INTERVAL :seconds SECOND)'
        );
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('seconds', $seconds, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetch()['total'];
    }

    public static function statsByAffiliate(int $affiliateId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) AS total_clicks,
                    COALESCE(SUM(clicked_at >= (NOW() - INTERVAL 1 DAY)), 0) AS clicks_last_24h,
                    COALESCE(SUM(clicked_at >= (NOW() - INTERVAL 7 DAY)), 0) AS clicks_last_7days
             FROM click_logs
             WHERE affiliate_id = :affiliate_id'
        );
        $stmt->execute(['affiliate_id' => $affiliateId]);

        $stats = $stmt->fetch();

        return [
            'total_clicks' => (int) ($stats['total_clicks'] ?? 0),
            'clicks_last_24h' => (int) ($stats['clicks_last_24h'] ?? 0),
            'clicks_last_7days' => (int) ($stats['clicks_last_7days'] ?? 0),
        ];
    }
}
