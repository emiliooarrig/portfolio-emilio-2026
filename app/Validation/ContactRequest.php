<?php

declare(strict_types=1);

namespace App\Validation;

use App\Core\Request;
use App\Models\ContactMessage;

/**
 * Valida y normaliza los datos del formulario de contacto.
 */
final class ContactRequest
{
    /** @var array{name: string, email: string, topic: string, message: string} */
    private array $data;
    /** @var array<string, string> */
    private array $errors = [];

    public function __construct(Request $request)
    {
        $this->data = [
            'name'    => self::clean($request->string('nombre')),
            'email'   => mb_strtolower($request->string('correo')),
            'topic'   => $request->string('tema'),
            'message' => self::cleanMultiline($request->string('mensaje')),
        ];

        $this->validate();
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /**
     * Errores con los nombres de campo del formulario (nombre, correo, tema, mensaje).
     *
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return array{name: string, email: string, topic: string, message: string}
     */
    public function validated(): array
    {
        return $this->data;
    }

    /**
     * Valores para repoblar el formulario cuando se envía sin JavaScript.
     *
     * @return array<string, string>
     */
    public function old(): array
    {
        return [
            'nombre'  => $this->data['name'],
            'correo'  => $this->data['email'],
            'tema'    => $this->data['topic'],
            'mensaje' => $this->data['message'],
        ];
    }

    private function validate(): void
    {
        $nameLength = mb_strlen($this->data['name']);
        if ($nameLength < 2) {
            $this->errors['nombre'] = 'Escribe tu nombre.';
        } elseif ($nameLength > 80) {
            $this->errors['nombre'] = 'El nombre es demasiado largo (máx. 80 caracteres).';
        }

        if ($this->data['email'] === '') {
            $this->errors['correo'] = 'Escribe tu correo para poder responderte.';
        } elseif (mb_strlen($this->data['email']) > 160 || filter_var($this->data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $this->errors['correo'] = 'Ese correo no parece válido.';
        }

        if (!array_key_exists($this->data['topic'], ContactMessage::TOPICS)) {
            $this->errors['tema'] = 'Elige un tema.';
        }

        $messageLength = mb_strlen($this->data['message']);
        if ($messageLength < 10) {
            $this->errors['mensaje'] = 'Cuéntame un poco más (mín. 10 caracteres).';
        } elseif ($messageLength > 3000) {
            $this->errors['mensaje'] = 'El mensaje es demasiado largo (máx. 3000 caracteres).';
        }
    }

    /** Una línea: sin saltos ni caracteres de control (evita inyección de cabeceras). */
    private static function clean(string $value): string
    {
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s{2,}/u', ' ', $value) ?? '');
    }

    /** Texto multilínea: conserva saltos, elimina otros caracteres de control. */
    private static function cleanMultiline(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $value) ?? '';

        return trim($value);
    }
}
