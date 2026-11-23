<?php

declare(strict_types=1);

namespace App\Validation;

use App\Core\Request;
use App\Models\Project;

/**
 * Valida el formulario de proyectos del panel.
 */
final class ProjectRequest
{
    /** Campos de una sola línea (colapsan espacios). */
    private const LINE_FIELDS = ['title', 'slug', 'subtitle', 'kicker', 'status', 'description', 'repo_url'];

    private const STAR_MAX = 2000;

    /** @var array<string, mixed> */
    private array $data;
    /** @var array<string, string> */
    private array $errors = [];
    /** @var array<string, string|list<string>> */
    private array $old = [];

    public function __construct(Request $request, private readonly ?int $id = null)
    {
        foreach (self::LINE_FIELDS as $field) {
            $this->old[$field] = $request->string($field);
        }
        $this->old['stack'] = $request->string('stack');
        $this->old['visual'] = $request->string('visual') ?: 'dash';
        foreach (array_keys(Project::STAR) as $field) {
            $this->old[$field] = $request->string($field);
        }
        foreach (['is_live', 'is_featured', 'is_published'] as $flag) {
            $this->old[$flag] = $request->string($flag) === '1' ? '1' : '0';
        }

        $rawCategories = $request->input('categories', []);
        $valid = array_diff(array_keys(Project::FILTERS), ['all']);
        $this->old['categories'] = is_array($rawCategories)
            ? array_values(array_intersect($valid, array_filter($rawCategories, 'is_string')))
            : [];

        $title = self::clean((string) $this->old['title']);
        $slug = self::clean((string) $this->old['slug']);
        if ($slug === '') {
            $slug = Project::slugify($title);
        }

        $this->data = [
            'slug'        => $slug,
            'title'       => $title,
            'subtitle'    => self::clean((string) $this->old['subtitle']),
            'kicker'      => self::clean((string) $this->old['kicker']),
            'status'      => self::clean((string) $this->old['status']),
            'is_live'     => $this->old['is_live'] === '1',
            'is_featured' => $this->old['is_featured'] === '1',
            'visual'      => (string) $this->old['visual'],
            'categories'  => $this->old['categories'],
            'description' => self::clean((string) $this->old['description']),
            'stack'       => array_slice(self::splitStack((string) $this->old['stack']), 0, 12),
            'repo_url'    => self::clean((string) $this->old['repo_url']),
        ];
        foreach (array_keys(Project::STAR) as $field) {
            $this->data[$field] = self::multiline((string) $this->old[$field]);
        }
        $this->data['is_published'] = $this->old['is_published'] === '1';

        $this->validate();

        // Una ilustración inválida ya tiene su error; para el formulario se usa la de por defecto.
        if (!array_key_exists($this->data['visual'], Project::VISUALS)) {
            $this->data['visual'] = 'dash';
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

    /**
     * Valores para repoblar el formulario (categories es una lista de claves).
     *
     * @return array<string, string|list<string>>
     */
    public function old(): array
    {
        return $this->old;
    }

    /**
     * @return array{slug: string, title: string, subtitle: string, kicker: string, status: string, is_live: bool, is_featured: bool, visual: string, categories: list<string>, description: string, stack: list<string>, repo_url: string, situation: string, task: string, action: string, result: string, learning: string, is_published: bool}
     */
    public function validated(): array
    {
        return $this->data;
    }

    private function validate(): void
    {
        $d = $this->data;

        if ($d['title'] === '') {
            $this->errors['title'] = 'Escribe el título del proyecto.';
        } elseif (mb_strlen($d['title']) > 120) {
            $this->errors['title'] = 'Máximo 120 caracteres.';
        }

        foreach (['subtitle' => 200, 'kicker' => 80, 'status' => 60] as $field => $max) {
            if (mb_strlen($d[$field]) > $max) {
                $this->errors[$field] = "Máximo {$max} caracteres.";
            }
        }

        if (mb_strlen($d['description']) > 400) {
            $this->errors['description'] = 'Máximo 400 caracteres (' . mb_strlen($d['description']) . ' ahora).';
        }

        if ($d['slug'] === '') {
            // Solo si el título no produjo letras ni números (el error del título ya se muestra si está vacío).
            if (!isset($this->errors['title'])) {
                $this->errors['slug'] = 'No se pudo generar la dirección desde el título. Escríbela a mano (minúsculas, números y guiones).';
            }
        } elseif (mb_strlen($d['slug']) > 80) {
            $this->errors['slug'] = 'Máximo 80 caracteres.';
        } elseif (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $d['slug']) !== 1) {
            $this->errors['slug'] = 'Usa solo minúsculas sin acentos, números y guiones sueltos (por ejemplo: mi-proyecto).';
        } elseif (Project::slugExists($d['slug'], $this->id)) {
            $this->errors['slug'] = 'Ya existe otro proyecto con esa dirección. Elige una distinta.';
        }

        if (!array_key_exists($d['visual'], Project::VISUALS)) {
            $this->errors['visual'] = 'Elige una ilustración de la lista.';
        }

        $stackRaw = self::splitStack((string) $this->old['stack']);
        if (count($stackRaw) > 12) {
            $this->errors['stack'] = 'Máximo 12 tecnologías (' . count($stackRaw) . ' ahora).';
        } else {
            foreach ($stackRaw as $item) {
                if (mb_strlen($item) > 40) {
                    $this->errors['stack'] = 'Cada tecnología admite hasta 40 caracteres.';
                    break;
                }
            }
        }

        if ($d['repo_url'] !== '') {
            $parts = parse_url($d['repo_url']);
            $ok = filter_var($d['repo_url'], FILTER_VALIDATE_URL) !== false
                && is_array($parts)
                && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
                && !empty($parts['host']);

            if (mb_strlen($d['repo_url']) > 500) {
                $this->errors['repo_url'] = 'Máximo 500 caracteres.';
            } elseif (!$ok) {
                $this->errors['repo_url'] = 'Escribe una URL completa que empiece con http:// o https://.';
            }
        }

        foreach (array_keys(Project::STAR) as $field) {
            if (mb_strlen($d[$field]) > self::STAR_MAX) {
                $this->errors[$field] = 'Máximo ' . self::STAR_MAX . ' caracteres (' . mb_strlen($d[$field]) . ' ahora).';
            }
        }
    }

    /**
     * Texto "React, Node.js, MySQL" en lista sin vacíos ni duplicados.
     *
     * @return list<string>
     */
    private static function splitStack(string $raw): array
    {
        $seen = [];
        $items = [];

        foreach (explode(',', $raw) as $part) {
            $item = self::clean($part);
            $key = mb_strtolower($item);
            if ($item === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $items[] = $item;
        }

        return $items;
    }

    private static function clean(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    /** Conserva los saltos de línea: normaliza, recorta y colapsa 3+ saltos en 2. */
    private static function multiline(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = (string) preg_replace('/[ \t]+\n/', "\n", $value);

        return trim((string) preg_replace("/\n{3,}/", "\n\n", $value));
    }
}
