<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Logger;
use Throwable;

/**
 * Categorías del stack (tabla stack_categories). Cada una tiene N herramientas (StackTool).
 */
final class StackCategory
{
    /** Íconos disponibles: valor => etiqueta. */
    public const ICONS = [
        'code'      => 'Código',
        'server'    => 'Servidor',
        'database'  => 'Base de datos',
        'shield'    => 'Escudo',
        'grid'      => 'Módulos',
        'briefcase' => 'Negocio',
        'cap'       => 'Formación',
        'award'     => 'Medalla',
    ];

    private const COLUMNS = 'id, name, icon, sort_order, is_published, updated_at';

    /**
     * Categorías publicadas con sus herramientas publicadas, en orden, para el sitio.
     * Una sola consulta (LEFT JOIN) agrupada aquí.
     *
     * @return list<array{id: int, name: string, icon: string, tools: list<string>}>
     */
    public static function published(): array
    {
        if (!Database::enabled()) {
            return [];
        }

        try {
            $rows = Database::connection()->query(
                'SELECT c.id, c.name, c.icon, t.name AS tool
                 FROM stack_categories c
                 LEFT JOIN stack_tools t ON t.category_id = c.id AND t.is_published = 1
                 WHERE c.is_published = 1
                 ORDER BY c.sort_order, c.id, t.sort_order, t.id'
            )->fetchAll();
        } catch (Throwable $e) {
            Logger::error('No se pudo leer el stack', ['exception' => $e]);
            return [];
        }

        $groups = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $groups[$id] ??= ['id' => $id, 'name' => (string) $row['name'], 'icon' => (string) $row['icon'], 'tools' => []];
            if ($row['tool'] !== null) {
                $groups[$id]['tools'][] = (string) $row['tool'];
            }
        }

        return array_values($groups);
    }

    /**
     * Todas las categorías con todas sus herramientas, para el panel.
     *
     * @return list<array<string, mixed>>
     */
    public static function allWithTools(): array
    {
        $pdo = Database::connection();
        $categories = $pdo->query('SELECT ' . self::COLUMNS . ' FROM stack_categories ORDER BY sort_order, id')->fetchAll();
        $byCategory = [];

        foreach (StackTool::all() as $tool) {
            $byCategory[(int) $tool['category_id']][] = $tool;
        }

        foreach ($categories as $i => $category) {
            $categories[$i]['tools'] = $byCategory[(int) $category['id']] ?? [];
        }

        return $categories;
    }

    /**
     * Para los <select>: id => nombre.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (Database::connection()->query('SELECT id, name FROM stack_categories ORDER BY sort_order, id') as $row) {
            $options[(int) $row['id']] = (string) $row['name'];
        }

        return $options;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT ' . self::COLUMNS . ' FROM stack_categories WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM stack_categories')->fetchColumn();
    }

    public static function nameExists(string $name, int $exceptId = 0): bool
    {
        $stmt = Database::connection()->prepare('SELECT 1 FROM stack_categories WHERE name = ? AND id <> ?');
        $stmt->execute([$name, $exceptId]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * @param array{name: string, icon: string, sort_order: int, is_published: bool} $data
     */
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare('INSERT INTO stack_categories (name, icon, sort_order, is_published) VALUES (?, ?, ?, ?)')
            ->execute([$data['name'], $data['icon'], $data['sort_order'], $data['is_published'] ? 1 : 0]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * @param array{name: string, icon: string, sort_order: int, is_published: bool} $data
     */
    public static function update(int $id, array $data): void
    {
        Database::connection()
            ->prepare('UPDATE stack_categories SET name = ?, icon = ?, sort_order = ?, is_published = ? WHERE id = ?')
            ->execute([$data['name'], $data['icon'], $data['sort_order'], $data['is_published'] ? 1 : 0, $id]);
    }

    /** Borra la categoría y, por la llave foránea, todas sus herramientas. */
    public static function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM stack_categories WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public static function nextOrder(): int
    {
        return (int) Database::connection()->query('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM stack_categories')->fetchColumn();
    }
}
