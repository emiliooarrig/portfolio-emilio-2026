<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Router mínimo: método + ruta exacta → [Controlador::class, 'método'].
 */
final class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = [];

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
        $this->routes[$method]['/' . trim($path, '/')] = $action;

        return $this;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method() === 'HEAD' ? 'GET' : $request->method();
        $path = $request->path();

        $action = $this->routes[$method][$path] ?? null;

        if ($action === null) {
            $allowed = array_keys(array_filter($this->routes, static fn (array $r): bool => isset($r[$path])));

            if ($allowed !== []) {
                header('Allow: ' . implode(', ', $allowed));
                Response::html(View::render('errors/404', ['title' => 'Método no permitido']), 405);
                return;
            }

            Response::html(View::render('errors/404', ['title' => 'Página no encontrada']), 404);
            return;
        }

        [$class, $handler] = $action;
        (new $class())->{$handler}($request);
    }
}
