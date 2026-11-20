<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Mensajes flash: viven solo hasta la siguiente petición (patrón PRG).
 */
final class Session
{
    private const FLASH = '_flash';

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION[self::FLASH][$key] = $value;
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[self::FLASH][$key] ?? $default;
        unset($_SESSION[self::FLASH][$key]);

        return $value;
    }
}
