<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Límite de intentos por ventana de tiempo, guardado en archivos (no requiere BD).
 */
final class RateLimiter
{
    public static function tooManyAttempts(string $key, int $max, int $windowSeconds): bool
    {
        return count(self::hits($key, $windowSeconds)) >= $max;
    }

    public static function hit(string $key, int $windowSeconds): void
    {
        $hits = self::hits($key, $windowSeconds);
        $hits[] = time();
        @file_put_contents(self::file($key), json_encode($hits), LOCK_EX);
    }

    public static function clear(string $key): void
    {
        @unlink(self::file($key));
    }

    /**
     * @return list<int>
     */
    private static function hits(string $key, int $windowSeconds): array
    {
        $file = self::file($key);

        if (!is_file($file)) {
            return [];
        }

        $data = json_decode((string) @file_get_contents($file), true);
        $limit = time() - $windowSeconds;

        return array_values(array_filter(
            is_array($data) ? $data : [],
            static fn (mixed $t): bool => is_int($t) && $t > $limit
        ));
    }

    private static function file(string $key): string
    {
        $dir = App::basePath('storage/cache/ratelimit');

        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . '/' . hash('sha256', $key) . '.json';
    }
}
