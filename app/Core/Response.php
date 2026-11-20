<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function status(int $code): void
    {
        if (!headers_sent()) {
            http_response_code($code);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $status = 200): void
    {
        self::status($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function redirect(string $to, int $status = 303): void
    {
        header('Location: ' . $to, true, $status);
    }

    public static function html(string $html, int $status = 200): void
    {
        self::status($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
}
