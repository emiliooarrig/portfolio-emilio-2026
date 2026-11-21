<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Csrf;

/**
 * Escapa texto para HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Lee una variable de entorno (.env o del sistema).
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null) {
        return $default;
    }

    return match (strtolower((string) $value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        'empty', '(empty)' => '',
        default => $value,
    };
}

/**
 * Lee configuración con notación de puntos: config('mail.host').
 */
function config(string $key, mixed $default = null): mixed
{
    return App::config($key, $default);
}

/**
 * URL respecto al directorio público (funciona también en subcarpetas).
 */
function url(string $path = '/'): string
{
    return App::url($path);
}

/**
 * URL de un asset con cache-busting por fecha de modificación.
 */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = App::publicPath($path);
    $version = is_file($file) ? '?v=' . filemtime($file) : '';

    return App::url('/' . $path) . $version;
}

/**
 * Muestra el valor o, si está vacío, un placeholder visible para completarlo.
 */
function ph(?string $value, string $placeholder): string
{
    if ($value === null || trim($value) === '') {
        return '<span class="todo">' . e($placeholder) . '</span>';
    }

    return e($value);
}

/**
 * Fecha en español: "12 oct 2026" o, con hora, "12 oct 2026, 14:05".
 */
function date_es(?string $value, bool $withTime = false): string
{
    $ts = $value !== null ? strtotime($value) : false;

    if ($ts === false) {
        return '';
    }

    static $months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    $text = date('j', $ts) . ' ' . $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);

    return $withTime ? $text . ', ' . date('H:i', $ts) : $text;
}

/**
 * Campo oculto con el token CSRF.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

/**
 * Controles para reordenar un elemento de una lista [data-sort] del panel:
 * el asa para arrastrar (solo con JS, admin.js) y los botones ↑/↓ (solo sin JS).
 *
 * @param array<string, int|string> $extra Campos ocultos extra (p. ej. category_id)
 */
function sort_controls(string $action, int $id, string $name, array $extra = [], bool $inline = false): string
{
    $hidden = '';
    foreach ($extra as $key => $value) {
        $hidden .= '<input type="hidden" name="' . e($key) . '" value="' . e((string) $value) . '">';
    }

    [$before, $after] = $inline ? ['antes', 'después'] : ['más arriba', 'más abajo'];
    [$icBefore, $icAfter] = $inline ? ['arrow-left', 'arrow-right'] : ['arrow-up', 'arrow-down'];

    return '<button type="button" class="drag" data-sort-handle data-requires-js aria-label="Mover ' . e($name) . '" aria-describedby="sort-help" title="Arrastra para ordenar">'
        . icon('grip') . '</button>'
        . '<form class="mv" method="post" action="' . e(url($action)) . '">'
        . csrf_field() . '<input type="hidden" name="id" value="' . $id . '">' . $hidden
        . '<button type="submit" name="dir" value="up" aria-label="Mover ' . e($name) . ' ' . $before . '">' . icon($icBefore) . '</button>'
        . '<button type="submit" name="dir" value="down" aria-label="Mover ' . e($name) . ' ' . $after . '">' . icon($icAfter) . '</button>'
        . '</form>';
}

/**
 * Ícono SVG de trazo (decorativo).
 */
function icon(string $name, string $class = 'ic'): string
{
    static $icons = [
        'arrow-right'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-up-right' => '<path d="M7 17L17 7M8 7h9v9"/>',
        'arrow-up'       => '<path d="M12 19V5M6 11l6-6 6 6"/>',
        'download'       => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
        'menu'           => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'          => '<path d="M6 6l12 12M18 6L6 18"/>',
        'check'          => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'plus'           => '<path d="M12 5v14M5 12h14"/>',
        'code'           => '<path d="M8 8l-4 4 4 4M16 8l4 4-4 4M13.5 5l-3 14"/>',
        'server'         => '<rect x="4" y="4" width="16" height="7" rx="2"/><rect x="4" y="13" width="16" height="7" rx="2"/><path d="M8 7.5h.01M8 16.5h.01"/>',
        'database'       => '<ellipse cx="12" cy="6" rx="7" ry="3"/><path d="M5 6v12c0 1.7 3.1 3 7 3s7-1.3 7-3V6M5 12c0 1.7 3.1 3 7 3s7-1.3 7-3"/>',
        'shield'         => '<path d="M12 3l7 3v5c0 4.5-3 8.3-7 10-4-1.7-7-5.5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
        'cap'            => '<path d="M3 9l9-5 9 5-9 5-9-5z"/><path d="M7 11.5V16c0 1.5 2.2 3 5 3s5-1.5 5-3v-4.5"/>',
        'award'          => '<circle cx="12" cy="9" r="5"/><path d="M8.5 13l-1.5 8 5-3 5 3-1.5-8"/>',
        'user'           => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
        // Panel de administración
        'grid'           => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/>',
        'mail'           => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.5 6.5L12 13l8.5-6.5"/>',
        'logout'         => '<path d="M15 4h3a2 2 0 012 2v12a2 2 0 01-2 2h-3M10 16l-4-4 4-4M6 12h10"/>',
        'external'       => '<path d="M14 4h6v6M20 4l-9 9M18 14v4a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h4"/>',
        'trash'          => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3"/>',
        'edit'           => '<path d="M4 20h4L19 9l-4-4L4 16v4zM13.5 6.5l4 4"/>',
        'search'         => '<circle cx="11" cy="11" r="6.5"/><path d="M20 20l-4.2-4.2"/>',
        'arrow-left'     => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'arrow-down'     => '<path d="M12 5v14M6 13l6 6 6-6"/>',
        'grip'           => '<path d="M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01"/>',
        'reply'          => '<path d="M10 8L4 13l6 5M4 13h10a6 6 0 016 6"/>',
        'eye'            => '<path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'        => '<path d="M10.6 5.1A9.8 9.8 0 0112 5c6 0 9.5 7 9.5 7a17 17 0 01-2.7 3.6M6.6 6.6A16.6 16.6 0 002.5 12S6 19 12 19a9.6 9.6 0 005.4-1.6M9.9 9.9a3 3 0 004.2 4.2M3 3l18 18"/>',
        'briefcase'      => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M3 13h18"/>',
    ];

    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
        . ($icons[$name] ?? '')
        . '</svg>';
}
