<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Admin;

/**
 * Sesión del panel de administración.
 *
 * - El ID de sesión se regenera al entrar y al salir (evita fijación de sesión).
 * - La sesión caduca tras 2 h sin actividad o 12 h desde el login.
 * - Se ata al user agent: una cookie robada en otro navegador no sirve.
 * - Guarda una huella del hash de la contraseña: al cambiarla se cierran todas las sesiones.
 */
final class Auth
{
    private const KEY = '_admin';
    private const IDLE_SECONDS = 7200;
    private const MAX_SECONDS = 43200;

    /** @var array<string, mixed>|null */
    private static ?array $user = null;

    /**
     * Verifica correo y contraseña. Tarda lo mismo exista o no el correo.
     *
     * @return array<string, mixed>|null
     */
    public static function attempt(string $email, string $password): ?array
    {
        $admin = Admin::findByEmail($email);

        // Hash ficticio para que un correo inexistente cueste lo mismo que uno real.
        $hash = $admin['password_hash'] ?? '$2y$12$ai8eIi1.A4d0II14kjMySOTy2/micszS9p8VN60QuKYklTYu0nwYi';
        $valid = password_verify($password, (string) $hash);

        if (!$valid || $admin === null) {
            return null;
        }

        if (password_needs_rehash((string) $admin['password_hash'], PASSWORD_DEFAULT)) {
            Admin::updatePassword((int) $admin['id'], $password);
            // La huella de la sesión debe salir del hash nuevo.
            $admin = Admin::findByEmail($email) ?? $admin;
        }

        return $admin;
    }

    /**
     * @param array<string, mixed> $admin
     */
    public static function login(array $admin, Request $request): void
    {
        session_regenerate_id(true);

        $_SESSION[self::KEY] = [
            'id'   => (int) $admin['id'],
            'at'   => time(),
            'seen' => time(),
            'ua'   => hash('sha256', $request->userAgent()),
            'pw'   => self::fingerprint((string) $admin['password_hash']),
        ];

        Csrf::rotate();
        Admin::touchLogin((int) $admin['id']);
    }

    public static function logout(): void
    {
        unset($_SESSION[self::KEY]);
        self::$user = null;
        session_regenerate_id(true);
        Csrf::rotate();
    }

    public static function check(Request $request): bool
    {
        return self::user($request) !== null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function user(Request $request): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }

        $session = $_SESSION[self::KEY] ?? null;

        if (!is_array($session) || !isset($session['id'], $session['at'], $session['seen'], $session['ua'], $session['pw'])) {
            return null;
        }

        $now = time();
        $expired = $now - (int) $session['seen'] > self::IDLE_SECONDS || $now - (int) $session['at'] > self::MAX_SECONDS;

        if ($expired || !hash_equals((string) $session['ua'], hash('sha256', $request->userAgent()))) {
            self::logout();
            return null;
        }

        $admin = Admin::findWithHash((int) $session['id']);

        if ($admin === null || !hash_equals((string) $session['pw'], self::fingerprint((string) $admin['password_hash']))) {
            self::logout();
            return null;
        }

        unset($admin['password_hash']);
        $_SESSION[self::KEY]['seen'] = $now;

        return self::$user = $admin;
    }

    /** Huella del hash (no la contraseña) con APP_KEY; cambia si cambia la contraseña. */
    private static function fingerprint(string $passwordHash): string
    {
        $key = (string) config('app.key');

        return hash_hmac('sha256', $passwordHash, $key !== '' ? $key : 'portfolio');
    }
}
