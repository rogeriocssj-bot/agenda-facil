<?php
declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static ?array $dados = null;

    public static function carregar(?array $dados = null): void
    {
        self::$dados = $dados ?? require dirname(__DIR__, 2) . '/config/config.php';
    }

    public static function get(string $chave, mixed $padrao = null): mixed
    {
        if (self::$dados === null) {
            self::carregar();
        }
        $valor = self::$dados;
        foreach (explode('.', $chave) as $parte) {
            if (!is_array($valor) || !array_key_exists($parte, $valor)) {
                return $padrao;
            }
            $valor = $valor[$parte];
        }
        return $valor;
    }
}
