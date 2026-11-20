<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Mensajes del formulario de contacto (tabla contact_messages).
 */
final class ContactMessage
{
    /** Temas permitidos: valor del formulario => etiqueta. */
    public const TOPICS = [
        'desarrollo' => 'Desarrollo de software',
        'soporte'    => 'Soporte técnico',
        'datos'      => 'Datos',
        'vacante'    => 'Vacante',
    ];

    /** Estados del panel: valor => etiqueta. */
    public const STATUSES = [
        'nuevo'      => 'Nuevo',
        'leido'      => 'Leído',
        'respondido' => 'Respondido',
        'archivado'  => 'Archivado',
    ];

    public const PER_PAGE = 20;

    /**
     * @param array{name: string, email: string, topic: string, message: string} $data
     */
    public static function create(array $data, string $ipHash, string $userAgent): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO contact_messages (name, email, topic, message, ip_hash, user_agent, created_at)
             VALUES (:name, :email, :topic, :message, :ip_hash, :user_agent, NOW())'
        );

        $stmt->execute([
            ':name'       => $data['name'],
            ':email'      => $data['email'],
            ':topic'      => $data['topic'],
            ':message'    => $data['message'],
            ':ip_hash'    => $ipHash,
            ':user_agent' => $userAgent,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Lista paginada con filtros opcionales.
     *
     * @param array{status?: string, topic?: string, q?: string} $filters
     * @return array{items: list<array<string, mixed>>, total: int, page: int, pages: int}
     */
    public static function paginate(array $filters, int $page): array
    {
        [$where, $params] = self::where($filters);
        $pdo = Database::connection();

        $count = $pdo->prepare('SELECT COUNT(*) FROM contact_messages' . $where);
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $pages);

        $stmt = $pdo->prepare(
            'SELECT id, name, email, topic, LEFT(message, 160) AS excerpt, status, created_at
             FROM contact_messages' . $where . '
             ORDER BY created_at DESC, id DESC
             LIMIT ' . self::PER_PAGE . ' OFFSET ' . (($page - 1) * self::PER_PAGE)
        );
        $stmt->execute($params);

        return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, name, email, topic, message, status, created_at FROM contact_messages WHERE id = ?'
        );
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public static function countNew(): int
    {
        return (int) Database::connection()->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'nuevo'")->fetchColumn();
    }

    public static function updateStatus(int $id, string $status): bool
    {
        if (!isset(self::STATUSES[$status])) {
            return false;
        }

        $stmt = Database::connection()->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);

        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM contact_messages WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Cifras del dashboard.
     *
     * @return array{total: int, new: int, week: int, by_status: array<string, int>, by_topic: array<string, int>, daily: array<string, int>}
     */
    public static function stats(int $days = 14): array
    {
        $pdo = Database::connection();

        $byStatus = array_fill_keys(array_keys(self::STATUSES), 0);
        foreach ($pdo->query('SELECT status, COUNT(*) AS n FROM contact_messages GROUP BY status') as $row) {
            $byStatus[(string) $row['status']] = (int) $row['n'];
        }

        $byTopic = array_fill_keys(array_keys(self::TOPICS), 0);
        foreach ($pdo->query('SELECT topic, COUNT(*) AS n FROM contact_messages GROUP BY topic') as $row) {
            $byTopic[(string) $row['topic']] = (int) $row['n'];
        }
        arsort($byTopic);

        // Un valor por día, incluidos los días sin mensajes.
        $daily = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $daily[date('Y-m-d', strtotime("-{$i} days"))] = 0;
        }

        $stmt = $pdo->prepare(
            'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM contact_messages
             WHERE created_at >= ? GROUP BY DATE(created_at)'
        );
        $stmt->execute([array_key_first($daily) . ' 00:00:00']);
        foreach ($stmt as $row) {
            if (isset($daily[$row['d']])) {
                $daily[$row['d']] = (int) $row['n'];
            }
        }

        $week = $pdo->prepare('SELECT COUNT(*) FROM contact_messages WHERE created_at >= ?');
        $week->execute([date('Y-m-d H:i:s', strtotime('-7 days'))]);

        return [
            'total'     => array_sum($byStatus),
            'new'       => $byStatus['nuevo'],
            'week'      => (int) $week->fetchColumn(),
            'by_status' => $byStatus,
            'by_topic'  => $byTopic,
            'daily'     => $daily,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function latest(int $limit = 5): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, name, email, topic, LEFT(message, 120) AS excerpt, status, created_at
             FROM contact_messages ORDER BY created_at DESC, id DESC LIMIT ' . max(1, $limit)
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param array{status?: string, topic?: string, q?: string} $filters
     * @return array{0: string, 1: list<string>}
     */
    private static function where(array $filters): array
    {
        $clauses = [];
        $params = [];

        if (isset(self::STATUSES[$filters['status'] ?? ''])) {
            $clauses[] = 'status = ?';
            $params[] = $filters['status'];
        }

        if (isset(self::TOPICS[$filters['topic'] ?? ''])) {
            $clauses[] = 'topic = ?';
            $params[] = $filters['topic'];
        }

        $q = trim($filters['q'] ?? '');
        if ($q !== '') {
            $like = '%' . addcslashes($q, '\\%_') . '%';
            $clauses[] = '(name LIKE ? OR email LIKE ? OR message LIKE ?)';
            array_push($params, $like, $like, $like);
        }

        return [$clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses), $params];
    }
}
