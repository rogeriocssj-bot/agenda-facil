-- AgendaFácil - estrutura do banco para o MODO LOCAL (SQLite).
-- Mesmas tabelas, chaves e restrições do schema.sql (MySQL).
-- Este arquivo é usado automaticamente; não é preciso importar nada.
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
CREATE INDEX idx_ag_prof_data ON agendamentos (profissional_id, data, status);
CREATE INDEX idx_ag_cliente ON agendamentos (cliente_id, data);
