<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Contenedor ligero de la aplicación: configuración, rutas base y arranque.
 */
final class App
{
    private static string $basePath = '';
    private static string $baseUrl = '';
    /** @var array<string, mixed> */
    private static array $config = [];

    public static function boot(string $basePath): void
    {
        self::$basePath = rtrim($basePath, '/\\');

        Env::load(self::$basePath . '/.env');
        self::$config = require self::$basePath . '/config/config.php';

        date_default_timezone_set((string) self::config('app.timezone', 'America/Mexico_City'));

        self::$baseUrl = self::detectBaseUrl();

        self::configureErrors();
        self::startSession();
        self::sendSecurityHeaders();
    }

    public static function run(Router $router): void
    {
        try {
            $router->dispatch(Request::capture());
        } catch (Throwable $e) {
            Logger::error('Excepción no controlada', ['exception' => $e]);
            Response::status(500);

            if (self::config('app.debug')) {
                echo '<pre>' . e((string) $e) . '</pre>';
                return;
            }

            echo View::render('errors/500', ['title' => 'Algo salió mal']);
        }
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        $value = self::$config;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public static function basePath(string $path = ''): string
    {
        return self::$basePath . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }

    public static function publicPath(string $path = ''): string
    {
        return self::basePath('public' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }

    public static function url(string $path = '/'): string
    {
        if (preg_match('#^(https?:)?//#', $path) || str_starts_with($path, 'mailto:') || str_starts_with($path, '#')) {
            return $path;
        }

        return self::$baseUrl . '/' . ltrim($path, '/');
    }

    public static function baseUrl(): string
    {
        return self::$baseUrl;
    }

    /**
     * Ruta base cuando el sitio vive en una subcarpeta (p. ej. XAMPP: /portfolio/public).
     */
    private static function detectBaseUrl(): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');

        return $dir === '.' ? '' : $dir;
    }

    private static function configureErrors(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', self::config('app.debug') ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', self::basePath('storage/logs/php-error.log'));
    }

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE || PHP_SAPI === 'cli') {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        session_name('pf_session');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => self::$baseUrl === '' ? '/' : self::$baseUrl . '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        session_start();
    }

    private static function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data:",
            "connect-src 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);

        header('Content-Security-Policy: ' . $csp);
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()');
        header_remove('X-Powered-By');
    }
}
