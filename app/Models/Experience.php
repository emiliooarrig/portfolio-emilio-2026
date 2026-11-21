<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use App\Core\Database;
use App\Core\Logger;
use Throwable;

/**
 * Experiencia laboral (tabla experiences), editable desde /admin/experiencia.
 * data/experience.json solo sirve para la carga inicial (composer experience:import)
 * y como respaldo cuando DB_ENABLED=false.
 */
final class Experience
{
    public const MONTHS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    private const COLUMNS = 'id, company, role, summary, start_year, start_month, end_year, end_month, is_current, sort_order, is_published, updated_at';

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
            return Database::connection()
                ->query('SELECT ' . self::COLUMNS . ' FROM experiences WHERE is_published = 1 ORDER BY sort_order, id')
                ->fetchAll();
        } catch (Throwable $e) {
            Logger::error('No se pudo leer la experiencia', ['exception' => $e]);
            return [];
        }
    }

    /**
     * Todos, para el panel.
     *
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return Database::connection()
            ->query('SELECT ' . self::COLUMNS . ' FROM experiences ORDER BY sort_order, id')
            ->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT ' . self::COLUMNS . ' FROM experiences WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public static function count(bool $publishedOnly = false): int
    {
        return (int) Database::connection()
            ->query('SELECT COUNT(*) FROM experiences' . ($publishedOnly ? ' WHERE is_published = 1' : ''))
            ->fetchColumn();
    }

    /**
     * Se agrega al final de la lista, salvo que $data traiga sort_order (importación).
     *
     * @param array<string, mixed> $data Ver ExperienceRequest::validated()
     */
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO experiences (company, role, summary, start_year, start_month, end_year, end_month, is_current, sort_order, is_published)
             VALUES (:company, :role, :summary, :start_year, :start_month, :end_year, :end_month, :is_current, :sort_order, :is_published)'
        )->execute(self::params($data) + [':sort_order' => $data['sort_order'] ?? self::nextOrder()]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data Ver ExperienceRequest::validated()
     */
    public static function update(int $id, array $data): void
    {
        Database::connection()->prepare(
            'UPDATE experiences
             SET company = :company, role = :role, summary = :summary,
                 start_year = :start_year, start_month = :start_month, end_year = :end_year, end_month = :end_month,
                 is_current = :is_current, is_published = :is_published
             WHERE id = :id'
        )->execute(self::params($data) + [':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM experiences WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public static function nextOrder(): int
    {
        return Ordering::next('experiences');
    }

    /**
     * Periodo legible: "mar 2023 — hoy", "2021 — 2023", "Actual", "Hasta 2023" o "".
     *
     * @param array<string, mixed> $row
     */
    public static function period(array $row): string
    {
        $date = static function (mixed $year, mixed $month): string {
            if ($year === null || $year === '') {
                return '';
            }
            $m = (int) $month;

            return ($m >= 1 && $m <= 12 ? self::MONTHS[$m - 1] . ' ' : '') . (int) $year;
        };

        $start = $date($row['start_year'] ?? null, $row['start_month'] ?? null);
        $end = (int) ($row['is_current'] ?? 0) === 1 ? 'hoy' : $date($row['end_year'] ?? null, $row['end_month'] ?? null);

        return match (true) {
            $start !== '' && $end !== '' => $start . ' — ' . $end,
            $start !== ''                => 'Desde ' . $start,
            $end === 'hoy'               => 'Actual',
            $end !== ''                  => 'Hasta ' . $end,
            default                      => '',
        };
    }

    /**
     * Lee data/experience.json con la misma forma que la tabla.
     * Los textos de ejemplo ("…_placeholder") no se importan.
     *
     * @return list<array<string, mixed>>
     */
    public static function fromJson(): array
    {
        $path = App::basePath('data/experience.json');
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        $items = [];

        foreach (is_array($data) ? $data : [] as $i => $row) {
            if (!is_array($row) || trim((string) ($row['company'] ?? '')) === '') {
                continue;
            }

            $start = isset($row['start']) && ctype_digit((string) $row['start']) ? (int) $row['start'] : null;
            $end = (string) ($row['end'] ?? '');

            $items[] = [
                'id'           => $i + 1,
                'company'      => (string) $row['company'],
                'role'         => (string) ($row['role'] ?? ''),
                'summary'      => (string) ($row['summary'] ?? ''),
                'start_year'   => $start,
                'start_month'  => null,
                'end_year'     => ctype_digit($end) ? (int) $end : null,
                'end_month'    => null,
                'is_current'   => mb_strtolower($end) === 'hoy' ? 1 : 0,
                'sort_order'   => ($i + 1) * 10,
                'is_published' => 1,
            ];
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function params(array $data): array
    {
        $current = (bool) $data['is_current'];

        return [
            ':company'      => $data['company'],
            ':role'         => $data['role'],
            ':summary'      => $data['summary'],
            ':start_year'   => $data['start_year'],
            ':start_month'  => $data['start_year'] !== null ? $data['start_month'] : null,
            ':end_year'     => $current ? null : $data['end_year'],
            ':end_month'    => $current || $data['end_year'] === null ? null : $data['end_month'],
            ':is_current'   => $current ? 1 : 0,
            ':is_published' => $data['is_published'] ? 1 : 0,
        ];
    }
}
