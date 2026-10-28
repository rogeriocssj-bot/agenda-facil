-- Versão para hospedagem: importe no phpMyAdmin do banco já criado pelo painel.
-- =====================================================================
-- AgendaFácil - Projeto físico do banco de dados
-- SGBD: MySQL 8.0 (compatível com MariaDB 10.4+)
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- =====================================================================

SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS agendamentos;
DROP TABLE IF EXISTS profissional_servico;
DROP TABLE IF EXISTS profissionais;
DROP TABLE IF EXISTS servicos;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- USUARIOS: clientes e administradores do sistema
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nome        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL,
    telefone    VARCHAR(20)   NULL,
    senha_hash  VARCHAR(255)  NOT NULL,
    perfil      ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
    criado_em   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_usuarios PRIMARY KEY (id),
    CONSTRAINT uq_usuarios_email UNIQUE (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- SERVICOS: catálogo de serviços oferecidos
-- ---------------------------------------------------------------------
CREATE TABLE servicos (
    id           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    nome         VARCHAR(100)   NOT NULL,
    descricao    VARCHAR(255)   NULL,
    duracao_min  SMALLINT UNSIGNED NOT NULL,
    preco        DECIMAL(10,2)  NOT NULL,
    ativo        TINYINT(1)     NOT NULL DEFAULT 1,
    CONSTRAINT pk_servicos PRIMARY KEY (id),
    CONSTRAINT uq_servicos_nome UNIQUE (nome),
    CONSTRAINT ck_servicos_duracao CHECK (duracao_min BETWEEN 10 AND 480),
    CONSTRAINT ck_servicos_preco CHECK (preco >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- PROFISSIONAIS: quem executa os serviços
-- ---------------------------------------------------------------------
CREATE TABLE profissionais (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nome           VARCHAR(100)  NOT NULL,
    especialidade  VARCHAR(100)  NULL,
    ativo          TINYINT(1)    NOT NULL DEFAULT 1,
    CONSTRAINT pk_profissionais PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- PROFISSIONAL_SERVICO: quais serviços cada profissional realiza (N:N)
-- ---------------------------------------------------------------------
CREATE TABLE profissional_servico (
    profissional_id  INT UNSIGNED NOT NULL,
    servico_id       INT UNSIGNED NOT NULL,
    CONSTRAINT pk_profissional_servico PRIMARY KEY (profissional_id, servico_id),
    CONSTRAINT fk_ps_profissional FOREIGN KEY (profissional_id)
        REFERENCES profissionais(id) ON DELETE CASCADE,
    CONSTRAINT fk_ps_servico FOREIGN KEY (servico_id)
        REFERENCES servicos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- AGENDAMENTOS: reservas de horário feitas pelos clientes
-- ---------------------------------------------------------------------
CREATE TABLE agendamentos (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    cliente_id       INT UNSIGNED  NOT NULL,
    servico_id       INT UNSIGNED  NOT NULL,
    profissional_id  INT UNSIGNED  NOT NULL,
    data             DATE          NOT NULL,
    hora_inicio      TIME          NOT NULL,
    hora_fim         TIME          NOT NULL,
    status           ENUM('agendado','confirmado','concluido','cancelado') NOT NULL DEFAULT 'agendado',
    observacao       VARCHAR(255)  NULL,
    criado_em        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_agendamentos PRIMARY KEY (id),
    CONSTRAINT fk_ag_cliente FOREIGN KEY (cliente_id)
        REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_ag_servico FOREIGN KEY (servico_id)
        REFERENCES servicos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_ag_profissional FOREIGN KEY (profissional_id)
        REFERENCES profissionais(id) ON DELETE RESTRICT,
    CONSTRAINT ck_ag_horario CHECK (hora_fim > hora_inicio)
) ENGINE=InnoDB;

-- Índice para acelerar a verificação de conflito de horário
CREATE INDEX idx_ag_prof_data ON agendamentos (profissional_id, data, status);
CREATE INDEX idx_ag_cliente ON agendamentos (cliente_id, data);

-- =====================================================================
-- AgendaFácil - Dados iniciais para teste
-- Usuário administrador: admin@agendafacil.com / senha: admin123
-- Usuário cliente:       cliente@agendafacil.com / senha: cliente123
-- =====================================================================
SET NAMES utf8mb4;

INSERT INTO usuarios (nome, email, telefone, senha_hash, perfil) VALUES
('Administrador', 'admin@agendafacil.com', '(11) 99999-0000',
 '$2y$10$haHxRty21DDRg5xd4UuNXePBqEN5gKnFrGqjvSLNqZemdZOpR4A7S', 'admin'),
('Maria Cliente', 'cliente@agendafacil.com', '(11) 98888-1111',
 '$2y$10$b3lLa0DMtm3XzEk8JYn2A.s8LbCSvnhtv3h0aeMIdxEmsu5l2Oc9m', 'cliente');

INSERT INTO servicos (nome, descricao, duracao_min, preco) VALUES
('Corte de cabelo', 'Corte masculino ou feminino com lavagem', 30, 45.00),
('Barba', 'Barba completa com toalha quente', 30, 30.00),
('Manicure', 'Cutilagem e esmaltação', 60, 35.00),
('Escova', 'Escova modeladora', 60, 50.00),
('Coloração', 'Coloração completa', 120, 150.00);

INSERT INTO profissionais (nome, especialidade) VALUES
('Carlos Souza', 'Barbeiro'),
('Ana Lima', 'Cabeleireira'),
('Juliana Rocha', 'Manicure');

INSERT INTO profissional_servico (profissional_id, servico_id) VALUES
(1, 1), (1, 2),
(2, 1), (2, 4), (2, 5),
(3, 3);
