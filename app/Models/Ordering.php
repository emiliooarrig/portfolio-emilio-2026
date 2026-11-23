<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use Throwable;

/**
 * Orden manual (columna sort_order) de las listas del panel.
 *
 * El panel ya no pide un número: se arrastra en la lista (o se usan ↑/↓ sin JS)
 * y aquí se reescribe sort_order como 10, 20, 30… en una transacción.
 *
 * $table y $scope vienen siempre de constantes del código, nunca del usuario.
 */
final class Ordering
{
    private const TABLES = ['experiences', 'certifications', 'stack_categories', 'stack_tools', 'projects'];
    private const SCOPES = ['category_id'];

    /**
     * Ids de la lista en su orden actual.
     *
     * @return list<int>
     */
    public static function ids(string $table, ?string $scope = null, ?int $scopeId = null): array
    {
        [$where, $params] = self::where($table, $scope, $scopeId);
        $stmt = Database::connection()->prepare("SELECT id FROM {$table}{$where} ORDER BY sort_order, id");
        $stmt->execute($params);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    /**
     * Guarda el orden recibido. Debe traer exactamente los mismos ids que hay
     * en la lista; si no (alguien agregó o borró algo en otra pestaña) no toca nada.
     *
     * @param list<int> $ids
     */
    public static function save(string $table, array $ids, ?string $scope = null, ?int $scopeId = null): bool
    {
        $current = self::ids($table, $scope, $scopeId);

        if ($ids === [] || count($ids) !== count(array_unique($ids)) || self::sorted($ids) !== self::sorted($current)) {
            return false;
        }

        if ($ids === $current) {
            return true;
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare("UPDATE {$table} SET sort_order = ? WHERE id = ?");

        try {
            $pdo->beginTransaction();
            foreach ($ids as $i => $id) {
                $stmt->execute([($i + 1) * 10, $id]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Sube (-1) o baja (+1) un elemento una posición. Para el panel sin JS.
     */
    public static function move(string $table, int $id, int $step, ?string $scope = null, ?int $scopeId = null): bool
    {
        $ids = self::ids($table, $scope, $scopeId);
        $from = array_search($id, $ids, true);

        if ($from === false) {
            return false;
        }

        $to = $from + ($step < 0 ? -1 : 1);

        if ($to < 0 || $to >= count($ids)) {
            return true; // ya está en el borde
        }

        [$ids[$from], $ids[$to]] = [$ids[$to], $ids[$from]];

        return self::save($table, $ids, $scope, $scopeId);
    }

    /** sort_order para un elemento nuevo: al final de la lista. */
    public static function next(string $table, ?string $scope = null, ?int $scopeId = null): int
    {
        [$where, $params] = self::where($table, $scope, $scopeId);
        $stmt = Database::connection()->prepare("SELECT COALESCE(MAX(sort_order), 0) + 10 FROM {$table}{$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array{0: string, 1: list<int>}
     */
    private static function where(string $table, ?string $scope, ?int $scopeId): array
    {
        if (!in_array($table, self::TABLES, true)) {
            throw new \InvalidArgumentException("Tabla no ordenable: {$table}");
        }

        if ($scope === null) {
            return ['', []];
        }

        if (!in_array($scope, self::SCOPES, true) || $scopeId === null) {
            throw new \InvalidArgumentException("Ámbito no válido: {$scope}");
        }

        return [" WHERE {$scope} = ?", [$scopeId]];
    }

    /**
     * @param list<int> $ids
     * @return list<int>
     */
    private static function sorted(array $ids): array
    {
        sort($ids);

        return $ids;
    }
}
