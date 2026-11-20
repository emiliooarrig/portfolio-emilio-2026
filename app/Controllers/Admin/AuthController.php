<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Logger;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

/**
 * /admin/login y /admin/salir.
 */
final class AuthController
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 900;

    public function show(Request $request): void
    {
        AdminController::privateHeaders();

        if (!Database::enabled()) {
            AdminController::unavailable();
            return;
        }

        if (Auth::check($request)) {
            Response::redirect(url('/admin'));
            return;
        }

        Response::html(View::render('admin/login', [
            'title'  => 'Iniciar sesión · Panel',
            'error'  => (string) Session::pull('login_error', ''),
            'notice' => (string) Session::pull('login_notice', ''),
            'email'  => (string) Session::pull('login_email', ''),
            'bye'    => (bool) Session::pull('login_bye', false),
        ], 'admin/layout'));
    }

    public function login(Request $request): void
    {
        AdminController::privateHeaders();

        if (!Database::enabled()) {
            AdminController::unavailable();
            return;
        }

        $email = mb_strtolower($request->string('email'));
        $password = (string) $request->input('password', '');

        if (!Csrf::verify($request->string('_csrf'))) {
            $this->fail('La página expiró. Intenta de nuevo.', $email);
            return;
        }

        // Límite por IP y por correo: frena fuerza bruta y relleno de credenciales.
        $keys = ['admin-login|ip|' . $request->ip(), 'admin-login|email|' . $email];

        foreach ($keys as $key) {
            if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS, self::WINDOW_SECONDS)) {
                $this->fail('Demasiados intentos. Espera 15 minutos y vuelve a intentar.', $email);
                return;
            }
        }

        if ($email === '' || $password === '' || mb_strlen($email) > 160 || strlen($password) > 1024) {
            $this->fail('Escribe tu correo y tu contraseña.', $email);
            return;
        }

        $admin = Auth::attempt($email, $password);

        if ($admin === null) {
            foreach ($keys as $key) {
                RateLimiter::hit($key, self::WINDOW_SECONDS);
            }
            Logger::info('Inicio de sesión fallido en el panel', ['ip_hash' => hash('sha256', $request->ip())]);
            $this->fail('Correo o contraseña incorrectos.', $email);
            return;
        }

        foreach ($keys as $key) {
            RateLimiter::clear($key);
        }

        Auth::login($admin, $request);
        Session::flash('admin_welcome', true); // la primera página del panel hace la transición de entrada

        $intended = (string) ($_SESSION['_admin_intended'] ?? '');
        unset($_SESSION['_admin_intended']);

        // Solo se vuelve a rutas internas del panel.
        $to = preg_match('#^/admin(/[a-z0-9/_-]*)?(\?[^\s]*)?$#i', $intended) ? $intended : '/admin';
        Response::redirect(url($to));
    }

    public function logout(Request $request): void
    {
        AdminController::privateHeaders();

        if (Csrf::verify($request->string('_csrf'))) {
            Auth::logout();
            Session::flash('login_notice', 'Cerraste sesión.');
            Session::flash('login_bye', true);
        }

        Response::redirect(url('/admin/login'));
    }

    private function fail(string $message, string $email): void
    {
        Session::flash('login_error', $message);
        Session::flash('login_email', $email);
        Response::redirect(url('/admin/login'));
    }
}
