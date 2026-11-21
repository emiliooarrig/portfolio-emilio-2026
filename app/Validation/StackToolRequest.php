<?php

declare(strict_types=1);

namespace App\Validation;

use App\Core\Request;
use App\Models\StackCategory;
use App\Models\StackTool;

/**
 * Valida el formulario de herramientas del stack (completo o el "agregar rápido").
 * En el rápido solo llegan category_id y name: el resto toma valores por defecto.
 */
final class StackToolRequest
{
    /** @var array{category_id: int, name: string, in_marquee: bool, is_published: bool} */
    private array $data;
    /** @var array<string, string> */
    private array $errors = [];
    /** @var array<string, string> */
    private array $old;

    public function __construct(Request $request, int $exceptId = 0, bool $quick = false)
    {
        $this->old = [
            'category_id'  => $request->string('category_id'),
            'name'         => $request->string('name'),
            'in_marquee'   => $request->string('in_marquee') === '1' ? '1' : '0',
            'is_published' => $quick || $request->string('is_published') === '1' ? '1' : '0',
        ];

        $categoryId = ctype_digit($this->old['category_id']) ? (int) $this->old['category_id'] : 0;
        $categoryOk = $categoryId > 0 && StackCategory::find($categoryId) !== null;

        $this->data = [
            'category_id'  => $categoryId,
            'name'         => trim((string) preg_replace('/\s+/u', ' ', $this->old['name'])),
            'in_marquee'   => $this->old['in_marquee'] === '1',
            'is_published' => $this->old['is_published'] === '1',
        ];

        $d = $this->data;

        if (!$categoryOk) {
            $this->errors['category_id'] = 'Elige una categoría.';
        }

        if ($d['name'] === '') {
            $this->errors['name'] = 'Escribe el nombre de la herramienta.';
        } elseif (mb_strlen($d['name']) > 60) {
            $this->errors['name'] = 'Máximo 60 caracteres.';
        } elseif ($categoryOk && StackTool::exists($categoryId, $d['name'], $exceptId)) {
            $this->errors['name'] = '«' . $d['name'] . '» ya está en esa categoría.';
        }
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

    /** Primer error, para el aviso del formulario rápido. */
    public function firstError(): string
    {
        return (string) (array_values($this->errors)[0] ?? '');
    }

    /** @return array<string, string> */
    public function old(): array
    {
        return $this->old;
    }

    /** @return array{category_id: int, name: string, in_marquee: bool, is_published: bool} */
    public function validated(): array
    {
        return $this->data;
    }
}
