<?php
declare(strict_types=1);

namespace App\Core;

/** Proteção contra CSRF (envio de formulários forjados). */
final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf', $token);
        }
        return $token;
    }

    public static function campo(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function validar(?string $token): bool
    {
        $esperado = Session::get('_csrf');
        return is_string($token) && is_string($esperado) && hash_equals($esperado, $token);
    }
}
