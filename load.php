<?php

$pdo = new PDO(
    'mysql:host=127.0.0.1;port=6034;dbname=ditco_affiliate',
    'db_user',
    'db_user_pass',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

foreach (explode(';', file_get_contents('schema.sql')) as $stmt) {
    if (trim($stmt)) {
        try {
            $pdo->exec($stmt);
            echo "Executed: " . substr(trim($stmt), 0, 40) . "..." . PHP_EOL;
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                echo "Skipped (exists): " . substr(trim($stmt), 0, 40) . "..." . PHP_EOL;
                continue;
            }
            throw $e;
        }
    }
}

echo "Schema loaded." . PHP_EOL;