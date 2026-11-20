<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Administradores del panel (tabla admins).
 */
final class Admin
{
    public const MIN_PASSWORD = 12;

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, name, email, last_login_at FROM admins WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Igual que find() pero con el hash, para validar la sesión. No pasar a las vistas.
     *
     * @return array<string, mixed>|null
     */
    public static function findWithHash(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, name, email, last_login_at, password_hash FROM admins WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, name, email, password_hash FROM admins WHERE email = ?');
        $stmt->execute([mb_strtolower(trim($email))]);

        return $stmt->fetch() ?: null;
    }

    public static function create(string $name, string $email, string $password): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([trim($name), mb_strtolower(trim($email)), password_hash($password, PASSWORD_DEFAULT)]);

        return (int) $pdo->lastInsertId();
    }

    public static function updatePassword(int $id, string $password): void
    {
        Database::connection()
            ->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public static function touchLogin(int $id): void
    {
        Database::connection()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')->execute([$id]);
    }
}
