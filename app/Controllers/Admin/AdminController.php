<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\ContactMessage;

/**
 * Base de los controladores del panel: exige base de datos y sesión de admin.
 *
 * Cada acción empieza con `if (!$this->guard($request)) return;`.
 */
abstract class AdminController
{
    /** @var array<string, mixed> */
    protected array $admin = [];

    /**
     * Devuelve false si ya respondió (BD apagada, sin sesión o CSRF inválido).
     */
    protected function guard(Request $request): bool
    {
        self::privateHeaders();

        if (!Database::enabled()) {
            self::unavailable();
            return false;
        }

        $admin = Auth::user($request);

        if ($admin === null) {
            if ($request->method() === 'GET') {
                $_SESSION['_admin_intended'] = $request->uri();
            }
            Session::flash('login_notice', 'Inicia sesión para continuar.');
            Response::redirect(url('/admin/login'));
            return false;
        }

        $this->admin = $admin;

        if ($request->method() === 'POST' && !Csrf::verify($request->string('_csrf'))) {
            $this->flash('error', 'La página expiró. Intenta de nuevo.');
            Response::redirect(url('/admin'));
            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $view, string $title, string $section, array $data = [], int $status = 200): void
    {
        Response::html(View::render('admin/' . $view, $data + [
            'title'    => $title . ' · Panel',
            'heading'  => $title,
            'section'  => $section,
            'admin'    => $this->admin,
            'newCount' => ContactMessage::countNew(),
            'flash'    => Session::pull('admin_flash'),
            'welcome'  => (bool) Session::pull('admin_welcome', false),
        ], 'admin/layout'), $status);
    }

    protected function flash(string $type, string $text): void
    {
        Session::flash('admin_flash', ['type' => $type, 'text' => $text]);
    }

    protected function notFound(string $what): void
    {
        $this->render('not-found', $what . ' no encontrado', '', [], 404);
    }

    /** El panel nunca se guarda en caché ni lo indexan buscadores. */
    public static function privateHeaders(): void
    {
        if (!headers_sent()) {
            header('Cache-Control: no-store, private');
            header('X-Robots-Tag: noindex, nofollow');
        }
    }

    public static function unavailable(): void
    {
        Response::html(View::render('admin/unavailable', ['title' => 'Panel no disponible'], 'admin/layout'), 503);
    }
}
