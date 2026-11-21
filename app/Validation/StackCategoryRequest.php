<?php

declare(strict_types=1);

namespace App\Validation;

use App\Core\Request;
use App\Models\StackCategory;

/**
 * Valida el formulario de categorías del stack.
 */
final class StackCategoryRequest
{
    /** @var array{name: string, icon: string, is_published: bool} */
    private array $data;
    /** @var array<string, string> */
    private array $errors = [];
    /** @var array<string, string> */
    private array $old;

    public function __construct(Request $request, int $exceptId = 0)
    {
        $this->old = [
            'name'         => $request->string('name'),
            'icon'         => $request->string('icon'),
            'is_published' => $request->string('is_published') === '1' ? '1' : '0',
        ];

        $this->data = [
            'name'         => trim((string) preg_replace('/\s+/u', ' ', $this->old['name'])),
            'icon'         => $this->old['icon'],
            'is_published' => $this->old['is_published'] === '1',
        ];

        $d = $this->data;

        if ($d['name'] === '') {
            $this->errors['name'] = 'Escribe el nombre de la categoría.';
        } elseif (mb_strlen($d['name']) > 80) {
            $this->errors['name'] = 'Máximo 80 caracteres.';
        } elseif (StackCategory::nameExists($d['name'], $exceptId)) {
            $this->errors['name'] = 'Ya existe una categoría con ese nombre.';
        }

        if (!isset(StackCategory::ICONS[$d['icon']])) {
            $this->errors['icon'] = 'Elige un ícono de la lista.';
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

    /** @return array<string, string> */
    public function old(): array
    {
        return $this->old;
    }

    /** @return array{name: string, icon: string, is_published: bool} */
    public function validated(): array
    {
        return $this->data;
    }
}
