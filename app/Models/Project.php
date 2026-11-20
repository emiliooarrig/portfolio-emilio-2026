<?php

declare(strict_types=1);

namespace App\Models;

final class Project extends JsonRepository
{
    /** Filtros visibles en la sección de proyectos (clave => etiqueta). */
    public const FILTERS = [
        'all'  => 'Todos',
        'dev'  => 'Desarrollo',
        'ops'  => 'Soporte & infra',
        'data' => 'Datos & seguridad',
    ];

    /** Ilustraciones disponibles en app/Views/partials/visuals. */
    private const VISUALS = ['dash', 'menu', 'chain', 'list'];

    protected static function file(): string
    {
        return 'projects.json';
    }

    /**
     * Proyectos normalizados y listos para la vista.
     *
     * @return list<array<string, mixed>>
     */
    public static function forView(): array
    {
        return array_map(static function (array $p): array {
            $visual = in_array($p['visual'] ?? '', self::VISUALS, true) ? $p['visual'] : 'dash';

            return [
                'id'          => preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($p['id'] ?? ''))),
                'visual'      => $visual,
                'featured'    => (bool) ($p['featured'] ?? false),
                'categories'  => array_values(array_intersect((array) ($p['categories'] ?? []), array_keys(self::FILTERS))),
                'kicker'      => (string) ($p['kicker'] ?? ''),
                'status'      => (string) ($p['status'] ?? ''),
                'live'        => (bool) ($p['live'] ?? false),
                'title'       => (string) ($p['title'] ?? ''),
                'subtitle'    => (string) ($p['subtitle'] ?? ''),
                'description' => (string) ($p['description'] ?? ''),
                'care'        => (string) ($p['care'] ?? ''),
                'stack'       => array_map('strval', (array) ($p['stack'] ?? [])),
                'repo'        => isset($p['repo']) && is_string($p['repo']) && $p['repo'] !== '' ? $p['repo'] : null,
            ];
        }, self::all());
    }
}
