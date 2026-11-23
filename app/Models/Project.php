<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use App\Core\Database;
use App\Core\Logger;
use Throwable;

/**
 * Proyectos (tabla projects), editables desde /admin/proyectos. Cada uno tiene su
 * página en /proyectos/{slug} con el caso en formato STAR + aprendizaje.
 * data/projects.json solo sirve para la carga inicial (composer projects:import)
 * y como respaldo cuando DB_ENABLED=false.
 *
 * Todas las lecturas devuelven la misma fila normalizada (ver normalize()).
 */
final class Project
{
    /** Filtros visibles en la sección de proyectos (clave => etiqueta). */
    public const FILTERS = [
        'all'  => 'Todos',
        'dev'  => 'Desarrollo',
        'ops'  => 'Soporte & infra',
        'data' => 'Datos & seguridad',
    ];

    /** Ilustraciones disponibles en app/Views/partials/visuals (clave => etiqueta). */
    public const VISUALS = [
        'dash'  => 'Dashboard',
        'menu'  => 'Menú',
        'chain' => 'Cadena de bloques',
        'list'  => 'Lista',
    ];

    /** Los cinco campos del caso (clave => etiqueta). */
    public const STAR = [
        'situation' => 'Situación',
        'task'      => 'Tarea',
        'action'    => 'Acción',
        'result'    => 'Resultado',
        'learning'  => 'Aprendizaje',
    ];

    private const COLUMNS = 'id, slug, title, subtitle, kicker, status, is_live, is_featured, visual, categories, description, stack, repo_url, situation, task, action, result, learning, sort_order, is_published, updated_at';

    /**
     * Los publicados, en orden, para el sitio.
     *
     * @return list<array<string, mixed>>
     */
    public static function published(): array
    {
        if (!Database::enabled()) {
            return self::fromJson();
        }

        try {
            $rows = Database::connection()
                ->query('SELECT ' . self::COLUMNS . ' FROM projects WHERE is_published = 1 ORDER BY sort_order, id')
                ->fetchAll();

            return array_map(self::normalize(...), $rows);
        } catch (Throwable $e) {
            Logger::error('No se pudieron leer los proyectos', ['exception' => $e]);
            return [];
        }
    }

    /**
     * Un proyecto publicado por su slug (página pública).
     *
     * @return array<string, mixed>|null
     */
    public static function findPublishedBySlug(string $slug): ?array
    {
        if (!Database::enabled()) {
            foreach (self::fromJson() as $project) {
                if ($project['slug'] === $slug) {
                    return $project;
                }
            }

            return null;
        }

        try {
            $stmt = Database::connection()->prepare(
                'SELECT ' . self::COLUMNS . ' FROM projects WHERE slug = ? AND is_published = 1'
            );
            $stmt->execute([$slug]);
            $row = $stmt->fetch();

            return $row ? self::normalize($row) : null;
        } catch (Throwable $e) {
            Logger::error('No se pudo leer el proyecto', ['exception' => $e, 'slug' => $slug]);
            return null;
        }
    }

