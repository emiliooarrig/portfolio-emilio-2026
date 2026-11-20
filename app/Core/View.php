<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Plantillas PHP nativas con layout y parciales.
 */
final class View
{
    /**
     * Renderiza una vista dentro de un layout (null = sin layout).
     *
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = [], ?string $layout = 'layout'): string
    {
        $content = self::capture($template, $data);

        if ($layout === null) {
            return $content;
        }

        return self::capture($layout, $data + ['content' => $content]);
    }

    /**
     * Renderiza un parcial de app/Views/partials.
     *
     * @param array<string, mixed> $data
     */
    public static function partial(string $name, array $data = []): string
    {
        return self::capture('partials/' . $name, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function capture(string $template, array $data): string
    {
        if (!preg_match('#^[a-z0-9/_-]+$#i', $template)) {
            throw new RuntimeException('Nombre de vista inválido: ' . $template);
        }

        $file = App::basePath('app/Views/' . $template . '.php');

        if (!is_file($file)) {
            throw new RuntimeException('No existe la vista: ' . $template);
        }

        extract($data, EXTR_SKIP);
        ob_start();

        try {
            include $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}
