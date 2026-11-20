<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use App\Core\Database;
use App\Core\Logger;
use Throwable;

/**
 * Certificaciones (tabla certifications), editables desde /admin/certificaciones.
 * data/certifications.json solo sirve para la carga inicial (composer certs:import)
 * y como respaldo cuando DB_ENABLED=false.
 */
final class Certification
{
    /** Íconos disponibles: valor => etiqueta. */
    public const ICONS = [
        'award'    => 'Medalla',
        'cap'      => 'Birrete',
        'shield'   => 'Escudo',
        'code'     => 'Código',
        'database' => 'Base de datos',
        'server'   => 'Servidor',
    ];

    private const COLUMNS = 'id, title, issuer, year, status, url, icon, sort_order, is_published, updated_at';

    /**
     * Las publicadas, en orden, para el sitio.
     *
     * @return list<array<string, mixed>>
     */
    public static function published(): array
    {
        if (!Database::enabled()) {
            return self::fromJson();
        }

        try {
            return Database::connection()
                ->query('SELECT ' . self::COLUMNS . ' FROM certifications WHERE is_published = 1 ORDER BY sort_order, id')
                ->fetchAll();
        } catch (Throwable $e) {
            Logger::error('No se pudieron leer las certificaciones', ['exception' => $e]);
            return [];
        }
    }

    /**
     * Todas, para el panel.
     *
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return Database::connection()
            ->query('SELECT ' . self::COLUMNS . ' FROM certifications ORDER BY sort_order, id')
            ->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT ' . self::COLUMNS . ' FROM certifications WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public static function count(bool $publishedOnly = false): int
    {
        return (int) Database::connection()
            ->query('SELECT COUNT(*) FROM certifications' . ($publishedOnly ? ' WHERE is_published = 1' : ''))
            ->fetchColumn();
    }

    /**
     * @param array{title: string, issuer: string, year: ?int, status: string, url: string, icon: string, sort_order: int, is_published: bool} $data
     */
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO certifications (title, issuer, year, status, url, icon, sort_order, is_published)
             VALUES (:title, :issuer, :year, :status, :url, :icon, :sort_order, :is_published)'
        )->execute(self::params($data));

        return (int) $pdo->lastInsertId();
    }

    /**
     * @param array{title: string, issuer: string, year: ?int, status: string, url: string, icon: string, sort_order: int, is_published: bool} $data
     */
    public static function update(int $id, array $data): void
    {
        Database::connection()->prepare(
            'UPDATE certifications
             SET title = :title, issuer = :issuer, year = :year, status = :status, url = :url,
                 icon = :icon, sort_order = :sort_order, is_published = :is_published
             WHERE id = :id'
        )->execute(self::params($data) + [':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM certifications WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public static function nextOrder(): int
    {
        return (int) Database::connection()->query('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM certifications')->fetchColumn();
    }

    /**
     * Lee data/certifications.json con la misma forma que la tabla.
     * Las tarjetas de ejemplo ("placeholder": true) se omiten.
     *
     * @return list<array<string, mixed>>
     */
    public static function fromJson(): array
    {
        $path = App::basePath('data/certifications.json');
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        $items = [];

        foreach (is_array($data) ? $data : [] as $i => $row) {
            if (!is_array($row) || !empty($row['placeholder']) || trim((string) ($row['title'] ?? '')) === '') {
                continue;
            }

            $icon = (string) ($row['icon'] ?? 'award');
            $items[] = [
                'id'           => $i + 1,
                'title'        => (string) $row['title'],
                'issuer'       => (string) ($row['issuer'] ?? ''),
                'year'         => isset($row['year']) ? (int) $row['year'] : null,
                'status'       => (string) ($row['status'] ?? ''),
                'url'          => (string) ($row['url'] ?? ''),
                'icon'         => isset(self::ICONS[$icon]) ? $icon : 'award',
                'sort_order'   => ($i + 1) * 10,
                'is_published' => 1,
            ];
        }

        return $items;
    }

    /**
     * @param array{title: string, issuer: string, year: ?int, status: string, url: string, icon: string, sort_order: int, is_published: bool} $data
     * @return array<string, mixed>
     */
    private static function params(array $data): array
    {
        return [
            ':title'        => $data['title'],
            ':issuer'       => $data['issuer'],
            ':year'         => $data['year'],
            ':status'       => $data['status'],
            ':url'          => $data['url'],
            ':icon'         => $data['icon'],
            ':sort_order'   => $data['sort_order'],
            ':is_published' => $data['is_published'] ? 1 : 0,
        ];
    }
}
