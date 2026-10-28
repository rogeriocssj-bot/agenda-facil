<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Conexão única (Singleton) com o banco de dados via PDO.
 * Permite injetar outra conexão nos testes (ex.: SQLite em memória).
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function conexao(): PDO
    {
        if (self::$pdo === null) {
            $cfg = Config::get('db');
            $sqlite = str_starts_with($cfg['dsn'], 'sqlite:');
            $arquivo = $sqlite ? substr($cfg['dsn'], 7) : '';
            $novo = $sqlite && (!is_file($arquivo) || filesize($arquivo) === 0);

            self::$pdo = new PDO($cfg['dsn'], $sqlite ? null : $cfg['usuario'], $sqlite ? null : $cfg['senha'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            if ($sqlite) {
                self::$pdo->exec('PRAGMA foreign_keys = ON');
                if ($novo) {
                    self::criarBancoLocal(self::$pdo);
                }
            }
        }
        return self::$pdo;
    }

    /**
     * Modo local (SQLite): cria as tabelas e os dados iniciais automaticamente
     * na primeira vez que o sistema é aberto. Não precisa de MySQL nem de XAMPP.
     */
    private static function criarBancoLocal(PDO $pdo): void
    {
        $pasta = dirname(__DIR__, 2) . '/database';
        $seed = (string) file_get_contents($pasta . '/seed.sql');
        $seed = (string) preg_replace('/^\s*(USE|SET NAMES)[^;]*;\s*$/mi', '', $seed);
        $pdo->exec((string) file_get_contents($pasta . '/sqlite-schema.sql'));
        $pdo->exec($seed);
    }

    /** Usado pelos testes automatizados. */
    public static function definirConexao(?PDO $pdo): void
    {
        self::$pdo = $pdo;
    }
}