    /**
     * Todos, para el panel.
     *
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $rows = Database::connection()
            ->query('SELECT ' . self::COLUMNS . ' FROM projects ORDER BY sort_order, id')
            ->fetchAll();

        return array_map(self::normalize(...), $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT ' . self::COLUMNS . ' FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? self::normalize($row) : null;
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM projects WHERE slug = ? AND id <> ?');
        $stmt->execute([$slug, $exceptId ?? 0]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function count(bool $publishedOnly = false): int
    {
        return (int) Database::connection()
            ->query('SELECT COUNT(*) FROM projects' . ($publishedOnly ? ' WHERE is_published = 1' : ''))
            ->fetchColumn();
    }

    /**
     * Se agrega al final de la lista, salvo que $data traiga sort_order (importación).
     *
     * @param array<string, mixed> $data Ver ProjectRequest::validated()
     */
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO projects (slug, title, subtitle, kicker, status, is_live, is_featured, visual, categories, description, stack, repo_url,
                                   situation, task, action, result, learning, sort_order, is_published)
             VALUES (:slug, :title, :subtitle, :kicker, :status, :is_live, :is_featured, :visual, :categories, :description, :stack, :repo_url,
                     :situation, :task, :action, :result, :learning, :sort_order, :is_published)'
        )->execute(self::params($data) + [':sort_order' => $data['sort_order'] ?? self::nextOrder()]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * No toca sort_order: el orden se cambia arrastrando en el panel.
     *
     * @param array<string, mixed> $data Ver ProjectRequest::validated()
     */
    public static function update(int $id, array $data): void
    {
        Database::connection()->prepare(
            'UPDATE projects
             SET slug = :slug, title = :title, subtitle = :subtitle, kicker = :kicker, status = :status,
                 is_live = :is_live, is_featured = :is_featured, visual = :visual, categories = :categories,
                 description = :description, stack = :stack, repo_url = :repo_url,
                 situation = :situation, task = :task, action = :action, result = :result, learning = :learning,
                 is_published = :is_published
             WHERE id = :id'
        )->execute(self::params($data) + [':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM projects WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public static function nextOrder(): int
    {
        return Ordering::next('projects');
    }

    /**
     * Lee data/projects.json con la misma forma que las filas normalizadas.
     *
     * @return list<array<string, mixed>>
     */
    public static function fromJson(): array
    {
        $path = App::basePath('data/projects.json');
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        $items = [];

        foreach (is_array($data) ? $data : [] as $row) {
            if (!is_array($row)) {
                continue;
            }

            $slug = self::slugify((string) ($row['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $i = count($items);
            $items[] = self::normalize([
                'id'           => $i + 1,
                'slug'         => $slug,
                'title'        => $row['title'] ?? '',
                'subtitle'     => $row['subtitle'] ?? '',
                'kicker'       => $row['kicker'] ?? '',
                'status'       => $row['status'] ?? '',
                'is_live'      => !empty($row['is_live']),
                'is_featured'  => !empty($row['is_featured']),
                'visual'       => $row['visual'] ?? '',
                'categories'   => (array) ($row['categories'] ?? []),
                'description'  => $row['description'] ?? '',
                'stack'        => (array) ($row['stack'] ?? []),
                'repo_url'     => $row['repo_url'] ?? null,
                'situation'    => $row['situation'] ?? '',
                'task'         => $row['task'] ?? '',
                'action'       => $row['action'] ?? '',
                'result'       => $row['result'] ?? '',
                'learning'     => $row['learning'] ?? '',
                'sort_order'   => ($i + 1) * 10,
                'is_published' => true,
                'updated_at'   => null,
            ]);
        }

        return $items;
    }

    /**
     * Texto → slug: minúsculas, sin acentos, [a-z0-9-], guiones simples, máx. 80.
     */
    public static function slugify(string $text): string
    {
        $text = trim($text);
        $ascii = false;

        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        }

        if ($ascii === false || $ascii === '') {
            $ascii = strtr($text, [
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
                'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            ]);
        }

        // Algunos iconv dejan marcas como ' ^ ~ ` en la transliteración: se descartan.
        $slug = strtolower((string) preg_replace('/[\'"`^~]/', '', $ascii));
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $slug), '-');

        return trim(substr($slug, 0, 80), '-');
    }

    /**
     * Fila de BD (o equivalente del JSON) → fila normalizada.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function normalize(array $row): array
    {
        $list = static function (mixed $value): array {
            $items = is_array($value) ? $value : explode(',', (string) $value);

            return array_values(array_filter(
                array_map(static fn (mixed $v): string => trim((string) $v), $items),
                static fn (string $v): bool => $v !== ''
            ));
        };

        $filterKeys = array_diff(array_keys(self::FILTERS), ['all']);
        $visual = (string) ($row['visual'] ?? '');
        $repo = trim((string) ($row['repo_url'] ?? ''));

        return [
            'id'           => (int) ($row['id'] ?? 0),
            'slug'         => (string) ($row['slug'] ?? ''),
            'title'        => (string) ($row['title'] ?? ''),
            'subtitle'     => (string) ($row['subtitle'] ?? ''),
            'kicker'       => (string) ($row['kicker'] ?? ''),
            'status'       => (string) ($row['status'] ?? ''),
            'is_live'      => (bool) ($row['is_live'] ?? false),
            'is_featured'  => (bool) ($row['is_featured'] ?? false),
            'visual'       => isset(self::VISUALS[$visual]) ? $visual : 'dash',
            'categories'   => array_values(array_unique(array_intersect($list($row['categories'] ?? ''), $filterKeys))),
            'description'  => (string) ($row['description'] ?? ''),
            'stack'        => $list($row['stack'] ?? ''),
            'repo_url'     => $repo !== '' ? $repo : null,
            'situation'    => (string) ($row['situation'] ?? ''),
            'task'         => (string) ($row['task'] ?? ''),
            'action'       => (string) ($row['action'] ?? ''),
            'result'       => (string) ($row['result'] ?? ''),
            'learning'     => (string) ($row['learning'] ?? ''),
            'sort_order'   => (int) ($row['sort_order'] ?? 0),
            'is_published' => (bool) ($row['is_published'] ?? true),
            'updated_at'   => isset($row['updated_at']) ? (string) $row['updated_at'] : null,
        ];
    }

    /**
     * ProjectRequest::validated() → parámetros PDO (sin sort_order).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function params(array $data): array
    {
        return [
            ':slug'         => $data['slug'],
            ':title'        => $data['title'],
            ':subtitle'     => $data['subtitle'],
            ':kicker'       => $data['kicker'],
            ':status'       => $data['status'],
            ':is_live'      => $data['is_live'] ? 1 : 0,
            ':is_featured'  => $data['is_featured'] ? 1 : 0,
            ':visual'       => $data['visual'],
            ':categories'   => implode(',', $data['categories']),
            ':description'  => $data['description'],
            ':stack'        => implode(',', $data['stack']),
            ':repo_url'     => $data['repo_url'] ?? '',
            ':situation'    => $data['situation'],
            ':task'         => $data['task'],
            ':action'       => $data['action'],
            ':result'       => $data['result'],
            ':learning'     => $data['learning'],
            ':is_published' => ($data['is_published'] ?? true) ? 1 : 0,
        ];
    }
}
