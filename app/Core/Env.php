<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Cargador mínimo de archivos .env (KEY=VALUE).
 * No sobrescribe variables que ya existan en el entorno del servidor.
 */
final class Env
{
    public static function load(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));

            if ($key === '' || !preg_match('/^[A-Z0-9_]+$/', $key)) {
                continue;
            }

            // Quita comentarios al final si el valor no va entre comillas.
            if ($value !== '' && $value[0] !== '"' && $value[0] !== "'") {
                $value = trim(preg_replace('/\s+#.*$/', '', $value) ?? $value);
            }

            // Quita comillas envolventes.
            if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[-1] === $value[0]) {
                $value = substr($value, 1, -1);
            }

            if (getenv($key) !== false || array_key_exists($key, $_ENV)) {
                continue;
            }

            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}
