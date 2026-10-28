<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
        }
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }

    public static function get(string $chave, mixed $padrao = null): mixed
    {
        return $_SESSION[$chave] ?? $padrao;
    }

    public static function set(string $chave, mixed $valor): void
    {
        $_SESSION[$chave] = $valor;
    }

    public static function remover(string $chave): void
    {
        unset($_SESSION[$chave]);
    }

    /** Mensagem que aparece uma única vez (após redirecionamento). */
    public static function flash(string $tipo, string $mensagem): void
    {
        $_SESSION['_flash'][$tipo] = $mensagem;
    }

    /** @return array<string,string> */
    public static function consumirFlash(): array
    {
        $msgs = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $msgs;
    }

    public static function destruir(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            session_destroy();
        }
    }
}
