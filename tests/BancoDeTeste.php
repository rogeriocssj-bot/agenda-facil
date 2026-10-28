<?php
declare(strict_types=1);

namespace Tests;

use PDO;

/**
 * Cria um banco SQLite em memória com a mesma estrutura do MySQL
 * (tabelas, chaves primárias/estrangeiras e restrições) para os testes de integração.
 */
final class BancoDeTeste
{
    public static function criar(): PDO
    {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec(<<<SQL
            CREATE TABLE usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                telefone VARCHAR(20),
                senha_hash VARCHAR(255) NOT NULL,
                perfil VARCHAR(10) NOT NULL DEFAULT 'cliente' CHECK (perfil IN ('admin','cliente')),
                criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE servicos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome VARCHAR(100) NOT NULL UNIQUE,
                descricao VARCHAR(255),
                duracao_min INTEGER NOT NULL CHECK (duracao_min BETWEEN 10 AND 480),
                preco DECIMAL(10,2) NOT NULL CHECK (preco >= 0),
                ativo INTEGER NOT NULL DEFAULT 1
            );
            CREATE TABLE profissionais (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome VARCHAR(100) NOT NULL,
                especialidade VARCHAR(100),
                ativo INTEGER NOT NULL DEFAULT 1
            );
            CREATE TABLE profissional_servico (
                profissional_id INTEGER NOT NULL REFERENCES profissionais(id) ON DELETE CASCADE,
                servico_id INTEGER NOT NULL REFERENCES servicos(id) ON DELETE CASCADE,
                PRIMARY KEY (profissional_id, servico_id)
            );
            CREATE TABLE agendamentos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cliente_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE RESTRICT,
                servico_id INTEGER NOT NULL REFERENCES servicos(id) ON DELETE RESTRICT,
                profissional_id INTEGER NOT NULL REFERENCES profissionais(id) ON DELETE RESTRICT,
                data DATE NOT NULL,
                hora_inicio TIME NOT NULL,
                hora_fim TIME NOT NULL,
                status VARCHAR(12) NOT NULL DEFAULT 'agendado'
                    CHECK (status IN ('agendado','confirmado','concluido','cancelado')),
                observacao VARCHAR(255),
                criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CHECK (hora_fim > hora_inicio)
            );
        SQL);

        // Dados básicos
        $pdo->exec("INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES
            ('Admin', 'admin@teste.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin'),
            ('Maria', 'maria@teste.com', '" . password_hash('maria123', PASSWORD_DEFAULT) . "', 'cliente'),
            ('João',  'joao@teste.com',  '" . password_hash('joao123', PASSWORD_DEFAULT) . "', 'cliente')");
        $pdo->exec("INSERT INTO servicos (nome, duracao_min, preco, ativo) VALUES
            ('Corte', 30, 45.00, 1),
            ('Coloração', 120, 150.00, 1),
            ('Serviço desativado', 30, 10.00, 0)");
        $pdo->exec("INSERT INTO profissionais (nome, ativo) VALUES ('Carlos', 1), ('Ana', 1), ('Inativo', 0)");
        $pdo->exec("INSERT INTO profissional_servico VALUES (1,1), (2,1), (2,2), (3,1)");

        return $pdo;
    }
}
