<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactMessage;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

/**
 * Envía la notificación del formulario por SMTP con PHPMailer.
 */
final class Mailer
{
    public static function enabled(): bool
    {
        return (bool) config('mail.enabled', false);
    }

    /**
     * @param array{name: string, email: string, topic: string, message: string} $data
     */
    public static function sendContactNotification(array $data): void
    {
        $to = (string) config('mail.to');
        $from = (string) config('mail.from');

        if ($to === '' || $from === '') {
            throw new RuntimeException('Faltan CONTACT_TO o MAIL_FROM_ADDRESS en .env');
        }

        $topic = ContactMessage::TOPICS[$data['topic']] ?? $data['topic'];
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = (string) config('mail.host');
            $mail->Port = (int) config('mail.port', 587);
            $mail->SMTPAuth = config('mail.username') !== '';
            $mail->Username = (string) config('mail.username');
            $mail->Password = (string) config('mail.password');
            $mail->Timeout = 15;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $encryption = strtolower((string) config('mail.encryption', 'tls'));
            $mail->SMTPSecure = match ($encryption) {
                'ssl'  => PHPMailer::ENCRYPTION_SMTPS,
                'none' => '',
                default => PHPMailer::ENCRYPTION_STARTTLS,
            };
            if ($encryption === 'none') {
                $mail->SMTPAutoTLS = false;
            }

            $mail->setFrom($from, (string) config('mail.from_name', 'Portafolio'));
            $mail->addAddress($to);
            // Responder va directo a quien escribió. Nombre y correo ya vienen validados.
            $mail->addReplyTo($data['email'], $data['name']);

            $mail->Subject = 'Portafolio · ' . $topic . ' · ' . $data['name'];
            $mail->isHTML(true);
            $mail->Body = self::html($data, $topic);
            $mail->AltBody = self::text($data, $topic);

            $mail->send();
        } catch (MailException $e) {
            throw new RuntimeException('No se pudo enviar el correo: ' . $mail->ErrorInfo, 0, $e);
        }
    }

    /**
     * @param array{name: string, email: string, topic: string, message: string} $data
     */
    private static function html(array $data, string $topic): string
    {
        $rows = [
            'Nombre' => e($data['name']),
            'Correo' => '<a href="mailto:' . e($data['email']) . '">' . e($data['email']) . '</a>',
            'Tema'   => e($topic),
        ];

        $html = '<div style="font-family:Arial,sans-serif;font-size:15px;color:#0A1433;line-height:1.6">';
        $html .= '<h2 style="margin:0 0 16px;color:#1F47E0">Nuevo mensaje desde tu portafolio</h2><table cellpadding="6">';
        foreach ($rows as $label => $value) {
            $html .= '<tr><td style="color:#47557A"><strong>' . $label . '</strong></td><td>' . $value . '</td></tr>';
        }
        $html .= '</table><p style="margin:20px 0 6px;color:#47557A"><strong>Mensaje</strong></p>';
        $html .= '<div style="padding:16px;background:#EEF3FF;border-radius:10px">' . nl2br(e($data['message'])) . '</div></div>';

        return $html;
    }

    /**
     * @param array{name: string, email: string, topic: string, message: string} $data
     */
    private static function text(array $data, string $topic): string
    {
        return "Nuevo mensaje desde tu portafolio\n\n"
            . "Nombre: {$data['name']}\n"
            . "Correo: {$data['email']}\n"
            . "Tema: {$topic}\n\n"
            . "Mensaje:\n{$data['message']}\n";
    }
}
