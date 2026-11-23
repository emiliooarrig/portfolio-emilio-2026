<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Router mínimo: método + ruta → [Controlador::class, 'método'].
 * Admite segmentos {nombre} (solo [a-z0-9-]+); la ruta exacta tiene prioridad.
 */
final class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = [];

    /** @var array<string, list<array{0: string, 1: list<string>, 2: array{0: class-string, 1: string}}>> método => [regex, nombres, acción] */
    private array $patterns = [];

    /**
     * @param array{0: class-string, 1: string} $action
     */
    public function get(string $path, array $action): self
    {
        return $this->add('GET', $path, $action);
    }

    /**
     * @param array{0: class-string, 1: string} $action
     */
    public function post(string $path, array $action): self
    {
        return $this->add('POST', $path, $action);
    }

    /**
     * @param array{0: class-string, 1: string} $action
     */
    private function add(string $method, string $path, array $action): self
    {
        $path = '/' . trim($path, '/');

        if (!str_contains($path, '{')) {
            $this->routes[$method][$path] = $action;

            return $this;
        }

        $names = [];
        $regex = preg_replace_callback(
            '/\\\\\{([a-z_]+)\\\\\}/',
            static function (array $m) use (&$names): string {
                $names[] = $m[1];

                return '([a-z0-9-]+)';
            },
            preg_quote($path, '#')
        );
        $this->patterns[$method][] = ['#^' . $regex . '$#', $names, $action];

        return $this;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method() === 'HEAD' ? 'GET' : $request->method();
        $path = $request->path();

        [$action, $params] = $this->match($method, $path);

        if ($action === null) {
            $allowed = [];
            foreach (array_unique(array_merge(array_keys($this->routes), array_keys($this->patterns))) as $other) {
                if ($this->match($other, $path)[0] !== null) {
                    $allowed[] = $other;
                }
            }

            if ($allowed !== []) {
                header('Allow: ' . implode(', ', $allowed));
                Response::html(View::render('errors/404', ['title' => 'Método no permitido']), 405);
                return;
            }

            Response::html(View::render('errors/404', ['title' => 'Página no encontrada']), 404);
            return;
        }

        [$class, $handler] = $action;
        (new $class())->{$handler}($params === [] ? $request : $request->withParams($params));
    }

    /**
     * Ruta exacta primero; si no, la primera con patrón que coincida.
     *
     * @return array{0: array{0: class-string, 1: string}|null, 1: array<string, string>}
     */
    private function match(string $method, string $path): array
    {
        if (isset($this->routes[$method][$path])) {
            return [$this->routes[$method][$path], []];
        }

        foreach ($this->patterns[$method] ?? [] as [$regex, $names, $action]) {
            if (preg_match($regex, $path, $m) === 1) {
                return [$action, array_combine($names, array_slice($m, 1))];
            }
        }

        return [null, []];
    }
}
