<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Logger;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\ContactMessage;
use App\Services\Mailer;
use App\Validation\ContactRequest;
use Throwable;

/**
 * POST /contacto — funciona con fetch (JSON) y sin JavaScript (redirección PRG).
 */
final class ContactController
{
    public function store(Request $request): void
    {
        // 1. CSRF
        if (!Csrf::verify($request->string('_csrf'))) {
            $this->fail($request, 419, 'Tu sesión expiró. Recarga la página e intenta de nuevo.');
            return;
        }

        // 2. Honeypot: si un bot llenó el campo oculto, se responde "ok" sin hacer nada.
        if ($request->string('website') !== '') {
            Logger::info('Honeypot activado en formulario de contacto');
            $this->success($request);
            return;
        }

        // 3. Límite de envíos por IP
        $key = 'contact|' . $request->ip();
        $max = (int) config('contact.max_attempts', 5);
        $window = (int) config('contact.window_seconds', 600);

        if (RateLimiter::tooManyAttempts($key, $max, $window)) {
            $this->fail($request, 429, 'Recibí varios mensajes seguidos desde tu conexión. Intenta de nuevo en unos minutos.');
            return;
        }

        // 4. Validación
        $form = new ContactRequest($request);

        if (!$form->passes()) {
            $this->fail($request, 422, 'Revisa los campos marcados.', $form->errors(), $form->old());
            return;
        }

        RateLimiter::hit($key, $window);
        $data = $form->validated();

        // 5. Guardar y notificar. Basta con que uno de los dos funcione.
        $stored = false;
        $mailed = false;

        if (Database::enabled()) {
            try {
                ContactMessage::create($data, $this->ipHash($request->ip()), $request->userAgent());
                $stored = true;
            } catch (Throwable $e) {
                Logger::error('No se pudo guardar el mensaje de contacto', ['exception' => $e]);
            }
        }

        if (Mailer::enabled()) {
            try {
                Mailer::sendContactNotification($data);
                $mailed = true;
            } catch (Throwable $e) {
                Logger::error('No se pudo enviar la notificación de contacto', ['exception' => $e]);
            }
        }

        if (!$stored && !$mailed) {
            if (!Database::enabled() && !Mailer::enabled()) {
                Logger::error('Formulario recibido pero DB_ENABLED y MAIL_ENABLED están en false');
            }

            $this->fail($request, 503, 'No pude recibir tu mensaje en este momento. Escríbeme directo por correo, por favor.', [], $form->old());
            return;
        }

        $this->success($request);
    }

    private function success(Request $request): void
    {
        if ($request->wantsJson()) {
            Response::json(['ok' => true, 'message' => 'Mensaje enviado.']);
            return;
        }

        Session::flash('contact_sent', true);
        Response::redirect(url('/') . '#contacto');
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, string> $old
     */
    private function fail(Request $request, int $status, string $message, array $errors = [], array $old = []): void
    {
        if ($request->wantsJson()) {
            Response::json(['ok' => false, 'message' => $message, 'errors' => $errors], $status);
            return;
        }

        Session::flash('contact_message', $message);
        Session::flash('contact_errors', $errors);
        Session::flash('contact_old', $old);
        Response::redirect(url('/') . '#contacto');
    }

    /** La IP se guarda como hash con APP_KEY: sirve para detectar abuso sin almacenar el dato personal. */
    private function ipHash(string $ip): string
    {
        $key = (string) config('app.key');

        return hash_hmac('sha256', $ip, $key !== '' ? $key : 'portfolio');
    }
}
