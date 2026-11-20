<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Petición HTTP actual (solo lo que esta app necesita).
 */
final class Request
{
    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $query
     * @param array<string, mixed> $server
     */
    private function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $post,
        private readonly array $query,
        private readonly array $server,
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $base = App::baseUrl();

        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        // Si se entra como /index.php/ruta, se normaliza a /ruta.
        if (str_starts_with($uri, '/index.php')) {
            $uri = substr($uri, strlen('/index.php'));
        }

        $path = '/' . trim(rawurldecode($uri), '/');

        return new self($method, $path, $_POST, $_GET, $_SERVER);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /** Ruta con su query string, p. ej. /admin/mensajes?estado=nuevo */
    public function uri(): string
    {
        $query = (string) ($this->server['QUERY_STRING'] ?? '');

        return $this->path . ($query !== '' ? '?' . $query : '');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function string(string $key): string
    {
        $value = $this->input($key, '');

        return is_string($value) ? trim($value) : '';
    }

    /** Parámetro de la query string (?clave=valor), recortado. */
    public function query(string $key): string
    {
        $value = $this->query[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }

    /** Entero positivo de la query string o del POST; 0 si no es válido. */
    public function id(string $key = 'id'): int
    {
        $value = $this->method === 'POST' ? $this->string($key) : $this->query($key);

        return ctype_digit($value) ? (int) $value : 0;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return isset($this->server[$key]) ? (string) $this->server[$key] : null;
    }

    public function wantsJson(): bool
    {
        $accept = $this->header('Accept') ?? '';

        return str_contains($accept, 'application/json') || $this->header('X-Requested-With') !== null;
    }

    public function ip(): string
    {
        // Detrás de un proxy de confianza se puede leer X-Forwarded-For; por defecto se usa REMOTE_ADDR.
        if (config('app.trust_proxy') && ($forwarded = $this->header('X-Forwarded-For'))) {
            return trim(explode(',', $forwarded)[0]);
        }

        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function userAgent(): string
    {
        return mb_substr((string) ($this->server['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }
}
