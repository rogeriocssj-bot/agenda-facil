<?php
declare(strict_types=1);

namespace App\Core;

/** Controle de autenticação e de perfil (admin / cliente). */
final class Auth
{
    public static function entrar(array $usuario): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        Session::set('usuario', [
            'id'     => (int) $usuario['id'],
            'nome'   => $usuario['nome'],
            'email'  => $usuario['email'],
            'perfil' => $usuario['perfil'],
        ]);
    }

    public static function sair(): void
    {
        Session::destruir();
    }

    public static function usuario(): ?array
    {
        return Session::get('usuario');
    }

    public static function id(): ?int
    {
        return self::usuario()['id'] ?? null;
    }

    public static function logado(): bool
    {
        return self::usuario() !== null;
    }

    public static function admin(): bool
    {
        return (self::usuario()['perfil'] ?? null) === 'admin';
    }
}
