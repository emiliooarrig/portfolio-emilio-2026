<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Conexión PDO perezosa a MySQL (solo se abre cuando se usa).
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function enabled(): bool
    {
        return (bool) config('db.enabled', false);
    }

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            (string) config('db.host', '127.0.0.1'),
            (int) config('db.port', 3306),
            (string) config('db.name', ''),
            (string) config('db.charset', 'utf8mb4')
        );

        self::$pdo = new PDO($dsn, (string) config('db.user', ''), (string) config('db.pass', ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$pdo;
    }
}
