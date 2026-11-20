<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Token CSRF por sesión (patrón synchronizer token).
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY]) || !is_string($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    /** Token nuevo, p. ej. al iniciar o cerrar sesión. */
    public static function rotate(): void
    {
        $_SESSION[self::KEY] = bin2hex(random_bytes(32));
    }

    public static function verify(?string $token): bool
    {
        $expected = $_SESSION[self::KEY] ?? null;

        return is_string($expected) && is_string($token) && $token !== '' && hash_equals($expected, $token);
    }
}
