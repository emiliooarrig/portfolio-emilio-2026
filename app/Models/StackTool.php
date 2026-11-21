<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Logger;
use Throwable;

/**
 * Herramientas del stack (tabla stack_tools). Pertenecen a una StackCategory.
 */
final class StackTool
{
    private const COLUMNS = 'id, category_id, name, in_marquee, sort_order, is_published, updated_at';

    /**
     * Nombres para la cinta animada: publicadas, marcadas y de categorías publicadas.
     *
     * @return list<string>
     */
    public static function marquee(): array
    {
        if (!Database::enabled()) {
            return [];
        }

        try {
            return Database::connection()->query(
                'SELECT t.name FROM stack_tools t
                 JOIN stack_categories c ON c.id = t.category_id AND c.is_published = 1
                 WHERE t.is_published = 1 AND t.in_marquee = 1
                 ORDER BY c.sort_order, c.id, t.sort_order, t.id'
            )->fetchAll(\PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            Logger::error('No se pudo leer la cinta del stack', ['exception' => $e]);
            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return Database::connection()
            ->query('SELECT ' . self::COLUMNS . ' FROM stack_tools ORDER BY category_id, sort_order, id')
            ->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT ' . self::COLUMNS . ' FROM stack_tools WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM stack_tools')->fetchColumn();
    }

    /** Evita duplicados dentro de la misma categoría (también hay UNIQUE en la tabla). */
    public static function exists(int $categoryId, string $name, int $exceptId = 0): bool
    {
        $stmt = Database::connection()->prepare('SELECT 1 FROM stack_tools WHERE category_id = ? AND name = ? AND id <> ?');
        $stmt->execute([$categoryId, $name, $exceptId]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Se agrega al final de su categoría.
     *
     * @param array{category_id: int, name: string, in_marquee: bool, is_published: bool} $data
     */
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare('INSERT INTO stack_tools (category_id, name, in_marquee, sort_order, is_published) VALUES (?, ?, ?, ?, ?)')
            ->execute([$data['category_id'], $data['name'], $data['in_marquee'] ? 1 : 0, self::nextOrder($data['category_id']), $data['is_published'] ? 1 : 0]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * @param array{category_id: int, name: string, in_marquee: bool, is_published: bool} $data
     */
    public static function update(int $id, array $data): void
    {
        $pdo = Database::connection();
        $pdo->prepare('UPDATE stack_tools SET name = ?, in_marquee = ?, is_published = ? WHERE id = ?')
            ->execute([$data['name'], $data['in_marquee'] ? 1 : 0, $data['is_published'] ? 1 : 0, $id]);

        // Si cambia de categoría, entra al final de la nueva.
        $pdo->prepare('UPDATE stack_tools SET category_id = ?, sort_order = ? WHERE id = ? AND category_id <> ?')
            ->execute([$data['category_id'], self::nextOrder($data['category_id']), $id, $data['category_id']]);
    }

    public static function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM stack_tools WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public static function nextOrder(int $categoryId): int
    {
        return Ordering::next('stack_tools', 'category_id', $categoryId);
    }
}
