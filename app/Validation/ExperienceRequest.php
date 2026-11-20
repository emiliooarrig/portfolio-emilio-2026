<?php

declare(strict_types=1);

namespace App\Validation;

use App\Core\Request;

/**
 * Valida el formulario de experiencia del panel.
 */
final class ExperienceRequest
{
    private const FIELDS = ['company', 'role', 'summary', 'start_year', 'start_month', 'end_year', 'end_month', 'sort_order'];

    /** @var array{company: string, role: string, summary: string, start_year: ?int, start_month: ?int, end_year: ?int, end_month: ?int, is_current: bool, sort_order: int, is_published: bool} */
    private array $data;
    /** @var array<string, string> */
    private array $errors = [];
    /** @var array<string, string> */
    private array $old = [];

    public function __construct(Request $request)
    {
        foreach (self::FIELDS as $field) {
            $this->old[$field] = $request->string($field);
        }
        $this->old['is_current'] = $request->string('is_current') === '1' ? '1' : '0';
        $this->old['is_published'] = $request->string('is_published') === '1' ? '1' : '0';

        $int = static fn (string $v): ?int => ctype_digit($v) ? (int) $v : null;

        $this->data = [
            'company'      => self::clean($this->old['company']),
            'role'         => self::clean($this->old['role']),
            'summary'      => self::clean($this->old['summary']),
            'start_year'   => $int($this->old['start_year']),
            'start_month'  => $int($this->old['start_month']),
            'end_year'     => $int($this->old['end_year']),
            'end_month'    => $int($this->old['end_month']),
            'is_current'   => $this->old['is_current'] === '1',
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
     * @return array{company: string, role: string, summary: string, start_year: ?int, start_month: ?int, end_year: ?int, end_month: ?int, is_current: bool, sort_order: int, is_published: bool}
     */
    public function validated(): array
    {
        return $this->data;
    }

    private function validate(): void
    {
        $d = $this->data;
        $o = $this->old;
        $maxYear = (int) date('Y') + 1;

        if ($d['company'] === '') {
            $this->errors['company'] = 'Escribe la empresa, el proyecto o el cliente.';
        } elseif (mb_strlen($d['company']) > 120) {
            $this->errors['company'] = 'Máximo 120 caracteres.';
        }

        if (mb_strlen($d['role']) > 160) {
            $this->errors['role'] = 'Máximo 160 caracteres.';
        }

        if (mb_strlen($d['summary']) > 600) {
            $this->errors['summary'] = 'Máximo 600 caracteres (' . mb_strlen($d['summary']) . ' ahora).';
        }

        foreach (['start', 'end'] as $edge) {
            $year = $d[$edge . '_year'];
            $month = $d[$edge . '_month'];

            if ($o[$edge . '_year'] !== '' && ($year === null || $year < 1970 || $year > $maxYear)) {
                $this->errors[$edge . '_year'] = "Escribe un año entre 1970 y {$maxYear}, o déjalo vacío.";
            }
            if ($o[$edge . '_month'] !== '' && ($month === null || $month < 1 || $month > 12)) {
                $this->errors[$edge . '_month'] = 'Elige un mes de la lista.';
            }
        }

        // El fin no puede ser anterior al inicio.
        if (!$d['is_current'] && $d['start_year'] !== null && $d['end_year'] !== null
            && !isset($this->errors['start_year']) && !isset($this->errors['end_year'])) {
            $start = $d['start_year'] * 100 + ($d['start_month'] ?? 1);
            $end = $d['end_year'] * 100 + ($d['end_month'] ?? 12);
            if ($end < $start) {
                $this->errors['end_year'] = 'El fin no puede ser anterior al inicio.';
            }
        }

        if (!is_numeric($o['sort_order'] === '' ? '0' : $o['sort_order']) || abs($d['sort_order']) > 100000) {
            $this->errors['sort_order'] = 'Escribe un número entero.';
        }
    }

    private static function clean(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }
}
