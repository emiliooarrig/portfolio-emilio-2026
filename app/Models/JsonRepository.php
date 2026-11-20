<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use JsonException;
use RuntimeException;

/**
 * Lee contenido editable desde /data/*.json (sin BD para el contenido del sitio).
 */
abstract class JsonRepository
{
    /** @var array<string, list<array<string, mixed>>> */
    private static array $cache = [];

    abstract protected static function file(): string;

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $file = static::file();

        if (isset(self::$cache[$file])) {
            return self::$cache[$file];
        }

        $path = App::basePath('data/' . $file);

        if (!is_file($path)) {
            throw new RuntimeException('No existe el archivo de datos: ' . $file);
        }

        try {
            $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('JSON inválido en data/' . $file . ': ' . $e->getMessage(), 0, $e);
        }

        return self::$cache[$file] = array_values(is_array($data) ? $data : []);
    }
}
