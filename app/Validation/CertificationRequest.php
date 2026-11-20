<?php

declare(strict_types=1);

namespace App\Validation;

use App\Core\Request;
use App\Models\Certification;

/**
 * Valida el formulario de certificaciones del panel.
 */
final class CertificationRequest
{
    /** @var array{title: string, issuer: string, year: ?int, status: string, url: string, icon: string, sort_order: int, is_published: bool} */
    private array $data;
    /** @var array<string, string> */
    private array $errors = [];
    /** @var array<string, string> */
    private array $old;

    public function __construct(Request $request)
    {
        $this->old = [
            'title'        => $request->string('title'),
            'issuer'       => $request->string('issuer'),
            'year'         => $request->string('year'),
            'status'       => $request->string('status'),
            'url'          => $request->string('url'),
            'icon'         => $request->string('icon'),
            'sort_order'   => $request->string('sort_order'),
            'is_published' => $request->string('is_published') === '1' ? '1' : '0',
        ];

        $this->data = [
            'title'        => self::clean($this->old['title']),
            'issuer'       => self::clean($this->old['issuer']),
            'year'         => ctype_digit($this->old['year']) ? (int) $this->old['year'] : null,
            'status'       => self::clean($this->old['status']),
            'url'          => $this->old['url'],
            'icon'         => $this->old['icon'],
            'sort_order'   => is_numeric($this->old['sort_order']) ? (int) $this->old['sort_order'] : 0,
            'is_published' => $this->old['is_published'] === '1',
        ];

        $this->validate();
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string, string> */
    public function old(): array
    {
        return $this->old;
    }

    /**
     * @return array{title: string, issuer: string, year: ?int, status: string, url: string, icon: string, sort_order: int, is_published: bool}
     */
    public function validated(): array
    {
        return $this->data;
    }

    private function validate(): void
    {
        $d = $this->data;
        $maxYear = (int) date('Y') + 5;

        if ($d['title'] === '') {
            $this->errors['title'] = 'Escribe el nombre de la certificación.';
        } elseif (mb_strlen($d['title']) > 200) {
            $this->errors['title'] = 'Máximo 200 caracteres.';
        }

        if (mb_strlen($d['issuer']) > 160) {
            $this->errors['issuer'] = 'Máximo 160 caracteres.';
        }

        if ($this->old['year'] !== '' && ($d['year'] === null || $d['year'] < 1950 || $d['year'] > $maxYear)) {
            $this->errors['year'] = "Escribe un año entre 1950 y {$maxYear}, o déjalo vacío.";
        }

        if (mb_strlen($d['status']) > 60) {
            $this->errors['status'] = 'Máximo 60 caracteres.';
        }

        if ($d['url'] !== '') {
            $scheme = strtolower((string) parse_url($d['url'], PHP_URL_SCHEME));
            if (strlen($d['url']) > 500 || filter_var($d['url'], FILTER_VALIDATE_URL) === false || !in_array($scheme, ['http', 'https'], true)) {
                $this->errors['url'] = 'Escribe una URL completa que empiece con https://, o déjala vacía.';
            }
        }

        if (!isset(Certification::ICONS[$d['icon']])) {
            $this->errors['icon'] = 'Elige un ícono de la lista.';
        }

        if (!is_numeric($this->old['sort_order'] === '' ? '0' : $this->old['sort_order']) || abs($d['sort_order']) > 100000) {
            $this->errors['sort_order'] = 'Escribe un número entero.';
        }
    }

    private static function clean(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }
}
